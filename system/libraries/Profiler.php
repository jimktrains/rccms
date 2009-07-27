<<<<<<< HEAD:system/libraries/Profiler.php
<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Adds useful information to the bottom of the current page for debugging and optimization purposes.
 *
 * Benchmarks   - The times and memory usage of benchmarks run by the Benchmark library.
 * Database     - The raw SQL and number of affected rows of Database queries.
 * Session Data - Data stored in the current session if using the Session library.
 * POST Data    - The name and values of any POST data submitted to the current page.
 * Cookie Data  - All cookies sent for the current request.
 *
 * $Id: Profiler.php 4383 2009-06-03 00:17:24Z ixmatus $
 *
 * @package    Profiler
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Profiler_Core {

	protected $profiles = array();
	protected $show;

	public function __construct()
	{
		// Add all built in profiles to event
		Event::add('profiler.run', array($this, 'benchmarks'));
		Event::add('profiler.run', array($this, 'database'));
		Event::add('profiler.run', array($this, 'session'));
		Event::add('profiler.run', array($this, 'post'));
		Event::add('profiler.run', array($this, 'cookies'));

		// Add profiler to page output automatically
		Event::add('system.display', array($this, 'render'));

		Kohana::log('debug', 'Profiler Library initialized');
	}

	/**
	 * Magic __call method. Creates a new profiler section object.
	 *
	 * @param   string   input type
	 * @param   string   input name
	 * @return  object
	 */
	public function __call($method, $args)
	{
		if ( ! $this->show OR (is_array($this->show) AND ! in_array($args[0], $this->show)))
			return FALSE;

		// Class name
		$class = 'Profiler_'.ucfirst($method);

		$class = new $class();

		$this->profiles[$args[0]] = $class;

		return $class;
	}

	/**
	 * Disables the profiler for this page only.
	 * Best used when profiler is autoloaded.
	 *
	 * @return  void
	 */
	public function disable()
	{
		// Removes itself from the event queue
		Event::clear('system.display', array($this, 'render'));
	}

	/**
	 * Render the profiler. Output is added to the bottom of the page by default.
	 *
	 * @param   boolean  return the output if TRUE
	 * @return  void|string
	 */
	public function render($return = FALSE)
	{
		$start = microtime(TRUE);

		$get = isset($_GET['profiler']) ? explode(',', $_GET['profiler']) : array();
		$this->show = empty($get) ? Kohana::config('profiler.show') : $get;

		Event::run('profiler.run', $this);

		$styles = '';
		foreach ($this->profiles as $profile)
		{
			$styles .= $profile->styles();
		}

		// Don't display if there's no profiles
		if (empty($this->profiles))
			return;

		// Load the profiler view
		$data = array
		(
			'profiles' => $this->profiles,
			'styles'   => $styles,
			'execution_time' => microtime(TRUE) - $start
		);
		$view = new View('kohana_profiler', $data);

		// Return rendered view if $return is TRUE
		if ($return === TRUE)
			return $view->render();

		// Add profiler data to the output
		if (stripos(Kohana::$output, '</body>') !== FALSE)
		{
			// Closing body tag was found, insert the profiler data before it
			Kohana::$output = str_ireplace('</body>', $view->render().'</body>', Kohana::$output);
		}
		else
		{
			// Append the profiler data to the output
			Kohana::$output .= $view->render();
		}
	}

	/**
	 * Benchmark times and memory usage from the Benchmark library.
	 *
	 * @return  void
	 */
	public function benchmarks()
	{
		if ( ! $table = $this->table('benchmarks'))
			return;

		$table->add_column();
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_row(array('Benchmarks', 'Time', 'Count', 'Memory'), 'kp-title', 'background-color: #FFE0E0');

		$benchmarks = Benchmark::get(TRUE);

		// Moves the first benchmark (total execution time) to the end of the array
		$benchmarks = array_slice($benchmarks, 1) + array_slice($benchmarks, 0, 1);

		text::alternate();
		foreach ($benchmarks as $name => $benchmark)
		{
			// Clean unique id from system benchmark names
			$name = ucwords(str_replace(array('_', '-'), ' ', str_replace(SYSTEM_BENCHMARK.'_', '', $name)));

			$data = array($name, number_format($benchmark['time'], 3), $benchmark['count'], number_format($benchmark['memory'] / 1024 / 1024, 2).'MB');
			$class = text::alternate('', 'kp-altrow');

			if ($name == 'Total Execution')
				$class = 'kp-totalrow';

			$table->add_row($data, $class);
		}
	}

	/**
	 * Database query benchmarks.
	 *
	 * @return  void
	 */
	public function database()
	{
		if ( ! $table = $this->table('database'))
			return;

		$table->add_column();
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_row(array('Queries', 'Time', 'Rows'), 'kp-title', 'background-color: #E0FFE0');

		$queries = Database::$benchmarks;

		text::alternate();
		$total_time = $total_rows = 0;
		foreach ($queries as $query)
		{
			$data = array($query['query'], number_format($query['time'], 3), $query['rows']);
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
			$total_time += $query['time'];
			$total_rows += $query['rows'];
		}

		$data = array('Total: ' . count($queries), number_format($total_time, 3), $total_rows);
		$table->add_row($data, 'kp-totalrow');
	}

	/**
	 * Session data.
	 *
	 * @return  void
	 */
	public function session()
	{
		if (empty($_SESSION)) return;

		if ( ! $table = $this->table('session'))
			return;

		$table->add_column('kp-name');
		$table->add_column();
		$table->add_row(array('Session', 'Value'), 'kp-title', 'background-color: #CCE8FB');

		text::alternate();
		foreach($_SESSION as $name => $value)
		{
			if (is_object($value))
			{
				$value = get_class($value).' [object]';
			}

			$data = array($name, $value);
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}
	}

	/**
	 * POST data.
	 *
	 * @return  void
	 */
	public function post()
	{
		if (empty($_POST)) return;

		if ( ! $table = $this->table('post'))
			return;

		$table->add_column('kp-name');
		$table->add_column();
		$table->add_row(array('POST', 'Value'), 'kp-title', 'background-color: #E0E0FF');

		text::alternate();
		foreach($_POST as $name => $value)
		{
			$data = array($name, $value);
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}
	}

	/**
	 * Cookie data.
	 *
	 * @return  void
	 */
	public function cookies()
	{
		if (empty($_COOKIE)) return;

		if ( ! $table = $this->table('cookies'))
			return;

		$table->add_column('kp-name');
		$table->add_column();
		$table->add_row(array('Cookies', 'Value'), 'kp-title', 'background-color: #FFF4D7');

		text::alternate();
		foreach($_COOKIE as $name => $value)
		{
			$data = array($name, $value);
			$class = text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}
	}
}
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
 * CodeIgniter Profiler Class
 *
 * This class enables you to display benchmark, query, and other data
 * in order to help with debugging and optimization.
 *
 * Note: At some point it would be good to move all the HTML in this class
 * into a set of template files in order to allow customization.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/general/profiling.html
 */
