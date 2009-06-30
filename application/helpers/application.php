<?php defined('SYSPATH') or die('No direct script access.');

public function ACL_can_read($res_id, $user){
	$can_read = FALSE;
	
	$can_read ||= ACL_User::can_read($res_id, $user->id);
	foreach($user->groups as $group){
			$can_read ||= ACL_Group::can_read($res_id, $group->id);
	}
	
	return $can_read;
}

public function ACL_can_write($res_id, $user){
	$can_write = FALSE;
	
	$can_write ||= ACL_User::can_write($res_id, $user->id);
	foreach($user->groups as $group){
			$can_write ||= ACL_Group::can_write($res_id, $group->id);
	}
	
	return $can_write;
}
?>