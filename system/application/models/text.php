<?php

class Text extends DataMapper {

    var $has_one = array('user', 'item');

    var $validation = array(
    	array(
	        'field' => 'text',
	        'label' => 'Text Body',
	        'rules' => array('required', 'trim')
	    ),
        array(
            'field' => 'comments',
            'label' => 'Text Comments',
            'rules' => array('required')
        ),
    	array(
	        'field' => 'title',
	        'label' => 'Text Title',
	        'rules' => array('required', 'trim', 'max_size'=>255)
	    ),
		array(
	        'field' => 'summary',
	        'label' => 'Text Summary',
	        'rules' => array('required', 'trim', 'max_size'=>255)
	    ),
		array(
	        'field' => 'creator',
	        'label' => 'Text Creator',
	        'rules' => array('required', 'trim', 'max_size'=>255)
	    ),
		array(
		    'field' => 'rev_num',
	        'label' => 'Text Revision Number',
	        'rules' => array('required', 'integer')
	    )
    );

    function __construct(){
        parent::DataMapper();
    }
}
?>
