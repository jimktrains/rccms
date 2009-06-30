<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Library Class for handling OpenID authentication.
 *
 * $Id: Openid_Relying_Party.php 2008-08-12 09:28:34 BST Atomless $
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
abstract class Openid_Relying_Party_Core extends Openid_Response {

	/**
	 * Constructor.
	 *
	 * @param   array - openid fields to be set in the base Openid.php class
	 * @return  void
	 */
	public function __construct($fields = array())
	{
		parent::__construct($fields);
	}

   /**
	* start the authentication process for a passed openID identity
	*
	* @param   string    claimed_id string
	* @param   boolean   whether to request the extra user attributes defined in config/openid.php
	* @return  boolean
	*/
	public function start_authentication($claimed_id = FALSE, $request_attributes = FALSE)
	{
		// Clear any openid session settings from a previous authentication
		$this->clear_session();

		// See the set_authentication_fields method in the base Openid.php class
		$this->claimed_id = $claimed_id;

		// If $claimed_id was deemed to be invalid $this->claimed_id will still be unset
		if ($this->claimed_id === '')
			return FALSE;

		// *** DISCOVER ***
		if ($this->discover() !== TRUE)
			return FALSE;

		// *** ASSOCIATE ***
		if ($this->associate() !== TRUE)
		{
			$this->session_mode = Openid::OPENID_STATELESS;
		}

		// *** EXTENSIONS ***
		if ($request_attributes)
		{
			// Use available OpenID extensions to aquire user attributes from OpenID Provider
			$this->add_attribute_extension
			(
				 KOHANA::config('openid.user_attributes_required'),
				 KOHANA::config('openid.user_attributes_optional')
			);
		}

		// Use available OpenID extensions for increased security
		// (added according to security_level setting in config/openid.php)
		$this->add_security_extensions();

		return TRUE;
	}

   /**
	* Verify the response params contained in the response from the OpenID Provider after returning the
	* user to the return_to url.
	*
	* @return boolean
	*/
	public function complete_authentication()
	{
		// Load the settings stored prior to the redirect to the OpenID Provider
		// These will be used to verify the response params
		if ($this->load_from_session() === FALSE)
		{
			$this->session_mode = Openid::OPENID_STATELESS;
		}

		// Subject the response to extensive security and validity checks
		if ($this->verify_response($_SERVER['QUERY_STRING']) !== TRUE)
			return FALSE;

		if ($this->valid_association($this->association) === TRUE)
		{
			// Overwrite the cached association for this claimed_id adding the response nonce as a Cache tag to
			// prevent a further association with duplicate nonce being accepted from the same provider
			// (security counter measure against replay attacks)
			$tags = array(md5($this->response_fields[Openid::OPENID_NAMESPACE_ALIAS]['response_nonce']), md5($this->op_endpoint));

			$this->store_association($tags);
		}

		$this->save_to_session();

		return TRUE;
	}
}