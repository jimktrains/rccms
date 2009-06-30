<?php defined('SYSPATH') OR die('No direct access allowed.');

class User_Model extends ORM {

	protected $has_many = array('user_tokens');
	protected $has_one = array('auth_local');

	/**
	 * Validates and optionally saves a new user record from an array.
	 *
	 * @param  array    values to check
	 * @param  boolean  save the record when validation succeeds
	 * @return boolean
	 */
	public  function validate(array & $array, $save = FALSE){
		$array = Validation::factory($array)
			->pre_filter('trim')
			->add_rules('email', 'required', 'length[4,255]', 'valid::email', array($this, 'email_available'))
			->add_rules('user_name', 'required', 'length[4,32]', 'chars[a-zA-Z0-9_.]', array($this, 'user_name_available'));				
		return parent::validate($array, $save);
	}

	/**
	 * Tests if a user_name exists in the database. This can be used as a
	 * Valdidation rule.
	 *
	 * @param   mixed    id to check
	 * @return  boolean
	 * 
	 */
	public function user_name_exists($id){
		return $this->unique_key_exists($id);
	}

	/**
	 * Does the reverse of unique_key_exists() by returning TRUE if user id is available
	 * Validation rule.
	 *
	 * @param    mixed    id to check 
	 * @return   boolean
	 */
	public function user_name_available($user_name){
		return $this->key_avaliable($user_name);
	}

	/**
	 * Does the reverse of unique_key_exists() by returning TRUE if email is available
	 * Validation Rule
	 *
	 * @param string $email 
	 * @return void
	 */
	public function email_available($email){
		return $this->key_avaliable($email);
	}

	public function key_avaliable($id){
		return ! $this->unique_key_exists($id);
	}

	/**
	 * Tests if a unique key value exists in the database
	 *
	 * @param   mixed        value  the value to test
	 * @return  boolean
	 */
	public function unique_key_exists($value){
		return (bool) $this->db
			->where($this->unique_key($value), $value)
			->count_records($this->table_name);
	}

	/**
	 * Allows a model to be loaded by user_name, guid, or email address.
	 */
	public function unique_key($id){
		if(! empty($id) and substr($id, 0, 1) == '@'){
			return 'guid';
		}
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id)){
			return valid::email($id) ? 'email' : 'user_name';
		}

		return parent::unique_key($id);
	}
}