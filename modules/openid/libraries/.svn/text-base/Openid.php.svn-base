<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Library Class for OpenID Authentication.
 *
 * $Id: Openid.php 2008-08-12 09:28:34 BST Atomless $
 *
 * This is a base class containing getters and setters and other functions used by various
 * classes that extend it.
 *
 * Instantiated in a chain of extension :
 * Openid_Auth.php <- Openid_Relying_Party.php <- Openid_Response.php <-
 * Openid_Association.php <- Openid_Discovery.php <- Openid_Request.php <- Openid.php
 *
 * The chain of extension reflects the OpenID process of authentication. Along with ease of use for the
 * kohana developer the intention here is to reflect as transparently as possible the requirements and flow
 * outlined in the OpenID specification docs:
 * http://openid.net/specs/openid-authentication-2_0.html
 * http://openid.net/specs/openid-authentication-1_1.html
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Openid_Core {

	// OpenID 1.0 XML Namespace for Yadis XRD Documents
	const OPENID_XML_NAMESPACE = 'http://openid.net/xmlns/1.0';

	// This is used in requests to prefix kvf query params
	const OPENID_NAMESPACE_ALIAS = 'openid';

	// This namespace map is added to the default xrds namespaces (see XRDS.php) when parsing an xrds document into
	// a list of service endpoints (see XRDS->parse).
	public static $legacy_openid_xml_namespace_map = array
	(
		Openid::OPENID_NAMESPACE_ALIAS => Openid::OPENID_XML_NAMESPACE
	);

	// OpenID Namespaces
	const OPENID_2_0_NAMESPACE           = 'http://specs.openid.net/auth/2.0';
	const OPENID_2_0_NAMESPACE_SERVER    = 'http://specs.openid.net/auth/2.0/server';
	const OPENID_2_0_NAMESPACE_SIGNON    = 'http://specs.openid.net/auth/2.0/signon';
	const OPENID_1_2_NAMESPACE_SIGNON    = 'http://openid.net/signon/1.2';
	const OPENID_1_1_NAMESPACE_SIGNON    = 'http://openid.net/signon/1.1';
	const OPENID_1_0_NAMESPACE_SIGNON    = 'http://openid.net/signon/1.0';
	const OPENID_2_0_NAMESPACE_RETURN_TO = 'http://specs.openid.net/auth/2.0/return_to';

	// This is used as the claimed_id if the user enters an openid provider identifier
	// instead of their own - for example if they use yahoo, in which case they must be
	// redirected to the yahoo openid server in order to select their identifier
	const OPENID_2_0_NAMESPACE_IDENTIFIER_SELECT = 'http://specs.openid.net/auth/2.0/identifier_select';

	// The namespaces in the supported_openid_namespaces array dictate the values considered valid
	// for the main ns value for requests and responses.
	public static $supported_openid_namespaces = array
	(
		Openid::OPENID_2_0_NAMESPACE,
		Openid::OPENID_2_0_NAMESPACE_SERVER,
		Openid::OPENID_2_0_NAMESPACE_SIGNON,
		Openid::OPENID_1_2_NAMESPACE_SIGNON,
		Openid::OPENID_1_1_NAMESPACE_SIGNON,
		Openid::OPENID_1_0_NAMESPACE_SIGNON,
	);

	// The order of the NAMESPACES listed here influences the prefered order applied to services
	// during the discovery phase of the authentication process Services found with namespaces
	// matching namespaces listed earlier in this array will take precedence over those listed later.
	public static $yadis_service_types = array
	(
		Openid::OPENID_2_0_NAMESPACE_SERVER,
		Openid::OPENID_2_0_NAMESPACE_SIGNON,
		Openid::OPENID_1_2_NAMESPACE_SIGNON,
		Openid::OPENID_1_1_NAMESPACE_SIGNON,
		Openid::OPENID_1_0_NAMESPACE_SIGNON,
		Openid::OPENID_2_0_NAMESPACE_RETURN_TO
	);

	// The Openid Extension Namespaces supported by the current instance.
	const OPENID_EXTENSION_NAMESPACE_SREG = 'http://openid.net/extensions/sreg/1.1';
	const OPENID_EXTENSION_NAMESPACE_AX   = 'http://openid.net/srv/ax/1.0';
	const OPENID_EXTENSION_NAMESPACE_PAPE = 'http://specs.openid.net/extensions/pape/1.0';

	// Default supported extensions
	protected static $supported_extensions = array
	(
		'sreg' => Openid::OPENID_EXTENSION_NAMESPACE_SREG,
		'ax'   => Openid::OPENID_EXTENSION_NAMESPACE_AX,
		'pape' => Openid::OPENID_EXTENSION_NAMESPACE_PAPE
	);

	// Possible Openid modes
	// No-Encryption mode SHOULD be avoided unless using HTTPS!
	const OPENID_STATEFULL     = 'statefull';
	const OPENID_STATELESS     = 'stateless';
	const OPENID_NO_ENCRYPTION = 'no-encryption';

	//
	protected static $supported_session_modes = array
	(
		Openid::OPENID_STATEFULL,
		Openid::OPENID_STATELESS,
		Openid::OPENID_NO_ENCRYPTION
	);

	// Supported hash algorithms
	const HASH_ALGORITHM_OPENID_1 = 'SHA1';
	const HASH_ALGORITHM_OPENID_2 = 'SHA256';

	// Supported association encryption types
	const OPENID_1_ASSOCIATION_TYPE = 'HMAC-SHA1';
	const OPENID_2_ASSOCIATION_TYPE = 'HMAC-SHA256';

	//
	protected static $supported_association_types = array
	(
		Openid::HASH_ALGORITHM_OPENID_1 => Openid::OPENID_1_ASSOCIATION_TYPE,
		Openid::HASH_ALGORITHM_OPENID_2 => Openid::OPENID_2_ASSOCIATION_TYPE
	);

	// Supported session encryption types
	const OPENID_1_SESSION_TYPE = 'DH-SHA1';
	const OPENID_2_SESSION_TYPE = 'DH-SHA256';

	//
	protected static $supported_session_types = array
	(
		Openid::HASH_ALGORITHM_OPENID_1 => Openid::OPENID_1_SESSION_TYPE,
		Openid::HASH_ALGORITHM_OPENID_2 => Openid::OPENID_2_SESSION_TYPE,
		Openid::OPENID_NO_ENCRYPTION    => Openid::OPENID_NO_ENCRYPTION
	);

	// Supported 'rel' values for html link tags - used during html discovery
	const HTML_LINK_TAG_REL_PROVIDER_OPENID_2 = 'openid2.provider';
	const HTML_LINK_TAG_REL_LOCAL_OPENID_2    = 'openid2.local_id';
	const HTML_LINK_TAG_REL_PROVIDER_OPENID_1 = 'openid.server';
	const HTML_LINK_TAG_REL_LOCAL_OPENID_1    = 'openid.delegate';

	// Supported OpendID delegation tags - used during yadis discovery
	const XRDS_LOCAL_ID_TAG_OPENID_2 = 'xrd:LocalID';
	const XRDS_LOCAL_ID_TAG_OPENID_1 = 'openid:Delegate';

	// OpenID request modes
	// Used when requesting a trust association
	const OPENID_REQUEST_MODE_ASSOCIATE            = 'associate';
	// Used when redirecting to the OpenID provider to enable the user to authenticate on the provider page
	const OPENID_REQUEST_MODE_CHECK_ID_SETUP       = 'checkid_setup';
	// Used when redirecting to the OpenID provider
	// immediately returns to the return_to url without user authentication on the provider page
	const OPENID_REQUEST_MODE_CHECK_ID_IMMEDIATE   = 'checkid_immediate';
	// Used when needing to ask the OpenID provider to verify the sig in a response when there is no
	// association stored
	const OPENID_REQUEST_MODE_CHECK_AUTHENTICATION = 'check_authentication';

	//
	protected static $valid_request_modes = array
	(
		Openid::OPENID_REQUEST_MODE_ASSOCIATE,
		Openid::OPENID_REQUEST_MODE_CHECK_ID_SETUP,
		Openid::OPENID_REQUEST_MODE_CHECK_ID_IMMEDIATE,
		Openid::OPENID_REQUEST_MODE_CHECK_AUTHENTICATION
	);

	// OpenID response modes
	const OPENID_RESPONSE_MODE_ID_RES       = 'id_res';
	const OPENID_RESPONSE_MODE_SETUP_NEEDED = 'setup_needed';
	const OPENID_RESPONSE_MODE_CANCEL       = 'cancel';
	const OPENID_RESPONSE_MODE_ERROR        = 'error';

	//
	protected static $valid_response_modes = array
	(
		Openid::OPENID_RESPONSE_MODE_ID_RES,
		Openid::OPENID_RESPONSE_MODE_SETUP_NEEDED,
		Openid::OPENID_RESPONSE_MODE_CANCEL,
		Openid::OPENID_RESPONSE_MODE_ERROR
	);

	// When the extensions array is populated extension objects replace the boolean value.
	// Which extensions are added depends upon the support offered by the endpoints gathered during the
	// discovery phase and the security_level setting in config/openid.php
	protected $extensions = array
	(
		'sreg' => FALSE,
		'ax'   => FALSE,
		'pape' => FALSE
	);

	// XRDS document returned during discovery phase - instance of XRDS.php
	protected $xrds;

	// List of Openid_Service_Endpoint objects populated
	// during the openid discovery phase.
	// Setting of the $current_openid_service_endpoint shifts it from this array
	// so the $current_openid_service_endpoint is never included in the $openid_service_endpoints list
	protected $openid_service_endpoints;

	// The result of using array_shift to grab the next service endpoint
	// from the Openid_Discovery::$openid_service_endpoints that resulted from the openID discovery phase
	protected $current_openid_service_endpoint;

	// Associative array populated with any user attributes retrieved from the OpenID Provider using
	// either the AX or SREG attribute extension. See Openid_Extension_Ax and Openid_Extension_Sreg
	// for supported attributes and set the required and optional attributes requested during registration
	// in config/openid.php
	protected $user_attributes = array();

	//
	protected $internal_log = array();

	// Session library
	protected $session;

	// Array containing the authentication fields used in requests to openID Providers
	protected $fields = array
	(
		// If discovery is failing under version 2.0 it automatically degrades to version 1.0.
		// It may also be changed while validating responses when the OpenID provider specifies
		// a namespace value indicating the response should be interpreted under a different openid version.
		'openid_version' =>  2.0,

		// Current openID session mode - see openid::$supported_session_modes
		'session_mode' => OpenID::OPENID_STATEFULL,

		// OpenID Namespace required for openID 2.0 authentication
		'ns' => '',

		// The following settings are valid for mode:
		// checkid_setup - used in most cases - user required to authorize authentication on provider page
		// (see http://openid.net/specs/openid-authentication-2_0-12.html#anchor27)
		// checkid_immediate - (see http://openid.net/specs/openid-authentication-2_0-12.html#anchor28)
		// associate - used when performing DH Key exchange to form trust for subsequent requests
		// check_authentication - used when asking the OpenID Provider to verify a rsponse rather than
		// checking the sig etc locally - (see http://openid.net/specs/openid-authentication-2_0.html#verifying_signatures)
		// This library currently verifies the response locally so check_authentication is not used.
		'mode' => '',

		// Verbatum copy of the id submitted by the user in the openid_url form
		// usefull when needing to list the user's ids as it's reassuring to them to see the actual (claimed_id)
		// id they inputted rather than the id that resulted after normalization and discovery/resolution.
		// Note: this value is a convenience bespoke to this library, it is not part of the OpenID spec and
		// should not be used for anything other than the above stated purpose.
		'display_id' => '',

		// id resulting from normalization and discovery/resolution performed on the user submitted id
		'claimed_id' => '',

		// The user's Local Identifier or canonical_id as defined by the Openid Provider.
		// claimed_id and identity SHALL be either BOTH present or BOTH absent.
		// If neither value is present, the assertion is not about an identifier,
		// and will contain other information in its payload, using extensions (ax, sreg, pape).
		'identity' => '',

		// Url the OpenID provider server will redirect back to once authentication is complete or cancelled
		'return_to' => '',

		// The realm (formerly trust_root in openid spec 1) is the url the user is asked to give
		// permission to once redirected to the openid provider page
		// (see http://openid.net/specs/openid-authentication-2_0-12.html#realms)
		'realm' => '',

		// The url the user is asked if they wish to give
		// permission to once redirected to the openid provider page.
		// This has been changed to 'realm' in openid 2.0
		// so will only be set for openid 1 authentication.
		'trust_root' => '',

		// A handle for an association between the Relying Party and the OP that SHOULD be used
		// to sign the response.
		// Note: If no association handle is sent, the transaction will take place in
		// Stateless Mode.
		'assoc_handle' => '',

		// Openid Provider server url.
		// The url to send authentication requests to and to which the user will be
		// redirected when required to authorize the authentication.
		'op_endpoint'  => '',

		// *** ASSOCIATION REQUEST FIELDS ***

		// Association hash type,  see above : $supported_association_types
		'assoc_type'          => '',

		// Session hash type,  see above : $supported_session_types
		'session_type'        => '',

		// Generator integer used in diffie-hellman key exchange
		'dh_gen'              => '',

		// Modulus long number used in diffie-hellman key exchange
		'dh_modulus'          => '',

		// Private key generated during diffie-hellman key exchange
		'dh_consumer_private' => '',

		// Public key generated during diffie-hellman key exchange
		'dh_consumer_public'  => '',

		// Server public key received during diffie-hellman key exchange
		'dh_server_public'    => '',

		// Encoded shared sectret received during diffie-hellman key exchange
		'enc_mac_key'         => '',

		// Shared secret extracted during diffie-hellman key exchange
		'mac_key'             => '',

		// Time that the association was formed
		'assoc_issued'        => '',

		// Lifespan of the trust association
		'expires_in'          => ''
	);

	// Array listing all the required fields for openid 1.* authentication
	protected static $required_fields_for_openid_1_request = array
	(
		'mode',
		'return_to',
		'trust_root',
		'identity',
		// Under openid 1, claimed_id is appended to the end of the return_to url
		// along with the nonce.
		'claimed_id',
		'assoc_handle',
		'op_endpoint'
	);

	// Array listing all the required fields for openid 2 authentication
	protected static $required_fields_for_openid_2_request = array
	(
		'ns',
		'mode',
		'realm',
		'return_to',
		'identity',
		'claimed_id',
		'assoc_handle',
		'op_endpoint'
	);

	// Array listing all the required fields for sending a trust association request to the openID provider
	protected static $required_fields_for_association_request = array
	(
		'ns',
		'mode',
		'assoc_type',
		'session_type',
		'dh_modulus',
		'dh_gen',
		'dh_consumer_public'
	);

	// Array listing all the required fields in a valid openID association
	protected static $required_fields_for_valid_association_response = array
	(
		'assoc_handle',
		'assoc_type',
		'dh_server_public',
		'enc_mac_key',
		'expires_in',
		'ns',
		'session_type'
	);

	// Array listing all the required fields in a valid openID association
	protected static $required_fields_for_valid_association_response_no_encryption = array
	(
		'assoc_handle',
		'assoc_type',
		'dh_server_public',
		'expires_in',
		'mac_key',
		'ns',
		'session_type'
	);

	// Array listing all the required fields in a valid openID association.
	// After association request has been made and response received these fields must have been set
	// for the association to be valid.
	protected static $required_fields_for_valid_association = array
	(
		'ns',
		'assoc_type',
		'assoc_handle',
		'session_type',
		'dh_gen',
		'dh_modulus',
		'dh_consumer_private',
		'dh_consumer_public',
		'dh_server_public',
		'enc_mac_key',
		'mac_key',
		'assoc_issued',
		'expires_in'
	);

	// This will be set to the associative array extracted from the query params in the OpenID Provider
	// response (see Openid_Response.php)
	protected $response_fields = array();

	protected static $required_fields_for_valid_openid_1_response = array
	(
		'mode',
		'assoc_handle',
		'identity',
		'return_to',
		'sig',
		'signed'
	);

	protected static $required_fields_for_valid_openid_2_response = array
	(
		'ns',
		'mode',
		'op_endpoint',
		'claimed_id',
		'identity',
		'return_to',
		'response_nonce',
		'assoc_handle',
		'signed',
		'sig'
	);

	// Fields that must be signed for a valid response under OpenID 1.*
	protected static $required_signed_fields_for_valid_openid_1_response = array
	(
		'return_to',
		'identity'
	);

	// Fields that must be signed for a valid response under OpenID 2.0
	protected static $required_signed_fields_for_valid_openid_2_response = array
	(
		'return_to',
		'claimed_id',
		'identity',
		'response_nonce',
		'assoc_handle'
	);

	//
	protected static $required_fields_for_valid_check_authentication_openid_1_response = array
	(
		'mode',
		'is_valid'
	);

	//
	protected static $required_fields_for_valid_check_authentication_openid_2_response = array
	(
		'ns',
		'is_valid'
	);

	/**
	 * Constructor.
	 *
	 * @param   array   initial settings for the authentication fields
	 * @return  void
	 */
	public function __construct($fields = array())
	{
		$this->session = Session::instance();

		// Set fields to those passed, the Openid 2.0 namespace and the settings in the openid config
		// Any default config settings duplicated in the passed fields array will be overwritten by the
		// passed settings.
		$this->set_authentication_fields
		(
			array_merge(
							array
							(
								'ns'        => Openid::OPENID_2_0_NAMESPACE,
								'realm'     => KOHANA::config('openid.realm'),
								'return_to' => KOHANA::config('openid.return_to')
							),
							$fields
						)
		);
	}

	/**
	* Allow getting of protected fields and map certain alias field names to function calls
	*
	* @param  key of instance variable to get
	* @return mixed
	*/
	protected function __get($key)
	{
		switch ($key)
		{
			case 'current_openid_service_endpoint':

				return $this->current_openid_service_endpoint;

			break;
			case 'openid_provider_server_url':

				return $this->op_endpoint;

			break;
			case 'association_hash_type':

				return Openid::get_hash_type_from_assoc_or_session_type($this->assoc_type);

			break;
			case 'session_hash_type':

				return Openid::get_hash_type_from_assoc_or_session_type($this->session_type);

			break;
			case 'claimed_id_type':

				if(empty($this->fields['claimed_id']))
					return FALSE;

				return openid_identifier::detect_basic_type($this->fields['claimed_id']);

			break;
			case 'association':

				$association_fields = array();

				foreach (openid::$required_fields_for_valid_association as $key)
				{
					$association_fields[$key] = $this->fields[$key];
				}

				return $association_fields;

			break;
			case 'user_attributes':

				return $this->user_attributes;

			break;
			case 'attributes_requested':

				foreach ($this->internal_log as $entry)
				{
					if ($entry['type'] == 'attributes_requested')
						return TRUE;
				}

				return FALSE;

			break;
			// required_attributes_missing will always be FALSE in stateless OpenID session_mode or if the
			// second param passed to the Relying Party start_authentication method ($request_attributes)
			// was set to false.
			case 'required_attributes_missing':

				foreach ($this->internal_log as $entry)
				{
					if ($entry['type'] == 'missing_required_attribute')
						return TRUE;
				}

				return FALSE;

			break;
			case 'optional_attributes_missing':

				foreach ($this->internal_log as $entry)
				{
					if ($entry['type'] == 'missing_optional_attribute')
						return TRUE;
				}

				return FALSE;

			break;
			case 'internal_log':

				return $this->internal_log;

			break;
			case 'response_errors':

				$errors = array();

				foreach ($this->internal_log as $entry)
				{
					if ($entry['type'] == 'error' AND $entry['class'] == 'Openid_Response')
					{
						array_push($errors, $entry);
					}
				}

				return $errors;

			break;
			case 'errors':

				$errors = array();

				foreach ($this->internal_log as $event)
				{
					if ($event['type'] == 'error')
					{
						array_push($errors, $event);
					}
				}

				return $errors;

			break;
			default:

				if (array_key_exists($key, $this->fields))
				{
					return $this->fields[$key];
				}

		}
	}

	/**
	 * Allow setting of selected protected variables
	 *
	 * @param  string  field name
	 * @param  mixed   value
	 * @return boolean
	 */
	protected function __set($key, $val)
	{
		switch ($key)
		{
			case 'security_level':

				throw new Kohana_Exception('openid.illegal_action.runtime_setting_of_security_level', $val);

			break;
			case 'association_type':

				$this->assoc_type = $val;

			break;
			case 'realm':
			case 'trust_root':

				$val = url_openid::normalize($val);

				if ($val === FALSE)
					throw new Kohana_Exception('openid.invalid_realm_url');

			break;
			case 'openid_provider_server_url':

				$this->op_endpoint = $val;

			break;
			// ------
			// Create and populate the appropriate extension objects when extensions have been applied to an
			// authentication and so extension params are included in the response from the OpenID Provider
			case 'sreg':

				$this->extensions['sreg'] = Openid_Extension_Sreg::factory();

			break;
			case 'ax':

				$this->extensions['ax'] = Openid_Extension_Ax::factory();

			break;
			case 'pape':

				$this->extensions['pape'] = Openid_Extension_Pape::factory();

			break;
		}

		// Allow setting of protected openID fields
		if (array_key_exists($key, $this->fields))
		{
			$this->set_authentication_fields(array($key => $val));
		}
	}

   /**
	* Set fields for the openID nvp requests
	*
	* @param   array   associative array of authentication field settings
	* @return  void
	*/
	protected function set_authentication_fields($fields = array())
	{
		foreach ($fields as $key => $val)
		{
			// Set empty session_type to 'no-encryption'
			if ($key == 'session_type' AND empty($val))
			{
				$val = Openid::OPENID_NO_ENCRYPTION;
			}

			// Set empty namespace setting to Openid::OPENID_1_0_NAMESPACE
			if ($key == 'ns' AND empty($val))
			{
				$val = Openid::OPENID_1_0_NAMESPACE_SIGNON;
			}

			if ( ! empty($val))
			{
				if (array_key_exists($key, $this->fields))
				{
					switch ($key)
					{
						case 'mode':

							$val = strtolower($val);

							if ( ! (in_array($val, Openid::$valid_request_modes) OR in_array($val, Openid::$valid_response_modes)))
								throw new Kohana_Exception('openid.unsupported_mode', $val);

						break;
						case 'realm':
						case 'trust_root':

							$this->fields['trust_root'] = $val;

							$this->fields['realm'] = $val;

						break;
						case 'identity':
						case 'claimed_id':

							// Normailize the identifier
							$val = openid_identifier::normalize($val);

							if ($val === FALSE)
							{
								$this->log('invalid_openid', 'Openid', 'set_authentication_fields');
							}

						break;
						case 'ns':

							if ( ! in_array($val, Openid::$supported_openid_namespaces))
								throw new Kohana_Exception('openid.unsupported_openid_namespace', $val);

							// Set the openid_version appropriate to the namespace
							// Note : the association_type and session_type are both set whenever the openid_version
							// is set. (see below)
							$this->openid_version = Openid::get_openid_version_from_namespace($val);

						break;
						 // Set the current OpenID specification version.
						 // Also set the current namespace accordingly.
						 // This may be reset during service/OP discovery in order to force a
						 // fallback position to 1.1 if failing under 2.0 or if provider does not yet support 2.0.
						case 'openid_version':

							if ($val < 2)
							{
								// See the set_authentication_fields method for how the
								// association and session types are actually set
								$this->association_type = Openid::HASH_ALGORITHM_OPENID_1;

								$this->session_type     = Openid::HASH_ALGORITHM_OPENID_1;
							}

							switch ($val)
							{
								case 1:
								case 1.0:
									// Set the namespace authentication field for
									// the current openid spec version.
									$this->fields['ns'] = Openid::OPENID_1_0_NAMESPACE_SIGNON;

								break;
								case 1.1:
									// Set the namespace authentication field for
									// the current openid spec version.
									$this->fields['ns'] = Openid::OPENID_1_1_NAMESPACE_SIGNON;

								break;
								case 1.2:
									// Set the namespace authentication field for
									// the current openid spec version.
									$this->fields['ns'] = Openid::OPENID_1_2_NAMESPACE_SIGNON;

								break;
								case 2.0:

									// Set the namespace authentication field for
									// the current openid spec version.
									$this->fields['ns'] = Openid::OPENID_2_0_NAMESPACE;

									// See the set_authentication_fields method for how the
									// association and session types are actually set
									$this->association_type = Openid::HASH_ALGORITHM_OPENID_2;

									$this->session_type     = Openid::HASH_ALGORITHM_OPENID_2;

								break;
								default:
									echo "val=".$val;
									throw new Kohana_Exception('openid.invalid_openid_version', $val);
							}

						break;
						case 'assoc_type':

							// Ensure val is a supported association hash type
							$val = strtoupper($val);

							if ( ! array_key_exists($val, Openid::$supported_association_types) AND
								 ! in_array($val, Openid::$supported_association_types))
								throw new Kohana_Exception('openid.invalid_association_type', $val);

							// If got here and val not found in array then val must be a key
							// so set val to the value for that key in the supported types
							if ( ! in_array($val, Openid::$supported_association_types))
							{
								$val = Openid::$supported_association_types[$val];
							}

						break;
						case 'session_type':

							// Ensure val is a supported session hash type
							$val = strtoupper($val);

							// If val passed for session_type == 'no-encryption'
							// switch the session mode accordingly.
							if (strtolower($val) == Openid::OPENID_NO_ENCRYPTION)
							{
								if (($this->security_level === 0) OR isset($_SERVER['HTTPS']))
								{
									$val = Openid::OPENID_NO_ENCRYPTION;

									$this->fields['session_mode'] = Openid::OPENID_NO_ENCRYPTION;
								}
								else
								{
									throw new Kohana_Exception('openid.security_violation.encryption_required');
								}
							}

							if ( ! (array_key_exists($val, array_change_key_case(Openid::$supported_session_types, CASE_UPPER))
								 OR in_array($val, Openid::$supported_session_types)))
								 throw new Kohana_Exception('openid.invalid_session_type', $val);

							// If got here and val not found in array then val must be a key
							// so set val to the value for that key in the supported types
							if ( ! in_array($val, Openid::$supported_session_types))
							{
								$val = Openid::$supported_session_types[$val];
							}

						break;
						case 'session_mode':

							$val = strtolower($val);

							if ( ! in_array($val, Openid::$supported_session_modes))
								throw new Kohana_Exception('openid.invalid_session_mode', $val);

							if ($val == Openid::OPENID_STATELESS AND $this->security_level > 1)
								throw new Kohana_Exception('openid.security_violation.stateless_not_permitted', $this->security_level);

							// The relationship between session_mode and session_type is such that
							// if session_mode is Openid::OPENID_NO_ENCRYPTION then session_type must be set
							// to Openid::OPENID_NO_ENCRYPTION also.
							if ($val == Openid::OPENID_NO_ENCRYPTION)
							{
								$this->fields['session_type'] = Openid::OPENID_NO_ENCRYPTION;
							}

						break;
						// Association lifetime setting
						case 'expires_in':

							$val = intval($val);
							// Reject any setting for expires_in that cannot be cast as an integer.
							// A blank setting for expires_in will invalidate the association.
							// (see Openid_Association::valid_association)
							if ($val === 0)
								continue;

						break;
					}

					$this->fields[$key] = $val;
				}
			}
		}
	}

	/**
	 * Log internal authentication process so errors/failures can be traced to the exact point in the process
	 * at which they occured so appropriate feedback can be offered to the user and the developer can debug
	 * the application.
	 *
	 * @param  string   type of event to log
	 * @param  string   class in which the event occured
	 * @param  string   method / function in which the event occured
	 * @param  string   freeform event message (these have been mapped to lang files)
	 * @param  array    array of data containing the relevant internal settings at the time of the event
	 * @return void
	 */
	public function log($type, $class, $method, $message = '', $data = FALSE)
	{
		switch($class)
		{
			case 'discovery':

				$data['claimed_id'] = $this->claimed_id;

			break;
			case 'association':

				$data['claimed_id'] = $this->claimed_id;
				$data['op_endpoint'] = $this->op_endpoint;

			break;
		}

		$event = array
		(
			'type'   => $type,
			'class'  => $class,
			'method' => $method
		);

		if ( ! empty($message))
		{
			$event['message'] = $message;
		}

		if ( ! empty($data))
		{
			$event['data'] = $data;
		}

		array_push($this->internal_log, $event);

		// authentication_errors should only be created from the controller
		// Openid library errors are logged with type 'error'
		// The reason for this is that unlike errors that occur in the library, errors that occur in the
		// controller may trigger a redirect to an error page.
		if ($type == 'authentication_error')
		{
			$this->save_to_session();
		}
	}

	/**
	 * Save the fields array in the current session
	 *
	 * @return void
	 */
	protected function save_to_session()
	{
		$settings_to_save = array
		(
			'fields' 						  => $this->fields,
			'openid_service_endpoints' 		  => serialize($this->openid_service_endpoints),
			'current_openid_service_endpoint' => serialize($this->current_openid_service_endpoint),
			'extensions' 					  => serialize($this->extensions),
			'user_attributes' 				  => $this->user_attributes,
			'internal_log' 					  => $this->internal_log
		);

		$this->session->set(KOHANA::config('openid.session_name'), $settings_to_save);
	}

	/**
	 * Clear any Openid module settings currently stored in the kohana session
	 *
	 * @return void
	 */
	protected function clear_session()
	{
		$this->session->delete(KOHANA::config('openid.session_name'));
	}

	/**
	 * Load any Openid settings stored int eh current kohana session. - chainable.
	 *
	 * @return mixed   FALSE on failure or $this on success
	 */
	public function load_from_session()
	{
		$saved_settings = $this->session->get(KOHANA::config('openid.session_name'), FALSE);

		if ($saved_settings === FALSE)
		{
			$this->log('error', 'Openid', 'load_from_session', 'not_found');

			return FALSE;
		}

		if ( ! array_key_exists('fields', $saved_settings) OR
			 ! array_key_exists('openid_service_endpoints', $saved_settings) OR
			 ! array_key_exists('current_openid_service_endpoint', $saved_settings) OR
			 ! array_key_exists('extensions', $saved_settings) OR
			 ! array_key_exists('user_attributes', $saved_settings) OR
			 ! array_key_exists('internal_log', $saved_settings))
		{
			$this->log('error', 'Openid', 'load_from_session', 'corrupted', $saved_settings);

			return FALSE;
		}

		if ($saved_settings['fields']['session_mode'] == Openid::OPENID_STATELESS)
		{
			$this->log('error', 'Openid', 'load_from_session', 'session_mode_was_stateless');

			return FALSE;
		}

		$this->set_authentication_fields($saved_settings['fields']);

		$this->openid_service_endpoints = unserialize($saved_settings['openid_service_endpoints']);

		$this->current_openid_service_endpoint = unserialize($saved_settings['current_openid_service_endpoint']);

		$this->extensions = unserialize($saved_settings['extensions']);

		$this->user_attributes = $saved_settings['user_attributes'];

		$this->internal_log = $saved_settings['internal_log'];

		return $this;
	}

	/**
	 * Add attribute exchange extensions to the Openid authentication request.
	 * See settings for user_attributes_required and user_attributes_optional in config/openid.php
	 * Attributes are requested simply by passing TRUE as the second param when calling start_authentication.
	 * (See the register method in the openid_demo controller.)
	 *
	 * @param  array   list of attributes required to perform this authentication
	 * @param  array   list of optional attributes that will be accepted as part of this authentication
	 * @return void
	 */
	protected function add_attribute_extension($required_fields, $optional_fields)
	{
		// NOTE : No conditional to check for sreg support as some OpendID Providers support it
		// even though they fail to declare support for it in returned xrds.
		// Including the sreg params even without confirmed support for the extension doesn't break
		// anything - so either we'll get the attributes we want or the OpenID Provider simply ignores
		// the sreg params.

		$this->extensions['sreg'] = Openid_Extension_Sreg::factory($required_fields, $optional_fields);

		// Currently a good idea to hedge our bets and use both ax and sreg if available because
		// at time of writing this (03.08.2008) even the main OpenID providers that claim to support
		// ax in the info they return during the discovery phase, appear to not have yet fully
		// implimented ax as they fail to return any ax values in the GET response sent to the return_to
		// url after authenticating the user.

		if ($this->current_openid_service_endpoint
				 ->supports_openid_service_or_extension_type(Openid::OPENID_EXTENSION_NAMESPACE_AX))
		{
			$this->extensions['ax'] = Openid_Extension_Ax::factory($required_fields, $optional_fields);
		}

		$this->log('attributes_requested', 'Openid', 'add_attribute_extension', '', array_merge($required_fields, $optional_fields));
	}

	/**
	 * Add supported security extensions - see the comments for security_level in config/openid.php
	 * for an explaination of which pape extensions are required or optional at the various security levels.
	 *
	 * @return void
	 */
	protected function add_security_extensions()
	{
		// Phishing Resistant
		if (KOHANA::config('openid.security_level') > 1)
		{
			if ($this->current_openid_service_endpoint
					 ->supports_openid_service_or_extension_type(Openid::OPENID_EXTENSION_NAMESPACE_PAPE)
				OR
				$this->current_openid_service_endpoint
					 ->supports_openid_service_or_extension_type(Openid_Extension_Pape::PHISHING_RESISTANT)
				OR
				(KOHANA::config('openid.security_level') > 4))
			{
				$this->extensions['pape'] = Openid_Extension_Pape::instance();

				$this->extensions['pape']->add_preferred_auth_policy(Openid_Extension_Pape::PHISHING_RESISTANT);
			}
		}

		// Multi Factor
		if (KOHANA::config('openid.security_level') > 2)
		{
			if ($this->current_openid_service_endpoint
					 ->supports_openid_service_or_extension_type(Openid::OPENID_EXTENSION_NAMESPACE_PAPE)
				OR
				$this->current_openid_service_endpoint
					 ->supports_openid_service_or_extension_type(Openid_Extension_Pape::MULTI_FACTOR)
				OR
				(KOHANA::config('openid.security_level') > 5))
			{
				$this->extensions['pape'] = Openid_Extension_Pape::instance();

				$this->extensions['pape']->add_preferred_auth_policy(Openid_Extension_Pape::MULTI_FACTOR);
			}
		}

		// Physical Multi Factor
		if (KOHANA::config('openid.security_level') > 3)
		{
			if ($this->current_openid_service_endpoint
					 ->supports_openid_service_or_extension_type(Openid::OPENID_EXTENSION_NAMESPACE_PAPE)
				OR
				$this->current_openid_service_endpoint
					 ->supports_openid_service_or_extension_type(Openid_Extension_Pape::PHYSICAL_MULTI_FACTOR)
				OR
				(KOHANA::config('openid.security_level') > 6))
			{
				$this->extensions['pape'] = Openid_Extension_Pape::instance();

				$this->extensions['pape']->add_preferred_auth_policy(Openid_Extension_Pape::PHYSICAL_MULTI_FACTOR);
			}
		}
	}

	/**
	 * Get an associative array of the current authentication settings required to perform the current
	 * authentication action as defined by the current setting for mode in the fields array.
	 *
	 * @return array   associative array of the current authentication settings
	 */
	protected function get_authentication_fields()
	{
		$required_fields = $this->get_required_fields();

		if ( ! Openid::check_required_fields_set($this->fields, $required_fields))
			throw new Kohana_Exception('openid.required', '<p>Required Fields:</p>'.KOHANA::debug($required_fields).'<p>Supplied Fields:</p>'.KOHANA::debug($this->fields));

		ksort($required_fields);

		$authentication_fields = array();

		foreach ($required_fields as $key)
		{
			// Omit the openid server url (op_endpoint) from the authentication fields array
			if ($key != 'op_endpoint')
			{
				$value = $this->fields[$key];

				// Convert any diffie-hellman key values to base64 before sending
				if (strpos($key, 'dh_') === 0)
				{
					$value = crypt::long_to_base64($value);
				}

				$authentication_fields[Openid::OPENID_NAMESPACE_ALIAS.'.'.$key] = $value;
			}
		}

		if ($this->fields['mode'] != Openid::OPENID_REQUEST_MODE_ASSOCIATE)
		{
			$extension_fields = array();

			foreach ($this->extensions as $extension_name => $extension)
			{
				if ($extension != FALSE)
				{
					$extension_fields = array_merge($extension_fields, $extension->get_fields());
				}
			}

			// Add any extension fields that have been set
			if ( ! empty($extension_fields))
			{
				foreach ($extension_fields as $key => $value)
				{
					$authentication_fields[Openid::OPENID_NAMESPACE_ALIAS.'.'.$key] = $value;
				}
			}
		}

		return $authentication_fields;
	}

   /**
	* Get a list of the fields required to perform the current authentication action as defined by
	* the current setting for mode in the fields array.
	*
	* @return array   linear array of required fields
	*/
	protected function get_required_fields()
	{
		if ($this->fields['mode'] == Openid::OPENID_REQUEST_MODE_ASSOCIATE)
		{
			$required_fields = Openid::$required_fields_for_association_request;
		}
		else
		{
			if ($this->openid_version < 2.0)
			{
				$required_fields = Openid::$required_fields_for_openid_1_request;
			}
			else
			{
				switch ($this->openid_version)
				{
					case 2.0:
					default:
						$required_fields = Openid::$required_fields_for_openid_2_request;
				}
			}

			// Remove assoc_handle from required_fields if in stateless mode
			if ($this->session_mode == Openid::OPENID_STATELESS)
			{
				$required_fields = arr_openid::linear_remove($required_fields, array('assoc_handle'));
			}
		}

		return $required_fields;
	}

	/**
	 * Check that all of the required fields are set - also used by the association class (an ancestor of this class)
	 *
	 * @param  array     associative array of fields to check
	 * @param  array     linear array of fields that MUST be set
	 * @return boolean
	 */
	public static function check_required_fields_set($fields, array $required_fields)
	{
		if (empty($fields) OR empty($required_fields) OR ! is_array($fields))
			return FALSE;

		foreach($required_fields as $key)
		{
			if ( ! array_key_exists($key, $fields))
				return FALSE;

			if (empty($fields[$key]))
				return FALSE;
		}

		return TRUE;
	}

   /**
	* Return the openID version corresponding to the passed namespace
	*
	* @param  string  namespace url
	* @return mixed   supported openID version or FALSE if not found
	*/
	public static function get_openid_version_from_namespace($ns)
	{
		switch ($ns)
		{
			case Openid::OPENID_2_0_NAMESPACE_SERVER:
			case Openid::OPENID_2_0_NAMESPACE_SIGNON:
			case Openid::OPENID_2_0_NAMESPACE:

				return 2.0;

			break;
			case Openid::OPENID_1_2_NAMESPACE_SIGNON:

				return 1.2;

			break;
			case Openid::OPENID_1_1_NAMESPACE_SIGNON:

				return 1.1;

			break;
			case Openid::OPENID_1_0_NAMESPACE_SIGNON:

				return 1.0;

			break;
		}

		return FALSE;
	}

	/**
	 * Return the hash algorithm type according to the passed session o association type
	 *
	 * @param  string   association or session type
	 * @return string   supported openid hash algorithm type
	 */
	public static function get_hash_type_from_assoc_or_session_type($type)
	{
		if (stripos($type, Openid::HASH_ALGORITHM_OPENID_2))
		{
			return Openid::HASH_ALGORITHM_OPENID_2;
		}
		elseif (stripos($type, Openid::HASH_ALGORITHM_OPENID_1))
		{
			return Openid::HASH_ALGORITHM_OPENID_1;
		}

		return FALSE;
	}
}