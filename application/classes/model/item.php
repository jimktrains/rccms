<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Item extends Version_ORM {
	protected $_created_column = array('column'=>'created', 'format'=>'c');
	protected $_has_one = array('parent'=>array('model'=>'items', 'foreign_key'=>'id'));
	
	public function children(){
		$item = ORM::factory('Item');
		$item->parent = $this->pk();
		return $item->find_all();
	}
	
	public function parent(){
		$item = ORM::factory('Item', $this->parent);
		return $item;
	}
}
?>