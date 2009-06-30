<?php defined('SYSPATH') or die('No direct script access.');
/**
 * helper methods to assist in diffie-hellman key exchange.
 *
 * $Id: diffie_hellman.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class diffie_hellman_Core {

	// Default Diffie-Hellman Constants

	// Confirmed Prime Number used for default Modulus
	const DEFAULT_MODULUS   = '155172898181473697471232257763715539915724801966915404479707795314057629378541917580651227423698188993727816152646631438561595825688188889951272158842675419950341258706556549803580104870537681476726513255747040765857479291291572334510643245094715007229621094194349783925984760375594985848253359305585439638443';

	const DEFAULT_GENERATOR = '2';

	/**
	 * Generate initial diffie hellman exchange keys
	 *
	 * @param  string   long number passed as string used instead of the default modulus when generating public and private keys
	 * @param  string   number passed as string used instead of the default modulus when generating public and private keys
	 * @param  string   long number passed as string and used instead of generating a new private key
	 * @return array    associative array containign the dh keys : modulus, gen, private and public
	 */
	public static function get_exchange_keys($modulus = FALSE, $generator = FALSE, $private = FALSE)
	{
		$modulus = ($modulus === FALSE)? diffie_hellman::DEFAULT_MODULUS : $modulus;

		$generator = ($generator === FALSE)? diffie_hellman::DEFAULT_GENERATOR : $generator;

		if ($private === FALSE)
		{
			$private = bcadd(crypt::rand($modulus), 1);
		}

		$public = bcpowmod($generator, $private, $modulus);

		return array('dh_modulus' => $modulus, 'dh_gen' => $generator, 'dh_consumer_private' => $private, 'dh_consumer_public' => $public);
	}

	/**
	 * Compute the mac_key (shared secret) as a base 64 encoded binary string based on the passed dh keys
	 *
	 * @param  string   long number passed as string used as the public key
	 * @param  string   the encoded mac_key that will be used to establish the mac_key
	 * @param  string   the type of encryption used - see Openid::$supported_association_types
	 * @param  string   long number passed as string used as the private key
	 * @param  string   number used as the modulus when extracting the mac_key
	 * @return string   base 64 encoded string of the binary mac_key
	 */
	function compute_mac_key($dh_server_public = FALSE, $enc_mac_key = FALSE, $assoc_type = FALSE, $private = FALSE, $modulus = FALSE)
	{
		if ($dh_server_public === FALSE OR $enc_mac_key === FALSE OR $assoc_type === FALSE OR $modulus === FALSE OR $private === FALSE)
			return FALSE;

		$dh_server_public = crypt::base64_to_long($dh_server_public);

		$enc_mac_key = base64_decode($enc_mac_key);

		$hash_type = Openid::get_hash_type_from_assoc_or_session_type($assoc_type);

		if ($hash_type === FALSE)
			return FALSE;

		// encode the secret - this may need decoding later -
		// but without this encoding it breaks kohana's debug output
		return base64_encode(diffie_hellman::xor_mac_key($dh_server_public, $enc_mac_key, $hash_type, $private, $modulus));
	}

	/**
	 * Compute the mac_key (shared secret) as a binary string based on the passed dh keys
	 *
	 * @param  string   long number passed as string used as the server public key
	 * @param  string   the encoded mac_key that will be used to establish the mac_key
	 * @param  string   the type of hash algorithm used - see Supported hash algorithms in Openid.php
	 * @param  string   long number passed as string used as the private key
	 * @param  string   number used as the modulus when extracting the mac_key
	 * @return string   mac_key as binary string
	 */
	public static function xor_mac_key($composite, $enc_mac_key, $hash_type, $private, $modulus)
	{
		$dh_mac_key = diffie_hellman::get_mac_key($composite, $private, $modulus);

		$dh_mac_key_str = crypt::long_to_binary($dh_mac_key);

		if ( ! in_array(strtolower($hash_type), hash_algos()))
			throw new Kohana_Exception('diffie_hellman.unsupported_hash_type', $hash_type);

		$hash_dh_mac_key = hash(strtolower($hash_type), $dh_mac_key_str, true);

		$xor_mac_key = '';

		for ($i = 0; $i < crypt::count_bytes($enc_mac_key); $i++)
		{
			$xor_mac_key .= chr(ord($enc_mac_key[$i]) ^ ord($hash_dh_mac_key[$i]));
		}

		return $xor_mac_key;
	}

	/**
	 * Compute the mac_key (shared secret) as a long number based on the passed dh keys
	 *
	 * @param  string   long number passed as string used as the server public key
	 * @param  string   long number passed as string used as the private key
	 * @param  string   number used as the modulus when computing the mac_key
	 * @return integer  mac_key as long number
	 */
	public static function get_mac_key($composite, $private, $modulus)
	{
		$modulus = ($modulus === FALSE)? diffie_hellman::DEFAULT_MODULUS : $modulus;

		$private = ($private === FALSE)? bcadd(crypt::rand($modulus), 1) : $private;

		return bcpowmod($composite, $private, $modulus);
	}

}