class CI_Profiler {

	var $CI;
 	
 	function CI_Profiler()
 	{
 		$this->CI =& get_instance();
 		$this->CI->load->language('profiler');
 	}
 	
	// --------------------------------------------------------------------

	/**
	 * Auto Profiler
	 *
	 * This function cycles through the entire array of mark points and
	 * matches any two points that are named identically (ending in "_start"
	 * and "_end" respectively).  It then compiles the execution times for
	 * all points and returns it as an array
	 *
	 * @access	private
	 * @return	array
	 */
 	function _compile_benchmarks()
 	{
  		$profile = array();
 		foreach ($this->CI->benchmark->marker as $key => $val)
 		{
 			// We match the "end" marker so that the list ends
 			// up in the order that it was defined
 			if (preg_match("/(.+?)_end/i", $key, $match))
 			{ 			
 				if (isset($this->CI->benchmark->marker[$match[1].'_end']) AND isset($this->CI->benchmark->marker[$match[1].'_start']))
 				{
 					$profile[$match[1]] = $this->CI->benchmark->elapsed_time($match[1].'_start', $key);
 				}
 			}
 		}

		// Build a table containing the profile data.
		// Note: At some point we should turn this into a template that can
		// be modified.  We also might want to make this data available to be logged
	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #990000;padding:6px 10px 10px 10px;margin:0 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#990000;">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_benchmarks').'&nbsp;&nbsp;</legend>';
		$output .= "\n";			
		$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
		
		foreach ($profile as $key => $val)
		{
			$key = ucwords(str_replace(array('_', '-'), ' ', $key));
			$output .= "<tr><td width='50%' style='color:#000;font-weight:bold;background-color:#ddd;'>".$key."&nbsp;&nbsp;</td><td width='50%' style='color:#990000;font-weight:normal;background-color:#ddd;'>".$val."</td></tr>\n";
		}
		
		$output .= "</table>\n";
		$output .= "</fieldset>";
 		
 		return $output;
 	}
 	
