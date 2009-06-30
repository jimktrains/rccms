<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Library Class for storing Openid Service Endpoints.
 *
 * $Id: Openid_service_Endpoint.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

class Openid_Service_Endpoint_Core {

	protected $_fields = array
	(
		'claimed_id'  => FALSE,
		'identity'	  => FALSE,
		'display_id'  => FALSE,
		// openid provider server url
		'op_endpoint' => FALSE,
		'type_uris'   => array()
	);

	/**
	 * Create an instance of Openid_Service_Endpoint.
	 *
	 * @return  Openid_Service_Endpoint class instance
	 */
	public static function factory($fields = array())
	{
		return new Openid_Service_Endpoint($fields);
	}

	/**
	 * Create an instance of Openid_Service_Endpoint.
	 *
	 * @return  void
	 */
	public function __construct($fields = array())
	{
		if ( ! empty($fields))
		{
			$this->set_fields($fields);
		}
	}

	/**
	 *
	 */
	public function __get($key)
	{
		switch ($key)
		{
			case 'display_id':

				return ($this->display_id === FALSE)? $this->claimed_id
													: $this->fields['display_id'];

			break;

			case 'fields':

				return $this->_fields;

			break;

			default:

				return (array_key_exists($key, $this->_fields))? $this->_fields[$key] : NULL;
		}
	}

	/**
	 *
	 */
	public function __set($key, $val)
	{
		switch ($key)
		{
			default:
				if (array_key_exists($key, $this->_fields) AND ! empty($val))
				{
					$this->_fields[$key] = $val;
				}
		}
	}

   /**
	* Allow setting of protected variables
	*
	* @param   array
	*/
	public function set_fields($fields = array())
	{
		foreach ($fields as $key => $val)
		{
			$this->_fields[$key] = $val;
		}
	}

	/**
	 * Determine whether or not this endpoint is a provider type endpoint
	 *
	 * @return boolean
	 */
	public function is_openid_provider_type()
	{
		return in_array(Openid::OPENID_2_0_NAMESPACE_SERVER, $this->type_uris);
	}

	/**
	 * Determine whether this endpoint supports the passed type uri
	 *
	 * @return boolean
	 */
	public function supports_openid_service_or_extension_type($type_uri)
	{
		// If type_uris does not contain Openid::OPENID_2_0_NAMESPACE
		// but does contain either Openid::OPENID_2_0_NAMESPACE_SERVER or Openid::OPENID_2_0_NAMESPACE_SIGNON
		// then return TRUE
		if ($type_uri == Openid::OPENID_2_0_NAMESPACE AND ! in_array($type_uri, $this->type_uris))
		{
			if (in_array(Openid::OPENID_2_0_NAMESPACE_SERVER, $this->type_uris) OR
				in_array(Openid::OPENID_2_0_NAMESPACE_SIGNON, $this->type_uris))
			{
				return TRUE;
			}
		}
		// Does this endpoint support the passed type?
		return (in_array($type_uri, $this->type_uris));
	}

	/**
	 * Get the version of OpenID required for this endpoint according to it's type uris
	 */
	public function get_required_openid_version()
	{
		foreach ($this->type_uris as $namespace)
		{
			$version = Openid::get_openid_version_from_namespace($namespace);

			if ($version !== FALSE)
				return $version;
		}

		return 1.0;
	}

}