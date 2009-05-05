<?php

class Group extends DataMapper {

    var $has_many = array('item', 'user');

    var $validation = array(
    	array(
	        'field' => 'name',
	        'label' => 'Group Name',
	        'rules' => array('required', 'trim', 'max_size'=>255)
	    ),
        array(
            'field' => 'chapter',
            'label' => 'Group is a Chapter',
            'rules' => array('required', 'integer')
        ),
    	array(
	        'field' => 'description',
	        'label' => 'Group Description',
	        'rules' => array('required', 'trim')
	    ),
		array(
	        'field' => 'location',
	        'label' => 'Group Location',
	        'rules' => array('trim')
	    )
    );

    function Group(){
        parent::DataMapper();
    }
}
?>