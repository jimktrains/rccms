<?php

class License extends DataMapper {

    var $has_many = array('item');

    var $validation = array(
    	array(
	        'field' => 'name',
	        'label' => 'License Name',
	        'rules' => array('required', 'max_size'=>255)
	    ),
        array(
            'field' => 'url',
            'label' => 'License URL',
            'rules' => array('required', 'max_size'=>255)
        ),
    	array(
	        'field' => 'description',
	        'label' => 'License Description',
	        'rules' => array('required')
	    ),
		array(
	        'field' => 'large_icon_html',
	        'label' => 'License Large Icon',
	        'rules' => array('required')
	    ),
		array(
	        'field' => 'small_icon_html',
	        'label' => 'License Small HTML',
	        'rules' => array('required')
	    )
    );

    function License(){
        parent::DataMapper();
    }
}
?>