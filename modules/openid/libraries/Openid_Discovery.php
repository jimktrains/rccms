<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Library Class for performing OpenID discovery.
 *
 * $Id: Openid_Discovery.php 2008-08-12 09:28:34 BST Atomless $
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
abstract class Openid_Discovery_Core extends Openid_Request {

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
	 * Perform Openid discovery based on the current claimed_id setting. The object of which is to aquire
	 * the service endpoint details associated with this id, such as the OpenID Provider server url,
	 * and the protocol version, encryption and extension types supported by that provider.
	 * If the claiemd_id is found to be an xri then xri resolution is performed otherwise
	 * first yadis discovery is attempted then html discovery.
	 *
	 * @return boolean
	 */
	protected function discover()
	{
		$identity_url = FALSE;

		$this->openid_service_endpoints = array();

		// *** XRI RESOLUTION ***
		// If identifier is an xri then first try xri resolution for discovery
		if ($this->claimed_id_type == 'xri')
		{
			list($identity_url, $this->openid_service_endpoints) = $this->discover_xri($this->claimed_id, Openid::$yadis_service_types);
		}

		// *** YADIS DISCOVERY ***
		// If identifier not an xri or xri resolution failed then first try Yadis discovery
		if ( ! $identity_url)
		{
			list($identity_url, $this->openid_service_endpoints) = $this->discover_yadis($this->claimed_id, Openid::$yadis_service_types);
		}

		// *** HTML DISCOVERY ***
		// If identifier not an xri or xri resolution failed and Yadis discovery also failed
		// then fall back to legacy html openid discovery method
		if ( ! $identity_url)
		{
			list($identity_url, $this->openid_service_endpoints) = $this->discover_html($this->claimed_id);
		}

		// Discovery failed?
		if ( ! $identity_url OR empty($this->openid_service_endpoints))
		{
			$this->log('error', 'Openid_Discovery', 'discover', 'no_valid_endpoints_found');

			return FALSE;
		}

		// Filter endpoints according to security level
		$this->openid_service_endpoints = Openid_Discovery::filter_openid_service_endpoints_according_to_security_level($this->openid_service_endpoints);

		// No sufficiently secure endpoints?
		if(empty($this->openid_service_endpoints))
		{
			$this->log('error', 'Openid_Discovery', 'discover', 'no_sufficiently_secure_endpoints_found');

			return FALSE;
		}

		// Set the current endpoint to the next openid_service_endpoint and remove it from the list
		$this->current_openid_service_endpoint = $this->get_next_openid_service_endpoint();

		// Set the openid fields to those found in the current service endpoint
		$this->set_authentication_fields($this->current_openid_service_endpoint->fields);

		// Set the version of the openid spec we're running under according to the
		// version supported by the current service endpoint
		$this->openid_version = $this->current_openid_service_endpoint->get_required_openid_version();

		// Save current openid fields in kohana session
		$this->save_to_session();

		return TRUE;
	}

	/**
	 * Perform OpenID discovery on an xri.
	 *
	 * @param  string    claimed_id string
	 * @param  array     linear array of service types used to prioritize the resulting service endpoints
	 * @return boolean
	 */
	protected function discover_xri($claimed_id, $service_types)
	{
		$this->clear_response_record();

		// Prepend the xri with the proxy xri resolution url
		$url = xri::to_hxri($claimed_id);

		$canonical_id = FALSE;

		$services = array();

		foreach ($service_types as $service_type)
		{
			$geturl = $url.'?'.Openid_Request::get_xri_resolution_query_string($service_type);

			$this->request = $this->http_get($geturl);

			if ($this->request['status'] != 200)
				continue;

			if ( ! $this->response_contained_supported_xrds_type())
				continue;

			$this->xrds = XRDS::factory($this->response_body, Openid::$legacy_openid_xml_namespace_map);

			if ( ! $this->xrds->parse(FALSE, Openid::$legacy_openid_xml_namespace_map))
				continue;

			$temp_canonical_id = $this->xrds->get_canonical_id($this->xrds);

			if ($temp_canonical_id === FALSE)
				continue;

			$canonical_id = $temp_canonical_id;

			$services = array_merge($services, $this->xrds->service_list);
		}

		if ($canonical_id === FALSE)
		{
			$this->log('error', 'Openid_Discovery', 'xri_resolution', 'failed_to_locate_canonical_id', array('xrds' => $this->xrds->as_xml()));

			return FALSE;
		}

		// Add the successful xri-resolution to the internal log
		$this->log('success', 'Openid_Discovery', 'xri_resolution', '', array('canonical_id' => $canonical_id, 'claimed_id' => $claimed_id));

		$service_endpoints = openid_services_xrds::get_filtered_and_prioritized_service_endpoints($services, $canonical_id, $claimed_id);

		$this->openid_service_endpoints = $service_endpoints;

		return array($canonical_id, $service_endpoints);
	}

