<<<<<<< HEAD:system/libraries/Router.php
<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Router
 *
 * $Id: Router.php 4391 2009-06-04 03:10:12Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Router_Core {

	protected static $routes;

	public static $current_uri  = '';
	public static $query_string = '';
	public static $complete_uri = '';
	public static $routed_uri   = '';
	public static $url_suffix   = '';

	public static $segments;
	public static $rsegments;

	public static $controller;
	public static $controller_path;

	public static $method    = 'index';
	public static $arguments = array();

	/**
	 * Router setup routine. Automatically called during Kohana setup process.
	 *
	 * @return  void
	 */
	public static function setup()
	{
		if ( ! empty($_SERVER['QUERY_STRING']))
		{
			// Set the query string to the current query string
			Router::$query_string = '?'.trim($_SERVER['QUERY_STRING'], '&/');
		}

		if (Router::$routes === NULL)
		{
			// Load routes
			Router::$routes = Kohana::config('routes');
		}

		// Default route status
		$default_route = FALSE;

		if (Router::$current_uri === '')
		{
			// Make sure the default route is set
			if ( ! isset(Router::$routes['_default']))
				throw new Kohana_Exception('core.no_default_route');

			// Use the default route when no segments exist
			Router::$current_uri = Router::$routes['_default'];

			// Default route is in use
			$default_route = TRUE;
		}

		// Make sure the URL is not tainted with HTML characters
		Router::$current_uri = html::specialchars(Router::$current_uri, FALSE);

		// Remove all dot-paths from the URI, they are not valid
		Router::$current_uri = preg_replace('#\.[\s./]*/#', '', Router::$current_uri);

		// At this point segments, rsegments, and current URI are all the same
		Router::$segments = Router::$rsegments = Router::$current_uri = trim(Router::$current_uri, '/');

		// Set the complete URI
		Router::$complete_uri = Router::$current_uri.Router::$query_string;

		// Explode the segments by slashes
		Router::$segments = ($default_route === TRUE OR Router::$segments === '') ? array() : explode('/', Router::$segments);

		if ($default_route === FALSE AND count(Router::$routes) > 1)
		{
			// Custom routing
			Router::$rsegments = Router::routed_uri(Router::$current_uri);
		}

		// The routed URI is now complete
		Router::$routed_uri = Router::$rsegments;

		// Routed segments will never be empty
		Router::$rsegments = explode('/', Router::$rsegments);

		// Prepare to find the controller
		$controller_path = '';
		$method_segment  = NULL;

		// Paths to search
		$paths = Kohana::include_paths();

		foreach (Router::$rsegments as $key => $segment)
		{
			// Add the segment to the search path
			$controller_path .= $segment;

			$found = FALSE;
			foreach ($paths as $dir)
			{
				// Search within controllers only
				$dir .= 'controllers/';

				if (is_dir($dir.$controller_path) OR is_file($dir.$controller_path.EXT))
				{
					// Valid path
					$found = TRUE;

					// The controller must be a file that exists with the search path
					if ($c = str_replace('\\', '/', realpath($dir.$controller_path.EXT))
					    AND is_file($c) AND strpos($c, $dir) === 0)
					{
						// Set controller name
						Router::$controller = $segment;

						// Change controller path
						Router::$controller_path = $c;

						// Set the method segment
						$method_segment = $key + 1;

						// Stop searching
						break;
					}
				}
			}

			if ($found === FALSE)
			{
				// Maximum depth has been reached, stop searching
				break;
			}

			// Add another slash
			$controller_path .= '/';
		}

		if ($method_segment !== NULL AND isset(Router::$rsegments[$method_segment]))
		{
			// Set method
			Router::$method = Router::$rsegments[$method_segment];

			if (isset(Router::$rsegments[$method_segment + 1]))
			{
				// Set arguments
				Router::$arguments = array_slice(Router::$rsegments, $method_segment + 1);
			}
		}

		// Last chance to set routing before a 404 is triggered
		Event::run('system.post_routing');

		if (Router::$controller === NULL)
		{
			// No controller was found, so no page can be rendered
			Event::run('system.404');
		}
	}

	/**
	 * Attempts to determine the current URI using CLI, GET, PATH_INFO, ORIG_PATH_INFO, or PHP_SELF.
	 *
	 * @return  void
	 */
	public static function find_uri()
	{
		if (PHP_SAPI === 'cli')
		{
			// Command line requires a bit of hacking
			if (isset($_SERVER['argv'][1]))
			{
				Router::$current_uri = $_SERVER['argv'][1];

				// Remove GET string from segments
				if (($query = strpos(Router::$current_uri, '?')) !== FALSE)
				{
					list (Router::$current_uri, $query) = explode('?', Router::$current_uri, 2);

					// Parse the query string into $_GET
					parse_str($query, $_GET);

					// Convert $_GET to UTF-8
					$_GET = utf8::clean($_GET);
				}
			}
		}
		elseif (isset($_GET['kohana_uri']))
		{
			// Use the URI defined in the query string
			Router::$current_uri = $_GET['kohana_uri'];

			// Remove the URI from $_GET
			unset($_GET['kohana_uri']);

			// Remove the URI from $_SERVER['QUERY_STRING']
			$_SERVER['QUERY_STRING'] = preg_replace('~\bkohana_uri\b[^&]*+&?~', '', $_SERVER['QUERY_STRING']);
		}
		elseif (isset($_SERVER['PATH_INFO']) AND $_SERVER['PATH_INFO'])
		{
			Router::$current_uri = $_SERVER['PATH_INFO'];
		}
		elseif (isset($_SERVER['ORIG_PATH_INFO']) AND $_SERVER['ORIG_PATH_INFO'])
		{
			Router::$current_uri = $_SERVER['ORIG_PATH_INFO'];
		}
		elseif (isset($_SERVER['PHP_SELF']) AND $_SERVER['PHP_SELF'])
		{
			Router::$current_uri = $_SERVER['PHP_SELF'];
		}
		
		if (($strpos_fc = strpos(Router::$current_uri, KOHANA)) !== FALSE)
		{
			// Remove the front controller from the current uri
			Router::$current_uri = (string) substr(Router::$current_uri, $strpos_fc + strlen(KOHANA));
		}
		
		// Remove slashes from the start and end of the URI
		Router::$current_uri = trim(Router::$current_uri, '/');
		
		if (Router::$current_uri !== '')
		{
			if ($suffix = Kohana::config('core.url_suffix') AND strpos(Router::$current_uri, $suffix) !== FALSE)
			{
				// Remove the URL suffix
				Router::$current_uri = preg_replace('#'.preg_quote($suffix).'$#u', '', Router::$current_uri);

				// Set the URL suffix
				Router::$url_suffix = $suffix;
			}

			// Reduce multiple slashes into single slashes
			Router::$current_uri = preg_replace('#//+#', '/', Router::$current_uri);
		}
	}

	/**
	 * Generates routed URI from given URI.
	 *
	 * @param  string  URI to convert
	 * @return string  Routed uri
	 */
	public static function routed_uri($uri)
	{
		if (Router::$routes === NULL)
		{
			// Load routes
			Router::$routes = Kohana::config('routes');
		}

		// Prepare variables
		$routed_uri = $uri = trim($uri, '/');

		if (isset(Router::$routes[$uri]))
		{
			// Literal match, no need for regex
			$routed_uri = Router::$routes[$uri];
		}
		else
		{
			// Loop through the routes and see if anything matches
			foreach (Router::$routes as $key => $val)
			{
				if ($key === '_default') continue;

				// Trim slashes
				$key = trim($key, '/');
				$val = trim($val, '/');

				if (preg_match('#^'.$key.'$#u', $uri))
				{
					if (strpos($val, '$') !== FALSE)
					{
						// Use regex routing
						$routed_uri = preg_replace('#^'.$key.'$#u', $val, $uri);
					}
					else
					{
						// Standard routing
						$routed_uri = $val;
					}

					// A valid route has been found
					break;
				}
			}
		}

		if (isset(Router::$routes[$routed_uri]))
		{
			// Check for double routing (without regex)
			$routed_uri = Router::$routes[$routed_uri];
		}

		return trim($routed_uri, '/');
	}

} // End Router
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
 * Router Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author		ExpressionEngine Dev Team
 * @category	Libraries
 * @link		http://codeigniter.com/user_guide/general/routing.html
 */
