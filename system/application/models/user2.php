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

	var $has_one = array("group");

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
			'field' => 'confirm_password',
			'label' => 'Confirm Password',
			'rules' => array('encrypt', 'matches' => 'password')
		),
		array(
			'field' => 'email',
			'label' => 'Email Address',
			'rules' => array('required', 'trim', 'unique', 'valid_email')
		)
/* Uncomment to force the requirement of a user having to belong to a group
		array(
			'field' => 'group',
			'label' => 'Group',
			'rules' => array('required')
		)
*/
	);

	/**
	 * Constructor
	 *
	 * Initialize DataMapper.
	 */
	function User()
	{
		parent::DataMapper();
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Login
	 *
	 * Authenticates a user for logging in.
	 *
	 * @access	public
	 * @return	bool
	 */
	function login()
	{
		// Create a temporary user object
		$u = new User();

		// Get this users stored record via their username
		$u->where('username', $this->username)->get();

		// Give this user their stored salt
		$this->salt = $u->salt;

		// Validate and get this user by their property values,
		// this will see the 'encrypt' validation run, encrypting the password with the salt
		$this->validate()->get();

		// If the username and encrypted password matched a record in the database,
		// this user object would be fully populated, complete with their ID.

		// If there was no matching record, this user would be completely cleared so their id would be empty.
		if (empty($this->id))
		{
			// Login failed, so set a custom error message
			$this->error_message('login', 'Username or password invalid');

			return FALSE;
		}
		else
		{
			// Login succeeded
			return TRUE;
		}
	}
	 
	// --------------------------------------------------------------------
	
	/**
	 * Encrypt (prep)
	 *
	 * Encrypts this objects password with a random salt.
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */
	function _encrypt($field)
	{
		if (!empty($this->{$field}))
		{
			if (empty($this->salt))
			{
				$this->salt = md5(uniqid(rand(), true));
			}

			$this->{$field} = sha1($this->salt . $this->{$field});
		}
	}
}

/* End of file user.php */
/* Location: ./application/models/user.php */