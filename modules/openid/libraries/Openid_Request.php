<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Library Class for executing OpenID get and post requests using CURL.
 *
 * $Id: Openid_Request.php 2008-08-12 09:28:34 BST Atomless $
 *
 * Instantiated in a chain of extension :
 * Openid_Auth.php <- Openid_Relying_Party.php <- Openid_Response.php <-
 * Openid_Association.php <- Openid_Discovery.php <- Openid_Request.php <- Openid.php
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 *
 *
 */
abstract class Openid_Request_Core extends Openid {

	// Default config settings in auth/config/openid_request.php
	protected $config;

	protected $response_headers;

	protected $response_bodies;

	protected $response_body;

	protected $response;

	// After a request has been made $request will be set to an associative array :
	// array('status' => 200, 'final_url' => 'openid server url')
	protected $request;

	protected $curl_request_info;

	/**
	 * Constructor.
	 *
	 * @return  void
	 */
	public function __construct($fields = array())
	{
		parent::__construct($fields);

		// Set config
		$this->config = KOHANA::config('openid.request');

		$this->clear_response_record();
	}

	/**
	 * Clear any stored data from a previous request
	 *
	 * @return void
	 */
	protected function clear_response_record()
	{
		$this->response_headers = array();

		$this->response_bodies  = array();

		$this->response_body = '';

		$this->response = FALSE;

		$this->curl_request_info = FALSE;
	}

	/**
	 * Clear any data stored in the response_body from a previous request
	 *
	 * @return void
	 */
	protected function clear_response_body()
	{
		if ( ! empty($this->response_body))
		{
			array_push($this->response_bodies, $this->response_body);
		}

		$this->response_body = '';
	}

	/**
	 * Called via CURLOPT_HEADERFUNCTION of either the get or post method to record each response header
	 * received into an array.
	 *
	 * @param $ch       Curl instance
	 * @param $header   Header data to add to header response record
	 * @return int      Important : Must return string length of header or curl throws error
	 */
	public function record_headers($ch, $header)
	{
		array_push($this->response_headers, rtrim($header));

		return strlen($header);
	}

	/**
	 * Called via the CURLOPT_WRITEFUNCTION of either the get or post method
	 *
	 * @param $ch     Curl instance
	 * @param $data   Data to add to response body record
	 * @return int    Important : Must return string length of second param or curl throws error
	 */
	public function record_body($ch, $data)
	{
		$this->response_body .= $data;

		return strlen($data);
	}

	/**
	 * Used by the http_get method to find the url of redirects in the header
	 *
	 * @return mixed   the redirect location url if found or FALSE
	 */
	protected function find_redirect_location()
	{
		foreach ($this->response_headers as $line)
		{
			if (strpos(strtolower($line), "location: ") === 0)
			{
				$parts = explode(" ", $line, 2);

				return $parts[1];
			}
		}
		return FALSE;
	}

   /**
	* Build an xri resolution query string using the passed service type
	*
	* @param  string   service type url like those listed in Openid::$yadis_service_types
	* @return string   query string
	*/
	public static function get_xri_resolution_query_string($service_type = FALSE)
	{
		if ($service_type)
		{
			$nvps = array('_xrd_r' => XRDS::CONTENT_TYPE_XRDS_AND_XML, '_xrd_t' => $service_type);
		}
		else
		{
			// Without service endpoint selection.
			$nvps = array('_xrd_r' => ';sep=false');
		}

		return http_build_query($nvps);
	}

   /**
	* Return the contents of response_headers as an associative array
	*
	* @return array   associative array
	*/
	protected function get_headers_as_associative_array()
	{
		$new_headers = array();

		foreach ($this->response_headers as $header)
		{
			if (preg_match("/:/", $header))
			{
				list($name, $value) = explode(": ", $header, 2);

				$new_headers[$name] = $value;
			}
		}

		return $new_headers;
	}

	/**
	 * Determine whether the response contained a supported xrds type
	 *
	 * @return boolean
	 */
	protected function response_contained_supported_xrds_type()
	{
		$content_types = explode(';', $this->curl_request_info['content_type']);

		return (array_intersect(XRDS::$supported_content_types, $content_types) !== array());
	}

	/**
	 * Determine whether the response contained a supported html type
	 *
	 * @return boolean
	 */
	protected function response_contained_supported_html_type()
	{
		$content_types = explode(';', $this->curl_request_info['content_type']);

		return (array_intersect(array('text/html'), $content_types) !== array());
	}

	/**
	 * Extract an xrds location header from the response headers received during this request
	 *
	 * @return mixed   xrds location url or FALSE if none found
	 */
	protected function get_response_xrds_location_header()
	{
		$response_headers = $this->get_headers_as_associative_array();

		foreach ($response_headers as $name => $value)
		{
			if (strtolower($name) == XRDS::XRDS_LOCATION_HEADER)
				return $value;
		}

		return FALSE;
	}

