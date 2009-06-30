<?php defined('SYSPATH') or die('No direct script access.');
/**
 * helper class for extracting openid service info from html content
 *
 * $Id: openid_services_html.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class openid_services_html_Core {

	// Array containing the various sets of html tags searched for within the html document
	public static $openid_html_discovery_versions = array
	(
		// OpenID 2.0 tags
		array
		(
			'type_uri'   => Openid::OPENID_2_0_NAMESPACE_SIGNON,
			'server_rel' => Openid::HTML_LINK_TAG_REL_PROVIDER_OPENID_2,
			'local_rel'  => Openid::HTML_LINK_TAG_REL_LOCAL_OPENID_2
		),
		// OpenID 1.1 and 1.0 tags
		array
		(
			'type_uri'   => Openid::OPENID_1_1_NAMESPACE_SIGNON,
			'server_rel' => Openid::HTML_LINK_TAG_REL_PROVIDER_OPENID_1,
			'local_rel'  => Openid::HTML_LINK_TAG_REL_LOCAL_OPENID_1
		)
	);

	/**
	 * Parse html response to extract the xrds location from the http-equiv meta tag.
	 * Used during Yadis discovery.
	 *
	 * @param  string  html string
	 * @return mixed   url of the xrds document or FALSE if not found
	 */
	public static function get_yadis_xrds_location_from_html_meta_tag_httpequiv($html_content)
	{
		$html = @DOMDocument::loadHTML($html_content);

		$meta_tags = $html->getElementsByTagName('meta');

		if ($meta_tags->length == 0)
			return FALSE;

		foreach ($meta_tags as $tag)
		{
			if (strtolower($tag->getAttribute('http-equiv')) === 'x-xrds-location')
			{
				$xrds_location = $tag->getAttribute('content');

				if ( ! empty($xrds_location))
					return $xrds_location;
			}
		}

		return FALSE;
	}

	/**
	 * Extract service endpoints described in an html document into an array of Openid_Service_Endpoint instances
	 *
	 * @param  string   html string
	 * @param  string   the claimed id used to locate the passed html
	 * @return array    array of Openid_Service_Endpoint instances
	 */
	public static function get_openid_service_endpoints($html_content, $claimed_id)
	{
		$services = array();

		foreach (openid_services_html::$openid_html_discovery_versions as $discovery_version) {

			$openid_urls = openid_services_html::get_openid_server_and_local_urls
			(
				$html_content,
				$discovery_version['server_rel'],
				$discovery_version['local_rel']
			);

			if ($openid_urls === FALSE)
				continue;

			if ($openid_urls['local_url'] === FALSE)
			{
				$openid_urls['local_url'] = $claimed_id;
			}

			$service_endpoint_fields = array
			(
				'claimed_id'       => $claimed_id,
				'identity'         => $openid_urls['local_url'],
				'display_id' => $claimed_id,
				'op_endpoint'      => $openid_urls['server_url'],
				'type_uris'        => array($discovery_version['type_uri'])
			);

			$service_endpoint = Openid_Service_Endpoint::factory($service_endpoint_fields);

			array_push($services, $service_endpoint);
		}

		return $services;
	}

	/**
	 * Extract the server and local openid delegation urls from a given html string
	 *
	 * @param  string   html string
	 * @param  string   what html attribute for server_url to look for - see $openid_html_discovery_versions above
	 * @param  string   what html attribute for local_url to look for - see $openid_html_discovery_versions above
	 * @return mixed    associative array or FALSE
	 */
	public static function get_openid_server_and_local_urls($html_content, $server_rel, $local_rel)
	{
		$html = @DOMDocument::loadHTML($html_content);

		$links = $html->getElementsByTagName('link');

		$server_url = FALSE;

		$local_url  = FALSE;

		foreach ($links as $link)
		{
			if (strtolower($link->getAttribute('rel')) === $server_rel)
			{
				$server_url = $link->getAttribute('href');
			}
			elseif (strtolower($link->getAttribute('rel')) === $local_rel)
			{
				$local_url = $link->getAttribute('href');
			}

			// Stop searching links if both already set
			if ( ! (empty($server_url) OR empty($local_url)))
				return array('server_url' => $server_url, 'local_url' => $local_url);
		}

		if (empty($server_url))
			return FALSE;

		return array('server_url' => $server_url, 'local_url' => $local_url);
	}

}