<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Auth extends Controller_MasterTemplate {

	public function action_index(){
		$this->request->redirect('/auth/login');
	}
	
	public function get_action_login(){
		$this->title = "Login";
	}
	
	public function post_action_login(){
		$auth = Auth::instance('Local');
		$user = $auth->login($_POST['username'], array('password'=>$_POST['password']));
		if(! $user){
			echo("Wrong info, dude.");
			exit;
		}
		echo "All good!, user " . $user->user_name . "(".$user->id.")";
		exit;
	}
	
	public function get_action_register(){
		$this->title = "Register";
	}
	
	public function post_action_register(){
		$auth = Auth::instance('Local');
		$user = $auth->register($_POST['username'], $_POST['email'], array('password'=>$_POST['password'], 'password_confirm'=>$_POST['password_confirm']));
		if(is_array($user)){
			var_dump($user->errors());
			exit;
		}
		echo "All good!, user " . $user->user_name . "(".$user->id.")";
		exit;
	}
	
	public function not_found(){}

}
