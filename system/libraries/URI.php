<<<<<<< HEAD:system/libraries/URI.php
<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * URI library.
 *
 * $Id: URI.php 4072 2009-03-13 17:20:38Z jheathco $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class URI_Core extends Router {

	/**
	 * Returns a singleton instance of URI.
	 *
	 * @return  object
	 */
	public static function instance()
	{
		static $instance;

		if ($instance == NULL)
		{
			// Initialize the URI instance
			$instance = new URI;
		}

		return $instance;
	}

	/**
	 * Retrieve a specific URI segment.
	 *
	 * @param   integer|string  segment number or label
	 * @param   mixed           default value returned if segment does not exist
	 * @return  string
	 */
	public function segment($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, URI::$segments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(URI::$segments[$index]) ? URI::$segments[$index] : $default;
	}

	/**
	 * Retrieve a specific routed URI segment.
	 *
	 * @param   integer|string  rsegment number or label
	 * @param   mixed           default value returned if segment does not exist
	 * @return  string
	 */
	public function rsegment($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, URI::$rsegments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(URI::$rsegments[$index]) ? URI::$rsegments[$index] : $default;
	}

	/**
	 * Retrieve a specific URI argument.
	 * This is the part of the segments that does not indicate controller or method
	 *
	 * @param   integer|string  argument number or label
	 * @param   mixed           default value returned if segment does not exist
	 * @return  string
	 */
	public function argument($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, URI::$arguments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(URI::$arguments[$index]) ? URI::$arguments[$index] : $default;
	}

	/**
	 * Returns an array containing all the URI segments.
	 *
	 * @param   integer  segment offset
	 * @param   boolean  return an associative array
	 * @return  array
	 */
	public function segment_array($offset = 0, $associative = FALSE)
	{
		return $this->build_array(URI::$segments, $offset, $associative);
	}

	/**
	 * Returns an array containing all the re-routed URI segments.
	 *
	 * @param   integer  rsegment offset
	 * @param   boolean  return an associative array
	 * @return  array
	 */
	public function rsegment_array($offset = 0, $associative = FALSE)
	{
		return $this->build_array(URI::$rsegments, $offset, $associative);
	}

	/**
	 * Returns an array containing all the URI arguments.
	 *
	 * @param   integer  segment offset
	 * @param   boolean  return an associative array
	 * @return  array
	 */
	public function argument_array($offset = 0, $associative = FALSE)
	{
		return $this->build_array(URI::$arguments, $offset, $associative);
	}

	/**
	 * Creates a simple or associative array from an array and an offset.
	 * Used as a helper for (r)segment_array and argument_array.
	 *
	 * @param   array    array to rebuild
	 * @param   integer  offset to start from
	 * @param   boolean  create an associative array
	 * @return  array
	 */
	public function build_array($array, $offset = 0, $associative = FALSE)
	{
		// Prevent the keys from being improperly indexed
		array_unshift($array, 0);

		// Slice the array, preserving the keys
		$array = array_slice($array, $offset + 1, count($array) - 1, TRUE);

		if ($associative === FALSE)
			return $array;

		$associative = array();
		$pairs       = array_chunk($array, 2);

		foreach ($pairs as $pair)
		{
			// Add the key/value pair to the associative array
			$associative[$pair[0]] = isset($pair[1]) ? $pair[1] : '';
		}

		return $associative;
	}

	/**
	 * Returns the complete URI as a string.
	 *
	 * @return  string
	 */
	public function string()
	{
		return URI::$current_uri;
	}

	/**
	 * Magic method for converting an object to a string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return URI::$current_uri;
	}

	/**
	 * Returns the total number of URI segments.
	 *
	 * @return  integer
	 */
	public function total_segments()
	{
		return count(URI::$segments);
	}

	/**
	 * Returns the total number of re-routed URI segments.
	 *
	 * @return  integer
	 */
	public function total_rsegments()
	{
		return count(URI::$rsegments);
	}

	/**
	 * Returns the total number of URI arguments.
	 *
	 * @return  integer
	 */
	public function total_arguments()
	{
		return count(URI::$arguments);
	}

	/**
	 * Returns the last URI segment.
	 *
	 * @param   mixed   default value returned if segment does not exist
	 * @return  string
	 */
	public function last_segment($default = FALSE)
	{
		if (($end = $this->total_segments()) < 1)
			return $default;

		return URI::$segments[$end - 1];
	}

	/**
	 * Returns the last re-routed URI segment.
	 *
	 * @param   mixed   default value returned if segment does not exist
	 * @return  string
	 */
	public function last_rsegment($default = FALSE)
	{
		if (($end = $this->total_segments()) < 1)
			return $default;

		return URI::$rsegments[$end - 1];
	}

	/**
	 * Returns the path to the current controller (not including the actual
	 * controller), as a web path.
	 *
	 * @param   boolean  return a full url, or only the path specifically
	 * @return  string
	 */
	public function controller_path($full = TRUE)
	{
		return ($full) ? url::site(URI::$controller_path) : URI::$controller_path;
	}

	/**
	 * Returns the current controller, as a web path.
	 *
	 * @param   boolean  return a full url, or only the controller specifically
	 * @return  string
	 */
	public function controller($full = TRUE)
	{
		return ($full) ? url::site(URI::$controller_path.URI::$controller) : URI::$controller;
	}

	/**
	 * Returns the current method, as a web path.
	 *
	 * @param   boolean  return a full url, or only the method specifically
	 * @return  string
	 */
	public function method($full = TRUE)
	{
		return ($full) ? url::site(URI::$controller_path.URI::$controller.'/'.URI::$method) : URI::$method;
	}

} // End URI Class
=======
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * URI Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	URI
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/uri.html
 */