	// --------------------------------------------------------------------

	/**
	 * Compile Queries
	 *
	 * @access	private
	 * @return	string
	 */	
	function _compile_queries()
	{
		$dbs = array();
		
		// Let's determine which databases are currently connected to
		foreach (get_object_vars($this->CI) as $CI_object)
		{
			if ( is_subclass_of(get_class($CI_object), 'CI_DB') )
			{
				$dbs[] = $CI_object;
			}
		}
					
		if (count($dbs) == 0)
		{
			$output  = "\n\n";
			$output .= '<fieldset style="border:1px solid #0000FF;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
			$output .= "\n";
			$output .= '<legend style="color:#0000FF;">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_queries').'&nbsp;&nbsp;</legend>';
			$output .= "\n";		
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
			$output .="<tr><td width='100%' style='color:#0000FF;font-weight:normal;background-color:#eee;'>".$this->CI->lang->line('profiler_no_db')."</td></tr>\n";
			$output .= "</table>\n";
			$output .= "</fieldset>";
			
			return $output;
		}
		
		// Load the text helper so we can highlight the SQL
		$this->CI->load->helper('text');

		// Key words we want bolded
		$highlight = array('SELECT', 'DISTINCT', 'FROM', 'WHERE', 'AND', 'LEFT&nbsp;JOIN', 'ORDER&nbsp;BY', 'GROUP&nbsp;BY', 'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR', 'HAVING', 'OFFSET', 'NOT&nbsp;IN', 'IN', 'LIKE', 'NOT&nbsp;LIKE', 'COUNT', 'MAX', 'MIN', 'ON', 'AS', 'AVG', 'SUM', '(', ')');

		$output  = "\n\n";
			
		foreach ($dbs as $db)
		{
			$output .= '<fieldset style="border:1px solid #0000FF;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
			$output .= "\n";
			$output .= '<legend style="color:#0000FF;">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_database').':&nbsp; '.$db->database.'&nbsp;&nbsp;&nbsp;'.$this->CI->lang->line('profiler_queries').': '.count($this->CI->db->queries).'&nbsp;&nbsp;&nbsp;</legend>';
			$output .= "\n";		
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
		
			if (count($db->queries) == 0)
			{
				$output .= "<tr><td width='100%' style='color:#0000FF;font-weight:normal;background-color:#eee;'>".$this->CI->lang->line('profiler_no_queries')."</td></tr>\n";
			}
			else
			{				
				foreach ($db->queries as $key => $val)
				{					
					$time = number_format($db->query_times[$key], 4);

					$val = highlight_code($val, ENT_QUOTES);
	
					foreach ($highlight as $bold)
					{
						$val = str_replace($bold, '<strong>'.$bold.'</strong>', $val);	
					}
					
					$output .= "<tr><td width='1%' valign='top' style='color:#990000;font-weight:normal;background-color:#ddd;'>".$time."&nbsp;&nbsp;</td><td style='color:#000;font-weight:normal;background-color:#ddd;'>".$val."</td></tr>\n";
				}
			}
			
