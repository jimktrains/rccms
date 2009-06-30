<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Library class for working with xrds documents - extends XML.php
 *
 * $Id: XRDS.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class XRDS_Core extends XML {

	// XRD Namespace (used for OpenID 2.0 XRDS instead of the deprecated openID namespace)
	const XMLNS_XRD_2_0 = 'xri://$xrd*($v*2.0)';

	// XRDS Namespace
	const XMLNS_XRDS    = 'xri://$xrds';

	// Array of default supported namespaces
	public static $namespace_map = array
	(
		'xrds' => XRDS::XMLNS_XRDS,
		'xrd'  => XRDS::XMLNS_XRD_2_0
	);

	// Establish a lowest priority integer;
	// Let's take the upper integer limit: 2^31.
	// Highest priority is 0.
	const SERVICE_LOWEST_PRIORITY = 2147483647;

	// Populated by the parse method with xrds objects for each service node found in the xml
	protected $service_list = array();

	//
	const CONTENT_TYPE_XRDS_AND_XML = 'application/xrds+xml';
	const CONTENT_TYPE_XRD_AND_XML  = 'application/xrd+xml';
	const CONTENT_TYPE_TEXT_AND_XML = 'text/xml';

	// Used by Openid_Request to determine whether the
	// http response content type indicates that xrds
	// data was contained in the response
	public static $supported_content_types = array
	(
		XRDS::CONTENT_TYPE_XRDS_AND_XML,
		XRDS::CONTENT_TYPE_XRD_AND_XML,
		XRDS::CONTENT_TYPE_TEXT_AND_XML
	);

	const XRDS_LOCATION_HEADER = 'x-xrds-location';

	/**
	 * Create an instance of custom XML.
	 *
	 * @param   string    xml file url or xmlstring
	 * @return  XRDS class instance
	 */
	public static function factory($xml_string, $extra_ns_map = array())
	{
		return new XRDS($xml_string, $extra_ns_map);
	}

	/**
	 * @param   mixed   SimpleXMLElement or xml file path / url
	 * @return  void
	 */
	public function __construct($xml_string, $extra_ns_map = array())
	{
		parent::__construct($xml_string, array_merge(XRDS::$namespace_map, $extra_ns_map));
	}

	/**
	 * Overload xml load method in order to pass default xrds namespace map
	 *
	 * @param  mixed   SimpleXMLElement or xml file path / url
	 * @param  array   Associative array of namespaces (of the form Openid::$legacy_openid_xml_namespace_map)
	 * @return boolean
	 */
	public function load($xml_string, $extra_ns_map = array())
	{
		return parent::load($xml_string, array_merge(XRDS::$namespace_map, $extra_ns_map));
	}

	/**
	 * Get a node (or list of nodes) contained in the xrds by name or the loaded status of this instance or
	 * the service_list defiend by this document
	 *
	 * @param  string
	 * @return mixed
	 */
	public function __get($element)
	{
		if ($element == 'service_list')
			return $this->service_list;

		return parent::__get($element);
	}

	/**
	 * Creates the service list array using nodes from the XRDS document.
	 *
	 * @param  mixed   SimpleXMLElement or xml file path / url
	 * @param  array   Associative array of namespaces (of the form Openid::$legacy_openid_xml_namespace_map)
	 * @return boolean
	 */
	public function parse($xml_string = FALSE, $extra_ns_map = array())
	{
		if ($xml_string !== FALSE)
		{
			$this->load($xml_string, array_merge(XRDS::$namespace_map, $extra_ns_map));
		}

		if ($this->loaded === FALSE)
			return FALSE;

		if ( ! XRDS::valid($this))
			return FALSE;

		$xrd_nodes = $this->xpath('/xrds:XRDS[1]/xrd:XRD');

		// Set the xml content of this object to the last XRD node
		// As specified in the OpenID 2.0 spec - (just incase there's more than 1!).
		$this->xml = $xrd_nodes[count($xrd_nodes) - 1];

		$this->populate_service_list_with_xrds_services($extra_ns_map);

		if (empty($this->service_list))
			return FALSE;

		return TRUE;
	}

	/**
	 * Popultate the service_list with XRDS instances for each service node contained in this document.
	 *
	 * @param  array   Associative array of namespaces (of the form Openid::$legacy_openid_xml_namespace_map)
	 * @return void
	 */
	public function populate_service_list_with_xrds_services($extra_ns_map = array())
	{
		$this->service_list = array();

		// Thanks to the XML parent's __get method
		// $this->Service returns all the Service nodes in the XRDS XML
		foreach ($this->Service as $service_element)
		{
			// set service to an xrds object so the relevant xpath namespaces can be used later
			$service = XRDS::factory($service_element, $extra_ns_map);

			$priority = intval($service->attribute('priority'));

			if ($priority === NULL)
			{
				$priority = XRDS::SERVICE_LOWEST_PRIORITY;
			}

			if ( ! array_key_exists($priority, $this->service_list))
			{
				$this->service_list[$priority] = array();
			}

			array_push($this->service_list[$priority], $service);
		}
	}

	/**
	 * Retrieve a canonical id from this XRDS document
	 *
	 * @return mixed   False on failure or a canonical xri string
	 */
	public function get_canonical_id()
	{
		$canonical_id_nodes = $this->xpath('//xrd:CanonicalID');

		if (empty($canonical_id_nodes))
			return FALSE;

		return (string)$canonical_id_nodes[count($canonical_id_nodes) - 1];
	}

	/**
	 * Validate XRDS document
	 *
	 * @param   mixed   xml string or xml file url or simpleXMLElement instance
	 * @return  boolean
	 */
	public static function valid($xml)
	{
		if (get_class($xml) == 'XRDS')
		{
			$simplexml = $xml;
		}
		else if (is_string($xml) OR get_class($xml) == 'SimpleXMLElement')
		{
			$simplexml = XRDS::factory($xml);
		}
		else
		{
			return FALSE;
		}

		// Try to get root element.
		$root = $simplexml->xpath('/xrds:XRDS[1]');

		// If no root is found it must not be a valid xrds doc
		if ( ! $root)
			return FALSE;

		if (is_array($root))
		{
			$root = $root[0];
		}

		// Get array of attributes of root
		$attributes = $root->attributes();

		if (array_key_exists('xmlns:xrd', $attributes) AND $attributes['xmlns:xrd'] != XRDS::XMLNS_XRDS)
			return FALSE;

		if (array_key_exists('xmlns', $attributes) AND
			preg_match('/xri/', $attributes['xmlns']) AND
			$attributes['xmlns'] != XRDS::XMLNS_XRD_2_0)
			return FALSE;

		// Get the last XRD node.
		$xrd_nodes = $simplexml->xpath('/xrds:XRDS[1]/xrd:XRD');

		if ( ! $xrd_nodes)
			return FALSE;

		// If gets here then appears to be valid XRDS
		return TRUE;
	}

}
