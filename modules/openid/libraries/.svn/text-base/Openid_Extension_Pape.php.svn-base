<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Library Class for applying the Pape OpenID Security Extension policies.
 *
 * $Id: Openid_Extension_Pape.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Openid_Extension_Pape_Core {

	// Namespaces
	const NAMESPACE = 'http://specs.openid.net/extensions/pape/1.0';

	const NAMESPACE_ALIAS = 'pape';

	public static $supported_namespaces = array
	(
		Openid_Extension_Pape::NAMESPACE
	);

	// Policies
	const PHISHING_RESISTANT = 'http://schemas.openid.net/pape/policies/2007/06/phishing-resistant';

	const MULTI_FACTOR = 'http://schemas.openid.net/pape/policies/2007/06/multi-factor';

	const PHYSICAL_MULTI_FACTOR = 'http://schemas.openid.net/pape/policies/2007/06/multi-factor-physical';

	public static $supported_policies = array
	(
		Openid_Extension_Pape::PHISHING_RESISTANT,
		Openid_Extension_Pape::MULTI_FACTOR,
		Openid_Extension_Pape::PHYSICAL_MULTI_FACTOR
	);

	protected $fields = array
	(
		'ns' => Openid_Extension_PAPE::NAMESPACE,
		// Integer value greater than or equal to zero in seconds.
		// The number of seconds to allow for the user authentication with the OpenID Provider.
		// When this limit is passed the OpenID Provider should authenticate the request and return the
		// User agent to this Relying Party at the return_to URL where the response verification should fail
		// the authentication.
		// This default setting is over-written by the setting in config/openid.php
		'max_auth_age' => 270,
		// Array of zero or more authentication policy URIs that the OpenID Provider SHOULD conform to when
		// authenticating the user. If multiple policies are requested, the OpenID Provider SHOULD satisfy
		// as many as it can. This array is converted to a space separated string by the get_fields method
		// prior to sending.
		'preferred_auth_policies' => array()
	);

	protected $response_fields = array
	(
		'auth_time'       => FALSE,
		'auth_policies'   => FALSE,
		'nist_auth_level' => FALSE,
	);

	// Openid_Extension_Pape singleton
	private static $instance;

	/**
	 * Singleton instance of Openid_Extension_Pape.
	 */
	public static function instance()
	{
		// Create the instance if it does not exist
		(self::$instance === NULL) and self::$instance = new Openid_Extension_Pape;

		return self::$instance;
	}

	/**
	 * Create an instance of Openid_Extension_Sreg.
	 *
	 * @param   array - openid fields contained in parent Openid class
	 * @return  object
	 */
	public static function factory($policies = array())
	{
		return new Openid_Extension_Pape($policies);
	}

	/**
	 * Constructor.
	 *
	 * @return  void
	 */
	public function __construct($policies = array())
	{
		$config = KOHANA::config('openid.extensions.pape');

		$this->max_auth_age = $config['max_auth_age'];

		$this->preferred_auth_policies = $policies;
	}

	/**
	 *
	 */
	public function __get($key)
	{
		if (array_key_exists($key, $this->fields))
		{
			return $this->fields[$key];
		}
		else
		{
			throw new Kohana_Exception('openid.pape.unsupported_field', $key);
		}
	}

	/**
	 * Get a formatted associative array that will be parsed into a key value (http query) format
	 * by the Openid_Request class prior to sending to the OpenID Provider.
	 *
	 * @return array   associative array of Pape settings
	 */
	public function get_fields()
	{
		return array
		(
			'ns.'.Openid_Extension_Pape::NAMESPACE_ALIAS => $this->fields['ns'],

			Openid_Extension_Pape::NAMESPACE_ALIAS.'.max_auth_age' => $this->fields['max_auth_age'],

			Openid_Extension_Pape::NAMESPACE_ALIAS.'.preferred_auth_policies' => implode(" ", $this->fields['preferred_auth_policies'])
		);
	}

	/**
	 *
	 */
	public function __set($key, $value)
	{
		if (array_key_exists($key, $this->fields))
		{
			$this->set_fields(array($key => $value));
		}
		else
		{
			throw new Kohana_Exception('openid.extensions.pape.unsupported_field', $key);
		}
	}

	/**
	 * Allow setting of certain protected variables and ensure the new settings are valid.
	 *
	 * @param  array   associative array of Pape settings
	 * @return void
	 */
	public function set_fields($fields = array())
	{
		foreach ($fields as $key => $value)
		{
			switch ($key)
			{
				case 'ns':

					if ( ! in_array($value, Openid_Extension_Pape::$supported_namespaces))
						throw new Kohana_Exception('openid.extensions.pape.unsupported_namespace', $value);

					$this->fields[$key] = $value;

				break;
				case 'preferred_auth_policies':

					if (is_array($value))
					{
						$this->fields['preferred_auth_policies'] = array();

						foreach($value as $policy_schema_url)
						{
							$this->add_preferred_auth_policy($policy_schema_url);
						}
					}
					else
					{
						$this->add_preferred_auth_policy($value);
					}

				break;
				case 'max_auth_age':

					$this->fields['max_auth_age'] = $value;

				break;
				default:

					throw new Kohana_Exception('openid.extensions.pape.unsupported_field', $value);
			}
		}
	}

	/**
	 * Add a Pape policy url to the policy urls listed in preferred_auth_policies
	 *
	 * @param string   Pape policy url string
	 */
	public function add_preferred_auth_policy($policy_schema_url)
	{
		if ( ! in_array($policy_schema_url, Openid_Extension_Pape::$supported_policies))
			throw new Kohana_Exception('openid.extensions.pape.unsupported_policy', $policy_schema_url);

		if ( ! in_array($policy_schema_url, $this->fields['preferred_auth_policies']))
		{
			array_push($this->fields['preferred_auth_policies'], $policy_schema_url);
		}
	}
}