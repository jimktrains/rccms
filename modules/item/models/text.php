<?php defined('SYSPATH') OR die('No direct access allowed.');

class Text_Model extends ORM_Versioned {
	protected $belongs_to = array('item');
	protected $has_one = array('creator'=>'user');
}