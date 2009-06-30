<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Library Class for forming OpenID trust associations between an OpenID Provider and this Relying Party (consumer website).
 *
 * $Id: Openid_Association.php 2008-08-12 09:28:34 BST Atomless $
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
abstract class Openid_Association_Core extends Openid_Discovery {

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
	 * Attempt to form trust association with openid provider.
	 *
	 * @return boolean
	 */
	protected function associate()
	{
		// No point establishing trust association in stateless session mode.
		if ($this->session_mode == Openid::OPENID_STATELESS)
		{
			$this->log('error', 'Openid_Association', 'associate', 'no_associations_in_stateless_session_mode');

			return FALSE;
		}

		$association = $this->retrieve_stored_association();

		// If no valid association stored
		if ($association !== TRUE)
		{
			// Attempt to form a new trust association with the OpenID provider
			$association = $this->form_new_trust_association();
		}

		// If association failed iterate through the endpoints in the service endpoints list
		// untill a successfull association is made or all endpoints have been unsuccessfully tried.
		if ($association !== TRUE)
		{
			$this->log('error', 'Openid_Association', 'associate', 'failed_once');

			if ( ! empty($this->openid_service_endpoints))
			{
				// Get the next endpoint in the service endponts list
				$this->get_next_openid_service_endpoint();

				// and retry forming a trust association
				return $this->associate();
			}
			else
			{
				$this->log('error', 'Openid_Association', 'associate', 'failed_on_all_endpoints');

				return FALSE;
			}
		}

		// Add the successful association to the internal log
		$this->log('success', 'Openid_Association', 'associate', '', array('claimed_id' => $this->claimed_id, 'op_endpoint' => $this->op_endpoint));

		// Store association - this is overwritten after successfull response (see the complete_authentication
		// method of the Openid_Relying_Party class) when the response_nonce is added as a cache tag.
		$this->store_association();

		return TRUE;
	}

	/**
	 * Store an association in a kohana cache
	 *
	 * @param  string   cache tag
	 * @return void
	 */
	protected function store_association($tag_array = NULL)
	{
		Cache::instance()->set
		(
			// Cache id
			md5($this->claimed_id),
			// Data to be stored
			array
			(
				'claimed_id'  => $this->claimed_id,
				'op_endpoint' => $this->op_endpoint,
				// $this->association returns all the required fields in a valid association
				// via the __get method of the parent Openid class. No need to serialize this here
				// as the cache set method will do that automatically.
				'association' => $this->association
			),
			// Tags
			$tag_array,
			// Limit association lifetimes to the maximum limit set in the config
			min(KOHANA::config('openid.max_association_lifetime'), $this->expires_in + 30)
		);
	}

	/**
	 * Attempt to retrieve an association stored in a kohana cache that matches the current authentication settings
	 *
	 * @return boolean
	 */
	protected function retrieve_stored_association()
	{
		$cache = Cache::instance();

		$stored_association = $cache->get(md5($this->claimed_id));

		if ($stored_association === NULL)
		{
			$this->log('error', 'Openid_Association', 'retrieve_stored_association', 'cached_association_not_found. This is expected on first attempt at authentication for this user id with this OpenID Provider.', array('cache_name' => md5($this->claimed_id)));

			return FALSE;
		}

		if ( ! array_key_exists('op_endpoint', $stored_association))
		{
			$this->log('error', 'Openid_Association', 'retrieve_stored_association', 'cached_association_has_no_op_endpoint');

			return FALSE;
		}

		if ($stored_association['op_endpoint'] != $this->op_endpoint)
		{
			$this->log('error', 'Openid_Association', 'retrieve_stored_association', 'cached_association_has_wrong_op_endpoint');

			return FALSE;
		}

		// Validate ALL the association fields required for valid association
		if ($this->valid_association($stored_association['association']) !== TRUE)
		{
			$this->log('error', 'Openid_Association', 'retrieve_stored_association', 'cached_association_is_not_valid');

			$cache->delete(md5($this->claimed_id));

			return FALSE;
		}

		// Add the successful association to the internal log
		$this->log('success', 'Openid_Association', 'retrieve_stored_association', '', array('claimed_id' => $this->claimed_id, 'op_endpoint' => $this->op_endpoint));

		$this->set_authentication_fields($stored_association['association']);

		return TRUE;
	}


