<?php defined('SYSPATH') OR die('No direct access allowed.');

class Group_Model extends ORM {
	protected $has_and_belongs_to_many = array('users');
}