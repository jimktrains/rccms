<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Array helper class.
 *
 * $Id: arr_openid.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class arr_openid_Core {

	/**
	 * Remove an entry or entries from a linear array and return re-indexed array.
	 *
	 * @param   array    array to work on
	 * @param   mixed    linear array of mixed items or string or number to search for and remove from passed array
	 * @param   boolean  whether to check for strict equality when searching source array
	 * @return  mixed    value of the requested array key
	 */
	public function linear_remove(array $source_array, $search, $strict = TRUE)
	{
		if ( ! is_array($search))
		{
			$search = array($search);
		}

		foreach ($search as $needle)
		{
			array_splice($source_array, array_search($needle, $source_array, $strict), 1);
		}

		return $source_array;
	}

} // End arr