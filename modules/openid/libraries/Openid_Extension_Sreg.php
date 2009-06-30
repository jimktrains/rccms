<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Library Class for user attribute exchange using the OpenID Simple Registration Extension.
 *
 * $Id: Openid_Extension_Sreg.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Openid_Extension_Sreg_Core {

	const NAMESPACE_OPENID_1_0 = 'http://openid.net/sreg/1.0';

	const NAMESPACE_OPENID_1_1 = 'http://openid.net/extensions/sreg/1.1';

	const NAMESPACE_ALIAS      = 'sreg';

	public static $supported_namespaces = array
	(
		Openid_Extension_Sreg::NAMESPACE_OPENID_1_0,
		Openid_Extension_Sreg::NAMESPACE_OPENID_1_1
	);

	// List of user data/attributes that can be requested using SREG
	public static $supported_user_attribute_fields = array
	(
		// UTF-8 string
		'fullname',
		// UTF-8 string
		'nickname',
		// Date of Birth : YYYY-MM-DD
		'dob',
		'email',
		// 'M' or 'F'
		'gender',
		// Conforming to user's local postcode format
		'postcode',
		// Country Code : listed here http://www.iso.org/iso/country_codes
		'country',
		// Language Code : listed here http://www.w3.org/WAI/ER/IG/ert/iso639.htm
		'language',
		// ASCII string : listed here http://www.twinsun.com/tz/tz-link.htm
		'timezone'
	);

	protected $fields = array
	(
		'ns'         => Openid_Extension_Sreg::NAMESPACE_OPENID_1_1,
		// Array (converted to comma-separated list prior to sending) of user attribute field names
		// to request from the OpenID Provider, which if not returned will prevent the Relying Party
		// from completing the registration.
		'required'   => array(),
		// Array (converted to comma-separated list of field names to request from the OpenID Provider
		// but if not returned will not prevent the registration/authentication from completing.
		// The field names are those that are specified in the Response Format (Response Format),
		// with the "openid.sreg." prefix removed.
		'optional'   => array(),
		// The URL of the Relying Party's privacy policy informing the end user how any personal data will
		// be used and protected.
		'policy_url' => ''
	);

	/**
	 * Create an instance of Openid_Extension_Sreg.
	 *
	 * @param   array - openid fields contained in parent Openid class
	 *
	 * @return  object
	 */
	public static function factory($required = array(), $optional = array())
	{
		return new Openid_Extension_Sreg($required, $optional);
	}

	/**
	 * Constructor.
	 *
	 * @return  void
	 */
	public function __construct($required = array(), $optional = array())
	{
		$config = KOHANA::config('openid.extensions.sreg');

		$this->set_fields(array_merge($config, array('required' => $required, 'optional' => $optional)));
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
			throw new Kohana_Exception('openid.extensions.sreg.unsupported_attribute', $key);
		}
	}

	/**
	 * Get a formatted associative array that will be parsed into a key value (http query) format
	 * by the Openid_Request class prior to sending to the OpenID Provider.
	 *
	 * @return array   associative array of Sreg settings
	 */
	public function get_fields()
	{
		return array
		(
			'ns.'.Openid_Extension_Sreg::NAMESPACE_ALIAS => $this->fields['ns'],

			Openid_Extension_Sreg::NAMESPACE_ALIAS.'.required' => implode(",", $this->fields['required']),

			Openid_Extension_Sreg::NAMESPACE_ALIAS.'.optional' => implode(",", $this->fields['optional']),

			Openid_Extension_Sreg::NAMESPACE_ALIAS.'.policy_url' => $this->fields['policy_url']
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
			throw new Kohana_Exception('openid.extensions.sreg.unsupported_attribute', $key);
		}
	}

	/**
	 * Allow setting of certain protected variables and ensure the new settings are valid.
	 *
	 * @param  array   associative array of Sreg settings
	 * @return void
	 */
	public function set_fields($fields = array())
	{
		foreach ($fields as $key => $value)
		{
			switch ($key)
			{
				case 'ns':

					if ( ! in_array($value, Openid_Extension_Sreg::$supported_namespaces))
						throw new Kohana_Exception('openid.extensions.sreg.unsupported_namespace', $value);

					$this->fields[$key] = $value;

				break;
				case 'required' :
				case 'optional' :

					if (is_array($value))
					{
						foreach($value as $val)
						{
							if ( ! Openid_Extension_Sreg::valid_attribute($val))
								throw new Kohana_Exception('openid.extensions.sreg.unsupported_attribute', $key.' : '.$val);

							if (Openid_Extension_Sreg::valid_attribute($val))
							{
								array_push($this->fields[$key], $val);
							}
						}
					}
					else
					{
						if ( ! Openid_Extension_Sreg::valid_attribute($value))
							throw new Kohana_Exception('openid.extensions.sreg.unsupported_attribute', $key.' : '.$value);

						if (Openid_Extension_Sreg::valid_attribute($value))
						{
							array_push($this->fields[$key], $value);
						}
					}

					$this->remove_duplicate_attributes();

				break;
				case 'policy_url':

					if ( ! valid::url($value))
						throw new Kohana_Exception('openid.extensions.sreg.invalid_policy_url', $value);

					$this->fields[$key] = $value;

				break;
				default:

					throw new Kohana_Exception('openid.extensions.sreg.unsupported_attribute', $value);
			}
		}
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
	 * @return void
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
	 * 				    Openid_Extension_Sreg::$supported_user_attribute_fields.
	 */
	protected function valid_attribute($attribute)
	{
		return in_array($attribute, Openid_Extension_Sreg::$supported_user_attribute_fields);
	}
}