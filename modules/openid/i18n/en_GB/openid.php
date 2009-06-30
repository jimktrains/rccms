<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	// Form errors
	'fullname'                      => array
	(
		'required' => 'Please enter a valid user_name.',
		'length'   => 'This user_name must be between 4 and 20 characters.',
		'exists'   => 'Sorry, this user_name is not available.',
		'default'  => 'Please enter a valid user_name.'
	),

	'email'							=> array
	(
		'required' => 'Please enter a valid email address.',
		'length'   => 'email addresses must be between 4 and 20 characters.',
		'exists'   => 'Sorry, an account using this email address already exists.',
		'default'  => 'Please enter a valid user_name.'
	),

	'openid_url'					=> array
	(
		'claimed_id_invalid'		=> 'Sorry, we were unable to locate the OpenID provider for this id.',
		'claimed_id_not_found'      => 'Sorry, no registered account was found for this OpenID.',
		'claimed_id_exists'				=> 'Sorry, an account using this OpenID already exists.',
	),

	// Demo View text
	'attributes_page_title'			=> 'Your Details',
	'attributes_page_subtitle'		=> 'We were unable to aquire all of the required information from your OpenID Provider. Please fill in the missing fields to complete your registration.',


	// Kohana exceptions
	'unsupported_mode'				=> '%s is not a valid openid_mode.',
	'unsupported_openid_namespace'  => '%s is not a supported OpenID namespace.',

	'invalid_openid_identity'		=> 'The identity : %s is not a valid OpenID identity.',
	'invalid_realm_url'				=> 'The URL defined for the OpenID Realm %s is not valid please check the config/openid.php file.',
	'invalid_openid_version'		=> '%s is not a valid OpenID version.',
	'invalid_association_type'		=> '%s is not a valid OpenID association_type.',
	'invalid_session_type'			=> '%s is not a valid OpenID session_type.',
	'invalid_session_mode'			=> '%s is not a valid OpenID session_mode.',

	'required'						=> 'Auth OpenID Field Error: One or more of the required fields were not supplied: %s',

	'association' => array
	(
		'error_code_missing' => 'The error_code param was missing from the association response from the OpenID Provider.',
		'assoc_type_missing' => 'The assoc_type param was missing from the association response from the OpenID Provider.'
	),

	'curl' => array
	(
		'missing' => 'Curl Missing! The OpenID Auth module requires the PHP CURL library.',
		'error'   => 'Curl Error: %s'
	),

	'security_violation' => array
	(
		'encryption_required'	  => 'At the current security_level setting the no-encryption session_type is not permitted unless running under HTTPS.',
		'stateless_not_permitted' => 'At the current security_level setting [ %s ] stateless mode is not permitted.'
	),

	'illegal_action' => array
	(
		'runtime_setting_of_security_level' => 'security_level CANNOT be set at runtime'
	),

	'extensions' => array
	(
		'sreg' => array
		(
			'unsupported_attribute' => 'Attribute %s is not currently included in the supported attribute list.',
			'invalid_policy_url'	=> '%s is not a valid policy url.',
			'unsupported_namespace' => '%s is not a supported SREG namespace.'
		),

		'ax' => array
		(
			'unsupported_attribute' => 'Attribute %s is not currently included in the supported attribute list.',
			'unsupported_mode' 		=> '%s is not a valid AX mode.',
			'unsupported_namespace' => '%s is not a supported AX namespace.',
			'invalid_url'			=> '%s is not a valid url.',
			'no_such_field'			=> '%s not found in the OpenID Extension AX class.'
		),

		'pape' => array
		(
			'unsupported_field' 	=> '%s is not currently included in the supported PAPE fields.',
			'unsupported_namespace' => '%s is not a valid PAPE namespace.',
			'unsupported_policy' 	=> '%s is not a supported PAPE policy.',
		)
	),

	'user_model' => array
	(
		'notloaded' => 'The requested action cannot be applied to an empty user model.'
	)
);