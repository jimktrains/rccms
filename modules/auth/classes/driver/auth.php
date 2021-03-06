<?php defined('SYSPATH') OR die('No direct access allowed.');
abstract class Driver_Auth {

	// Session instance
	protected $session;

	// Configuration
	protected $_config;

	/**
	 * Creates a new driver instance, loading the session and storing config.
	 *
	 * @param   array  configuration
	 * @return  void
	 */
	public function __construct(array $config){
		// Load Session
		$this->session = Session::instance();

		// Store config
		$this->_config = $config;
	}

	/**
	 * Checks if a session is active.
	 *
	 * @param   string   role name
	 * @param   array    collection of role names
	 * @return  boolean
	 */
	public function logged_in(){

		$user = $this->session->get($this->_config['session_key']);
		if (is_object($user) AND $user instanceof User_Model AND $user->loaded){
			return TRUE;
		}

		return FALSE;
	}
	
	public function save(){
		$this->prefs = json_encode($this->_prefs);
		parent::save();
	}


	/**
	 * Gets the currently logged in user from the session.
	 * Returns FALSE if no user is currently logged in.
	 *
	 * @return  mixed
	 */
	public function get_user(){

		if ($this->logged_in()){
			return $this->session->get($this->_config['session_key']);
		}
		return FALSE;
	}

	/**
	 * Logs a user in.
	 *
	 * @param   array   credentails
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	abstract public function login($user_name, array $credentials, $remember);

	/**
	 * Registers a user
	 * 
	 * @param string user_name
	 * @param stirng email
	 * @param array  credentials
	 */
	public function register($user_name, $email, $creds, $save = TRUE){
		$user = ORM::factory('user');
		$user->user_name = $user_name;
		$user->email = $email;
		if($save){
			$user->save();
		}
		return $user;
	}
	/**
	 * Forces a user to be logged in, without specifying a credentials.
	 *
	 * @param   mixed    user_name
	 * @return  boolean
	 */
	public function force_login($user){
		if ( ! is_object($user)){
			$user = ORM::factory('user', $user);
		}

		// Mark the session as forced, to prevent users from changing account information
		$this->session->auth_forced = TRUE;

		// Run the standard completion
		$this->complete_login($user);
	}

	/**
	 * Logs a user in, based on the authautologin cookie.
	 *
	 * @return  boolean
	 */
	public function auto_login(){
		if ($token = cookie::get('authautologin')){
			
			$token = ORM::factory('user_token', $token);

			if ($token->loaded AND $token->user->loaded){
				if ($token->user_agent === sha1(Kohana::$user_agent)){
					// Save the token to create a new unique token
					$token->save();

					cookie::set('authautologin', $token->token, $token->expires - time());
					$this->complete_login($token->user);
					return TRUE;
				}
				$token->delete();
			}
		}
		return FALSE;
	}

	/**
	 * Log a user out.
	 *
	 * @param   boolean  completely destroy the session
	 * @return  boolean
	 */
	public function logout($destroy){
		if ($token = cookie::get('authautologin')){
			cookie::delete('authautologin');
			$token = ORM::factory('user_token', $token);
			if ($token->loaded){
				$token->delete();
			}
		}
		if ($destroy === TRUE){
			Session::instance()->destroy();
		}
		else{
			$this->session->delete($this->_config['session_key']);
			$this->session->regenerate();
		}

		// Double check
		return ! $this->logged_in(NULL);
	}


	/**
	 * Completes a login by assigning the user to the session key.
	 *
	 * @param   string   user_name
	 * @return  TRUE
	 */
	protected function complete_login($user, $remember){
		if ( ! is_object($user)){
			throw "Needs to be an object"l
		}
		
		if ($remember === TRUE){
			$user->user_token->generate();
			#TODO: Finish session stuff
		}
		
		$this->session->regenerate();
		$this->session->key = $this->_config['session_key']
		$this->session->user = $user->user_name;
		$this->session->set("rc_ugh", "ugh");
		return TRUE;
	}

}