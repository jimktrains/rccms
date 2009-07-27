<<<<<<< HEAD:system/libraries/Calendar.php
<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Calendar creation library.
 *
 * $Id: Calendar.php 3769 2008-12-15 00:48:56Z zombor $
 *
 * @package    Calendar
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Calendar_Core extends Event_Subject {

	// Start the calendar on Sunday by default
	public static $start_monday = FALSE;

	// Month and year to use for calendaring
	protected $month;
	protected $year;

	// Week starts on Sunday
	protected $week_start = 0;

	// Observed data
	protected $observed_data;

	/**
	 * Returns an array of the names of the days, using the current locale.
	 *
	 * @param   integer  left of day names
	 * @return  array
	 */
	public static function days($length = TRUE)
	{
		// strftime day format
		$format = ($length > 3) ? '%A' : '%a';

		// Days of the week
		$days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

		if (Calendar::$start_monday === TRUE)
		{
			// Push Sunday to the end of the days
			array_push($days, array_shift($days));
		}

		if (strpos(Kohana::config('locale.language.0'), 'en') !== 0)
		{
			// This is a bit awkward, but it works properly and is reliable
			foreach ($days as $i => $day)
			{
				// Convert the English names to i18n names
				$days[$i] = strftime($format, strtotime($day));
			}
		}

		if (is_int($length) OR ctype_digit($length))
		{
			foreach ($days as $i => $day)
			{
				// Shorten the days to the expected length
				$days[$i] = utf8::substr($day, 0, $length);
			}
		}

		return $days;
	}

	/**
	 * Create a new Calendar instance. A month and year can be specified.
	 * By default, the current month and year are used.
	 *
	 * @param   integer  month number
	 * @param   integer  year number
	 * @return  object
	 */
	public static function factory($month = NULL, $year = NULL)
	{
		return new Calendar($month, $year);
	}

	/**
	 * Create a new Calendar instance. A month and year can be specified.
	 * By default, the current month and year are used.
	 *
	 * @param   integer  month number
	 * @param   integer  year number
	 * @return  void
	 */
	public function __construct($month = NULL, $year = NULL)
	{
		empty($month) and $month = date('n'); // Current month
		empty($year)  and $year  = date('Y'); // Current year

		// Set the month and year
		$this->month = (int) $month;
		$this->year  = (int) $year;

		if (Calendar::$start_monday === TRUE)
		{
			// Week starts on Monday
			$this->week_start = 1;
		}
	}

	/**
	 * Allows fetching the current month and year.
	 *
	 * @param   string  key to get
	 * @return  mixed
	 */
	public function __get($key)
	{
		if ($key === 'month' OR $key === 'year')
		{
			return $this->$key;
		}
	}

	/**
	 * Calendar_Event factory method.
	 *
	 * @param   string  unique name for the event
	 * @return  object  Calendar_Event
	 */
	public function event($name = NULL)
	{
		return new Calendar_Event($this);
	}

	/**
	 * Calendar_Event factory method.
	 *
	 * @chainable
	 * @param   string  standard event type
	 * @return  object
	 */
	public function standard($name)
	{
		switch ($name)
		{
			case 'today':
				// Add an event for the current day
				$this->attach($this->event()->condition('timestamp', strtotime('today'))->add_class('today'));
			break;
			case 'prev-next':
				// Add an event for padding days
				$this->attach($this->event()->condition('current', FALSE)->add_class('prev-next'));
			break;
			case 'holidays':
				// Base event
				$event = $this->event()->condition('current', TRUE)->add_class('holiday');

				// Attach New Years
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 1)->condition('day', 1));

				// Attach Valentine's Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 2)->condition('day', 14));

				// Attach St. Patrick's Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 3)->condition('day', 17));

				// Attach Easter
				$holiday = clone $event;
				$this->attach($holiday->condition('easter', TRUE));

				// Attach Memorial Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 5)->condition('day_of_week', 1)->condition('last_occurrence', TRUE));

				// Attach Independance Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 7)->condition('day', 4));

				// Attach Labor Day
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 9)->condition('day_of_week', 1)->condition('occurrence', 1));

				// Attach Halloween
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 10)->condition('day', 31));

				// Attach Thanksgiving
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 11)->condition('day_of_week', 4)->condition('occurrence', 4));

				// Attach Christmas
				$holiday = clone $event;
				$this->attach($holiday->condition('month', 12)->condition('day', 25));
			break;
			case 'weekends':
				// Weekend events
				$this->attach($this->event()->condition('weekend', TRUE)->add_class('weekend'));
			break;
		}

		return $this;
	}

	/**
	 * Returns an array for use with a view. The array contains an array for
	 * each week. Each week contains 7 arrays, with a day number and status:
	 * TRUE if the day is in the month, FALSE if it is padding.
	 *
	 * @return  array
	 */
	public function weeks()
	{
		// First day of the month as a timestamp
		$first = mktime(1, 0, 0, $this->month, 1, $this->year);

		// Total number of days in this month
		$total = (int) date('t', $first);

		// Last day of the month as a timestamp
		$last  = mktime(1, 0, 0, $this->month, $total, $this->year);

		// Make the month and week empty arrays
		$month = $week = array();

		// Number of days added. When this reaches 7, start a new week
		$days = 0;
		$week_number = 1;

		if (($w = (int) date('w', $first) - $this->week_start) < 0)
		{
			$w = 6;
		}

		if ($w > 0)
		{
			// Number of days in the previous month
			$n = (int) date('t', mktime(1, 0, 0, $this->month - 1, 1, $this->year));

			// i = number of day, t = number of days to pad
			for ($i = $n - $w + 1, $t = $w; $t > 0; $t--, $i++)
			{
				// Notify the listeners
				$this->notify(array($this->month - 1, $i, $this->year, $week_number, FALSE));

				// Add previous month padding days
				$week[] = array($i, FALSE, $this->observed_data);
				$days++;
			}
		}

		// i = number of day
		for ($i = 1; $i <= $total; $i++)
		{
			if ($days % 7 === 0)
			{
				// Start a new week
				$month[] = $week;
				$week = array();

				$week_number++;
			}

			// Notify the listeners
			$this->notify(array($this->month, $i, $this->year, $week_number, TRUE));

			// Add days to this month
			$week[] = array($i, TRUE, $this->observed_data);
			$days++;
		}

		if (($w = (int) date('w', $last) - $this->week_start) < 0)
		{
			$w = 6;
		}

		if ($w >= 0)
		{
			// i = number of day, t = number of days to pad
			for ($i = 1, $t = 6 - $w; $t > 0; $t--, $i++)
			{
				// Notify the listeners
				$this->notify(array($this->month + 1, $i, $this->year, $week_number, FALSE));

				// Add next month padding days
				$week[] = array($i, FALSE, $this->observed_data);
			}
		}

		if ( ! empty($week))
		{
			// Append the remaining days
			$month[] = $week;
		}

		return $month;
	}

	/**
	 * Adds new data from an observer. All event data contains and array of CSS
	 * classes and an array of output messages.
	 *
	 * @param   array  observer data.
	 * @return  void
	 */
	public function add_data(array $data)
	{
		// Add new classes
		$this->observed_data['classes'] += $data['classes'];

		if ( ! empty($data['output']))
		{
			// Only add output if it's not empty
			$this->observed_data['output'][] = $data['output'];
		}
	}

	/**
	 * Resets the observed data and sends a notify to all attached events.
	 *
	 * @param   array  UNIX timestamp
	 * @return  void
	 */
	public function notify($data)
	{
		// Reset observed data
		$this->observed_data = array
		(
			'classes' => array(),
			'output' => array(),
		);

		// Send a notify
		parent::notify($data);
	}

	/**
	 * Convert the calendar to HTML using the kohana_calendar view.
	 *
	 * @return  string
	 */
	public function render()
	{
		$view =  new View('kohana_calendar', array
		(
			'month'  => $this->month,
			'year'   => $this->year,
			'weeks'  => $this->weeks(),
		));

		return $view->render();
	}

	/**
	 * Magically convert this object to a string, the rendered calendar.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->render();
	}

} // End Calendar
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
 * CodeIgniter Calendar Class
 *
 * This class enables the creation of calendars
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/calendar.html
 */