	/**
	 * Delete any association found in the kohana cache that has the passed claimed_id
	 *
	 * @param  string   claimed_id string
	 * @return void
	 */
	protected static function delete_stored_association($claimed_id)
	{
		Cache::instance()->delete(md5($claimed_id));
	}

	/**
	 * Negotiate a new trust association with the current OpenID Provider
	 *
	 * @return boolean
	 */
	protected function form_new_trust_association()
	{
		// Set the Openid base class field 'mode' to 'associate'
		$this->mode = Openid::OPENID_REQUEST_MODE_ASSOCIATE;

		if ($this->session_mode == Openid::OPENID_STATEFULL)
		{
			// This sets the diffie-hellman exchange keys to default values.
			// To set to custom values pass custom modulus and gen to get_exchange_keys method.
			$this->set_authentication_fields(diffie_hellman::get_exchange_keys());
		}

		if ($this->request_association() !== TRUE)
		{
			$this->log('error', 'Openid_Association', 'form_new_trust_association', 'request_failed');

			return FALSE;
		}

		$association_fields = openid_key_value_form::string_to_array($this->response_body);

		if (empty($association_fields['ns']))
		{
			$association_fields['ns'] = $this->ns;
		}

		$association_fields['assoc_issued'] = time();

		// Validate just the association response fields
		if ($this->valid_association($association_fields, FALSE) !== TRUE)
		{
			$this->log('error', 'Openid_Association', 'form_new_trust_association', 'response_fields_invalid');

			return FALSE;
		}

		// If mac key not returned then must be in encrypted mode so set mac_key to decrypted enc_mac_key
		if (empty($association_fields['mac_key']))
		{
			// Extract shared secret from association response
			$association_fields['mac_key'] = diffie_hellman::compute_mac_key
										   (
											$association_fields['dh_server_public'],
											$association_fields['enc_mac_key'],
											$association_fields['assoc_type'],
											$this->dh_consumer_private,
											$this->dh_modulus
										   );

			if ($association_fields['mac_key'] === FALSE)
			{
				$this->log('error', 'Openid_Association', 'form_new_trust_association', 'failed_to_extract_mac_key');

				return FALSE;
			}
		}

		$association_fields = array_merge($this->association, $association_fields);

		// Validate the complete association
		if ($this->valid_association($association_fields) !== TRUE)
		{
			$this->log('error', 'Openid_Association', 'form_new_trust_association', 'new_association_invalid');

			return FALSE;
		}

		$this->set_authentication_fields($association_fields);

		return TRUE;
	}

