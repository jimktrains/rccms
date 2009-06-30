<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Openid configuration.
 *
 * $Id: openid_demo.php 2008-08-12 09:28:34 BST Atomless $
 *
 *
 * IMPORTANT - The settings that MUST be edited are: return_to and realm
 *
 * OPTIONAL  - If you plan to exchange attributes with OpenID Providers you can also edit
 * 			   user_attributes_required, user_attributes_optional and the sreg and ax settings in
 *			   the extensions array.
 *
 * You can of course also change the other settings in this file but you're advised not to do so unless
 * you're familiar with the OpenID protocol and the classes in this module.
 */

// The URL to the controler method the user will be redirected to after authticating
// with their OpenID Provider.
// IMPORTANT! The same URL  MUST be used for the AX extension 'update_url' (see below)
// IMPORTANT! This url must be in the same domain as the realm url
$config['return_to'] = 'http://example.com/openid_demo/complete_authentication/';

// This is the URL that the user will be asked to grant authorisation to on their OpenID Provider page
// IMPORTANT! This url must be in the same domain as the return_to url
$config['realm'] = 'http://example.com/';

// User attributes to require from the OpenID Provider during authentication for registration
// For list of commonly supported attributes see: Openid_Extension_Sreg::$supported_user_attribute_fields
$config['user_attributes_required'] = array
(
	'fullname',
	'email'
);

// User attributes to requested as 'optional' from the OpenID Provider during authentication for registration
// For list of commonly supported attributes see: Openid_Extension_Sreg::$supported_user_attribute_fields
$config['user_attributes_optional'] = array
(
	'nickname',
	'language',
	'country',
	'language',
	'timezone'
);

// Usage of OpenID extensions is dependant upon which extensions are supported by the current OpenID Provider
// As determined during the discovery phase.
$config['extensions'] = array
(
	'sreg' => array
	(
		// This is the url to the page displaying the privacy policy governing how the user's private data
		// will be used and protected.
		'policy_url' => 'http://example.com/privacy_policy'
	),

	'ax'   => array
	(
		// The URL to which the OpenId Provider can re-post the fetch-response at some time after the
		// initial request.
		// IMPORTANT! This URL MUST be the same as the 'return_to' URL (see above)
		'update_url' => 'http://example.com/openid_demo/verify_response/'
	),

	'pape' => array
	(
		// Integer value greater than or equal to zero in seconds.
		// The number of seconds to allow for the user authentication with the OpenID Provider.
		// When this limit is passed the OpenID Provider should authenticate the request and return the
		// User agent to this Relying Party at the return_to URL where the response verification will fail
		// the authentication.
		'max_auth_age' => 360
	)
);

// The security_level setting is specific to this library and not a standard
// part of OpenID spec, but enables the developer to easily set the level of security
// required for login using just this one setting (default setting in config/openid.php). Each level of
// security includes all of the requirements of the preceding levels plus an additional requirement.
// $security_level = 0 - allows no-encryption mode and sessions even when not running under HTTPS
// $security_level = 1 - does not allow no-encryption unless running under HTTPS
// $security_level = 2 - if supported will require the OpenID Provider to enforce PAPE phishing resistant policy - stateless session_mode is not allowed at a security level of 2 or up
// $security_level = 3 - if supported will require the OpenID Provider to enforce PAPE multi factor policy
// $security_level = 4 - if supported will require the OpenID Provider to enforce PAPE physical multi factor policy
// $security_level = 5 - requires the OpenID Provider to enforce PAPE phishing resistant policy
// $security_level = 6 - requires the OpenID Provider to enforce PAPE multi factor policy
// $security_level = 7 - requires the OpenID Provider to enforce PAPE physical multi factor policy
// *NOTE! : If the $security_level is set to 5, 6, or 7 and the required PAPE policies are not listed as
// supported types by the OpenID Provider in the services gathered during the discovery phase then
// authentication will be denied. The number of OpenID Providers currently offering sufficient support to
// comply with a security_level of over 5 is very limited - so setting the security level above 5 will
// severely limit the number of users able to login to your site -
// (nothing quite as secure as a site nobody can login to! :)
$config['security_level'] = 2;

// Name of the array that all of the openid specific session data will be stored in.
// See the save_to_session method of the Opendid.php library class
$config['session_name'] = 'kohana_openid_session';

//
$config['request'] = array
(
	'curl_config' => array
	(
		CURLOPT_NOSIGNAL	   => TRUE,
		CURLOPT_MAXREDIRS	   => 10,
		CURLOPT_TIMEOUT		   => 20,
		CURLOPT_FOLLOWLOCATION => TRUE,
		CURLOPT_SSL_VERIFYPEER => FALSE,
		CURLOPT_SSL_VERIFYHOST => FALSE,
		CURLOPT_RETURNTRANSFER => TRUE
	),

	'timelimit' => 20,

	// The name used for the nonce query param appended to the end of the return_to url
	'nonce_name' => 'kohana',

	// The application/x-openid-kvf content type is yet to be implimented by most openid providers
	// but is currently the proposed content type for openid requests (when sent as POST data in
	// key:value\n format) in future openid specifications
	'default_http_headers' => array
	(
		//'Content-Type: application/x-openid-kvf'
	)

);

// Maximum age of valid stored association in seconds
$config['max_association_lifetime'] = 18000;


// Set the auto-login (remember me) cookie lifetime, in seconds. The default
// lifetime is two weeks.
$config['login_token_lifetime'] = 1209600;

