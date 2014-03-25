#phpaggcat

##Overview

**Intuit Partner Platform Aggration / Categorization and Customer Account Data for financial instutition accounts.**

PHP Sample Code for Authenticating to Intuit's Agg &amp; Cat APIs.

Steps for using this sample:

1. Prepare a PHP / Apache environment (if you are here, you probably already have one)
2. Download The files in this project to a folder of your choice. **phpaggcat/** is assumed from here on out.  This can be in an apache document root, or just a directory if you'll be using the command line.  If you're using apache and things aren't working, *check permissions.*
3. Download [SimpleSAML](http://simplesamlphp.org/download) to **phpaggcat/simplesamlphp**
4. Download [OAuthSimple](https://github.com/jrconlin/oauthsimple/tree/master/php) OAuthSimple.php to **phpaggcat/oauth_simple/OAuthSimple.php**
5. Reference [Intuit's documentation about Creating X.509 Keys](https://developer.intuit.com/docs/0020_customeraccountdata/009_using_customeraccountdata/0010_gettingstarted/0015_create_an_cad_integration/0010_creating_x.509_public_certificates), used to sign the SAML assertion you send to Intuit's OAuth Server. You'll need a self-signed certificate at the very least for a development deployment of an application.
*As a general practice, you should create a folder somewhere outside of your document root to hold these certificates.*  Make a note of where these are, you'll need them when you configure the examples.
6. Copy **config.sample.php** to **config.php** and edit it.

##Configuring The Examples
After downloading this project, open config.php and edit:

	<?php
	define( 'SIMPLESAML_PATH',  './simplesamlphp' );
	define( 'SIMPLEOAUTH_PATH',  './oauth_simple' );

This information is found on your app dashboard under the section labeled: **Unique OAuth Key and SAML ID**


	define( 'OAUTH_CONSUMER_KEY', '' );
	define( 'OAUTH_SHARED_SECRET', '' );
	define('SAML_IDENTITY_PROVIDER_ID', '');

This is not just the path, it's the full path to the files.
The CERT_PATH should be a file that ends in .crt
The KEY_PATH should be a file that ends in .key

	define( 'SAML_X509_CERT_PATH', '' );
	define( 'SAML_X509_PRIVATE_KEY_PATH', '' );


this is your customer identifier
you'll probably use some sort of database record ID for this value
for testing, we're going to default it to 1000
for a non production account with Intuit, you're limited to 5 of these
each having a maximum of 10 accounts.

	define( 'SAML_NAME_ID', '1000');

 And finally, you shouldn't need to change these, but if you do, here they are.
 
	define( 'OAUTH_SAML_URL', 'https://oauth.intuit.com/oauth/v1/get_access_token_by_saml' );
	define( 'FINANCIAL_FEED_HOST', 'financialdatafeed.platform.intuit.com' );
	define( 'FINANCIAL_FEED_URL', 'https://'.FINANCIAL_FEED_HOST.'/' );

 require our needed libraries and some local project functions.
 
	require_once SIMPLESAML_PATH . '/lib/xmlseclibs.php';
	require_once SIMPLESAML_PATH . '/lib/SimpleSAML/Utilities.php';
	require_once SIMPLEOAUTH_PATH . '/OAuthSimple.php';
	require_once( dirname( __FILE__ ) . '/functions.php' );

##Useful Resources


Intuit's steps for becoming [authorized to integrate with Agg & Cat](https://developer.intuit.com/docs/0020_aggregation_categorization_apps/009_using_aggcat)

Intuit's documentation of an [example SAML Assertion:](https://developer.intuit.com/docs/0020_aggregation_categorization_apps/009_using_aggcat/0010_gettingstarted/0025_making_your_first_connection/saml_assertion_sample)

Intuit's use cases [documentation](https://developer.intuit.com/docs/0020_customeraccountdata/customer_account_data_api/0005_key_concepts) - very valuable.

Intuit's [Support Channels](https://developer.intuit.com/docs/9_other_resources/0030_support)

##Examples
You'll notice that the examples, where data structures are passed, use JSON. JSON is far easier to work with under PHP than the xml structures.  However, as of March 2014, Intuit's JSON documentation seems like it's an afterthought that was just directly translated from the XML.  **Beware of the docs**!

---------------------
###example.php
This does not perform any use specific action - it simply gets the details of the financial institution id 100000 - intuit's test bank which is "CC Bank."

---------------------
###example-post.php
This attempts to perform a login to CC Bank, but the username passed is "tfa_text" which causes the server to respond with a Multi Factor Authentication reply.

---------------------
###example-mfa.php
This does both a login and MFA answer to perform a "discoverAndAddAccounts" api call.

---------------------
###example-exec.php
this is the common CURL code that all the examples call - it should not be executed directly.

---------------------
###example-delete.php
This deletes the customer from Intuit's server.  It will remove all accounts that have been added, allowing you to test anew.

---------------------