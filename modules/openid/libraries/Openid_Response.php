<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Library Class for verifying authentication responses from OpenID Providers.
 *
 * $Id: Openid_Response.php 2008-08-12 09:28:34 BST Atomless $
 *
 * Instantiated in a chain of extension :
 * Openid_Auth.php <- Openid_Relying_Party.php <- Openid_Response.php <-
 * Openid_Association.php <- Openid_Discovery.php <- Openid_Request.php <- Openid.php
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Openid_Response_Core extends Openid_Association {

	/**
	 * Constructor.
	 *
	 * @return  void
	 */
	public function __construct($fields = array())
	{
		parent::__construct($fields);
	}

	/**
	 * Verify a response from an OpenID Provider
	 *
	 * @param  string - url encoded querystring
	 * @return boolean
	 */
	protected function verify_response($response_querystring)
	{
		$this->response_fields = Openid_Response::to_associative_array($response_querystring);

		if (empty($this->response_fields) OR ! array_key_exists(Openid::OPENID_NAMESPACE_ALIAS, $this->response_fields))
		{
			$this->log('error', 'Openid_Response', 'verify_response', 'invalid_response');

			return FALSE;
		}

		if ($this->valid_response_mode() !== TRUE)
		{
			$this->log('error', 'Openid_Response', 'verify_response', 'invalid_response_mode');

			return FALSE;
		}

		switch ($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['mode'])
		{
			case 'id_res':
				// Setting the namespace automatically sets the openid_version accordingly
				$this->set_namespace_according_to_response();

				$required_fields = ($this->openid_version < 2)? Openid::$required_fields_for_valid_openid_1_response
															  : Openid::$required_fields_for_valid_openid_2_response;

				if (Openid::check_required_fields_set
				   ($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS], $required_fields) !== TRUE)
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'all_reuired_fields_not_set');

					return FALSE;
				}

				// Under OpenID 1 the user_setup_url is passed when additional setup is required
				if (array_key_exists('user_setup_url', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'openid_provider_specified_additional_setup_required');

					$this->redirect_to_openid_provider($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['user_setup_url']);

					return FALSE;
				}

				if ($this->check_return_to_url_matches_current_url() !== TRUE)
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'return_to_url_does_not_match_current');

					return FALSE;
				}

				// Return false if any of the fields that MUST be signed under current OpenID version
				// are not listed in the openid signed field (comma separated list)
				if ($this->check_required_signed_fields_are_listed_as_signed() !== TRUE)
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'not_all_required_signed_fields_were_listed_as_signed');

					return FALSE;
				}

				if ($this->check_all_signed_fields_are_included_in_response() !== TRUE)
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'not_all_signed_fields_were_found_in_response');

					return FALSE;
				}

				// If invalidate_handle included in response then delete association from store and set
				// to stateless session mode
				if (array_key_exists('invalidate_handle', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'invalidated_handle');

					Openid_Association::delete_stored_association($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['claimed_id']);

					$this->session_mode = Openid::OPENID_STATELESS;
				}

				// If loading stored fields from session failed or running in Openid::OPENID_STATELESS mode
				// We need to perform discovery on the claimed_id
				if ($this->op_endpoint == '' OR $this->session_mode == Openid::OPENID_STATELESS)
				{
					$this->claimed_id = $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['claimed_id'];

					if ($this->discover() !== TRUE)
					{
						$this->log('error', 'Openid_Response', 'verify_response', 'discovery_failed');

						return FALSE;
					}

					if ( ! empty($this->openid_service_endpoints))
					{
						if ($this->select_endpoint_that_best_matches_response() !== TRUE)
						{
							$this->log('error', 'Openid_Response', 'verify_response', 'no_matching_endpoint_found_in_discovered_data');

							return FALSE;
						}
					}
				}
				else
				{
					// Load stored association for this claimed_id
					if ($this->retrieve_stored_association() !== TRUE)
					{
						$this->log('error', 'Openid_Response', 'verify_response', 'failed_to_retrieve_stored_association');

						return FALSE;
					}
				}

				// Check nonce is unique
				if($this->check_response_nonce_has_not_been_used() !== TRUE)
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'response_nonce_already_used');

					return FALSE;
				}

				// Check nonce is within allowed lifespan ($config['max_association_lifetime'] set in config/openid.php)
				if(nonce::is_alive($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['response_nonce']) !== TRUE)
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'response_nonce_has_expired');

					return FALSE;
				}

				// If loading stored fields failed or running in Openid::OPENID_STATELESS mode
				// We need to ask the OP to verify the sig
				if ($this->valid_association($this->association) !== TRUE OR $this->session_mode == Openid::OPENID_STATELESS)
				{
					if ($this->request_sig_verification_from_openid_provider() !== TRUE)
					{
						$this->log('error', 'Openid_Response', 'verify_response', 'op_claims_sig_not_valid');

						return FALSE;
					}
				}
				else
				{
					// Check sig
					if ($this->verify_sig($response_querystring) !== TRUE)
					{
						$this->log('error', 'Openid_Response', 'verify_response', 'failed_to_verify_sig');

						return FALSE;
					}
				}

				// If user entered an openid provider type url like yahoo.com
				// the claimed_id would have been set to Openid::OPENID_2_0_NAMESPACE_IDENTIFIER_SELECT
				// so we need to set the claimed_id to the one contained in the response - this will be
				// the id that the user selected on the Provider page.
				if ($this->claimed_id == Openid::OPENID_2_0_NAMESPACE_IDENTIFIER_SELECT)
				{
					$this->claimed_id = $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['claimed_id'];

					$this->identity	  = $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['identity'];

					$this->display_id = $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['identity'];
				}

				// Check that response Openid fields match endpoint found in discovery phase (stored in session)
				if ($this->check_response_fields_match_stored_fields() !== TRUE)
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'response_fields_do_not_match_stored_fields');

					return FALSE;
				}

				// Check required security extensions were applied (pape)
				if ($this->check_required_security_applied() !== TRUE)
				{
					$this->log('error', 'Openid_Response', 'verify_response', 'insufficient_security_to_meet_required_security_level');

					return FALSE;
				}

				// Only check if attributes are missing if not in stateless mode because in stateless mode we
				// have no way of determining whether the required attributes were requested or not. In other
				// words whether authentication was for registration or login.
				// Also only check for missing attributes if they were requested for this authentication.
				if ($this->session_mode != Openid::OPENID_STATELESS AND $this->attributes_requested)
				{
					// Verification of response doesn't rely on the required attributes being present.
					// If any required attributes are missing a 'missing_required_attribute' entry will be
					// added to the internal log.
					$this->extract_supplied_attributes();
				}

				// Add the successful response verification to the internal log
				$this->log('success', 'Openid_Response', 'verify_response', 'Response from the OpenID Provider passed all verification checks.');

				return TRUE;

			break;
			case 'setup_needed':
				$this->op_endpoint = $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['op_endpoint'];

				$this->redirect_to_openid_provider();

				return FALSE;
			break;
			case 'cancel':
				$this->log('error', 'Openid_Response', 'verify_response', 'authentication_cancelled_at_provider');

				return FALSE;
			break;
			case 'error':
				$this->log('error', 'Openid_Response', 'verify_response', 'authentication_error_at_provider');

				return FALSE;
			break;
		}
	}

	/**
	 * Parse an openid provider response into an associative array. The resulting multidimensional
	 * array will reflect the namespace hierarchy contained in the key names.
	 * This is used instead of the to_string method of the openid_key_value_form helper to convert
	 * the string into an array specially formatted to facilitate the subsequent verification checks.
	 *
	 * @param  string  query string
	 * @return array   associative array containing the key value pairs from the parsed query string
	 */
	public static function to_associative_array($response = FALSE, $flat = FALSE)
	{
		$response_components = explode('&', $response);

		$response_params = array();

		foreach($response_components as $component)
		{
			$kv = explode('=', $component, 2);

			if(count($kv) == 2)
			{
				$kv[1] = urldecode($kv[1]);

				if ($flat)
				{
					$response_params[$kv[0]] = $kv[1];

					continue;
				}

				if(stripos($component, Openid::OPENID_NAMESPACE_ALIAS.'.') === 0)
				{
					if ( ! array_key_exists(Openid::OPENID_NAMESPACE_ALIAS, $response_params))
					{
						$response_params[Openid::OPENID_NAMESPACE_ALIAS] = array();
					}

					$key_segments = explode('.', $kv[0], 3);

					if ($key_segments[1] == 'ns')
					{
						if (count($key_segments) > 2)
						{
							$response_params[Openid::OPENID_NAMESPACE_ALIAS][$key_segments[1].'.'.$key_segments[2]] = $kv[1];
						}
						else
						{
							$response_params[Openid::OPENID_NAMESPACE_ALIAS]['ns'] = $kv[1];
						}
					}// Group extension params in their own array
					elseif (array_key_exists($key_segments[1], Openid::$supported_extensions))
					{
						if ( ! array_key_exists($key_segments[1], $response_params[Openid::OPENID_NAMESPACE_ALIAS]))
						{
							$response_params[Openid::OPENID_NAMESPACE_ALIAS][$key_segments[1]] = array();
						}

						$response_params[Openid::OPENID_NAMESPACE_ALIAS][$key_segments[1]][$key_segments[2]] = $kv[1];
					}
					else
					{
						$response_params[Openid::OPENID_NAMESPACE_ALIAS][$key_segments[1]] = $kv[1];
					}
				}
				else
				{
					// In an OpenID 1 response the claimed_id should be included as a query param on the
					// return_to url rather than as a query param on the actual url and is prepended with
					// 'openid1_' rather than 'openid.'
					if(trim($kv[0]) == 'openid1_claimed_id')
					{
						if ( ! array_key_exists(Openid::OPENID_NAMESPACE_ALIAS, $response_params))
						{
							$response_params[Openid::OPENID_NAMESPACE_ALIAS] = array();
						}

						if ( ! array_key_exists('claimed_id', $response_params[Openid::OPENID_NAMESPACE_ALIAS]))
						{
							$response_params[Openid::OPENID_NAMESPACE_ALIAS]['claimed_id'] = $kv[1];
						}
					}

					$response_params[$kv[0]] = $kv[1];
				}
			}
		}

		return $response_params;
	}

	/**
	 * Check if the response contained a valid mode setting
	 *
	 * @return boolean
	 */
	protected function valid_response_mode()
	{
		if ( ! array_key_exists('mode', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
			return FALSE;

		if ( ! in_array($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['mode'], Openid::$valid_response_modes))
			return FALSE;

		return TRUE;
	}

	/**
	 * Set the current openid namespace setting according to the one specified in the response
	 *
	 * @return void
	 */
	protected function set_namespace_according_to_response()
	{
		// Setting the Openid namespace will automatically set the openid_version accordingly
		// (see the set_authentication_fields method of the Openid base class)
		$this->ns = (array_key_exists('ns', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
				  ? $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['ns']
				  : Openid::OPENID_1_0_NAMESPACE_SIGNON;
	}

	/**
	 * Check that the return_to url in the response matches the current url
	 *
	 * @return boolean
	 */
	protected function check_return_to_url_matches_current_url()
	{
		$current_url_components = @parse_url(url::base().url::current(TRUE));

		$return_to_url_components = @parse_url($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['return_to']);

		if ( ! ($current_url_components['scheme']         == $return_to_url_components['scheme'] AND
				$current_url_components['host']           == $return_to_url_components['host'] AND
				trim($current_url_components['path'],'/') == trim($return_to_url_components['path'], '/')))
				return FALSE;

		@parse_str($current_url_components['query'], $current_query_params);

		@parse_str($return_to_url_components['query'], $return_to_query_params);

		// Any query params set in the return_to url must also be included in the current url query
		foreach ($return_to_query_params as $key => $value)
		{
			if ( ! array_key_exists($key, $current_query_params))
				return FALSE;

			if ($current_query_params[$key] != $value)
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Check that all fields are signed that are required to be signed under the Openid spec version used.
	 *
	 * @return boolean
	 */
	protected function check_required_signed_fields_are_listed_as_signed()
	{
		$signed_fields = explode(',', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['signed']);

		$required_signed_fields = ($this->openid_version < 2)
								? Openid::$required_signed_fields_for_valid_openid_1_response
								: Openid::$required_signed_fields_for_valid_openid_2_response;

		return (count(array_intersect($signed_fields, $required_signed_fields)) == count($required_signed_fields));
	}

	/**
	 * Check that all fields listed as signed in the signed fields list are actually present in the response
	 *
	 * @return boolean
	 */
	protected function check_all_signed_fields_are_included_in_response()
	{
		$signed_fields = explode(',', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['signed']);

		foreach ($signed_fields as $field)
		{
			if (strpos($field, '.') !== FALSE AND strpos($field, 'ns.') === FALSE)
			{
				$key = explode('.', $field, 2);

				if ( ! array_key_exists($key[0], $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
					return FALSE;

				if ( ! array_key_exists($key[1], $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS][$key[0]]))
					return FALSE;
			}
			else
			{
				if ( ! array_key_exists($field, $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
					return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Check that the settings in the response match the current those currently stored in the authentication fields
	 *
	 * @return boolean
	 */
	protected function check_response_fields_match_stored_fields()
	{
		foreach ($this->response_fields as $key => $value)
		{
			if (array_key_exists($key, $this->fields))
			{
				if ($this->$key !== $value)
					return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Check that the response nonce has not expired
	 *
	 * @return boolean
	 */
	protected function check_response_nonce_has_not_been_used()
	{
		if ( ! array_key_exists('response_nonce', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
		{
			$return_to_query_params = array();

			$return_to_query_string = @parse_url($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['return_to'], PHP_URL_QUERY);

			@parse_str($return_to_query_string, $return_to_query_params);

			if ( ! array_key_exists(KOHANA::config('openid.request.nonce_name').'_nonce', $return_to_query_params))
				return FALSE;

			$this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['response_nonce'] = $return_to_query_params[KOHANA::config('openid.request.nonce_name').'_nonce'];
		}

		$stored_associations_with_matching_nonce = Cache::instance()->find(md5($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['response_nonce']));

		// If one of the cached associations tagged with the current response_nonce also has an op_endpoint
		// that matches the op_endpoint specified in the current response_nonce then it is already used
		// and so the response is invalid.
		foreach ($stored_associations_with_matching_nonce as $association)
		{
			if ($association['op_endpoint'] == $this->op_endpoint)
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Select the endpoint that best matches the current openid authentication settings.
	 * (Only used if operating under stateless session_mode or loading of stored session failed)
	 *
	 * @return boolean
	 */
	protected function select_endpoint_that_best_matches_response()
	{
		$matching_endpoints = array();

		$all_endpoints = array($this->current_openid_service_endpoint) + $this->openid_service_endpoints;

		foreach ($all_endpoints as $endpoint)
		{

			if ($endpoint->claimed_id == $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['claimed_id'] AND
				$endpoint->identity == $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['identity'])
			{
				// Under OpenID 1 op_endpoint may not be included in the response
				// So we should only require the response op_endpoint to match if it's included
				if (array_key_exists('op_endpoint', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
				{
					if ($endpoint->op_endpoint == $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['op_endpoint'])
					{
						$op_endpoint = TRUE;
					}
					else
					{
						$op_endpoint = FALSE;
					}
				}
				else
				{
					$op_endpoint = TRUE;
				}
				// Under OpenID 1 namespcae may not be included in the response
				// So we should only require the response namespcae to match if it's included
				if (array_key_exists('ns', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
				{
					if ($endpoint->supports_openid_service_or_extension_type($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['ns']))
					{
						$ns = TRUE;
					}
					else
					{
						$ns = FALSE;
					}
				}
				else
				{
					$ns = TRUE;
				}

				if ($ns AND $op_endpoint)
				{
					array_push($matching_endpoints, $endpoint);
				}
			}
		}

		// No matches found
		if (empty($matching_endpoints))
			return FALSE;

		if (count($matching_endpoints) > 1)
		{
			// TODO add some way to further narrow down the list and select the best endpoint?
		}

		// Even if there's still more than one matching endpoint any of them will now probably do
		// so let's use the first one
		$this->current_openid_service_endpoint = $matching_endpoints[0];

		$this->set_authentication_fields($this->current_openid_service_endpoint->fields);

		return TRUE;
	}

	/**
	 * Ask the OpenID Provider to verify the sig instead of performing verification locally (which would
	 * require a record of the association fields that will not be present in stateless mode.
	 * (Only used if operating under stateless session_mode or loading of stored session failed)
	 *
	 * @return boolean
	 */
	protected function request_sig_verification_from_openid_provider()
	{
		$fields_to_post = $this->response_fields;

		$fields_to_post[Openid::OPENID_NAMESPACE_ALIAS]['mode'] = Openid::OPENID_REQUEST_MODE_CHECK_AUTHENTICATION;

		$KVF_post_data = openid_key_value_form::associative_array_to_string($fields_to_post);

		$this->request = $this->http_post($this->op_endpoint, $KVF_post_data);

		if ($this->request['status'] != 200)
		{
			$this->log('error', 'Openid_Response', 'request_sig_verification_from_openid_provider', 'server_response_http_code_not_200');

			return FALSE;
		}

		$response_fields = openid_key_value_form::string_to_array($this->response_body);

		$required_fields = ($this->openid_version < 2)
						 ? Openid::$required_fields_for_valid_check_authentication_openid_1_response
						 : Openid::$required_fields_for_valid_check_authentication_openid_2_response;

		// Check the response contained the required fields
		if (Openid::check_required_fields_set
		   ($response_fields, $required_fields) !== TRUE)
		{
			$this->log('error', 'Openid_Response', 'request_sig_verification_from_openid_provider', 'all_required_fields_not_set', array('fields' => $response_fields, 'required_fields' => $required_fields));

			return FALSE;
		}

		if (strtolower($response_fields['is_valid']) == 'true')
		{
			if (array_key_exists('invalidate_handle', $response_fields))
			{
				$this->delete_stored_association($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['claimed_id']);
			}

			return TRUE;
		}

		$this->log('error', 'Openid_Response', 'request_sig_verification_from_openid_provider', 'is_valid_equals_false');

		return FALSE;
	}

	/**
	 * Verify that the sig used to sign the response fields is valid according to the stored association settings
	 *
	 * @return boolean
	 */
	protected function verify_sig($response_querystring)
	{
		$response_fields = Openid_Response::to_associative_array($response_querystring, TRUE);

		$signed_fields = explode(',', $response_fields[Openid::OPENID_NAMESPACE_ALIAS.'.signed']);

		$kv_form_data = '';

		foreach ($signed_fields as $key)
		{
			$kv_form_data .= $key.':'.$response_fields[Openid::OPENID_NAMESPACE_ALIAS.'.'.$key]."\n";
		}

		// See the __get method of the openid base class for how the association_hash_type value is aquired
		$hash_algorithm = $this->association_hash_type;

		$op_sig = $response_fields[Openid::OPENID_NAMESPACE_ALIAS.'.sig'];

		$sig = base64_encode(hash_hmac($hash_algorithm, $kv_form_data, base64_decode($this->mac_key), TRUE));

		return ($op_sig === $sig);
	}

	/**
	 * Check that the security required under the current security level setting has been applied by the
	 * OpenID Provider. (see config/openid.php securit_level)
	 *
	 * @return boolean
	 */
	protected function check_required_security_applied()
	{
		// TODO check nist_auth_level - need to figure out how it relates to the policies before can impliment this!
		if (KOHANA::config('openid.security_level') >= 5)
		{
			if ( ! array_key_exists('pape', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
			{
				$this->log('error', 'Openid_Response', 'check_required_security_applied', 'pape_namespace_missing_from_response');

				return FALSE;
			}

			if ( ! array_key_exists('pape', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['auth_policies']))
			{
				$this->log('error', 'Openid_Response', 'check_required_security_applied', 'pape_missing_from_response_auth_policies');

				return FALSE;
			}

			if (stripos(Openid_Extension_Pape::PHISHING_RESISTANT, $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['pape']['auth_policies']) === FALSE)
			{
				$this->log('error', 'Openid_Response', 'check_required_security_applied', 'required_pape_phishing_resistant_policy_not_applied');

				return FALSE;
			}

			if (KOHANA::config('openid.security_level') >= 6)
			{
				if (stripos(Openid_Extension_Pape::MULTI_FACTOR, $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['pape']['auth_policies']) === FALSE)
				{
					$this->log('error', 'Openid_Response', 'check_required_security_applied', 'required_pape_multi_factor_policy_not_applied');

					return FALSE;
				}
			}

			if (KOHANA::config('openid.security_level') >= 7)
			{
				if (stripos(Openid_Extension_Pape::PHYSICAL_MULTI_FACTOR, $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['pape']['auth_policies']) === FALSE)
				{
					$this->log('error', 'Openid_Response', 'check_required_security_applied', 'required_pape_physical_multi_factor_policy_not_applied');

					return FALSE;
				};
			}
		}

		return TRUE;
	}

	/**
	 * Extract any attributes returned by the OpenID Provider in response to a request containing
	 * Sreg or Ax parameters.
	 *
	 * @return void
	 */
	protected function extract_supplied_attributes()
	{
		$ax_attributes = array();

		if (array_key_exists('ax', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
		{
			$ax_attributes = $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['ax'];
		}

		$sreg_attributes = array();

		if (array_key_exists('sreg', $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]))
		{
			$sreg_attributes = $this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['sreg'];
		}

		// Note: Attributes not listed in either the optional or required attributes arrays
		// in config/openid.php are ignored.

		$received_attributes = array_merge($sreg_attributes, $ax_attributes);

		$required_attributes = KOHANA::config('openid.user_attributes_required');

		foreach ($required_attributes as $attribute)
		{
			if (array_key_exists($attribute, $received_attributes))
			{
				$this->user_attributes[$attribute] = $received_attributes[$attribute];
			}
			else
			{
				$this->log('missing_required_attribute', 'Openid_Response', 'extract_supplied_attributes', '', $attribute);
			}
		}

		$optional_attributes = KOHANA::config('openid.user_attributes_optional');

		foreach ($optional_attributes as $attribute)
		{
			if (array_key_exists($attribute, $received_attributes))
			{
				$this->user_attributes[$attribute] = $received_attributes[$attribute];
			}
			else
			{
				$this->log('missing_optional_attribute', 'Openid_Response', 'extract_supplied_attributes', '', $attribute);
			}
		}
	}
}