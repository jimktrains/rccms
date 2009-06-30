<?php defined('SYSPATH') or die('No direct script access.');
/**
 * helper class for working with xrds service elements
 *
 * $Id: openid_services_xrds.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class openid_services_xrds_Core {

	// This match mode means a given service must match ALL filters to
	// be returned by the openid_services_xrds::filtered_services() method.
	const MATCH_ALL = 'match all filters';

	// This match mode means a given service must match ANY filters (at
	// least one) to be returned by the openid_services_xrds::filtered_services() method.
	const MATCH_ANY = 'match any filter';

	// List of valid filter modes
	public static $valid_filter_modes = array
	(
		openid_services_xrds::MATCH_ALL,
		openid_services_xrds::MATCH_ANY
	);

	/**
	 * Callback filter function used by openid_services_xrds::get_filtered_xrds_services.
	 * Check all type uris listed in this xrds service node are supported openid types.
	 * See $yadis_service_types in Openid.php
	 *
	 * @param  XRDS   instance of XRDS.php containing a single service node
	 * @return boolean
	 */
	public static function filter_check_all_type_uris_are_openid_type($xrds_service)
	{
		$uris = $xrds_service->xpath('xrd:Type');

		foreach ($uris as $uri)
		{
			if (in_array($uri, Openid::$yadis_service_types))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Filter the passed array of xrds objects each containing an xrds service element
	 *
	 * The default mode is to return all service objects which match ANY of the
	 * specified filters, but $filter_mode may be
	 * SERVICES_YADIS_MATCH_ALL if you want to be sure that the
	 * returned services match all the given filters.
	 *
	 * @param mixed    $filters An array of callbacks to filter the
	 *                 returned services, or null if all services are to be returned.
	 * @param const    $filter_mode MATCH_ALL or MATCH_ANY, depending on whether the returned
	 *                 services should match ALL or ANY of the specified filters.
	 * @return mixed   $services An array of XRDS objects if $filter_mode is a valid
	 *                 mode; null if $filter_mode is an invalid mode.
	 */
	public static function get_filtered_xrds_services(array $xrds_services, $filters = FALSE, $filter_mode = openid_services_xrds::MATCH_ALL)
	{
		// If no filters are specified, return the entire service
		// list, ordered by priority.
		if ( ! $filters OR ( ! is_array($filters)))
			return $xrds_services;

		// If an unsupported filter mode is specified, return FALSE.
		if ( ! in_array($filter_mode, openid_services_xrds::$valid_filter_modes))
			return FALSE;

		// Otherwise, use the callbacks in the filter list to
		// determine which services are returned.
		$filtered = array();

		foreach ($xrds_services as $priority_key => $service_with_this_priority)
		{
			foreach ($service_with_this_priority as $service)
			{
				$matches = 0;

				$priority = $service->attribute('priority');

				if ($priority === NULL)
				{
					$priority = XRDS::SERVICE_LOWEST_PRIORITY;
				}

				foreach ($filters as $filter)
				{
					if (openid_services_xrds::$filter($service))
					{
						$matches++;

						if ($filter_mode == openid_services_xrds::MATCH_ANY)
						{
							if ( ! array_key_exists($priority, $filtered))
							{
								$filtered[$priority] = array();
							}

							array_push($filtered[$priority], $service);

							break;
						}
					}
				}

				if (($filter_mode == openid_services_xrds::MATCH_ALL) AND ($matches == count($filters))) {

					if ( ! array_key_exists($priority, $filtered))
					{
						$filtered[$priority] = array();
					}

					array_push($filtered[$priority], $service);
				}
			}
		}

		ksort($filtered, SORT_NUMERIC);

		return $filtered;
	}

	/**
	 * @param  XRDS     instance of XRDS.php containing a single service node
	 * @param  string   claimed_id resulting from the discovery phase
	 * @param  string   verbatum copy of the url/id entered by the user into the openid_url field
	 * @return array    array of filtered and prioritized Openid_Service_Endpoint instances
	 */
	public static function get_filtered_and_prioritized_service_endpoints($xrds_services, $discovered_claimed_id, $user_supplied_id)
	{
		$filters = array('filter_check_all_type_uris_are_openid_type');

		$filtered_services = openid_services_xrds::get_filtered_xrds_services($xrds_services, $filters);

		if (empty($filtered_services))
			return FALSE;

		$services_endpoints = openid_services_xrds::get_openid_service_endpoints($filtered_services, $discovered_claimed_id, $user_supplied_id);

		if (empty($services_endpoints))
			return FALSE;

		$services_endpoints = openid_services_xrds::get_prioritised_openid_provider_or_user_service_endpoints($services_endpoints);

		if (empty($services_endpoints))
			return FALSE;

		return $services_endpoints;
	}

	/**
	 * Convert a given array of xrds service nodes to an array of Openid_Service_Endpoint instances
	 *
	 * @param  XRDS     instance of XRDS.php containing a single service node
	 * @param  string   claimed_id resulting from the discovery phase
	 * @param  string   verbatum copy of the url/id entered by the user into the openid_url field
	 * @return array    array of Openid_Service_Endpoint instances
	 */
	public static function get_openid_service_endpoints($xrds_services = array(), $discovered_claimed_id, $user_supplied_id)
	{
		if (empty($xrds_services))
			return FALSE;

		$endpoints = array();

		foreach ($xrds_services as $services)
		{
			foreach($services as $xrds_service)
			{
				$type_uri_simple_xml_elements = $xrds_service->xpath('xrd:Type');

				$type_uris = array();

				foreach ($type_uri_simple_xml_elements as $element)
				{
					array_push($type_uris, (string) $element);
				}

				$uris = $xrds_service->xpath('xrd:URI');

				if (empty($type_uris))
					continue;

				foreach ($uris as $key => $xrds_service_url)
				{
					$openid_service_endpoint = openid_services_xrds::to_openid_service_endpoint
					(
						$xrds_service,
						$discovered_claimed_id,
						$user_supplied_id,
						(string)$xrds_service_url,
						$type_uris
					);

					if ($openid_service_endpoint !== FALSE)
					{
						array_push($endpoints, $openid_service_endpoint);
					}
				}
			}
		}

		if (empty($endpoints))
			return FALSE;

		return $endpoints;
	}

	/**
	 * Convert an XRDS instance containign a single service node into an Openid_Service_Endpoint
	 *
	 * @param  XRDS     instance of XRDS.php containing a single service node
	 * @param  string   claimed_id resulting from the discovery phase
	 * @param  string   verbatum copy of the url/id entered by the user into the openid_url field
	 * @return Openid_Service_Endpoint
	 */
	function to_openid_service_endpoint($xrds_service, $discovered_claimed_id, $user_supplied_id, $server_url, $type_uris)
	{
		$openid_service_endpoint = Openid_Service_Endpoint::factory();

		$openid_service_endpoint->type_uris = $type_uris;

		$openid_service_endpoint->op_endpoint = $server_url;

		$openid_service_endpoint->from_xrds = TRUE;

		if ( ! $openid_service_endpoint->is_openid_provider_type())
		{
			$openid_service_endpoint->claimed_id = $discovered_claimed_id;

			$openid_service_endpoint->display_id = $user_supplied_id;

			$local_id = openid_services_xrds::find_openid_provider_local_identifier($xrds_service, $type_uris);

			// OK if local_id returned as NULL,
			// not OK if returned as FALSE
			if ($local_id === FALSE)
				return FALSE;

			$openid_service_endpoint->identity = ($local_id !== NULL)? $local_id : $openid_service_endpoint->claimed_id;
		}
		else
		{
			$openid_service_endpoint->claimed_id = $openid_service_endpoint->identity = Openid::OPENID_2_0_NAMESPACE_IDENTIFIER_SELECT;//Openid::OPENID_2_0_NAMESPACE_SERVER;
		}

		return $openid_service_endpoint;
	}

	/**
	 * Extract an openid:Delegate or xrd:LocalID value from an XRDS Service element.
	 * NOTE: If no delegate is found, returns null. Returns FALSE if extraction fails
	 * (when multiple delegate/localID tags have different values).
	 *
	 * @param  XRDS     instance of XRDS.php containing a single service node
	 * @param  array    array of urls already extracted from the service node's type nodes.
	 * @return mixed    local_id url or NULL if no delegate found and FALSE on failure (invalid xrds).
	 */
	public static function find_openid_provider_local_identifier($xrds_service, $type_uris)
	{
		$xrds_service->register_xpath_namespace('openid', Openid::OPENID_XML_NAMESPACE);

		$permitted_tags = array();

		if (in_array(Openid::OPENID_1_1_NAMESPACE_SIGNON, $type_uris) OR
			in_array(Openid::OPENID_1_0_NAMESPACE_SIGNON, $type_uris))
		{
			array_push($permitted_tags, Openid::XRDS_LOCAL_ID_TAG_OPENID_1);
		}

		if (in_array(Openid::OPENID_2_0_NAMESPACE_SIGNON, $type_uris))
		{
			array_push($permitted_tags, Openid::XRDS_LOCAL_ID_TAG_OPENID_2);
		}

		$local_id = NULL;

		foreach ($permitted_tags as $tag)
		{
			$permitted_tag_nodes = $xrds_service->xpath($tag.'|'.strtolower($tag));

			foreach ($permitted_tag_nodes as $tagnode)
			{
				if ($local_id === NULL)
				{
					$local_id = (string)$tagnode;
				}
				else if ($local_id != (string)$tagnode)
				{
					// Conflicting delegation / LocalID found
					return FALSE;
				}
			}
		}

		return $local_id;
	}

	/**
	 * Prioritize user and provider type services in passed array of Openid_Service_Endpoint instances
	 *
	 * @param  array   array of Openid_Service_Endpoint instances.
	 * @return array   prioritzed array of Openid_Service_Endpoint instances.
	 */
	public static function get_prioritised_openid_provider_or_user_service_endpoints($openid_service_endpoints)
	{
		$openid_provider_service_endpoints = openid_services_xrds::arrange_by_type($openid_service_endpoints, array(Openid::OPENID_2_0_NAMESPACE_SERVER));

		$openid_service_endpoints = openid_services_xrds::arrange_by_type($openid_service_endpoints, Openid::$yadis_service_types);

		if ($openid_provider_service_endpoints)
			return $openid_provider_service_endpoints;

		return $openid_service_endpoints;
	}

	/**
	 * Prioritize Openid_Service_Endpoint instances listed in the passed array, ordering them based upon
	 * the types listed in the passed preferred_types array or the default Openid::$yadis_service_types.
	 *
	 * @param  array   array of Openid_Service_Endpoint instances.
	 * @param  array   array of uri types upon which to base the ordering of the endpoints
	 * @return array   prioritzed array of Openid_Service_Endpoint instances.
	 */
	public static function arrange_by_type($openid_service_endpoints, $preferred_types = array())
	{
		if (empty($preferred_types))
			$preferred_types = Openid::$yadis_service_types;

		$prioritized_services = array();

		foreach ($openid_service_endpoints as $index => $openid_service_endpoint)
		{
			array_push(
						$prioritized_services,
						array(
							  openid_services_xrds::index_of_best_matching_service($openid_service_endpoint, $preferred_types),
							  $index,
							  $openid_service_endpoint
							 )
					  );
		}

		sort($prioritized_services);

		// Now that the services are sorted by priority, remove the sort
		// keys from the list.
		foreach ($prioritized_services as $key => $openid_service_endpoint)
		{
			$prioritized_services[$key] = $prioritized_services[$key][2];
		}

		return $prioritized_services;
	}

   /**
	* Iterate through a list of prefered type uris and return the index of the first one found to be listed
	* as a supported type in the type_uris array of the passed Openid_Service_Endpoint
	*
	* @param  Openid_Service_Endpoint
	* @param  array     array of uri types in order of preference
	* @return integer   index of first supported type or count($preferred_types) if no match found
	*/
	public static function index_of_best_matching_service($openid_service_endpoint, $preferred_types = array())
	{
		if (empty($preferred_types))
			$preferred_types = Openid::$yadis_service_types;

		foreach ($preferred_types as $index => $type)
		{
			if (in_array($type, $openid_service_endpoint->type_uris))
			{
				return $index;
			}
		}

		return count($preferred_types);
	}
}