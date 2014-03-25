<?php

require_once 'config.php';

$response['credentials']['credential'] = array();
$response['credentials']['credential'][] = array
(
	'name'	=> 'Banking Userid',
	'value'	=> 'tfa_text'
);

$response['credentials']['credential'][] = array
(
	'name'	=> 'Banking Password',
	'value'	=> '123'
);

$postData = json_encode( $response );

echo PHP_EOL . '//------------------------------------------------------------------------------' . PHP_EOL;
echo 'Post Data:' . PHP_EOL;
echo $postData;


IntuitAggCatHelpers::GetOAuthTokens( $oauth_token, $oauth_token_secret);

$signatures = array( 'consumer_key'     => OAUTH_CONSUMER_KEY,
                     'shared_secret'    => OAUTH_SHARED_SECRET,
                     'oauth_token'      => $oauth_token,
                     'oauth_secret'     => $oauth_token_secret);

$url = FINANCIAL_FEED_URL .'v1/institutions/100000/logins';
$action = 'POST';

//------------------------------------------------------------------------------

$oauthObject = new OAuthSimple();
$oauthObject->setAction( $action );
$oauthObject->reset();

$result = $oauthObject->sign(
	array
	(
		'path'				=> $url,
		'parameters'	=>
		array
		(
			'oauth_signature_method' => 'HMAC-SHA1', 
			'Host' => FINANCIAL_FEED_HOST
		),
		'signatures'=> $signatures
	)
);

$options = array();

$options[CURLOPT_POST] = true;
$options[CURLOPT_POSTFIELDS] = $postData;

$options[CURLOPT_URL] = $result['signed_url'];
$options[CURLOPT_HEADER] = 1;
$options[CURLOPT_VERBOSE] = 1;
$options[CURLOPT_RETURNTRANSFER] = 1;
$options[CURLOPT_SSL_VERIFYPEER] = true;
$options[CURLOPT_HTTPHEADER] = array
(
	'Accept: application/json',
	'Content-Type: application/json',
	'Content-Length: ' . strlen( $postData ),
	'Host:'. FINANCIAL_FEED_HOST,
	//'Authorization:' . $result['header']
); 

include 'example-exec.php';

$headers = splitHeadersBody( $responseText );

var_dump( $headers );

$postData = '{"challengeResponses": {"response": ["any value"]}}';

echo PHP_EOL . '//------------------------------------------------------------------------------' . PHP_EOL;
echo 'MFA Post Data:' . PHP_EOL;
echo $postData;

IntuitAggCatHelpers::GetOAuthTokens( $oauth_token, $oauth_token_secret);

$signatures = array( 'consumer_key'     => OAUTH_CONSUMER_KEY,
                     'shared_secret'    => OAUTH_SHARED_SECRET,
                     'oauth_token'      => $oauth_token,
                     'oauth_secret'     => $oauth_token_secret);

$url = FINANCIAL_FEED_URL .'v1/institutions/100000/logins';
$action = 'POST';

$oauthObject = new OAuthSimple();
$oauthObject->setAction( $action );
$oauthObject->reset();

$result = $oauthObject->sign(
	array
	(
		'path'				=> $url,
		'parameters'	=>
		array
		(
			'oauth_signature_method' => 'HMAC-SHA1', 
			'Host' => FINANCIAL_FEED_HOST
		),
		'signatures'=> $signatures
	)
);

$options = array();

$options[CURLOPT_POST] = true;
$options[CURLOPT_POSTFIELDS] = $postData;

$options[CURLOPT_URL] = $result['signed_url'];
$options[CURLOPT_HEADER] = 1;
$options[CURLOPT_VERBOSE] = 1;
$options[CURLOPT_RETURNTRANSFER] = 1;
$options[CURLOPT_SSL_VERIFYPEER] = true;
$options[CURLOPT_HTTPHEADER] = array
(
	'Accept: application/json',
	'Content-Type: application/json',
	'Content-Length: ' . strlen( $postData ),
	'Host:'. FINANCIAL_FEED_HOST,
	'challengeSessionId: ' . $headers['headers']['challengeSessionId'],
	'challengeNodeId: ' . $headers['headers']['challengeNodeId']
	//'Authorization:' . $result['header']
); 

include 'example-exec.php';