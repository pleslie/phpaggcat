<?php

// change if you place these libraries in a folder other than the main phpaggcat folder.
define( 'SIMPLESAML_PATH',  './simplesamlphp' );
define( 'SIMPLEOAUTH_PATH',  './oauth_simple' );

// get these from your intuit partner platform app page.
define( 'OAUTH_CONSUMER_KEY', '' );
define( 'OAUTH_SHARED_SECRET', '' );
define('SAML_IDENTITY_PROVIDER_ID', '');

// 
define( 'SAML_X509_CERT_PATH', '' );
define( 'SAML_X509_PRIVATE_KEY_PATH', '' );

// this is your customer identifier
// you'll probably use some sort of database record ID for this value
// for testing, we're going to default it to 1000
// for a non production account with Intuit, you're limited to 5 of these
// each having a maximum of 10 accounts.
define( 'SAML_NAME_ID', '1000');

// shouldn't need to change these
define( 'OAUTH_SAML_URL', 'https://oauth.intuit.com/oauth/v1/get_access_token_by_saml' );
define( 'FINANCIAL_FEED_HOST', 'financialdatafeed.platform.intuit.com' );
define( 'FINANCIAL_FEED_URL', 'https://' . FINANCIAL_FEED_HOST . '/' );

// require our needed libraries and some local project functions.
require_once SIMPLESAML_PATH . '/lib/xmlseclibs.php';
require_once SIMPLESAML_PATH . '/lib/SimpleSAML/Utilities.php';
require_once SIMPLEOAUTH_PATH . '/OAuthSimple.php';
require_once dirname( __FILE__ ) . '/functions.php';
require_once 'class.aggcatauth.php';