<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_User_Tokens extends ORM {
	protected $_belongs_to = array('user'=>array());
	
	public generate($expire = "P99Y"){
		$datetime = new DateTime();
		$datetime->add($expire);
		$this->expire = $datetime->format('c');
		$this->token = hash('sha256', $user->id . mt_rand() . $user->user_name . mt_rand() . $this->expire);
		$this->save();
	}
}
?>