<?php

define('SIMPLESAML_PATH',  'PLEASE FILL IN');
define('SIMPLEOAUTH_PATH',  'PLEASE FILL IN');

define('OAUTH_CONSUMER_KEY',   'PLEASE FILL IN');
define('OAUTH_SHARED_SECRET',  'PLEASE FILL IN');

define('SAML_IDENTITY_PROVIDER_ID',  'PLEASE FILL IN');
define('SAML_X509_CERT_PATH',        'PLEASE FILL IN');
define('SAML_X509_PRIVATE_KEY_PATH', 'PLEASE FILL IN');
define('SAML_NAME_ID',               'PLEASE FILL IN');  // Up to you; just "keep track" of what you use

define('OAUTH_SAML_URL', 'https://oauth.intuit.com/oauth/v1/get_access_token_by_saml');
define('FINANCIAL_FEED_HOST', 'financialdatafeed.platform.intuit.com');
define('FINANCIAL_FEED_URL', 'https://'.FINANCIAL_FEED_HOST.'/');

require_once(SIMPLESAML_PATH . "/lib/xmlseclibs.php");
require_once(SIMPLESAML_PATH . "/lib/SimpleSAML/Utilities.php");
require_once(SIMPLEOAUTH_PATH . "/OAuthSimple.php");

