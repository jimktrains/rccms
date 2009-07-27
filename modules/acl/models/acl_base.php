<?php defined('SYSPATH') OR die('No direct access allowed.');

class ACL_Base_Model extends ORM {

	static public $READ = 1;
	static public $WRITE = 2;
	
	static $ALL = 0;
	
	static private function get_acl($type){
		$type = ucfirst($type);
		$acl_name = "ACL_$type"."_Model";
		$acl = new $acl_name();
		return $acl;
	}
	
	static public function add_resource_for_entity($type, 
			$res_id, 
			$entity = NULL,
			$privilege = NULL
	){
		if(is_null($entity)) $entity=ACL_Base_Model::$ALL;
		if(is_null($privilege)) $privilege=ACL_Base_Model::$READ;
		$type = strtolower($type);
		$acl = ACL_Base_Model::get_acl($type);
		
		$acl->where('resource', $res_id)->where($type, $entity)->find();
		$acl->resource = $res_id;
		$acl->privilege = $privilege; 
		$acl->$type = $entity;
		$acl->save();
	}
	
	static public function can_read($type, $res_id, $entity){
		$type = strtolower($type);
		$acl = ACL_Base_Model::get_acl($type);

		$acl->where('resource', $res_id)->where($type, $entity)->find();
		return ($acl->privilege & ACL_Base_Model::$READ) == ACL_Base_Model::$READ;
	}
	
	static public function can_write($type, $res_id, $entity){
		$type = strtolower($type);
		$acl = ACL_Base_Model::get_acl($type);

		$acl->where('resource', $res_id)->where($type, $entity)->find();
		return ($acl->privilege & ACL_Base_Model::$WRITE) == ACL_Base_Model::$WRITE;
	}
	
	static public function remove_resource_for_entity($type, $res_id, $entity){
		$type = strtolower($type);
		$acl = ACL_Base_Model::get_acl($type);
		
		$acl->where('resource', $res_id)->where($type, $entity)->find();
		$acl->delete();
	}
	
	static public function remove_resource($type, $res_id){
		$type = strtolower($type);
		$acl = ACL_Base_Model::get_acl($type);

		$db = new Database();
		$res = $db->select('id')->where(array('resource'=>$res_id))->get();
		foreach($res as $row){
			$acl->delete($row['id']);
		}
	}
	
	static public function set_read($type, $res_id, $ent, $read=TRUE){
		$type = strtolower($type);
		$acl = ACL_Base_Model::get_acl($type);

		$acl->where('resource', $res_id)->where($type, $entity)->find();
		if($read){
			$acl->privilege = $acl->privilege | ACL_Base_Model::$READ;
		}else{
			$acl->privilege = $acl->privilege & ~ACL_Base_Model::$READ;
		}
		
		$acl->save();
		
	}
	
	static public function set_write($type, $res_id, $ent, $write=TRUE){
		$type = strtolower($type);
		$acl = ACL_Base_Model::get_acl($type);

		$acl->where('resource', $res_id)->where($type, $entity)->find();
		if($write){
			$acl->privilege = $acl->privilege | ACL_Base_Model::$WRITE;
		}else{
			$acl->privilege = $acl->privilege & ~ACL_Base_Model::$WRITE;
		}
		
		$acl->save();
	}
}