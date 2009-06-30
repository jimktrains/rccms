<?php defined('SYSPATH') or die('No direct script access.');
/**
 * helper methods for working with openID KVF strings
 *
 * $Id: openid_key_value_form.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class openid_key_value_form_Core {

	/**
	 * Convert an OpenID key - colon - value - newline, separated string into an
	 * associative array
	 *
	 * @param  string   string in key:value\n form
	 * @param  boolean  whether to use strict validation rules during conversion
	 * @return mixed    associative array on success False if value passed for kvf_string was deemed invalid
	 */
	public static function string_to_array($kvf_string, $strict = FALSE)
	{
		$lines = explode("\n", $kvf_string);

		$last_line = array_pop($lines);

		if ($last_line !== '')
		{
			array_push($lines, $last_line);

			if ($strict)
				return FALSE;
		}

		$values = array();

		foreach ($lines as $line)
		{
			$colon_delimited_segments = explode(':', $line, 2);

			if (count($colon_delimited_segments) < 2)
			{
				if ($strict)
					return false;

				continue;
			}

			$key = $colon_delimited_segments[0];

			$tkey = trim($key);

			if ($tkey != $key AND $strict)
				return false;

			$value = $colon_delimited_segments[1];

			$tvalue = trim($value);

			if ($tvalue != $value AND $strict)
				return false;

			$values[$tkey] = $tvalue;
		}

		return $values;
	}

	/**
	 * Convert an array into an key=value& or key:value\n formatted string
	 * Used when asking the OP to check the sig in the OpenID response
	 *
	 * @param  array    associative array
	 * @param  string   namespace string to be prepended onto each key
	 * @param  boolean  whether to use key:value\n or key=value& encoding
	 * @return string   key value pair string
	 */
	public static function associative_array_to_string($associative_array, $namespace_prefix = '', $form_encoded = FALSE)
	{
		ksort($associative_array);

		$KVF_string = '';

		$param_count = count($associative_array);

		foreach ($associative_array as $key => $value)
		{
			if (is_array($value))
			{
				$KVF_string .= openid_key_value_form::associative_array_to_string($value, $namespace_prefix.$key.'.', $form_encoded);

				continue;
			}

			if (strpos($key, ':') !== FALSE)
				return FALSE;

			if (strpos($key, "\n") !== FALSE)
				return FALSE;

			if (strpos($value, "\n") !== FALSE)
				return FALSE;

			if ($form_encoded)
			{
				$KVF_string .= $namespace_prefix.$key.':'.urlencode($value)."\n";
			}
			else
			{
				$KVF_string .= $namespace_prefix.$key.'='.urlencode($value).'&';
			}

			$param_count--;
		}

		return $KVF_string;
	}

}