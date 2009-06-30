<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_Local_Driver extends Auth_Driver {

	/**
	 * Logs a user in.
	 *
	 * @param   array   credentials (user_name, password)
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function login(array $credentials, $remember){
		$user = $credentials['user_name'];
		$password = $credentials['password'];
		if ( ! is_object($user)){
			$user = ORM::factory('user', $user);
		}
		$password = $user->auth_local->hash_password($password);
		if ( $user->auth_local->password === $password){
			$this->complete_login($user, $remember);
			return $user;
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
		$errors = array();
		$al  = ORM::factory('Auth_local');
		$al_a['password_confirm']= $creds['password_confirm'];
		$al_a['password'] = $creds['password'];
		$user_a = $user->as_array();

		$al->validate($al_a);
		$user->validate($user_a);
		if($al_a->validate() and $user_a->validate()){
			$user->save();
			$al->user_id = $user->id;
			$al->password = $al_a['password'];
			$al->user_id = $user->id;
			$al->save();

		}else{
			$errors = array_merge($user_a->errors(), $al_a->errors());
		}
		return $errors;
	}
}

?>