	/**
	 * Perform Yadis discovery for a uri type claimed_id
	 *
	 * @param  string    claimed_id string
	 * @param  array     linear array of service types used to prioritize the resulting service endpoints
	 * @return boolean
	 */
	protected function discover_yadis($claimed_id, $service_types)
	{
		$this->request = $this->http_get($claimed_id, array('Accept: '.XRDS::CONTENT_TYPE_XRDS_AND_XML.', text/html; q=0.3, application/xhtml+xml; 0.5'));

		if ($this->request['status'] != 200)
		{
			$this->log('error', 'Openid_Discovery', 'discover_yadis', 'server_response_not_200');

			return FALSE;
		}

		$yadis_result = array
		(
			'normalized_url' => $this->request['final_url'],
			'xrds_url'       => FALSE
		);

		if ($this->response_contained_supported_xrds_type())
		{
			$yadis_result['xrds_url'] = $yadis_result['normalized_url'];
		}
		else
		{

			$yadis_result['xrds_url'] = $this->get_response_xrds_location_header();

			// If xrds location not found in the location header
			// Attempt to extract it from the meta tags in text/html response body
			if ($yadis_result['xrds_url'] === FALSE AND $this->response_contained_supported_html_type())
			{
				$yadis_result['xrds_url'] = openid_services_html::get_yadis_xrds_location_from_html_meta_tag_httpequiv($this->response_body);
			}

			// If extracted yadis location either from location header
			// or from html meta tags then attempt to load the xrds document
			if ($yadis_result['xrds_url'] != FALSE)
			{
				$this->request = $this->http_get($yadis_result['xrds_url'], array('Accept: '.XRDS::CONTENT_TYPE_XRDS_AND_XML));

				if ($this->request['status'] != 200)
				{
					$this->log('error', 'Openid_Discovery', 'discover_yadis', 'server_response_not_200');

					return FALSE;
				}
			}
		}

		if ( ! $this->response_contained_supported_xrds_type())
		{
			$this->log('error', 'Openid_Discovery', 'discover_yadis', 'xrds_not_found_in_response');

			return FALSE;
		}

		$this->xrds = XRDS::factory($this->response_body, Openid::$legacy_openid_xml_namespace_map);

		if ( ! $this->xrds->parse(FALSE, Openid::$legacy_openid_xml_namespace_map))
		{
			$this->log('error', 'Openid_Discovery', 'discover_yadis', 'failed_to_parse_xrds');

			return FALSE;
		}

		$service_endpoints = openid_services_xrds::get_filtered_and_prioritized_service_endpoints($this->xrds->service_list, $yadis_result['normalized_url'], $claimed_id);

		if (empty($service_endpoints))
		{
			$this->log('error', 'Openid_Discovery', 'discover_yadis', 'no_valid_endpoints_found_in_xrds');

			return FALSE;
		}

		// Add the successful yadis discovery to the internal log
		$this->log('success', 'Openid_Discovery', 'discover_yadis', '', array('normailized' => $yadis_result['normalized_url']));

		return array($yadis_result['normalized_url'], $service_endpoints);
	}

