<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require 'lib/XeroOAuth.php';
require 'db.php';

/**
 * Define for file includes
 */
define ( 'BASE_PATH', dirname(__FILE__) );

/**
 * Define which app type you are using:
 * Private - private app method
 * Public - standard public app method
 * Public - partner app method
 */
define ( "XRO_APP_TYPE", "Public" );

/**
 * Set a user agent string that matches your application name as set in the Xero developer centre
 */
$useragent = "Xero-OAuth-PHP Public";

/**
 * Set your callback url or set 'oob' if none required
 * Make sure you've set the callback URL in the Xero Dashboard
 * Go to https://api.xero.com/Application/List and select your application
 * Under OAuth callback domain enter localhost or whatever domain you are using.
 */
define ( "OAUTH_CALLBACK", 'http://pg.chipap.com/chargebee/xero_oauth/public.php' );

/**
 * Application specific settings
 * Not all are required for given application types
 * consumer_key: required for all applications
 * consumer_secret: for partner applications, set to: s (cannot be blank)
 * rsa_private_key: application certificate private key - not needed for public applications
 * rsa_public_key: application certificate public cert - not needed for public applications
 */

include 'tests/testRunner.php';

$signatures = array (
		'consumer_key' => '7XLEYKNQYMYZGEIBBXBBOOJCCI3CLY',
		'shared_secret' => 'LJBSXBDJSXNWGYZ5RAVZ9SB9K7P03J',
		// API versions
		'core_version' => '2.0',
		'payroll_version' => '1.0',
		'file_version' => '1.0' 
);


$XeroOAuth = new XeroOAuth ( array_merge ( array (
		'application_type' => XRO_APP_TYPE,
		'oauth_callback' => OAUTH_CALLBACK,
		'user_agent' => $useragent 
), $signatures ) );

$initialCheck = $XeroOAuth->diagnostics ();
$checkErrors = count ( $initialCheck );
if ($checkErrors > 0) {
	// you could handle any config errors here, or keep on truckin if you like to live dangerously
	foreach ( $initialCheck as $check ) {
		echo 'Error: ' . $check . PHP_EOL;
	}
} else {
	
	$here = XeroOAuth::php_self ();
	session_start ();
    if(isset($_REQUEST['cbid'])){
		$oauthSession = retrieveSession1 ();
	} else {
		$oauthSession = retrieveSession ();
	}
	// print_r($oauthSession);
	
	include 'tests/xero_public.php';
	
	if (isset ( $_REQUEST ['oauth_verifier'] )) {
		$XeroOAuth->config ['access_token'] = $_SESSION ['oauth'] ['oauth_token'];
		$XeroOAuth->config ['access_token_secret'] = $_SESSION ['oauth'] ['oauth_token_secret'];
		
		$code = $XeroOAuth->request ( 'GET', $XeroOAuth->url ( 'AccessToken', '' ), array (
				'oauth_verifier' => $_REQUEST ['oauth_verifier'],
				'oauth_token' => $_REQUEST ['oauth_token'] 
		) );
		echo "here oauth_verifier";
		if ($XeroOAuth->response ['code'] == 200) {
			
			$response = $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] );
			$session = persistSession ( $response );
			
			unset ( $_SESSION ['oauth'] );
			$here_with_cbid = $here.'?cbwhid='.$session;
			header ( "Location: {$here_with_cbid}" );
		} else {
			outputError ( $XeroOAuth );
		}
		// start the OAuth dance
	} elseif (isset ( $_REQUEST ['authenticate'] ) || isset ( $_REQUEST ['authorize'] )) {
		echo "here authenticate authorize";
		$params = array (
				'oauth_callback' => OAUTH_CALLBACK 
		);
		
		$response = $XeroOAuth->request ( 'GET', $XeroOAuth->url ( 'RequestToken', '' ), $params );
		
		if ($XeroOAuth->response ['code'] == 200) {
			// print_r ( $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] ) );
			$_SESSION ['oauth'] = $XeroOAuth->extract_params ( $XeroOAuth->response ['response'] );
			
			$authurl = $XeroOAuth->url ( "Authorize", '' ) . "?oauth_token={$_SESSION['oauth']['oauth_token']}";
			// echo '<p>To complete the OAuth flow follow this URL: <a href="' . $authurl . '">' . $authurl . '</a></p>';
			// print_r(json_encode($_SESSION));
			header('Location: '.$authurl);
		} else {
			outputError ( $XeroOAuth );
		}
	}
	testLinks ();
}
