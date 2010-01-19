<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_User extends ORM {
	protected $_prefs = array();
	protected $_has_one = array('auth_local'=>array(), 'user_token'=>array());
	protected $_ignored_columns = array('password_confirm');
	/**
	 * Validates and optionally
	 *
	 * @param  array    values to check
	 * @param  boolean  save the record when validation succeeds
	 * @return boolean
	 */
	public  function validate(){
			$this->_filters += array(TRUE => array('trim'=>array()));
			#array($this, 'email_available')=>array()
			$this->_rules += array('email'=>array(
				'min_length'=>array(4),
				'max_length'=>array(255),
				'email'=>array())
			);
			# array($this, 'user_name_available')
			$this->_rules += array('user_name'=> array(
				'min_length'=>array(4),
				'max_length'=>array(255),
				'alpha_dash'=>array())
			);
		return parent::check();
	}
	
	public function __get($column){
		$this->load_prefs();
		if(! count($this->_prefs) and strlen($this->prefs)){
			$this->prefs = json_decode($this->prefs);
		}
		try{
			return parent::__get($column);
		}catch(Kohana_Exception $e){
			if(array_key_exists($column, $this->_prefs)){
				return $this->_prefs[$column];
			}
			throw $e;
		}
	}

	public function __set($column, $value){
		$this->load_prefs();
		if(! count($this->_prefs) and strlen($this->prefs)){
			$this->prefs = json_decode($this->prefs);
		}
		try{
			return parent::__set($column, $value);
		}catch(Kohana_Exception $e){
			$this->_prefs[$column] = $value;
		}
	}
	
	public function __unset($column){
		$this->load_prefs();
		if(array_key_exists($column, $this->_prefs)){
			unset($this->_prefs);
		}else{
			parent::__unset($column);
		}
	}
	
	protected function load_prefs(){
		if(! count($this->_prefs) and strlen($this->prefs)){
			$this->prefs = json_decode($this->prefs);
		}
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
		if(! empty($id)){
			if (is_string($id) and ! ctype_digit($id)){
				if(strpos($id, "@") !== FALSE){
					return 'email';
				}else{
					return 'user_name';
				}
			}
		}
		return parent::unique_key($id);
	}
}