	/**
	 * Perform html based OpenID discovery for a uri type claimed_id
	 *
	 * @param  string    claimed_id string
	 * @return boolean
	 */
	protected function discover_html($claimed_id)
	{
		$this->request = $this->http_get($claimed_id);

		if ($this->request['status'] != 200)
		{
			$this->log('error', 'Openid_Discovery', 'discover_html', 'server_response_not_200');

			return FALSE;
		}

		// Just incase something nasty has happened!
		if ( ! $this->response_contained_supported_html_type())
		{
			$this->log('error', 'Openid_Discovery', 'discover_html', 'unsupported_response_type');

			return FALSE;
		}

		$service_endpoints = openid_services_html::get_openid_service_endpoints($this->response_body, $claimed_id);

		if (empty($service_endpoints))
		{
			$this->log('error', 'Openid_Discovery', 'discover_html', 'no_valid_endpoints_found');

			return FALSE;
		}

		$yadis_service_endpoints = array();

		// OK, now we have service endpoint but other than the OpenID Signon version we know nothing about
		// the support provided by the OpenID Provider so lets try yadis discovery on the identity extracted
		// from the html - this should give us a list of service endpoints with more detail regarding what
		// extensions are supported by each OpenID Provider listed.
		list($identity_url, $yadis_service_endpoints) = $this->discover_yadis($service_endpoints[0]->identity, Openid::$yadis_service_types);

		if (KOHANA::config('openid.security_level') > 4)
		{
			// If the security level is above 4 we need to know more about the security extension support of
			// the OpenID Provider than pure html discovery will allow so if for some reason the yadis
			// discovery, performed on the identity extracted from the htm, returned no services then
			// authentication must fail here.
			if (empty($yadis_service_endpoints))
			{
				$this->log('error', 'Openid_Discovery', 'discover_html', 'no_secure_endpoints_found_via_yadis');

				return FALSE;
			}
		}
		else
		{
			if ( ! empty($yadis_service_endpoints))
			{
				// Great now we have a list of service endpoints hopefully with a little more
				// details (type uris) than the pure html discovery service endpoints
				$service_endpoints = $yadis_service_endpoints;

				// Now we just need to reset the claimed_id of the yadis endpoints to the normalised
				// version of the user submitted identity.
				foreach ($service_endpoints as $service_endpoint)
				{
					$service_endpoint->claimed_id = $claimed_id;

					$service_endpoint->display_id = $claimed_id;
				}
			}
		}

		// Add the successful yadis discovery to the internal log
		$this->log('success', 'Openid_Discovery', 'discover_html', '', array('final_url' => $this->request['final_url']));

		return array($this->request['final_url'], $service_endpoints);
	}

	/**
	 * Get the next Openid_Service_Enpoint and remove it from the openid_service_endpoints array
	 *
	 * @return Openid_Service_Enpoint
	 */
	protected function get_next_openid_service_endpoint()
	{
		return array_shift($this->openid_service_endpoints);
	}

	/**
	 * Filter out any Openid_Service_Enpoint instances in the passed array that do not support the
	 * level of security required by the current security_level setting in config/openid.php
	 *
	 * @param  array   linear array of Openid_Service_Enpoint instances
	 * @return array   linear array of Openid_Service_Enpoint instances
	 */
	protected static function filter_openid_service_endpoints_according_to_security_level($endpoints)
	{
		if (KOHANA::config('openid.security_level') > 4)
		{
			$required_pape_policies = array();

			array_push($required_pape_policies, Openid_Extension_Pape::PHISHING_RESISTANT);

			if (KOHANA::config('openid.security_level') > 5)
			{
				array_push($required_pape_policies, Openid_Extension_Pape::MULTI_FACTOR);

				if (KOHANA::config('openid.security_level') > 6)
				{
					array_push($required_pape_policies, Openid_Extension_Pape::PHYSICAL_MULTI_FACTOR);
				}
			}

			$filtered_endpoints = array();

			foreach ($required_pape_policies as $pape_policy)
			{
				foreach ($endpoints as $endpoint)
				{
					if ($endpoint->supports_openid_service_or_extension_type($pape_policy))
					{
						array_push($filtered_endpoints, $endpoint);
					}
				}
			}

			return $filtered_endpoints;
		}
		else
		{
			return $endpoints;
		}
	}
}
