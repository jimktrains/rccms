<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model for openid_user_identities for the Openid Module
 *
 * $Id: base_openid_user.php 2008-08-16 09:28:34 BST Atomless $
 */
class Base_Openid_User_Identity_Model extends ORM {

	// Relationships
	protected $belongs_to = array('openid_user');

	/**
	 * Tests if an openid_user_identity already exists in the database by checking
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
	 * Allows a model to be loaded by openid url (claimed_id) by overloading the parent ORM method.
	 *
	 * @param   string   valid openid
	 * @return  string
	 */
	public function unique_key($id)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
			return 'claimed_id';
		}

		return parent::unique_key($id);
	}

} // End User_Model