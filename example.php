<?php
require_once('config.php');
require_once("class.aggcatauth.php");

IntuitAggCatHelpers::GetOAuthTokens(&$oauth_token, &$oauth_token_secret);


$signatures = array( 'consumer_key'     => OAUTH_CONSUMER_KEY,
                     'shared_secret'    => OAUTH_SHARED_SECRET,
                     'oauth_token'      => $oauth_token,
                     'oauth_secret'     => $oauth_token_secret);

// 
// This HTTP GET to Intuit Agg & Cat RESTful APIs is based on the "institutions" endpoint
// described here:
// 
//   https://ipp.developer.intuit.com/index.php?title=0010_Intuit_Partner_Platform/0020_Aggregation_%26_Categorization_Apps/AggCat_API/0020_API_Documentation/0010Institutions
//

$oauthObject = new OAuthSimple();
$oauthObject->reset();
$result = $oauthObject->sign(array(
    'path'      => FINANCIAL_FEED_URL .'v1/institutions',
    'parameters'=> array('oauth_signature_method' => 'HMAC-SHA1', 
    'Host'=> FINANCIAL_FEED_HOST),
    'signatures'=> $signatures));

$options = array();
$options[CURLOPT_VERBOSE] = 1;
$options[CURLOPT_RETURNTRANSFER] = 1;

$ch = curl_init();
curl_setopt_array($ch, $options);
curl_setopt($ch, CURLOPT_URL, $result['signed_url']);
$r = curl_exec($ch);
curl_close($ch);
parse_str($r, $returned_items);		   

//
// Load Response Body into a SimpleXML object
//		
$ResponseXML = substr($r, strpos($r, "<" . "?xml"));
$xmlObj = simplexml_load_string($ResponseXML);

//
// Simple output to visually confirm that everything went well...
//
$IterCount = 0;
foreach($xmlObj as $OneInstitution)
{
	$IterCount++;
	echo "Institution Name ($IterCount):  " . (string)$OneInstitution->institutionName . "\n";
}

?>
