<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_local_Model extends ORM {
	protected $table_name = "auth_local";
	protected $belongs_to = array('user');
	protected $ignored_columns = array('password_confirm');
	
	public function __set($key, $value){
		if ($key === 'password'){
			
			$value = Auth_Local_Driver::hash_password($value);
		}
		parent::__set($key, $value);
	}
	
	public function validate(array & $array, $save = FALSE){
		if(isset($array['password_confirm'])){
			$array = Validation::factory($array)
				->pre_filter('trim')
				->add_rules('password', 'required', 'length[6,255]')
				->add_rules('password_confirm', 'required', 'matches[password]');
		} else {
			$array = Validation::factory($array)
				->pre_filter('trim')
				->add_rules('password', 'required', 'length[6,255]');
		}
		
		return parent::validate($array, $save);
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
			$salt = Auth_local_model::find_salt(Auth_local_Model::hash($password));
		}

		$config = Kohana::config('auth');
		$config['salt_pattern'] = preg_split('/,\s*/', $config['salt_pattern']);
		// Password hash that the salt will be inserted into
		$hash = Auth_local_Model::hash($salt.$password);

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
		$config['salt_pattern'] = preg_split('/,\s*/', $config['salt_pattern']);
		foreach ($config['salt_pattern'] as $i => $offset){
			// Find salt characters, take a good long look...
			$salt .= $password[$offset + $i];
		}

		return $salt;
	}
}
?>