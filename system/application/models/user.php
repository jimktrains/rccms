<?php

/**
 * User Class
 *
 * Transforms users table into an object.
 * This is just here for use with the example in the Controllers.
 *
 * @licence 	MIT Licence
 * @category	Models
 * @author  	Simon Stenhouse
 * @link    	http://stensi.com
 */
class User extends DataMapper {

	var $validation = array(
		array(
			'field' => 'username',
			'label' => 'Username',
			'rules' => array('required', 'trim', 'unique', 'min_length' => 3, 'max_length' => 20)
		),
		array(
			'field' => 'password',
			'label' => 'Password',
			'rules' => array('required', 'trim', 'min_length' => 3, 'max_length' => 40, 'encrypt')
		),
		array(
			'field' => 'email',
			'label' => 'Email Address',
			'rules' => array('required', 'trim', 'unique', 'valid_email')
		)
	);

	function User(){
		parent::DataMapper();
	}
	
}
?
