<?php defined('SYSPATH') OR die('No direct access allowed.');

class Item_Model extends ORM_Tree {

	protected $children = "items";
	protected $has_and_belongs_to_many = array('tags', 'authors');
	protected $has_one = array('body'=>'text');
	
	
	public function update_rating($rating){
		$this->num_votes++;
		$this->sum_votes += $rating;
		$this->sq_sum_votes += $rating*$rating;
		$this->save();
		
		$ttl = ORM::factory('ttl_sums')->find(1);
		$ttl->num_votes++;
		$ttl->sum_votes += $rating;
		$ttl->sq_sum_votes += $rating*$rating;
		$ttl->save();
	}
	
	public function save() {
		$this->updated_at = new DB_Exp('NOW()');
		return parent::save();
	}
	
}