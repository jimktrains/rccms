<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_Auth_Local extends Driver_Auth {

	/**
	 * Logs a user in.
	 *
	 * @param   array   credentials (user_name, password)
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function login($user_name, array $credentials, $remember){
		$user = ORM::factory('user', $user_name);		
		if ( $user->loaded() ){
			$password = Model_Auth_Local::hash_password($credentials['password']);
			if(!strcmp($user->auth_local->password, $password)){
				$this->complete_login($user, $remember);
				return $user;
			}
		}
		return FALSE;
	}
	
	/**
	 * Registers a user
	 * 
	 * @param string user_name
	 * @param stirng email
	 * @param array  credentials (password, password_confirm)
	 */
	public function register($user_name, $email, $creds, $save = TRUE){
		$user = parent::register($user_name, $email, $creds, FALSE);
		$al  = ORM::factory('Auth_local');
		$errors = array();
		$al->password = $creds['password'];
		$al->password_confirm = $creds['password_confirm'];
		if($user->validate()){
			$user->save();
			$al->user_id = $user->id;
			if($al->validate()){
				$al->save();
				return $user;
			}else{
				$errors = $al->errors('register');
			}

		}else{
			$errors = $user->errors('register');
		}
		return $errors;
	}
}

?>