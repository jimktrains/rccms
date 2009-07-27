<?php defined('SYSPATH') OR die('No direct access allowed.');

class ACL_User_Model extends ACL_Base_Model {
	
	static public function add_resource_for_entity($res_id, $entity = NULL, $privilege = NULL){
		parent::add_resource_for_entity('group', $res_id, $entity, $privilege);
	}
	
	static public function remove_resource_for_entity($res_id, $entity){
		parent::remove_resource_for_entity('group', $res_id, $entity);
	}
	
	static public function remove_resource($type, $res_id){
		parent::remove_resource('group', $res_id);
	}
	
	static public function can_read($type, $res_id, $ent){
		parent::can_read('group', $res_id, $ent);
	}
	
	static public function can_write($type, $res_id, $ent){
		parent::can_write('group', $res_id, $ent);
	}
	static public function set_read($res_id, $ent, $read=TRUE){
		parent::set_read('group', $res_id, $ent, $read);
	}
	
	static public function set_write($res_id, $ent, $write=TRUE){
		parent::set_write('group', $res_id, $ent, $read);
	}
}