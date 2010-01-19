<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Auth_local extends ORM {
	protected $_table_name = "auth_local";
	protected $_belongs_to = array('user'=>array());
	protected $_ignored_columns = array('password_confirm');
	
	public function __set($key, $value){
#		kohana::auto_load('Driver_Auth_Local');
		if (!strcmp($key, 'password') or !strcmp($key, 'password_confirm')){
			$value = Model_Auth_local::hash_password($value);
		}
		parent::__set($key, $value);
	}
	
	public function validate(){
		$this->_filters += array(TRUE=>array('trim'=>array()));
		$this->_rules = array('password'=> array(
			'min_length'=>array(6))
		);
			

		if(isset($this->password_confirm)){
			$this->_rules +=array('password_confirm'=> array(
				'matches'=>array('password'))
			);
		}		
		return parent::check();
	}
	
	/**
	 * Creates a hashed password from a plaintext password, inserting salt
	 * based on the configured salt pattern.
	 *
	 * @param   string  plaintext password
	 * @return  string  hashed password string
	 */
	static public function hash_password($password, $salt = FALSE){
		if ($salt === FALSE){
			// Create a salt seed, same length as the number of offsets in the pattern
			//$salt = substr($this->hash(uniqid(NULL, TRUE)), 0, count($this->config['salt_pattern']));
			$salt = Model_Auth_local::find_salt(Model_Auth_local::hash($password));
		}

		$config = Kohana::config('auth');
		if(! is_array($config['salt_pattern'])){
			$config['salt_pattern'] = preg_split('/,\s*/', $config['salt_pattern']);
		}
		// Password hash that the salt will be inserted into
		$hash = Model_Auth_local::hash($salt.$password);

		// Change salt to an array
		$salt = str_split($salt, 1);

		// Returned password
		$password = '';

		// Used to calculate the length of splits
		$last_offset = 0;

		foreach ($config['salt_pattern'] as $offset){
			// Split a new part of the hash off
			$part = substr($hash, 0, $offset - $last_offset);

			// Cut the current part out of the hash
			$hash = substr($hash, $offset - $last_offset);

			// Add the part to the password, appending the salt character
			$password .= $part.array_shift($salt);

			// Set the last offset to the current offset
			$last_offset = $offset;
		}

		// Return the password, with the remaining hash appended
		return $password.$hash;
	}

	/**
	 * Perform a hash, using the configured method.
	 *
	 * @param   string  string to hash
	 * @return  string
	 */
	static public function hash($str){
		$config = Kohana::config('auth');
		return hash($config['hash_method'], $str);
	}

	/**
	 * Finds the salt from a password, based on the configured salt pattern.
	 *
	 * @param   string  hashed password
	 * @return  string
	 */
	static public function find_salt($password){
		$salt = '';
		$config = Kohana::config('auth');
		if(! is_array($config['salt_pattern'])){
			$config['salt_pattern'] = preg_split('/,\s*/', $config['salt_pattern']);
		}
		foreach ($config['salt_pattern'] as $i => $offset){
			// Find salt characters, take a good long look...
			$salt .= $password[$offset + $i];
		}

		return $salt;
	}
}
?>