class CI_Router {

	var $config;	
	var $routes 		= array();
	var $error_routes	= array();
	var $class			= '';
	var $method			= 'index';
	var $directory		= '';
	var $uri_protocol 	= 'auto';
	var $default_controller;
	var $scaffolding_request = FALSE; // Must be set to FALSE
	
	/**
	 * Constructor
	 *
	 * Runs the route mapping function.
	 */
	function CI_Router()
	{
		$this->config =& load_class('Config');
		$this->uri =& load_class('URI');
		$this->_set_routing();
		log_message('debug', "Router Class Initialized");
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set the route mapping
	 *
	 * This function determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @access	private
	 * @return	void
	 */
	function _set_routing()
	{
		// Are query strings enabled in the config file?
		// If so, we're done since segment based URIs are not used with query strings.
		if ($this->config->item('enable_query_strings') === TRUE AND isset($_GET[$this->config->item('controller_trigger')]))
		{
			$this->set_class(trim($this->uri->_filter_uri($_GET[$this->config->item('controller_trigger')])));

			if (isset($_GET[$this->config->item('function_trigger')]))
			{
				$this->set_method(trim($this->uri->_filter_uri($_GET[$this->config->item('function_trigger')])));
			}
			
			return;
		}
		
		// Load the routes.php file.
		@include(APPPATH.'config/routes'.EXT);
		$this->routes = ( ! isset($route) OR ! is_array($route)) ? array() : $route;
		unset($route);

		// Set the default controller so we can display it in the event
		// the URI doesn't correlated to a valid controller.
		$this->default_controller = ( ! isset($this->routes['default_controller']) OR $this->routes['default_controller'] == '') ? FALSE : strtolower($this->routes['default_controller']);	
		
		// Fetch the complete URI string
		$this->uri->_fetch_uri_string();
	
		// Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
		if ($this->uri->uri_string == '')
		{
			if ($this->default_controller === FALSE)
			{
				show_error("Unable to determine what should be displayed. A default route has not been specified in the routing file.");
			}

			// Turn the default route into an array.  We explode it in the event that
			// the controller is located in a subfolder
			$segments = $this->_validate_request(explode('/', $this->default_controller));

			// Set the class and method
			$this->set_class($segments[0]);
			$this->set_method('index');
			
			// Assign the segments to the URI class
			$this->uri->rsegments = $segments;
			
			// re-index the routed segments array so it starts with 1 rather than 0
			$this->uri->_reindex_segments();
			
			log_message('debug', "No URI present. Default controller set.");
			return;
		}
		unset($this->routes['default_controller']);
		
		// Do we need to remove the URL suffix?
		$this->uri->_remove_url_suffix();
		
		// Compile the segments into an array
		$this->uri->_explode_segments();
		
		// Parse any custom routing that may exist
		$this->_parse_routes();		
		
		// Re-index the segment array so that it starts with 1 rather than 0
		$this->uri->_reindex_segments();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set the Route
	 *
	 * This function takes an array of URI segments as
	 * input, and sets the current class/method
	 *
	 * @access	private
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	function _set_request($segments = array())
	{	
		$segments = $this->_validate_request($segments);
		
		if (count($segments) == 0)
		{
			return;
		}
						
		$this->set_class($segments[0]);
		
		if (isset($segments[1]))
		{
			// A scaffolding request. No funny business with the URL
			if ($this->routes['scaffolding_trigger'] == $segments[1] AND $segments[1] != '_ci_scaffolding')
			{
				$this->scaffolding_request = TRUE;
				unset($this->routes['scaffolding_trigger']);
			}
			else
			{
				// A standard method request
				$this->set_method($segments[1]);
			}
		}
		else
		{
			// This lets the "routed" segment array identify that the default
			// index method is being used.
			$segments[1] = 'index';
		}
		
		// Update our "routed" segment array to contain the segments.
		// Note: If there is no custom routing, this array will be
		// identical to $this->uri->segments
		$this->uri->rsegments = $segments;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Validates the supplied segments.  Attempts to determine the path to
	 * the controller.
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */	
	function _validate_request($segments)
	{
		// Does the requested controller exist in the root folder?
		if (file_exists(APPPATH.'controllers/'.$segments[0].EXT))
		{
			return $segments;
		}

		// Is the controller in a sub-folder?
		if (is_dir(APPPATH.'controllers/'.$segments[0]))
		{		
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			$segments = array_slice($segments, 1);
			
			if (count($segments) > 0)
			{
				// Does the requested controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].EXT))
				{
					show_404($this->fetch_directory().$segments[0]);
				}
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
			
				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.EXT))
				{
					$this->directory = '';
					return array();
				}
			
			}

			return $segments;
		}

		// Can't find the requested controller...
		show_404($segments[0]);
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse Routes
	 *
	 * This function matches any routes that may exist in
	 * the config/routes.php file against the URI to
	 * determine if the class/method need to be remapped.
	 *
	 * @access	private
	 * @return	void
	 */
	function _parse_routes()
	{
		// Do we even have any custom routing to deal with?
		// There is a default scaffolding trigger, so we'll look just for 1
		if (count($this->routes) == 1)
		{
			$this->_set_request($this->uri->segments);
			return;
		}

		// Turn the segment array into a URI string
		$uri = implode('/', $this->uri->segments);

		// Is there a literal match?  If so we're done
		if (isset($this->routes[$uri]))
		{
			$this->_set_request(explode('/', $this->routes[$uri]));		
			return;
		}
				
		// Loop through the route array looking for wild-cards
		foreach ($this->routes as $key => $val)
		{						
			// Convert wild-cards to RegEx
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));
			
			// Does the RegEx match?
			if (preg_match('#^'.$key.'$#', $uri))
			{			
				// Do we have a back-reference?
				if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
				{
					$val = preg_replace('#^'.$key.'$#', $val, $uri);
				}
			
				$this->_set_request(explode('/', $val));		
				return;
			}
		}

		// If we got this far it means we didn't encounter a
		// matching route so we'll set the site default route
		$this->_set_request($this->uri->segments);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Set the class name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */	
	function set_class($class)
	{
		$this->class = $class;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Fetch the current class
	 *
	 * @access	public
	 * @return	string
	 */	
	function fetch_class()
	{
		return $this->class;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 *  Set the method name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */	
	function set_method($method)
	{
		$this->method = $method;
	}

	// --------------------------------------------------------------------
	
	/**
	 *  Fetch the current method
	 *
	 * @access	public
	 * @return	string
	 */	
	function fetch_method()
	{
		if ($this->method == $this->fetch_class())
		{
			return 'index';
		}

		return $this->method;
	}

	// --------------------------------------------------------------------
	
	/**
	 *  Set the directory name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */	
	function set_directory($dir)
	{
		$this->directory = $dir.'/';
	}

	// --------------------------------------------------------------------
	
	/**
	 *  Fetch the sub-directory (if any) that contains the requested controller class
	 *
	 * @access	public
	 * @return	string
	 */	
	function fetch_directory()
	{
		return $this->directory;
	}

}
// END Router Class

/* End of file Router.php */
/* Location: ./system/libraries/Router.php */
>>>>>>> d1820c69f526205428b481a5d333f6e657ccfb16:system/libraries/Router.php
