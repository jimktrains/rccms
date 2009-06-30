<?php defined('SYSPATH') OR die('No direct access allowed.');

class ACL_Base extends ORM {

	public static int READ = 1;
	public static int WRITE = 2;
	
	public static int ALL = 0;
	
	static private function get_acl($type){
		$type = ucfirst($type);
		$acl_name = "ACL_$type";
		$acl = new $acl_name();
		return $acl;
	}
	
	static public function add_resource($type, $res_id, $privilege = ACL_Base::READ, $entity = ACL_Base::ALL){
		$type = strtolower($type);
		$acl = get_acl($type);
		
		$acl->resource = $res_id
		$acl->privilege = $privilege; 
		$acl->$type = $entity;
	}
	
	static public can_read($type, $res_id, $entity){
		$type = strtolower($type);
		$acl = get_acl($type);

		$acl->where('resource', $res_id)->where($type, $entity)->find();
		return $acl->privilege & ACL_Base::READ;
	}
	
	static public can_write($type, $res_id, $entity){
		$type = strtolower($type);
		$acl = get_acl($type);

		$acl->where('resource', $res_id)->where($type, $entity)->find();
		return $acl->privilege & ACL_Base::READ;
	}
	
	static public function remove_resource_for_entity($type, $res_id, $entity){
		$type = strtolower($type);
		$acl = get_acl($type);
		
		$acl->where('resource', $res_id)->where($type, $entity)->find();
		$acl->delete();
	}
	
	static public function remove_resource($type, $res_id){
		$type = strtolower($type);
		$acl = get_acl($type);

		$db = new Database();
		$res = $db->select('id')->where('resource'=>$res_id)->get();
		foreach($res as $row){
			$acl->delete($row['id']);
		}
	}
	
	static public function set_read($type, $res_id, $ent, $read=TRUE){
		$type = strtolower($type);
		$acl = get_acl($type);

		$acl->where('resource', $res_id)->where($type, $entity)->find();
		if($read){
			$acl->privilege = $acl->privilege | ACL_Base::READ;
		}else{
			$acl->privilege = $acl->privilege & ~ACL_Base::READ;
		}
		
	}
	
	static public function set_write($type, $res_id, $ent, $write=TRUE){
		$type = strtolower($type);
		$acl = get_acl($type);

		$acl->where('resource', $res_id)->where($type, $entity)->find();
		if($write){
			$acl->privilege = $acl->privilege | ACL_Base::WRITE;
		}else{
			$acl->privilege = $acl->privilege & ~ACL_Base::WRITE;
		}	
	}
}