class CI_URI {

	var	$keyval	= array();
	var $uri_string;
	var $segments		= array();
	var $rsegments		= array();

	/**
	 * Constructor
	 *
	 * Simply globalizes the $RTR object.  The front
	 * loads the Router class early on so it's not available
	 * normally as other classes are.
	 *
	 * @access	public
	 */		
	function CI_URI()
	{
		$this->config =& load_class('Config');
		log_message('debug', "URI Class Initialized");
	}
	
	
	// --------------------------------------------------------------------
	
	/**
	 * Get the URI String
	 *
	 * @access	private
	 * @return	string
	 */	
	function _fetch_uri_string()
	{
		if (strtoupper($this->config->item('uri_protocol')) == 'AUTO')
		{
			// If the URL has a question mark then it's simplest to just
			// build the URI string from the zero index of the $_GET array.
			// This avoids having to deal with $_SERVER variables, which
			// can be unreliable in some environments
			if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '')
			{
				$this->uri_string = key($_GET);
				return;
			}
		
			// Is there a PATH_INFO variable?
			// Note: some servers seem to have trouble with getenv() so we'll test it two ways		
			$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');			
			if (trim($path, '/') != '' && $path != "/".SELF)
			{
				$this->uri_string = $path;
				return;
			}
					
			// No PATH_INFO?... What about QUERY_STRING?
			$path =  (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');	
			if (trim($path, '/') != '')
			{
				$this->uri_string = $path;
				return;
			}
			
			// No QUERY_STRING?... Maybe the ORIG_PATH_INFO variable exists?
			$path = (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO');	
			if (trim($path, '/') != '' && $path != "/".SELF)
			{
				// remove path and script information so we have good URI data
				$this->uri_string = str_replace($_SERVER['SCRIPT_NAME'], '', $path);
				return;
			}

			// We've exhausted all our options...
			$this->uri_string = '';
		}
		else
		{
			$uri = strtoupper($this->config->item('uri_protocol'));
			
			if ($uri == 'REQUEST_URI')
			{
				$this->uri_string = $this->_parse_request_uri();
				return;
			}
			
			$this->uri_string = (isset($_SERVER[$uri])) ? $_SERVER[$uri] : @getenv($uri);
		}
		
		// If the URI contains only a slash we'll kill it
		if ($this->uri_string == '/')
		{
			$this->uri_string = '';
		}		
	}

	// --------------------------------------------------------------------
	
	/**
	 * Parse the REQUEST_URI
	 *
	 * Due to the way REQUEST_URI works it usually contains path info
	 * that makes it unusable as URI data.  We'll trim off the unnecessary
	 * data, hopefully arriving at a valid URI that we can use.
	 *
	 * @access	private
	 * @return	string
	 */	
	function _parse_request_uri()
	{
		if ( ! isset($_SERVER['REQUEST_URI']) OR $_SERVER['REQUEST_URI'] == '')
		{
			return '';
		}
		
		$request_uri = preg_replace("|/(.*)|", "\\1", str_replace("\\", "/", $_SERVER['REQUEST_URI']));

		if ($request_uri == '' OR $request_uri == SELF)
		{
			return '';
		}
		
		$fc_path = FCPATH;		
		if (strpos($request_uri, '?') !== FALSE)
		{
			$fc_path .= '?';
		}
		
		$parsed_uri = explode("/", $request_uri);
				
		$i = 0;
		foreach(explode("/", $fc_path) as $segment)
		{
			if (isset($parsed_uri[$i]) && $segment == $parsed_uri[$i])
			{
				$i++;
			}
		}
		
		$parsed_uri = implode("/", array_slice($parsed_uri, $i));
		
		if ($parsed_uri != '')
		{
			$parsed_uri = '/'.$parsed_uri;
		}

		return $parsed_uri;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Filter segments for malicious characters
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */	
	function _filter_uri($str)
	{
		if ($str != '' && $this->config->item('permitted_uri_chars') != '' && $this->config->item('enable_query_strings') == FALSE)
		{
			if ( ! preg_match("|^[".preg_quote($this->config->item('permitted_uri_chars'))."]+$|i", $str))
			{
				exit('The URI you submitted has disallowed characters.');
			}
		}	
		
		// Convert programatic characters to entities
		$bad	= array('$', 		'(', 		')',	 	'%28', 		'%29');
		$good	= array('&#36;',	'&#40;',	'&#41;',	'&#40;',	'&#41;');
		
		return str_replace($bad, $good, $str);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Remove the suffix from the URL if needed
	 *
	 * @access	private
	 * @return	void
	 */	
	function _remove_url_suffix()
	{
		if  ($this->config->item('url_suffix') != "")
		{
			$this->uri_string = preg_replace("|".preg_quote($this->config->item('url_suffix'))."$|", "", $this->uri_string);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Explode the URI Segments. The individual segments will
	 * be stored in the $this->segments array.	
	 *
	 * @access	private
	 * @return	void
	 */		
	function _explode_segments()
	{
		foreach(explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $this->uri_string)) as $val)
		{
			// Filter segments for security
			$val = trim($this->_filter_uri($val));
			
			if ($val != '')
			{
				$this->segments[] = $val;
			}
		}
	}
	
	// --------------------------------------------------------------------	
	/**
	 * Re-index Segments
	 *
	 * This function re-indexes the $this->segment array so that it
	 * starts at 1 rather than 0.  Doing so makes it simpler to
	 * use functions like $this->uri->segment(n) since there is
	 * a 1:1 relationship between the segment array and the actual segments.
	 *
	 * @access	private
	 * @return	void
	 */	
	function _reindex_segments()
	{
		array_unshift($this->segments, NULL);
		array_unshift($this->rsegments, NULL);
		unset($this->segments[0]);
		unset($this->rsegments[0]);
	}	
	
	// --------------------------------------------------------------------
	
	/**
	 * Fetch a URI Segment
	 *
	 * This function returns the URI segment based on the number provided.
	 *
	 * @access	public
	 * @param	integer
	 * @param	bool
	 * @return	string
	 */
	function segment($n, $no_result = FALSE)
	{
		return ( ! isset($this->segments[$n])) ? $no_result : $this->segments[$n];
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch a URI "routed" Segment
	 *
	 * This function returns the re-routed URI segment (assuming routing rules are used)
	 * based on the number provided.  If there is no routing this function returns the
	 * same result as $this->segment()
	 *
	 * @access	public
	 * @param	integer
	 * @param	bool
	 * @return	string
	 */
	function rsegment($n, $no_result = FALSE)
	{
		return ( ! isset($this->rsegments[$n])) ? $no_result : $this->rsegments[$n];
	}

	// --------------------------------------------------------------------
	
	/**
	 * Generate a key value pair from the URI string
	 *
	 * This function generates and associative array of URI data starting
	 * at the supplied segment. For example, if this is your URI:
	 *
	 *	example.com/user/search/name/joe/location/UK/gender/male
	 *
	 * You can use this function to generate an array with this prototype:
	 *
	 * array (
	 *			name => joe
	 *			location => UK
	 *			gender => male
	 *		 )
	 *
	 * @access	public
	 * @param	integer	the starting segment number
	 * @param	array	an array of default values
	 * @return	array
	 */
	function uri_to_assoc($n = 3, $default = array())
	{
	 	return $this->_uri_to_assoc($n, $default, 'segment');
	}
	/**
	 * Identical to above only it uses the re-routed segment array
	 *
	 */
	function ruri_to_assoc($n = 3, $default = array())
	{
	 	return $this->_uri_to_assoc($n, $default, 'rsegment');
	}

	// --------------------------------------------------------------------
	
	/**
	 * Generate a key value pair from the URI string or Re-routed URI string
	 *
	 * @access	private
	 * @param	integer	the starting segment number
	 * @param	array	an array of default values
	 * @param	string	which array we should use
	 * @return	array
	 */
	function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')
	{
		if ($which == 'segment')
		{
			$total_segments = 'total_segments';
			$segment_array = 'segment_array';
		}
		else
		{
			$total_segments = 'total_rsegments';
			$segment_array = 'rsegment_array';
		}
		
		if ( ! is_numeric($n))
		{
			return $default;
		}
	
		if (isset($this->keyval[$n]))
		{
			return $this->keyval[$n];
		}
	
		if ($this->$total_segments() < $n)
		{
			if (count($default) == 0)
			{
				return array();
			}
			
			$retval = array();
			foreach ($default as $val)
			{
				$retval[$val] = FALSE;
			}		
			return $retval;
		}

		$segments = array_slice($this->$segment_array(), ($n - 1));

		$i = 0;
		$lastval = '';
		$retval  = array();
		foreach ($segments as $seg)
		{
			if ($i % 2)
			{
				$retval[$lastval] = $seg;
			}
			else
			{
				$retval[$seg] = FALSE;
				$lastval = $seg;
			}
		
			$i++;
		}

		if (count($default) > 0)
		{
			foreach ($default as $val)
			{
				if ( ! array_key_exists($val, $retval))
				{
					$retval[$val] = FALSE;
				}
			}
		}

		// Cache the array for reuse
		$this->keyval[$n] = $retval;
		return $retval;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate a URI string from an associative array
	 *
	 *
	 * @access	public
	 * @param	array	an associative array of key/values
	 * @return	array
	 */	
	function assoc_to_uri($array)
	{	
		$temp = array();
		foreach ((array)$array as $key => $val)
		{
			$temp[] = $key;
			$temp[] = $val;
		}
		
		return implode('/', $temp);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch a URI Segment and add a trailing slash
	 *
	 * @access	public
	 * @param	integer
	 * @param	string
	 * @return	string
	 */
	function slash_segment($n, $where = 'trailing')
	{
		return $this->_slash_segment($n, $where, 'segment');
	}

	// --------------------------------------------------------------------
	
	/**
	 * Fetch a URI Segment and add a trailing slash
	 *
	 * @access	public
	 * @param	integer
	 * @param	string
	 * @return	string
	 */
	function slash_rsegment($n, $where = 'trailing')
	{
		return $this->_slash_segment($n, $where, 'rsegment');
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Fetch a URI Segment and add a trailing slash - helper function
	 *
	 * @access	private
	 * @param	integer
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _slash_segment($n, $where = 'trailing', $which = 'segment')
	{	
		if ($where == 'trailing')
		{
			$trailing	= '/';
			$leading	= '';
		}
		elseif ($where == 'leading')
		{
			$leading	= '/';
			$trailing	= '';
		}
		else
		{
			$leading	= '/';
			$trailing	= '/';
		}
		return $leading.$this->$which($n).$trailing;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Segment Array
	 *
	 * @access	public
	 * @return	array
	 */
	function segment_array()
	{
		return $this->segments;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Routed Segment Array
	 *
	 * @access	public
	 * @return	array
	 */
	function rsegment_array()
	{
		return $this->rsegments;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Total number of segments
	 *
	 * @access	public
	 * @return	integer
	 */
	function total_segments()
	{
		return count($this->segments);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Total number of routed segments
	 *
	 * @access	public
	 * @return	integer
	 */
	function total_rsegments()
	{
		return count($this->rsegments);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Fetch the entire URI string
	 *
	 * @access	public
	 * @return	string
	 */
	function uri_string()
	{
		return $this->uri_string;
	}

	
	// --------------------------------------------------------------------
	
	/**
	 * Fetch the entire Re-routed URI string
	 *
	 * @access	public
	 * @return	string
	 */
	function ruri_string()
	{
		return '/'.implode('/', $this->rsegment_array()).'/';
	}

}
// END URI Class

/* End of file URI.php */
/* Location: ./system/libraries/URI.php */
>>>>>>> d1820c69f526205428b481a5d333f6e657ccfb16:system/libraries/URI.php