			$output .= "</table>\n";
			$output .= "</fieldset>";
			
		}
		
		return $output;
	}

	
	// --------------------------------------------------------------------

	/**
	 * Compile $_GET Data
	 *
	 * @access	private
	 * @return	string
	 */	
	function _compile_get()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #cd6e00;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#cd6e00;">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_get_data').'&nbsp;&nbsp;</legend>';
		$output .= "\n";
				
		if (count($_GET) == 0)
		{
			$output .= "<div style='color:#cd6e00;font-weight:normal;padding:4px 0 4px 0'>".$this->CI->lang->line('profiler_no_get')."</div>";
		}
		else
		{
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
		
			foreach ($_GET as $key => $val)
			{
				if ( ! is_numeric($key))
				{
					$key = "'".$key."'";
				}
			
				$output .= "<tr><td width='50%' style='color:#000;background-color:#ddd;'>&#36;_GET[".$key."]&nbsp;&nbsp; </td><td width='50%' style='color:#cd6e00;font-weight:normal;background-color:#ddd;'>";
				if (is_array($val))
				{
					$output .= "<pre>" . htmlspecialchars(stripslashes(print_r($val, true))) . "</pre>";
				}
				else
				{
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>\n";
			}
			
			$output .= "</table>\n";
		}
		$output .= "</fieldset>";

		return $output;	
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Compile $_POST Data
	 *
	 * @access	private
	 * @return	string
	 */	
	function _compile_post()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #009900;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#009900;">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_post_data').'&nbsp;&nbsp;</legend>';
		$output .= "\n";
				
		if (count($_POST) == 0)
		{
			$output .= "<div style='color:#009900;font-weight:normal;padding:4px 0 4px 0'>".$this->CI->lang->line('profiler_no_post')."</div>";
		}
		else
		{
			$output .= "\n\n<table cellpadding='4' cellspacing='1' border='0' width='100%'>\n";
		
			foreach ($_POST as $key => $val)
			{
				if ( ! is_numeric($key))
				{
					$key = "'".$key."'";
				}
			
				$output .= "<tr><td width='50%' style='color:#000;background-color:#ddd;'>&#36;_POST[".$key."]&nbsp;&nbsp; </td><td width='50%' style='color:#009900;font-weight:normal;background-color:#ddd;'>";
				if (is_array($val))
				{
					$output .= "<pre>" . htmlspecialchars(stripslashes(print_r($val, true))) . "</pre>";
				}
				else
				{
					$output .= htmlspecialchars(stripslashes($val));
				}
				$output .= "</td></tr>\n";
			}
			
			$output .= "</table>\n";
		}
		$output .= "</fieldset>";

		return $output;	
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Show query string
	 *
	 * @access	private
	 * @return	string
	 */	
	function _compile_uri_string()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #000;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#000;">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_uri_string').'&nbsp;&nbsp;</legend>';
		$output .= "\n";
		
		if ($this->CI->uri->uri_string == '')
		{
			$output .= "<div style='color:#000;font-weight:normal;padding:4px 0 4px 0'>".$this->CI->lang->line('profiler_no_uri')."</div>";
		}
		else
		{
			$output .= "<div style='color:#000;font-weight:normal;padding:4px 0 4px 0'>".$this->CI->uri->uri_string."</div>";				
		}
		
		$output .= "</fieldset>";

		return $output;	
	}

	// --------------------------------------------------------------------
	
	/**
	 * Show the controller and function that were called
	 *
	 * @access	private
	 * @return	string
	 */	
	function _compile_controller_info()
	{	
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #995300;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#995300;">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_controller_info').'&nbsp;&nbsp;</legend>';
		$output .= "\n";
		
		$output .= "<div style='color:#995300;font-weight:normal;padding:4px 0 4px 0'>".$this->CI->router->fetch_class()."/".$this->CI->router->fetch_method()."</div>";				

		
		$output .= "</fieldset>";

		return $output;	
	}
	// --------------------------------------------------------------------
	
	/**
	 * Compile memory usage
	 *
	 * Display total used memory
	 *
	 * @access	public
	 * @return	string
	 */
	function _compile_memory_usage()
	{
		$output  = "\n\n";
		$output .= '<fieldset style="border:1px solid #5a0099;padding:6px 10px 10px 10px;margin:20px 0 20px 0;background-color:#eee">';
		$output .= "\n";
		$output .= '<legend style="color:#5a0099;">&nbsp;&nbsp;'.$this->CI->lang->line('profiler_memory_usage').'&nbsp;&nbsp;</legend>';
		$output .= "\n";
		
		if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '')
		{
			$output .= "<div style='color:#5a0099;font-weight:normal;padding:4px 0 4px 0'>".number_format($usage).' bytes</div>';
		}
		else
		{
			$output .= "<div style='color:#5a0099;font-weight:normal;padding:4px 0 4px 0'>".$this->CI->lang->line('profiler_no_memory_usage')."</div>";				
		}
		
		$output .= "</fieldset>";

		return $output;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Run the Profiler
	 *
	 * @access	private
	 * @return	string
	 */	
	function run()
	{
		$output = "<div id='codeigniter_profiler' style='clear:both;background-color:#fff;padding:10px;'>";

		$output .= $this->_compile_uri_string();
		$output .= $this->_compile_controller_info();
		$output .= $this->_compile_memory_usage();
		$output .= $this->_compile_benchmarks();
		$output .= $this->_compile_get();
		$output .= $this->_compile_post();
		$output .= $this->_compile_queries();

		$output .= '</div>';

		return $output;
	}

}

// END CI_Profiler class

/* End of file Profiler.php */
/* Location: ./system/libraries/Profiler.php */
>>>>>>> d1820c69f526205428b481a5d333f6e657ccfb16:system/libraries/Profiler.php
