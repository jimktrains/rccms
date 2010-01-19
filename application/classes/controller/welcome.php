<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller_MasterTemplate {

	public function action_index(){
		$this->title = "YO";
		$this->test = "TESTING!";
	}
	
	public function action_create_item(){
		$item = ORM::factory('Item');
		$item->title = "Woot!";
		$item->text = "This is awesome!";
		$item->save();
		
		echo "Save: " . $item->title . "(" . $item->id . ":" . $item->version . ")";
	}
	
	public function action_create_tree(){
		$item = ORM::factory('Item');
		$item->title = "Parent";
		$item->text = "Parent";
		$item->save();
				
		$item2 = ORM::factory('Item');
		$item2->title = "Child";
		$item2->text = "Child";
		$item2->parent = $item->id;
		$item->save();

		
		
		echo "Save: " . $item->title . "(" . $item->id . ":" . $item->version . ")";
		echo "Save: " . $item2->title . "(" . $item2->id . ":" . $item->version . ")";
	}
	
	public function action_tree_test($parent, $child){
		$item = ORM::factory('Item', $child);
		echo "Parent for $child: " . $item->parent()->id;
		echo "<br/>";
		$item = ORM::factory('Item', $parent);
		foreach($item->children() as $child){
			echo "Child of $parent : " . $child->id . "<br/>";
		}
	}
	
	public function action_version_test(){
		$item = ORM::factory('Item', 8);
		echo "Parent for 8: " . $item->version . "<br/>";
		$item->raw_text = "Stupid!" . time();
		$item->version_reason = "This is all stupid";
		$item->save();
		echo "Parent for 8: " . $item->version . "<br/>";
	}

	public function not_found(){
		$this->title = "NOT FOUND";
		$this->test = "NOT COOL";
	}

}
