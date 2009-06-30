<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model for openid_users for the Openid Module
 *
 * $Id: base_openid_user.php 2008-08-16 09:28:34 BST Atomless $
 */
class Base_Openid_User_Model extends ORM {

	// Relationships
	protected $has_many = array('openid_user_identities');

	/**
	 * Insert a new user into the db.
	 *
	 * @param  string  user_name of new user (defaults to openid_user->id)
	 * @return Openid_User_Model
	 */
	public function create($user_name = FALSE)
	{
		$this->created = time();
		$this->save();

		// user_name defaults to openid_user->id
		$this->user_name = ($user_name === FALSE)
						? $this->id
						: $user_name;

		return $this->save();
	}

	/**
	 * Tests if an openid_user already exists in the database by checking
	 * submitted data against any db fields set as a unique key.
	 * NOTE: Only tested with the mysql db driver.
	 *
	 * @param  string   array of key value pairs to check
	 * @return boolean
	 */
	public function exists($user_data_array)
	{
		foreach($this->db->field_data($this->table_name) as $column)
		{
			if ($column->Key=='UNI')
			{
				if (array_key_exists($column->Field, $user_data_array))
				{
					if ($this->db->where($column->Field, $user_data_array[$column->Field])->count_records($this->table_name) > 0)
					{
						return TRUE;
					}
				}
			}
		}
		return FALSE;
	}

	/**
	 * Save a user identity.
	 *
	 * @param String   the claimed_id
	 * @param String   the display id - to be used for display purposes only! see Openid.php for more info.
	 * @param Array    associative array of optional attributes corresponding to db fields (that can be NULL).
	 * @return boolean TRUE on success and FALSE if failed to save new identity.
	 */
	public function add_identity($claimed_id, $display_id, $attributes = array())
	{
		if ( ! $this->loaded)
			throw new Kohana_Database_Exception('openid.user_model.notloaded');

		$user_identity = new Openid_User_Identity_Model;

		$user_identity->openid_user_id = $this->id;

		$user_identity->claimed_id = $claimed_id;

		// The display_id is the id the user entered in the form and is the id you should display when needed.
		// The claimed_id will be the id used when performing future authentications, but the
		// user may not be familiar with it so it should not be displayed on their profile pages.
		// This is because the claimed_id be resolved during the discovery phase to something quite different
		// from the id the user entered.
		$user_identity->display_id = $display_id;

		foreach ($attributes as $key => $value)
		{
			$user_identity->$key = $value;
		}

		$user_identity->save();
	}

	/**
	 * Allows a model to be loaded by user_name by overloading the parent ORM unique_key method (used by find()).
	 *
	 * @param   String   valid openid
	 * @return  String
	 */
	public function unique_key($id)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
				return 'user_name';
		}

		return parent::unique_key($id);
	}

} // End User_Model