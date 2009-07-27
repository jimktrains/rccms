<?php

class Tag extends DataMapper {

    var $has_many = array('item');

    var $validation = array(
    	array(
	        'field' => 'tag',
	        'label' => 'Tag Description',
	        'rules' => array('required', 'trim', 'max_size'=>25)
	    )
    );

    function Tag(){
        parent::DataMapper();
    }
}
?>