class CI_Calendar {

	var $CI;
	var $lang;
	var $local_time;
	var $template		= '';
	var $start_day		= 'sunday';
	var $month_type 	= 'long';
	var $day_type		= 'abr';
	var $show_next_prev	= FALSE;
	var $next_prev_url	= '';

	/**
	 * Constructor
	 *
	 * Loads the calendar language file and sets the default time reference
	 *
	 * @access	public
	 */
	function CI_Calendar($config = array())
	{		
		$this->CI =& get_instance();
		
		if ( ! in_array('calendar_lang'.EXT, $this->CI->lang->is_loaded, TRUE))
		{
			$this->CI->lang->load('calendar');
		}

		$this->local_time = time();
		
		if (count($config) > 0)
		{
			$this->initialize($config);
		}
		
		log_message('debug', "Calendar Class Initialized");
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Initialize the user preferences
	 *
	 * Accepts an associative array as input, containing display preferences
	 *
	 * @access	public
	 * @param	array	config preferences
	 * @return	void
	 */	
	function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			if (isset($this->$key))
			{
				$this->$key = $val;
			}
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Generate the calendar
	 *
	 * @access	public
	 * @param	integer	the year
	 * @param	integer	the month
	 * @param	array	the data to be shown in the calendar cells
	 * @return	string
	 */
	function generate($year = '', $month = '', $data = array())
	{
		// Set and validate the supplied month/year
		if ($year == '')
			$year  = date("Y", $this->local_time);
			
		if ($month == '')
			$month = date("m", $this->local_time);
			
 		if (strlen($year) == 1)
			$year = '200'.$year;
		
 		if (strlen($year) == 2)
			$year = '20'.$year;

 		if (strlen($month) == 1)
			$month = '0'.$month;
		
		$adjusted_date = $this->adjust_date($month, $year);
		
		$month	= $adjusted_date['month'];
		$year	= $adjusted_date['year'];
		
		// Determine the total days in the month
		$total_days = $this->get_total_days($month, $year);
						
		// Set the starting day of the week
		$start_days	= array('sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6);
		$start_day = ( ! isset($start_days[$this->start_day])) ? 0 : $start_days[$this->start_day];
		
		// Set the starting day number
		$local_date = mktime(12, 0, 0, $month, 1, $year);
		$date = getdate($local_date);
		$day  = $start_day + 1 - $date["wday"];
		
		while ($day > 1)
		{
			$day -= 7;
		}
		
		// Set the current month/year/day
		// We use this to determine the "today" date
		$cur_year	= date("Y", $this->local_time);
		$cur_month	= date("m", $this->local_time);
		$cur_day	= date("j", $this->local_time);
		
		$is_current_month = ($cur_year == $year AND $cur_month == $month) ? TRUE : FALSE;
	
		// Generate the template data array
		$this->parse_template();
	
		// Begin building the calendar output						
		$out = $this->temp['table_open'];
		$out .= "\n";	

		$out .= "\n";		
		$out .= $this->temp['heading_row_start'];
		$out .= "\n";
		
		// "previous" month link
		if ($this->show_next_prev == TRUE)
		{
			// Add a trailing slash to the  URL if needed
			$this->next_prev_url = preg_replace("/(.+?)\/*$/", "\\1/",  $this->next_prev_url);
		
			$adjusted_date = $this->adjust_date($month - 1, $year);
			$out .= str_replace('{previous_url}', $this->next_prev_url.$adjusted_date['year'].'/'.$adjusted_date['month'], $this->temp['heading_previous_cell']);
			$out .= "\n";
		}

		// Heading containing the month/year
		$colspan = ($this->show_next_prev == TRUE) ? 5 : 7;
		
		$this->temp['heading_title_cell'] = str_replace('{colspan}', $colspan, $this->temp['heading_title_cell']);
		$this->temp['heading_title_cell'] = str_replace('{heading}', $this->get_month_name($month)."&nbsp;".$year, $this->temp['heading_title_cell']);
		
		$out .= $this->temp['heading_title_cell'];
		$out .= "\n";

		// "next" month link
		if ($this->show_next_prev == TRUE)
		{		
			$adjusted_date = $this->adjust_date($month + 1, $year);
			$out .= str_replace('{next_url}', $this->next_prev_url.$adjusted_date['year'].'/'.$adjusted_date['month'], $this->temp['heading_next_cell']);
		}

		$out .= "\n";		
		$out .= $this->temp['heading_row_end'];
		$out .= "\n";

		// Write the cells containing the days of the week
		$out .= "\n";	
		$out .= $this->temp['week_row_start'];
		$out .= "\n";

		$day_names = $this->get_day_names();

		for ($i = 0; $i < 7; $i ++)
		{
			$out .= str_replace('{week_day}', $day_names[($start_day + $i) %7], $this->temp['week_day_cell']);
		}

		$out .= "\n";
		$out .= $this->temp['week_row_end'];
		$out .= "\n";

		// Build the main body of the calendar
		while ($day <= $total_days)
		{
			$out .= "\n";
			$out .= $this->temp['cal_row_start'];
			$out .= "\n";

			for ($i = 0; $i < 7; $i++)
			{
				$out .= ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_start_today'] : $this->temp['cal_cell_start'];
			
				if ($day > 0 AND $day <= $total_days)
				{ 					
					if (isset($data[$day]))
					{	
						// Cells with content
						$temp = ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_content_today'] : $this->temp['cal_cell_content'];
						$out .= str_replace('{day}', $day, str_replace('{content}', $data[$day], $temp));
					}
					else
					{
						// Cells with no content
						$temp = ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_no_content_today'] : $this->temp['cal_cell_no_content'];
						$out .= str_replace('{day}', $day, $temp);
					}
				}
				else
				{
					// Blank cells
					$out .= $this->temp['cal_cell_blank'];
				}
				
				$out .= ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_end_today'] : $this->temp['cal_cell_end'];					  	
				$day++;
			}
			
			$out .= "\n";		
			$out .= $this->temp['cal_row_end'];
			$out .= "\n";		
		}

		$out .= "\n";		
		$out .= $this->temp['table_close'];

		return $out;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get Month Name
	 *
	 * Generates a textual month name based on the numeric
	 * month provided.
	 *
	 * @access	public
	 * @param	integer	the month
	 * @return	string
	 */
	function get_month_name($month)
	{
		if ($this->month_type == 'short')
		{
			$month_names = array('01' => 'cal_jan', '02' => 'cal_feb', '03' => 'cal_mar', '04' => 'cal_apr', '05' => 'cal_may', '06' => 'cal_jun', '07' => 'cal_jul', '08' => 'cal_aug', '09' => 'cal_sep', '10' => 'cal_oct', '11' => 'cal_nov', '12' => 'cal_dec');
		}
		else
		{
			$month_names = array('01' => 'cal_january', '02' => 'cal_february', '03' => 'cal_march', '04' => 'cal_april', '05' => 'cal_may', '06' => 'cal_june', '07' => 'cal_july', '08' => 'cal_august', '09' => 'cal_september', '10' => 'cal_october', '11' => 'cal_november', '12' => 'cal_december');
		}
		
		$month = $month_names[$month];
		
		if ($this->CI->lang->line($month) === FALSE)
		{
			return ucfirst(str_replace('cal_', '', $month));
		}

		return $this->CI->lang->line($month);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Get Day Names
	 *
	 * Returns an array of day names (Sunday, Monday, etc.) based
	 * on the type.  Options: long, short, abrev
	 *
	 * @access	public
	 * @param	string
	 * @return	array
	 */
	function get_day_names($day_type = '')
	{
		if ($day_type != '')
			$this->day_type = $day_type;
	
		if ($this->day_type == 'long')
		{
			$day_names = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
		}
		elseif ($this->day_type == 'short')
		{
			$day_names = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
		}
		else
		{
			$day_names = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
		}
	
		$days = array();
		foreach ($day_names as $val)
		{			
			$days[] = ($this->CI->lang->line('cal_'.$val) === FALSE) ? ucfirst($val) : $this->CI->lang->line('cal_'.$val);
		}
	
		return $days;
	}
 	
	// --------------------------------------------------------------------

	/**
	 * Adjust Date
	 *
	 * This function makes sure that we have a valid month/year.
	 * For example, if you submit 13 as the month, the year will
	 * increment and the month will become January.
	 *
	 * @access	public
	 * @param	integer	the month
	 * @param	integer	the year
	 * @return	array
	 */
	function adjust_date($month, $year)
	{
		$date = array();

		$date['month']	= $month;
		$date['year']	= $year;

		while ($date['month'] > 12)
		{
			$date['month'] -= 12;
			$date['year']++;
		}

		while ($date['month'] <= 0)
		{
			$date['month'] += 12;
			$date['year']--;
		}

		if (strlen($date['month']) == 1)
		{
			$date['month'] = '0'.$date['month'];
		}

		return $date;
	}
 	
	// --------------------------------------------------------------------

	/**
	 * Total days in a given month
	 *
	 * @access	public
	 * @param	integer	the month
	 * @param	integer	the year
	 * @return	integer
	 */
	function get_total_days($month, $year)
	{
		$days_in_month	= array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

		if ($month < 1 OR $month > 12)
		{
			return 0;
		}

		// Is the year a leap year?
		if ($month == 2)
		{
			if ($year % 400 == 0 OR ($year % 4 == 0 AND $year % 100 != 0))
			{
				return 29;
			}
		}

		return $days_in_month[$month - 1];
	}
	
	// --------------------------------------------------------------------

	/**
	 * Set Default Template Data
	 *
	 * This is used in the event that the user has not created their own template
	 *
	 * @access	public
	 * @return array
	 */
	function default_template()
	{
		return  array (
						'table_open' 				=> '<table border="0" cellpadding="4" cellspacing="0">',
						'heading_row_start' 		=> '<tr>',
						'heading_previous_cell'		=> '<th><a href="{previous_url}">&lt;&lt;</a></th>',
						'heading_title_cell' 		=> '<th colspan="{colspan}">{heading}</th>',
						'heading_next_cell' 		=> '<th><a href="{next_url}">&gt;&gt;</a></th>',
						'heading_row_end' 			=> '</tr>',
						'week_row_start' 			=> '<tr>',
						'week_day_cell' 			=> '<td>{week_day}</td>',
						'week_row_end' 				=> '</tr>',
						'cal_row_start' 			=> '<tr>',
						'cal_cell_start' 			=> '<td>',
						'cal_cell_start_today'		=> '<td>',
						'cal_cell_content'			=> '<a href="{content}">{day}</a>',
						'cal_cell_content_today'	=> '<a href="{content}"><strong>{day}</strong></a>',
						'cal_cell_no_content'		=> '{day}',
						'cal_cell_no_content_today'	=> '<strong>{day}</strong>',
						'cal_cell_blank'			=> '&nbsp;',
						'cal_cell_end'				=> '</td>',
						'cal_cell_end_today'		=> '</td>',
						'cal_row_end'				=> '</tr>',
						'table_close'				=> '</table>'
					);	
	}
	
	// --------------------------------------------------------------------

	/**
	 * Parse Template
	 *
	 * Harvests the data within the template {pseudo-variables}
	 * used to display the calendar
	 *
	 * @access	public
	 * @return	void
	 */
 	function parse_template()
 	{
		$this->temp = $this->default_template();
 	
 		if ($this->template == '')
 		{
 			return;
 		}
 		
		$today = array('cal_cell_start_today', 'cal_cell_content_today', 'cal_cell_no_content_today', 'cal_cell_end_today');
		
		foreach (array('table_open', 'table_close', 'heading_row_start', 'heading_previous_cell', 'heading_title_cell', 'heading_next_cell', 'heading_row_end', 'week_row_start', 'week_day_cell', 'week_row_end', 'cal_row_start', 'cal_cell_start', 'cal_cell_content', 'cal_cell_no_content',  'cal_cell_blank', 'cal_cell_end', 'cal_row_end', 'cal_cell_start_today', 'cal_cell_content_today', 'cal_cell_no_content_today', 'cal_cell_end_today') as $val)
		{
			if (preg_match("/\{".$val."\}(.*?)\{\/".$val."\}/si", $this->template, $match))
			{
				$this->temp[$val] = $match['1'];
			}
			else
			{
				if (in_array($val, $today, TRUE))
				{
					$this->temp[$val] = $this->temp[str_replace('_today', '', $val)];
				}
			}
		} 	
 	}

}

// END CI_Calendar class

/* End of file Calendar.php */
/* Location: ./system/libraries/Calendar.php */
>>>>>>> d1820c69f526205428b481a5d333f6e657ccfb16:system/libraries/Calendar.php
