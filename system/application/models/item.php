<?php

class Item extends DataMapper {

    var $has_one = array('license', 'parent');
    var $has_many = array('group', 'tag', 'text', 'type', 'user');

    var $validation = array(
		array(
			  'field' => 'guid',
			  'label' => 'guid',
			  'rules' => array('required', 'trim', 'alpha_numeric', 'max_size'=>5)
		 ),
        array(
            'field' => 'rating_count',
            'label' => 'rating_count',
            'rules' => array('integer')
        ),
		array(
			  'field' => 'rating_sum',
			  'label' => 'rating_sum',
			  'rules' => array('integer')
		 ),
		array(
			  'field' => 'rating_sum_squares',
			  'label' => 'rating_sum_squares',
			  'rules' => array('integer')
		 ),
		array(
			  'field' => 'rating_average',
			  'label' => 'rating_average',
			  'rules' => array('numeric')
		 ),
		array(
			  'field' => 'rating_stdev',
			  'label' => 'rating_stdev',
			  'rules' => array('numeric')
		 )
    );

    function __construct($uuid = NULL){
        parent::DataMapper();
		  if(is_null($uuid)){
			$this->guid=getUUID(5,true);
		  }else{
			$this->get_where(array("guid"=>$uuid));
		}
    }

	static function create($user){
		
	}

	function rate($rating, $user){
		$rating = intval($rating);
		$this->rating_count++;
		$this->rating_sum += $rating;
		$this->rating_sum_squares += ($rating*$rating);
		$this->rating_average = $this->rating_sum/$this->rating_count;
		$this->rating_stdev = ($this->rating_sum_squares - $this->rating_count*$this->rating_average*$this->rating_average)/($this->rating_count - 1);
		$this->save();
		
		$r = new Rating();
		$r->item_id = $this->id;
		$r->user_id = $user->id;
		$r->rating = $rating;
		$r->save($this);
	}
	
	function current_revision(){
		$this->text->get();
		$this->text->order_by("saved_date", "desc");
		$this->text->limit(1);
		$this->text->get();
	}
	
	function tags(){
		return $this->tag->get()->all();
	}
	
	function owners(){
		return $this->user->get()->all();
	}
	
	function groups(){
		return $this->group->get()->all();
	}
	
	function edit($title, $text, $comments, $summary, $creator){
		$this->current_revision();
		$t = new Text();
		$t->title = $title;
		$t->text = $text;
		$t->comments = $comments;
		$t->summary = $summary;
		$t->creator = $creator;
		$t->rev_num = $this->rev_num + 1;
		$t->save($this);
	}
}
?>
