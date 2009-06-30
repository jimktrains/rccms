<?php defined('SYSPATH') or die('No direct script access.');
/**
 * helper class containing cryptographical functions used in OpenID authentication.
 *
 * $Id: crypt.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class crypt_Core {

	/**
	 * Returns a random number in the specified range.
	 *
	 * @param integer   $stop The end of the range, or the maximum random number to return
	 * @return integer  $result The resulting randomly-generated number
	 */
	function rand($max)
	{
		$duplicate_cache = array();

		// Used as the key for the duplicate cache
		$rbytes = crypt::long_to_binary($max);

		if (array_key_exists($rbytes, $duplicate_cache))
		{
			list($duplicate, $nbytes) = $duplicate_cache[$rbytes];
		}
		else
		{
			if ($rbytes[0] == "\x00")
			{
				$nbytes = crypt::count_bytes($rbytes) - 1;
			}
			else
			{
				$nbytes = crypt::count_bytes($rbytes);
			}

			$mxrand = bcpow(256, $nbytes);

			// If we get a number less than this, then it is in the
			// duplicated range.
			$duplicate = bcmod($mxrand, $max);

			if (count($duplicate_cache) > 10)
			{
				$duplicate_cache = array();
			}

			$duplicate_cache[$rbytes] = array($duplicate, $nbytes);
		}

		do {
			$bytes = "\x00" . crypt::get_pseudo_random_bytes($nbytes);

			$n = crypt::binary_to_long($bytes);

			// Keep looping if this value is in the low duplicated range
		} while (bccomp($n, $duplicate) < 0);

		return bcmod($n, $max);
	}

	/**
	 * pseudo-random number generator.
	 *
	 * @param  int     the length of the return value
	 * @return string  random bytes
	 */
	public static function get_pseudo_random_bytes($num_bytes)
	{
		$bytes = '';

		for ($i = 0; $i < $num_bytes; $i += 4)
		{
			$bytes .= pack('L', mt_rand());
		}

		$bytes = substr($bytes, 0, $num_bytes);

		return $bytes;
	}

	/**
	 * Count the number of bytes in a string independently of
	 * multibyte support conditions.
	 *
	 * @param  string  $str The string of bytes to count.
	 * @return int     The number of bytes in $str.
	 */
	public static function count_bytes($str)
	{
		return strlen(bin2hex($str)) / 2;
	}

	/**
	 * Convert a string to an array of bytes
	 * independently of multibyte support conditions.
	 *
	 * @param  string  $str The string to convert.
	 * @return array   array of bytes corresponding to the passed string.
	 */
	public static function string_to_byte_array($str)
	{
		$hex = bin2hex($str);

		if ( ! $hex)
			return array();

		$bytes = array();

		for ($i = 0; $i < strlen($hex); $i += 2)
		{
			array_push($bytes, chr(base_convert(substr($hex, $i, 2), 16, 10)));
		}

		return $bytes;
	}

	/**
	 * Given a long integer, returns the number converted to a binary string. This function accepts long
	 * integer values of arbitrary magnitude and requires the bc math library is enabled in your php install.
	 *
	 * @param integer   $long The long number (can be a normal PHP integer or a number created by one of the
	 * 					available long number libraries)
	 * @return string   $binary The binary version of $long
	 */
	public static function long_to_binary($long)
	{
		$comparrison = bccomp($long, 0);

		if ($comparrison < 0)
			throw new Kohana_Exception('crypt.long_to_binary', $long);

		if ($comparrison === 0)
			return "\x00";

		$bytes = array();

		while (bccomp($long, 0) > 0)
		{
			array_unshift($bytes, bcmod($long, 256));

			$long = bcdiv($long, bcpow(2, 8));
		}

		if ($bytes AND ($bytes[0] > 127))
		{
			array_unshift($bytes, 0);
		}

		$string = '';

		foreach ($bytes as $byte)
		{
			$string .= pack('C', $byte);
		}

		return $string;
	}

	/**
	 * Given a binary string, returns the binary string converted to a
	 * long number.
	 *
	 * @param string    $binary The binary version of a long number, probably as a result of calling longToBinary
	 * @return integer  $long The long number equivalent of the binary string $str
	 */
	public static function binary_to_long($bin_str)
	{
		// Use array_merge to return a zero-indexed array instead of a 1 indexed array.
		$bytes = array_merge(unpack('C*', $bin_str));

		$long = 0;

		if ($bytes && ($bytes[0] > 127))
			throw new Kohana_Exception('crypt.binary_to_long', $bin_str);

		foreach ($bytes as $byte)
		{
			$long = bcmul($long, bcpow(2, 8));

			$long = bcadd($long, $byte);
		}

		return $long;
	}

	/**
	 * Convert a base 64 encoded string to a long number
	 *
	 * @param  string   base 63 encoded string
	 * @return integer  long number
	 */
	public static function base64_to_long($b64_str)
	{
		$str = base64_decode($b64_str);

		if ($str === FALSE)
			return NULL;

		return crypt::binary_to_long($str);
	}

	/**
	 * Convert a long to a base 64 encoded string
	 *
	 * @param  integer  long number
	 * @return string   base 64 encoded version of the long number as a binary string
	 */
	function long_to_base64($long)
	{
		return base64_encode(crypt::long_to_binary($long));
	}
}