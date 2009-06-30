<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Library Class for user attribute exchange using the OpenID Attribute Exchange Extension.
 *
 * $Id: Openid_Extension_Ax.php 2008-08-12 09:28:34 BST Atomless $
 *
 * TODO : Sreg attribute exchange has been tested and is working well but as very few OpenID Providers
 * have enabled Ax so far - Ax exchange still needs to be more thoroughly tested - particularly
 * passing attributes back to the Provider.
 * TODO : Once requested Ax attributes may be 'pushed' through at a later date of the OpenID Provider's
 * choosing - the facsility to receive such delayed 'pushed' attributes has not yet been implimented in
 * this module.
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Openid_Extension_Ax_Core {

	const NAMESPACE = 'http://openid.net/srv/ax/1.0';

	const NAMESPACE_ALIAS = 'ax';

	public static $supported_namespaces = array
	(
		Openid_Extension_Ax::NAMESPACE
	);

	// ax.mode used to request user attributes are returned in the response from the OpenID Provider
	const FETCH_MODE = 'fetch_request';
	// ax.mode used to send user attributes to the OpenID Provider
	const STORE_MODE = 'store_request';

	public static $supported_modes = array
	(
		Openid_Extension_Ax::FETCH_MODE,
		Openid_Extension_Ax::STORE_MODE
	);

	// List of user data/attributes that can be requested using Ax
	// the keys of this array are the attribute label or alias and the values the Type URI
	// for the schema for that alias.
	// Ax actually potentially supports *any* attribute - to see how you could add
	// support for other AX attributes or even add your own see : http://www.axschema.org/types/
	public static $supported_user_attribute_fields = array
	(
		// UTF-8 string
		'nickname' => 'http://axschema.org/namePerson/friendly',
		// UTF-8 string
		'fullname' => 'http://axschema.org/namePerson',
		// 'M' or 'F'
		'gender'   => 'http://axschema.org/person/gender',
		// Date of Birth : YYYY-MM-DD
		'dob'      => 'http://axschema.org/birthDate',
		'email'    => 'http://axschema.org/contact/email',

		// Address - Home
		// UTF-8 string
		'address'  => 'http://axschema.org/contact/postalAddress/home',
		// UTF-8 string
		'address2' => 'http://axschema.org/contact/postalAddressAdditional/home',
		// UTF-8 string
		'city'     => 'http://axschema.org/contact/city/home',
		// UTF-8 string
		'state'    => 'http://axschema.org/contact/state/home',
		// Conforming to user's local postcode format
		'postcode' => 'http://axschema.org/contact/postalCode/home',
		// Country Code : listed here http://www.iso.org/iso/country_codes
		'country'  => 'http://axschema.org/contact/country/home',

		// Address - Business
		// UTF-8 string
		'business_address'  => 'http://axschema.org/contact/postalAddress/business',
		// UTF-8 string
		'business_address2' => 'http://axschema.org/contact/postalAddressAdditional/business',
		// UTF-8 string
		'business_city'     => 'http://axschema.org/contact/city/business',
		// UTF-8 string
		'business_state'    => 'http://axschema.org/contact/state/business',
		// Conforming to user's local postcode format
		'business_postcode' => 'http://axschema.org/contact/postalCode/business',
		// Country Code : listed here http://www.iso.org/iso/country_codes
		'business_country'  => 'http://axschema.org/contact/country/business',

		// Language Code : listed here http://www.w3.org/WAI/ER/IG/ert/iso639.htm
		'language' => 'http://axschema.org/pref/language',
		// ASCII string : listed here http://www.twinsun.com/tz/tz-link.htm
		'timezone' => 'http://axschema.org/pref/timezone'
	);

	protected $fields = array
	(
		'ns'           => Openid_Extension_Ax::NAMESPACE,
		'mode'         => Openid_Extension_Ax::FETCH_MODE,
		// Array (converted to comma-separated list prior to sending) of user attribute field names
		// to request from the OpenID Provider, which if not returned will prevent the Relying Party
		// from completing the registration.
		// All field names MUST have a corresponding type URI for the schema see: http://www.axschema.org/types/
		'required'     => array(),
		// Array (converted to comma-separated list prior to sending) of user attribute field names to
		// request from the OpenID Provider. If not returned will NOT prevent the Relying Party
		// from completing the registration.
		// All field names MUST have a corresponding type URI for the schema see: http://www.axschema.org/types/
		'optional' => array(),
		// Associative array of attributes to send to the OpenID Provider using an AX Store request
		// all array keys must have a corresponding key in Openid_Extension_Ax::$supported_user_attribute_fields
		'for_storage'  => array(),
		// The URL to which the OpenId Provider can re-post the fetch-response at some time after the
		// initial request.
		// The default setting here is overwritten by the setting in config/openid.php
		// (This MUST match the return_to URL specified in the openID request which is also set in the config file)
		'update_url'   => '',
	);

	/**
	 * Create an instance of Openid_Extension_Ax.
	 *
	 * @param   array - openid fields contained in parent Openid class
	 *
	 * @return  object
	 */
	public static function factory($required = array(), $optional = array(), $for_storage = array())
	{
		return new Openid_Extension_Ax($required, $optional, $for_storage);
	}

	/**
	 * Constructor.
	 *
	 * @return  void
	 */
	public function __construct($required = array(), $optional = array(), $for_storage = array())
	{
		$config = KOHANA::config('openid.extensions.ax');

		$this->set_fields($config);

		$this->required = $required;

		$this->optional = $optional;

		$this->for_storage = $for_storage;
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
			throw new Kohana_Exception('openid.extensions.ax.no_such_field', $key);
		}
	}

	/**
	 * Formats an associative array that will be parsed into a key value (http query) format
	 * by the Openid_Request class prior to sending to the OpenID Provider.
	 *
	 * @return array   associative array of Ax settings
	 */
	public function get_fields()
	{
		$fields = array();

		$fields['ns.'.Openid_Extension_Ax::NAMESPACE_ALIAS] = $this->fields['ns'];

		$fields[Openid_Extension_Ax::NAMESPACE_ALIAS.'.mode'] = $this->fields['ns'];

		if ($this->mode === Openid_Extension_Ax::STORE_MODE)
		{
			foreach ($this->attributes_to_store as $key => $value)
			{
				$fields[Openid_Extension_Ax::NAMESPACE_ALIAS.'.type.'.$key] = Openid_Extension_Ax::$supported_user_attribute_fields[$key];

				$fields[Openid_Extension_Ax::NAMESPACE_ALIAS.'.value.'.$key] = $value;
			}
		}
		else
		{
			foreach ($this->fields['required'] as $field)
			{
				$fields[Openid_Extension_Ax::NAMESPACE_ALIAS.'.type.'.$field] = Openid_Extension_Ax::$supported_user_attribute_fields[$field];
			}

			foreach ($this->fields['optional'] as $field)
			{
				$fields[Openid_Extension_Ax::NAMESPACE_ALIAS.'.type.'.$field] = Openid_Extension_Ax::$supported_user_attribute_fields[$field];
			}

			$fields[Openid_Extension_Ax::NAMESPACE_ALIAS.'.required']     = implode(",", $this->fields['required']);

			$fields[Openid_Extension_Ax::NAMESPACE_ALIAS.'.if_available'] = implode(",", $this->fields['optional']);

			if ( ! empty($this->fields['update_url']))
			{
				$fields[Openid_Extension_Ax::NAMESPACE_ALIAS.'.update_url'] = $this->fields['update_url'];
			}
		}

		return $fields;
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
			throw new Kohana_Exception('openid.extensions.ax.unsupported_attribute', $key);
		}
	}

	/**
	 * Allow setting of certain protected variables and ensure the new settings are valid.
	 *
	 * @param  array   associative array of Ax settings
	 * @return void
	 */
	protected function set_fields($fields = array())
	{
		foreach ($fields as $key => $value)
		{
			switch ($key)
			{
				case 'mode':

					if ( ! in_array($value, Openid_Extension_Ax::$supported_modes))
						throw new Kohana_Exception('openid.extensions.ax.unsupported_mode', $value);

				break;
				case 'ns':

					if ( ! in_array($value, Openid_Extension_Ax::$supported_namespaces))
						throw new Kohana_Exception('openid.extensions.ax.unsupported_namespace', $value);

					$this->fields[$key] = $value;

				break;
				case 'required' :
				case 'optional' :

					if (is_array($value))
					{
						foreach($value as $val)
						{
							if ( ! Openid_Extension_Ax::valid_attribute($val))
								throw new Kohana_Exception('openid.extensions.ax.unsupported_attribute', $key.' : '.$val);

							if (Openid_Extension_Ax::valid_attribute($val))
							{
								array_push($this->fields[$key], $val);
							}
						}
					}
					else
					{
						if ( ! Openid_Extension_Ax::valid_attribute($value))
							throw new Kohana_Exception('openid.extensions.ax.unsupported_attribute', $key.' : '.$value);

						if (Openid_Extension_Ax::valid_attribute($value))
						{
							array_push($this->fields[$key], $value);
						}
					}

					// Ensure that no attributes are duplicated in both required and optional arrays
					$this->remove_duplicate_attributes();

				break;
				case 'for_storage':

					foreach($value as $attribute => $val)
					{
						if ( ! Openid_Extension_Ax::valid_attribute($val))
							throw new Kohana_Exception('openid.extensions.ax.unsupported_attribute', $attribute.' : '.$val);

						$this->fields['for_storage'][$attribute] = $val;
					}

					$this->fields['for_storage'] = array_unique($this->fields['for_storage']);

				break;
				case 'update_url':

					if ( ! valid::url($value))
						throw new Kohana_Exception('openid.extensions.ax.invalid_url', $key.' : '.$value);

					$this->fields[$key] = $value;

				break;
				default:

					throw new Kohana_Exception('openid.extensions.ax.unsupported_attribute', $key.' : '.$value);
			}
		}
	}

	/**
	 * Add a user attribute to the Ax for_storage field to be later sent to the OpenID Provider
	 *
	 * @param  string   attribute key
	 * @param  string   attribute value
	 * @return void
	 */
	public function add_user_attribute_for_storage($key, $value)
	{
		$this->set_fields('for_storage', array($key => $value));
	}

	/**
	 * Add a user attribute to the required array to be later requested from the OpenID Provider
	 *
	 * @param  string   attribute key
	 * @return void
	 */
	public function add_user_attribute_to_required($key)
	{
		// When set_fields gets passed a value for required that is not an array it adds it to the end of
		// the existing list.
		$this->required = $key;
	}

	/**
	 * Add a user attribute to the optional array to be later requested from the OpenID Provider
	 *
	 * @param  string   attribute key
	 */
	public function add_user_attribute_to_optional($key)
	{
		// When set_fields gets passed a value for optional that is not an array it adds it to the end of
		// the existing list.
		$this->optional = $key;
	}

	/**
	 * Ensure that no attributes are listed in both the required list AND the optional list.
	 * Attributes in the required list will take precedence over those listed in the optional
	 * list, so when duplicates are found they will be removed from the optional list but will
	 * remain in the required list.
	 *
	 * @return void
	 */
	protected function remove_duplicate_attributes()
	{
		$this->fields['optional'] = array_unique($this->fields['optional']);

		$this->fields['required'] = array_unique($this->fields['required']);

		$this->fields['optional'] = array_diff($this->fields['optional'], $this->fields['required']);
	}

	/**
	 * Ensure only supported attributes are added to the required, optional or for_storage lists.
	 *
	 * @param string    attribute name
	 * @return boolean  whether the passed atribute name is a supported field listed in:
	 * 				    Openid_Extension_Ax::$supported_user_attribute_fields.
	 */
	protected static function valid_attribute($attribute)
	{
		return array_key_exists($attribute, Openid_Extension_Ax::$supported_user_attribute_fields);
	}
}