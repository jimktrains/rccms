<?php

class Type extends DataMapper {

    var $has_many = array('item');

    var $validation = array(
    	array(
	        'field' => 'name',
	        'label' => 'Type Name',
	        'rules' => array('required', 'trim', 'alpha_numeric', 'max_size'=>255)
	    )
    );

    function Type(){
        parent::DataMapper();
    }
}
?>