   /**
	* Redirect the user's browser to the OpenID Provider url for authentication
	*
	* @param  string  url of OpendID Provider's OpenID server
	* @param  string  openid mode - set when doing check_id_immediate.
	* @return void
	*/
	public function redirect_to_openid_provider($url = FALSE, $mode = FALSE)
	{
		$url = ($url === FALSE)? $this->op_endpoint : $url;

		$this->mode = ($mode === FALSE)? Openid::OPENID_REQUEST_MODE_CHECK_ID_SETUP : $mode;

		// get_authentication_fields is a method of the Openid parent class
		$authentication_fields = $this->get_authentication_fields();

		$returnto_key = Openid::OPENID_NAMESPACE_ALIAS.'.return_to';

		// Append a fresh nonce query param onto return_to url
		$authentication_fields[$returnto_key] .= '?'
											  .$this->config['nonce_name'].'_nonce='
											  .urlencode(nonce::create());

		if ($this->openid_version < 2.0)
		{
			// Append claimed_id as query param onto return_to url as required under OpenID 1.*
			$authentication_fields[$returnto_key] .= '&openid1_claimed_id='
												  .urlencode($authentication_fields[Openid::OPENID_NAMESPACE_ALIAS.'.claimed_id']);

			// Remove claimed_id from the authentication fields so that it's not included twice
			unset($authentication_fields[Openid::OPENID_NAMESPACE_ALIAS.'.claimed_id']);
		}

		$query_string = http_build_query($authentication_fields, '', '&');

		$url = $url.'?'.$query_string;

		// Add the redirect event to the internal log
		$this->log('redirect', 'Openid_Request', 'redirect_to_openid_provider', '', array('url' => $url));

		// Save current openid fields in kohana session
		$this->save_to_session();

		// Pass authentication fields as query string appended to the openid provider server url redirect url
		url::redirect($url);
	}

	/**
	 * Perform an http get request using CURL - see default CURL settings in config/openid.php
	 *
	 * @param string   URL used for get request
	 * @param array    Array of extra headers to add to the request
	 * @return mixed   Associative array containing status and final_url or FALSE on failure.
	 */
	protected function http_get($url, $extra_http_headers = array())
	{
		$timesup = time() + $this->config['timelimit'];

		$timesup_minus_now = $this->config['timelimit'];

		$redirect = TRUE;

		while ($redirect AND ($timesup_minus_now > 0))
		{
			$this->clear_response_body();

			$ch = curl_init($url);

			// Set default curl options
			curl_setopt_array($ch, $this->config['curl_config']);

			// Require SSL authentication if operating with higher than the minimum security level
			// and not using encryption.
			if (($this->security_level > 0) AND ($this->session_type == Openid::OPENID_NO_ENCRYPTION))
			{
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
			}

			curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'record_headers'));

			curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'record_body'));

			$http_headers = array_merge($this->config['default_http_headers'], $extra_http_headers);

			if ( ! empty($http_headers))
			{
				curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
			}

			$this->response = curl_exec($ch);

			$this->curl_request_info = curl_getinfo($ch);

			$code = $this->curl_request_info['http_code'];

			if ( ! $code)
			{
				$this->log('error', 'Openid_Request', 'http_get', 'http_code_missing_from_response');

				return FALSE;
			}

			if (in_array($code, array(301, 302, 303, 307)))
			{
				$url = $this->find_redirect_location();

				$redirect = TRUE;
			}
			else
			{
				if (curl_errno($ch))
				{
					$this->log('error', 'Openid_Request', 'http_get', 'curl_error', array('curl_error_no' => curl_errno($ch), 'curl_error_msg' => curl_error($ch)));

					return FALSE;
				}

				$redirect = FALSE;

				curl_close($ch);

				return array('status' => $code, 'final_url' => $this->curl_request_info['url']);
			}

			$timesup_minus_now = $timesup - time();
		}

		array_push($this->errors['request'], 'timed_out');

		return FALSE;
	}

	/**
	 * Perform an http POSt request using CURL - see default CURL settings in config/openid.php
	 *
	 * @param string   URL used for get request
	 * @param array    Array of name value pairs sent as postdata
	 * @return mixed   Associative array containing status and final_url or FALSE on failure.
	 */
	protected function http_post($url, $postdata, $extra_http_headers = array())
	{
		$this->clear_response_record();

		$ch = curl_init($url);

		// Set default curl options
		curl_setopt_array($ch, $this->config['curl_config']);

		// Require SSL authentication if operating with hihger than the minimum security level
		// and not using encryption.
		if (($this->security_level > 0) AND ($this->session_type == Openid::OPENID_NO_ENCRYPTION))
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
		}

		// Set custom curl options
		curl_setopt($ch, CURLOPT_POST, TRUE);

		curl_setopt($ch, CURLOPT_POSTFIELDS, trim($postdata, PHP_EOL.' &'));

		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, "record_headers"));

		curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, "record_body"));

		$http_headers = array_merge($this->config['default_http_headers'], $extra_http_headers);

		if ( ! empty($http_headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
		}

		$this->response = curl_exec($ch);

		$this->curl_request_info = curl_getinfo($ch);

		$code = $this->curl_request_info['http_code'];

		if (curl_errno($ch))
		{
			$this->log('error', 'Openid_Request', 'http_get', 'curl_error', array('curl_error_no' => curl_errno($ch), 'curl_error_msg' => curl_error($ch)));

			return FALSE;
		}

		curl_close($ch);

		return array('status' => $code, 'final_url' => $this->curl_request_info['url']);
	}
}

