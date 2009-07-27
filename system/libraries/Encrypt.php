<<<<<<< HEAD:system/libraries/Encrypt.php
<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The Encrypt library provides two-way encryption of text and binary strings
 * using the MCrypt extension.
 * @see http://php.net/mcrypt
 *
 * $Id: Encrypt.php 4072 2009-03-13 17:20:38Z jheathco $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Encrypt_Core {

	// OS-dependant RAND type to use
	protected static $rand;

	// Configuration
	protected $config;

	/**
	 * Returns a singleton instance of Encrypt.
	 *
	 * @param   array  configuration options
	 * @return  Encrypt_Core
	 */
	public static function instance($config = NULL)
	{
		static $instance;

		// Create the singleton
		empty($instance) and $instance = new Encrypt((array) $config);

		return $instance;
	}

	/**
	 * Loads encryption configuration and validates the data.
	 *
	 * @param   array|string      custom configuration or config group name
	 * @throws  Kohana_Exception
	 */
	public function __construct($config = FALSE)
	{
		if ( ! defined('MCRYPT_ENCRYPT'))
			throw new Kohana_Exception('encrypt.requires_mcrypt');

		if (is_string($config))
		{
			$name = $config;

			// Test the config group name
			if (($config = Kohana::config('encryption.'.$config)) === NULL)
				throw new Kohana_Exception('encrypt.undefined_group', $name);
		}

		if (is_array($config))
		{
			// Append the default configuration options
			$config += Kohana::config('encryption.default');
		}
		else
		{
			// Load the default group
			$config = Kohana::config('encryption.default');
		}

		if (empty($config['key']))
			throw new Kohana_Exception('encrypt.no_encryption_key');

		// Find the max length of the key, based on cipher and mode
		$size = mcrypt_get_key_size($config['cipher'], $config['mode']);

		if (strlen($config['key']) > $size)
		{
			// Shorten the key to the maximum size
			$config['key'] = substr($config['key'], 0, $size);
		}

		// Find the initialization vector size
		$config['iv_size'] = mcrypt_get_iv_size($config['cipher'], $config['mode']);

		// Cache the config in the object
		$this->config = $config;

		Kohana::log('debug', 'Encrypt Library initialized');
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 * @param   string  data to be encrypted
	 * @return  string  encrypted data
	 */
	public function encode($data)
	{
		// Set the rand type if it has not already been set
		if (Encrypt::$rand === NULL)
		{
			if (KOHANA_IS_WIN)
			{
				// Windows only supports the system random number generator
				Encrypt::$rand = MCRYPT_RAND;
			}
			else
			{
				if (defined('MCRYPT_DEV_URANDOM'))
				{
					// Use /dev/urandom
					Encrypt::$rand = MCRYPT_DEV_URANDOM;
				}
				elseif (defined('MCRYPT_DEV_RANDOM'))
				{
					// Use /dev/random
					Encrypt::$rand = MCRYPT_DEV_RANDOM;
				}
				else
				{
					// Use the system random number generator
					Encrypt::$rand = MCRYPT_RAND;
				}
			}
		}

		if (Encrypt::$rand === MCRYPT_RAND)
		{
			// The system random number generator must always be seeded each
			// time it is used, or it will not produce true random results
			mt_srand();
		}

		// Create a random initialization vector of the proper size for the current cipher
		$iv = mcrypt_create_iv($this->config['iv_size'], Encrypt::$rand);

		// Encrypt the data using the configured options and generated iv
		$data = mcrypt_encrypt($this->config['cipher'], $this->config['key'], $data, $this->config['mode'], $iv);

		// Use base64 encoding to convert to a string
		return base64_encode($iv.$data);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 * @param   string  encoded string to be decrypted
	 * @return  string  decrypted data
	 */
	public function decode($data)
	{
		// Convert the data back to binary
		$data = base64_decode($data);

		// Extract the initialization vector from the data
		$iv = substr($data, 0, $this->config['iv_size']);

		// Remove the iv from the data
		$data = substr($data, $this->config['iv_size']);

		// Return the decrypted data, trimming the \0 padding bytes from the end of the data
		return rtrim(mcrypt_decrypt($this->config['cipher'], $this->config['key'], $data, $this->config['mode'], $iv), "\0");
	}

} // End Encrypt
=======
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Encryption Class
 *
 * Provides two-way keyed encoding using XOR Hashing and Mcrypt
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/encryption.html
 */
class CI_Encrypt {

	var $CI;
	var $encryption_key	= '';
	var $_hash_type	= 'sha1';
	var $_mcrypt_exists = FALSE;
	var $_mcrypt_cipher;
	var $_mcrypt_mode;

	/**
	 * Constructor
	 *
	 * Simply determines whether the mcrypt library exists.
	 *
	 */
	function CI_Encrypt()
	{
		$this->CI =& get_instance();
		$this->_mcrypt_exists = ( ! function_exists('mcrypt_encrypt')) ? FALSE : TRUE;
		log_message('debug', "Encrypt Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the encryption key
	 *
	 * Returns it as MD5 in order to have an exact-length 128 bit key.
	 * Mcrypt is sensitive to keys that are not the correct length
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function get_key($key = '')
	{
		if ($key == '')
		{
			if ($this->encryption_key != '')
			{
				return $this->encryption_key;
			}

			$CI =& get_instance();
			$key = $CI->config->item('encryption_key');

			if ($key === FALSE)
			{
				show_error('In order to use the encryption class requires that you set an encryption key in your config file.');
			}
		}

		return md5($key);
	}

	// --------------------------------------------------------------------

	/**
	 * Set the encryption key
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_key($key = '')
	{
		$this->encryption_key = $key;
	}

	// --------------------------------------------------------------------

	/**
	 * Encode
	 *
	 * Encodes the message string using bitwise XOR encoding.
	 * The key is combined with a random hash, and then it
	 * too gets converted using XOR. The whole thing is then run
	 * through mcrypt (if supported) using the randomized key.
	 * The end result is a double-encrypted message string
	 * that is randomized with each call to this function,
	 * even if the supplied message and key are the same.
	 *
	 * @access	public
	 * @param	string	the string to encode
	 * @param	string	the key
	 * @return	string
	 */
	function encode($string, $key = '')
	{
		$key = $this->get_key($key);
		$enc = $this->_xor_encode($string, $key);
		
		if ($this->_mcrypt_exists === TRUE)
		{
			$enc = $this->mcrypt_encode($enc, $key);
		}
		return base64_encode($enc);
	}

	// --------------------------------------------------------------------

	/**
	 * Decode
	 *
	 * Reverses the above process
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function decode($string, $key = '')
	{
		$key = $this->get_key($key);
		
		if (preg_match('/[^a-zA-Z0-9\/\+=]/', $string))
		{
			return FALSE;
		}

		$dec = base64_decode($string);

		if ($this->_mcrypt_exists === TRUE)
		{
			if (($dec = $this->mcrypt_decode($dec, $key)) === FALSE)
			{
				return FALSE;
			}
		}

		return $this->_xor_decode($dec, $key);
	}

	// --------------------------------------------------------------------

	/**
	 * XOR Encode
	 *
	 * Takes a plain-text string and key as input and generates an
	 * encoded bit-string using XOR
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_encode($string, $key)
	{
		$rand = '';
		while (strlen($rand) < 32)
		{
			$rand .= mt_rand(0, mt_getrandmax());
		}

		$rand = $this->hash($rand);

		$enc = '';
		for ($i = 0; $i < strlen($string); $i++)
		{			
			$enc .= substr($rand, ($i % strlen($rand)), 1).(substr($rand, ($i % strlen($rand)), 1) ^ substr($string, $i, 1));
		}

		return $this->_xor_merge($enc, $key);
	}

	// --------------------------------------------------------------------

	/**
	 * XOR Decode
	 *
	 * Takes an encoded string and key as input and generates the
	 * plain-text original message
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_decode($string, $key)
	{
		$string = $this->_xor_merge($string, $key);

		$dec = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$dec .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}

		return $dec;
	}

	// --------------------------------------------------------------------

	/**
	 * XOR key + string Combiner
	 *
	 * Takes a string and key as input and computes the difference using XOR
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _xor_merge($string, $key)
	{
		$hash = $this->hash($key);
		$str = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Encrypt using Mcrypt
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function mcrypt_encode($data, $key)
	{
		$init_size = mcrypt_get_iv_size($this->_get_cipher(), $this->_get_mode());
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);
		return $this->_add_cipher_noise($init_vect.mcrypt_encrypt($this->_get_cipher(), $key, $data, $this->_get_mode(), $init_vect), $key);
	}

	// --------------------------------------------------------------------

	/**
	 * Decrypt using Mcrypt
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function mcrypt_decode($data, $key)
	{
		$data = $this->_remove_cipher_noise($data, $key);
		$init_size = mcrypt_get_iv_size($this->_get_cipher(), $this->_get_mode());

		if ($init_size > strlen($data))
		{
			return FALSE;
		}

		$init_vect = substr($data, 0, $init_size);
		$data = substr($data, $init_size);
		return rtrim(mcrypt_decrypt($this->_get_cipher(), $key, $data, $this->_get_mode(), $init_vect), "\0");
	}

	// --------------------------------------------------------------------

	/**
	 * Adds permuted noise to the IV + encrypted data to protect
	 * against Man-in-the-middle attacks on CBC mode ciphers
	 * http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
	 *
	 * Function description
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function _add_cipher_noise($data, $key)
	{
		$keyhash = $this->hash($key);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j)
		{
			if ($j >= $keylen)
			{
				$j = 0;
			}

			$str .= chr((ord($data[$i]) + ord($keyhash[$j])) % 256);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Removes permuted noise from the IV + encrypted data, reversing
	 * _add_cipher_noise()
	 *
	 * Function description
	 *
	 * @access	public
	 * @param	type
	 * @return	type
	 */
	function _remove_cipher_noise($data, $key)
	{
		$keyhash = $this->hash($key);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j)
		{
			if ($j >= $keylen)
			{
				$j = 0;
			}

			$temp = ord($data[$i]) - ord($keyhash[$j]);

			if ($temp < 0)
			{
				$temp = $temp + 256;
			}
			
			$str .= chr($temp);
		}

		return $str;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Set the Mcrypt Cipher
	 *
	 * @access	public
	 * @param	constant
	 * @return	string
	 */
	function set_cipher($cipher)
	{
		$this->_mcrypt_cipher = $cipher;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mcrypt Mode
	 *
	 * @access	public
	 * @param	constant
	 * @return	string
	 */
	function set_mode($mode)
	{
		$this->_mcrypt_mode = $mode;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Mcrypt cipher Value
	 *
	 * @access	private
	 * @return	string
	 */
	function _get_cipher()
	{
		if ($this->_mcrypt_cipher == '')
		{
			$this->_mcrypt_cipher = MCRYPT_RIJNDAEL_256;
		}

		return $this->_mcrypt_cipher;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Mcrypt Mode Value
	 *
	 * @access	private
	 * @return	string
	 */
	function _get_mode()
	{
		if ($this->_mcrypt_mode == '')
		{
			$this->_mcrypt_mode = MCRYPT_MODE_ECB;
		}
		
		return $this->_mcrypt_mode;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Hash type
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function set_hash($type = 'sha1')
	{
		$this->_hash_type = ($type != 'sha1' AND $type != 'md5') ? 'sha1' : $type;
	}

	// --------------------------------------------------------------------

	/**
	 * Hash encode a string
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */	
	function hash($str)
	{
		return ($this->_hash_type == 'sha1') ? $this->sha1($str) : md5($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate an SHA1 Hash
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function sha1($str)
	{
		if ( ! function_exists('sha1'))
		{
			if ( ! function_exists('mhash'))
			{
				require_once(BASEPATH.'libraries/Sha1'.EXT);
				$SH = new CI_SHA;
				return $SH->generate($str);
			}
			else
			{
				return bin2hex(mhash(MHASH_SHA1, $str));
			}
		}
		else
		{
			return sha1($str);
		}
	}

}

// END CI_Encrypt class

/* End of file Encrypt.php */
/* Location: ./system/libraries/Encrypt.php */
>>>>>>> d1820c69f526205428b481a5d333f6e657ccfb16:system/libraries/Encrypt.php
