<?php defined('SYSPATH') OR die('No direct access allowed.');

class ACL_User_Model extends ACL_Base_Model {	
	static public function add_resource_for_entity($res_id, $entity = NULL, $privilege = NULL){
		parent::add_resource_for_entity('user', $res_id, $entity, $privilege);
	}
	
	static public function remove_resource_for_entity($res_id, $entity){
		parent::remove_resource_for_entity('user', $res_id, $entity);
	}
	
	static public function remove_resource($res_id){
		parent::remove_resource('user', $res_id);
	}
	
	static public function can_read($res_id, $ent){
		return parent::can_read('user', $res_id, $ent);
	}
	
	static public function can_write($res_id, $ent){
		return parent::can_write('user', $res_id, $ent);
	}
	
	static public function set_read($res_id, $ent, $read=TRUE){
		parent::set_read('user', $res_id, $ent, $read);
	}
	
	static public function set_write($res_id, $ent, $write=TRUE){
		parent::set_write('user', $res_id, $ent, $read);
	}
}