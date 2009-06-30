<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Library class for working with XML - requires php's simpleXML
 *
 * $Id: XML.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class XML_Core {

	// SimpleXMLElement
	protected $simplexml;

	/**
	 * Create an instance of custom XML.
	 *
	 * @return  object
	 */
	public static function factory($xml_string, $namespace_map)
	{
		return new XML($xml_string, $namespace_map);
	}

	/**
	 * @param  mixed   SimpleXMLElement or xml file path / url
	 * @param  array   associative array of namespaces (of the form Openid::$legacy_openid_xml_namespace_map)
	 */
	public function __construct($xml_string, $namespace_map)
	{
		$this->load($xml_string, $namespace_map);
	}

	/**
	 * Get a node (or list of nodes) contained in the xml by name or the loaded status of this instance
	 *
	 * @param  string
	 * @return mixed  boolean or array of xml elements
	 */
	public function __get($element)
	{
		switch ($element)
		{
			case 'loaded':

				return (boolean)(get_class($this->simplexml) == 'SimpleXMLElement');

			break;

			default:

				$element_array = array();
				foreach ($this->simplexml->$element as $xml_element)
				{
					array_push($element_array, $xml_element);
				}

				return $element_array;
		}
	}

	/**
	 * Enables loading of xml using XML_instance->xml instead of load
	 *
	 * @return void
	 */
	public function __set($key, $val)
	{
		switch ($key)
		{
			case 'xml':

				$this->load($val);

			break;
		}
	}

	/**
	 * Retreive attributes by name
	 *
	 * @return mixed
	 */
	public function attribute($name)
	{
		foreach($this->simplexml->attributes() as $key => $val)
		{
			if ($key == $name)
				return (string) $val;
		}

		return NULL;
	}

	/**
	 * Proxy for the simplexml method asXML
	 *
	 * @return string   xml string
	 */
	public function as_xml()
	{
		return $this->simplexml->asXML();
	}

	/**
	 * Proxy for the simplexml method getNamespaces
	 *
	 * @return array
	 */
	public function get_namespaces($recursive = TRUE)
	{
		return $this->simplexml->getNamespaces($recursive);
	}

	/**
	 * Proxy for the simplexml method xpath
	 *
	 * @return array
	 */
	public function xpath($xpathquery)
	{
		return $this->simplexml->xpath($xpathquery);
	}

	/**
	 * Load xml from file or SimpleXMLElement into this->simplexml
	 *
	 * @param  mixed   SimpleXMLElement or xml file path / url
	 * @param  array   Associative array of namespaces (of the form Openid::$legacy_openid_xml_namespace_map)
	 * @return boolean
	 */
	public function load($xml_string, $namespace_map = array())
	{
		if (is_string($xml_string))
		{
			// Disable error reporting while loading the xml
			$ER = error_reporting(0);

			// Allow loading by filename or raw XML string.
			$this->simplexml = (is_file($xml_string) OR valid::url($xml_string))? simplexml_load_file($xml_string) : simplexml_load_string($xml_string);

			// Restore error reporting
			error_reporting($ER);
		}
		else if (get_class($xml_string) == 'SimpleXMLElement')
		{
			// Allow loading of a ready made SimpleXMLElement
			$this->simplexml = $xml_string;
		}

		if ($this->simplexml === FALSE)
			throw new Kohana_Exception('xml.failed_to_load', $xml_string);

		if (get_class($this->simplexml) == 'SimpleXMLElement' AND ! empty($namespace_map))
		{
			$this->register_xpath_namespaces($namespace_map);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Register a namespace with the SimpleXMLElement object.
	 * Proxy for the simplexml method registerXPathNamespace.
	 *
	 * @param  string   namespace alias
	 * @param  string   namespace url
	 * @return boolean
	 */
	public function register_xpath_namespace($prefix = FALSE, $namespace_url = FALSE)
	{
		if ($prefix === FALSE OR $namespace_url === FALSE)
			throw new Kohana_Exception('xml.prefix_and_namespace_required', KOHANA::debug(array('prefix' => $prefix, 'url' => $namespace_url)));

		if ( ! valid::url($namespace_url))
			throw new Kohana_Exception('xml.namespace_invalid_url', KOHANA::debug(array('prefix' => $prefix, 'url' => $namespace_url)));

		$this->simplexml->registerXPathNamespace($prefix, $namespace_url);
	}

	/**
	 * Register a list of namespaces with the SimpleXMLElement object.
	 *
	 * @param  array   Associative array of namespaces (of the form Openid::$legacy_openid_xml_namespace_map)
	 * @return boolean
	 */
	public function register_xpath_namespaces($namespace_map = array())
	{
		foreach ($namespace_map as $prefix => $url)
		{
			$this->register_xpath_namespace($prefix, $url);
		}
	}


}