   /**
	* Request a new association with the current OpenID Provider
	*
	* @return boolean
	*/
	protected function request_association($attempts = 2)
	{
		$authentication_fields = $this->get_authentication_fields();

		$post_data = http_build_query($authentication_fields, '', '&');

		$this->request = $this->http_post($this->op_endpoint, $post_data);

		$attempts--;

		// Openid Provider may return a 400 error if they do not support the requested assoc_type or
		// the requested session_type - if so attempt to extract the supported types from the response
		// and repeat association request - number of attempts allowed determined by initial passed
		// value for $attempts.
		if (($this->curl_request_info['http_code'] == 400
		   OR stripos($this->response_body, 'error_code:') !== FALSE)
		   AND $attempts > 0)
		{
			$response_fields = openid_key_value_form::string_to_array($this->response_body);

			if (array_key_exists('ns', $response_fields))
			{
				// See Openid parent set_authentication_fields method -
				// throws exception if unsupported namespace.
				$this->ns = $response_fields['ns'];
			}

			if ( ! array_key_exists('error_code', $response_fields))
			{
				$this->log('error', 'Openid_Association', 'request_association', 'error_code_missing');

				return FALSE;
			}

			// Check if error_code is 'unsupported_type' and if so switch assoc and session types
			// to those specified in the response and send new association request.
			if (strtolower($response_fields['error_code']) == 'unsupported-type')
			{
				if ( ! (array_key_exists('assoc_type', $response_fields) AND array_key_exists('session_type', $response_fields)))
				{
					$this->log('error', 'Openid_Association', 'request_association', 'assoc_type_missing_from_response');

					return FALSE;
				}
				// Switch current assoc_type to the type specified in the response from the OpenID Provider
				$this->assoc_type = $response_fields['assoc_type'];

				// We should not allow Openid Provider (or response from any as yet untrusted external source!)
				// to set our session mode to Openid::OPENID_NO_ENCRYPTION.
				if (empty($response_fields['session_type']) OR
					strtolower($response_fields['session_type']) == Openid::OPENID_NO_ENCRYPTION)
				{
					// Comply with externally received request to switch to no-encryption mode
					// only if operating under HTTPS or security_level is 0!
					if (isset($_SERVER['HTTPS']) OR KOHANA::config('openid.security_level') == 0)
					{
						$this->session_type = Openid::OPENID_NO_ENCRYPTION;
					}
					else
					{
						// If the original association request specified a session_type of sha256
						// if they don't support that session type some OpenID Providers will return the
						// session_type as empty. In such cases if we're not using HTTPS or the security_level
						// is higher than 0 then rather than switch to no-encryption we should first try
						// the other encyption type instead.
						$this->session_type = ($this->session_type == Openid::OPENID_1_SESSION_TYPE)
											? Openid::OPENID_2_SESSION_TYPE
											: Openid::OPENID_1_SESSION_TYPE;
					}
				}
				elseif (in_array(strtoupper($response_fields['session_type']), Openid::$supported_session_types))
				{
					$this->session_type = $response_fields['session_type'];
				}

				return $this->request_association($attempts);
			}
		}

		if ($this->request['status'] != 200)
		{
			$this->log('error', 'Openid_Association', 'request_association', 'server_response_not_200');

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Validate association fields
	 *
	 * @param array    fields to check
	 * @param boolean  if true validation will check all fields listed in :
	 *                 Openid::$required_fields_for_valid_association
	 *                 if false will check all only fields listed in :
	 *                 Openid::$required_fields_for_valid_association_response
	 * @return boolean
	 */
	protected function valid_association($association_fields, $strict = TRUE)
	{
		// If session_type is blank set it to Openid::OPENID_NO_ENCRYPTION
		if (empty($association_fields['session_type']))
		{
			$association_fields['session_type'] = Openid::OPENID_NO_ENCRYPTION;
		}

		if ($strict)
		{
			$required_fields = Openid::$required_fields_for_valid_association;
		}
		else
		{
			// If validating in non-strict mode (checking just association response fields)
			// mac_key is required and enc_mac_key is not required
			$required_fields = ($association_fields['session_type'] == Openid::OPENID_NO_ENCRYPTION)
							 ? Openid::$required_fields_for_valid_association_response_no_encryption
							 : Openid::$required_fields_for_valid_association_response;
		}

		// Check all required fields are set
		if ( ! Openid::check_required_fields_set($association_fields, $required_fields))
		{
			$this->log('error', 'Openid_Association', 'valid', 'required_fields_not_set', array('fields' => $association_fields, 'required_fields' => $required_fields));

			return FALSE;
		}

		// Check that the association is still within it's specified lifetime
		if ( ! Openid_Association::check_not_expired($association_fields['assoc_issued'], $association_fields['expires_in']))
		{
			$this->log('error', 'Openid_Association', 'valid', 'expired');

			return FALSE;
		}

		// Check if session type supported
		if ( ! in_array($association_fields['session_type'], Openid::$supported_session_types))
		{
			$this->log('error', 'Openid_Association', 'valid', 'unsupported_session_type');

			return FALSE;
		}

		// Check if assoc_type supported
		if ( ! in_array($association_fields['assoc_type'], Openid::$supported_association_types))
		{
			$this->log('error', 'Openid_Association', 'valid', 'unsupported_assoc_type');

			return FALSE;
		}

		// Check if namespace supported
		if ( ! in_array($association_fields['ns'], Openid::$supported_openid_namespaces))
		{
			$this->log('error', 'Openid_Association', 'valid', 'unsupported_namespace');

			return FALSE;
		}


		return TRUE;
	}

	/**
	 * Check the association has not expired
	 *
	 * @param  string   time the association was issued
	 * @param  string   valid lifespan
	 * @return boolean
	 */
	protected static function check_not_expired($issued, $lifetime)
	{
		return (time() < ($issued + $lifetime - 30));
	}
}