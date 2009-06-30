<?php defined('SYSPATH') OR die('No direct access allowed.');

class RC_User_Model extends User_Model {
	protected $table_name = "users";
	protected $belongs_and_belongs_to_many = array('groups', 'items');
}