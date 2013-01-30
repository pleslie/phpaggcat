<?php

require_once('config.php');

class IntuitAggCatHelpers {

	public static function GetOAuthTokens(&$oauth_token, &$oauth_token_secret)
	{
		IntuitAggCatHelpers::PrepSAMLAssertion($saml_xml_request);
		IntuitAggCatHelpers::PrepOAuthViaSAML($saml_xml_request, $oauth_token, $oauth_token_secret);
	}

	//
	// PrepSAMLAssertion
	//
	// Follows three steps:
	//
	//    1) Gather inputs needed during assembly of SAML Assertion (e.g., keys, certs, timestamps, IDs_
	//    2) Assemble DOM containing correct SAML assertion
	//    3) Prepare Base64-Encoded SAML Assertion request body based on DOM
	//
	// Args:
	//
	//    $saml_xml_request [out]: Base64-encoded SAML Assertion body
	//
	public static function PrepSAMLAssertion(&$saml_xml_request)
	{
		//
		// Gather inputs needed during assembly of SAML Assertion (e.g., keys, certs, timestamps, IDs_
		//
		$DateTimeNow = new DateTime(null, new DateTimeZone("UTC")); 
		$DateTimeNowString = $DateTimeNow->format("Y-m-d\TH:i:s.B\Z");

		$DateTime15Min = new DateTime(null, new DateTimeZone("UTC")); 
		$DateTime15Min->modify( '+900 sec' );
		$DateTime15MinString = $DateTime15Min->format("Y-m-d\TH:i:s.B\Z");

		$SAMLParams = array();
		$SAMLParams['IssueInstant'] 	= $DateTimeNowString;
		$SAMLParams['Issuer'] 			= SAML_IDENTITY_PROVIDER_ID;
		$SAMLParams['ID']				= SimpleSAML_Utilities::generateID();
		$SAMLParams['NameID']			= SAML_NAME_ID;
		$SAMLParams['NotBefore'] 		= $DateTimeNowString;
		$SAMLParams['NotOnOrAfter'] 	= $DateTime15MinString;
		$SAMLParams['AuthnInstant'] 	= $DateTimeNowString;		
		$SAMLParams['Audience']			= SAML_IDENTITY_PROVIDER_ID;
		$SAMLParams['x509']				= file_get_contents(SAML_X509_CERT_PATH);
		$SAMLParams['private_key']		= file_get_contents(SAML_X509_PRIVATE_KEY_PATH);

		//
		// Assemble DOM containing correct SAML assertion
		//
		$xml = new DOMDocument('1.0','utf-8');

		// Assertion
		$assertion = $xml->createElementNS('urn:oasis:names:tc:SAML:2.0:assertion','saml2:Assertion');
		$assertion->setAttribute('ID', $SAMLParams['ID']);
		$assertion->setAttribute('Version',	'2.0');
		$assertion->setAttribute('IssueInstant',	$SAMLParams['IssueInstant']);
		$xml->appendChild($assertion);

		// Issuer
		$issuer = $xml->createElement('saml2:Issuer', $SAMLParams['Issuer']);
		$assertion->appendChild($issuer);

		// Subject + NameID + SubjectConfirmation
		$subject = $xml->createElement('saml2:Subject');
		$assertion->appendChild($subject);

		// NameID
		$nameid = $xml->createElement('saml2:NameID',$SAMLParams['NameID']);
		$nameid->setAttribute('Format','urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified');
		$subject->appendChild($nameid);

		// SubjectConfirmation
		$confirmation = $xml->createElement('saml2:SubjectConfirmation');
		$confirmation->setAttribute('Method','urn:oasis:names:tc:SAML:2.0:cm:bearer');
		$subject->appendChild($confirmation);

		// Conditions + AudienceRestriction + Audience
		$condition = $xml->createElement('saml2:Conditions');
		$condition->setAttribute('NotBefore', $SAMLParams['NotBefore']);
		$condition->setAttribute('NotOnOrAfter', $SAMLParams['NotOnOrAfter']);
		$assertion->appendChild($condition);

		// AudienceRestriction
		$audiencer = $xml->createElement('saml2:AudienceRestriction');
		$condition->appendChild($audiencer);

		// Audience
		$audience = $xml->createElement('saml2:Audience', $SAMLParams['Audience']);
		$audiencer->appendChild($audience);

		// AuthnStatement + AuthnContext + AuthnContextClassRef
		$authnstat = $xml->createElement('saml2:AuthnStatement');
		$authnstat->setAttribute('AuthnInstant', $SAMLParams['AuthnInstant']);
		$authnstat->setAttribute('SessionIndex', $SAMLParams['ID']);
		$assertion->appendChild($authnstat);

		// AuthnContext
		$authncontext = $xml->createElement('saml2:AuthnContext');
		$authnstat->appendChild($authncontext);

		// AuthnContextClassRef
		$authncontext_ref = $xml->createElement('saml2:AuthnContextClassRef','urn:oasis:names:tc:SAML:2.0:ac:classes:unspecified');
		$authncontext->appendChild($authncontext_ref);	


		//Private KEY	
		$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
		$objKey->loadKey($SAMLParams['private_key']);

		//Sign the Assertion
		$objXMLSecDSig = new XMLSecurityDSig();
		$objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
		$objXMLSecDSig->addReferenceList(array($assertion),
		                                 XMLSecurityDSig::SHA1,
		                                 array('http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N),
		                                 array('URI'=>'ID','overwrite'=>false, 'id_name'=>'ID'));
		$objXMLSecDSig->sign($objKey);
		$objXMLSecDSig->add509Cert($SAMLParams['x509']);
		$objXMLSecDSig->insertSignature($assertion, $subject);

		$saml = $xml->saveXML();

		//
		// Change Reference URI locally (considered changing 'xmlseclibs.php', but 
		// that seemed inappropriate)
		//
		preg_match("/<ds:Reference URI=\"#(.+?)\">/is", $saml, $URI);
		$saml = str_replace("Id=\"".$URI[1]."\"", "", $saml);
		$saml = str_replace($URI[1], $SAMLParams["ID"], $saml);

		//
		// Prepare Base64-Encoded SAML Assertion request body based on DOM
		//
		$saml 				= str_replace('<?xml version="1.0" encoding="utf-8"?>','',$saml);
		$saml_xml_request 	= base64_encode(stripslashes($saml));
	}

	//
	// PrepOAuthViaSAML
	//
	// Using a SAML Assertion body, gather an OAuth token and secret, for use in upcoming RESTful API calls
	//
	// Args:
	//
	//    $oauth_token [out], $oauth_token_secret [out]: token and secret to use in upcoming calls
	// 
	public static function PrepOAuthViaSAML($saml_xml_request, &$oauth_token, &$oauth_token_secret)
	{
		$PostFields = http_build_query(array("saml_assertion" => $saml_xml_request));

		$httpHeaders = array(
		  	'Content-Type:application/x-www-form-urlencoded',
			'Content-Language:en-US',
			'Content-Length:'.strlen($PostFields),
			'Authorization:OAuth oauth_consumer_key="'.OAUTH_CONSUMER_KEY.'"',
			'Host:'. FINANCIAL_FEED_HOST
		);

		// setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, OAUTH_SAML_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $PostFields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);

		$ResponseText = urldecode(curl_exec($ch));

		//
		//	Response will look something like:
		//  ----------------------------------
		//
		//	HTTP/1.1 100 Continue
		//	
		//	HTTP/1.1 200 OK
		//	Date: Wed, 26 Dec 2012 23:26:06 GMT
		//	Server: Apache
		//	Content-Length: 120
		//	Connection: close
		//	Content-Type: text/plain
		//	
		//	oauth_token_secret=QHQaBlCC2LbWQfYfXsoUvij3ap1gkt93sMeRatBz&oauth_token=qyprdKyFdZSZa9lP3WdLHrpHlyNJXI3YzGNDzEE4D4OXrF0P
		//
		$ResponseText = substr($ResponseText, strpos($ResponseText, "oauth_token_secret"));
		$ResponseKVStrings = explode("&", $ResponseText);
		foreach($ResponseKVStrings as $OneKVString)
		{
			$OneKVPair = explode("=",$OneKVString);
			$ResponseKVPairs[$OneKVPair[0]] = $OneKVPair[1];
		}

		$oauth_token = $ResponseKVPairs['oauth_token'];
		$oauth_token_secret = $ResponseKVPairs['oauth_token_secret'];
	}	
}

?>
