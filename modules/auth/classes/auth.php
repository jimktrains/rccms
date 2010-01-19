<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth {

	// Session instance
	protected $session;

	// Configuration
	protected $config;

	/**
	 * Return a static instance of Auth.
	 *
	 * @return  object
	 */
	public static function instance($driver_string = NULL, $config = array())
	{
		static $instance = array();

		if(!array_key_exists($driver_string, $instance)){
			$instance[$driver_string] = new Auth($driver_string, $config);
		}

		return $instance[$driver_string];
	}

	/**
	 * Loads Session and configuration options.
	 *
	 * @return  void
	 */
	public function __construct($driver_string = NULL, $config = array()){

		// Append default auth configuration
		// $config += Kohana::config('auth');

		if(is_null($driver_string)) $driver_string = $config['driver'];


		// Clean up the salt pattern and split it into an array
		$config['salt_pattern'] = preg_split('/,\s*/', Kohana::config('auth.salt_pattern'));

		// Save the config in the object
		$this->config = $config;

		// Set the driver class name
		$driver = 'Driver_Auth_'.$driver_string;
	
		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Exception('core.driver_not_found', $config['driver'], get_class($this));

		// Load the driver
		$driver = new $driver($config);

		if ( ! ($driver instanceof Driver_Auth))
			throw new Kohana_Exception('core.driver_implements', $config['driver'], get_class($this), 'Driver_Auth');

		// Load the driver for access
		$this->driver = $driver;
	}

	/**
	 * Check if there is an active session. Optionally allows checking for a
	 * specific role.
	 *
	 * @param   string   role name
	 * @return  boolean
	 */
	public function logged_in($role = NULL)
	{
		return $this->driver->logged_in($role);
	}

	/**
	 * Returns the currently logged in user, or FALSE.
	 *
	 * @return  mixed
	 */
	public function get_user()
	{
		return $this->driver->get_user();
	}

	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   string   user_name to log in
	 * @param   string   password to check against
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function login($user_name, $credentials, $remember = FALSE){
		return $this->driver->login($user_name, $credentials, $remember);
	}

	/**
	 * Registers a user
	 * 
	 * @param string user_name
	 * @param stirng email
	 * @param array  credentials (password, password_confirm)
	 */
	public function register($user_name, $email, $creds, $save = TRUE){
		return $this->driver->register($user_name, $email, $creds, $save = TRUE);
	}
	/**
	 * Attempt to automatically log a user in.
	 *
	 * @return  boolean
	 */
	public function auto_login()
	{
		return $this->driver->auto_login();
	}

	/**
	 * Force a login for a specific user_name.
	 *
	 * @param   mixed    user_name
	 * @return  boolean
	 */
	public function force_login($user_name)
	{
		return $this->driver->force_login($user_name);
	}

	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  completely destroy the session
	 * @return  boolean
	 */
	public function logout($destroy = FALSE)
	{
		return $this->driver->logout($destroy);
	}

} // End Auth