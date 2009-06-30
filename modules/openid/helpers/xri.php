<?php defined('SYSPATH') or die('No direct script access.');
/**
 * helper class for working with Extensible Resource Identifiers.
 * TODO : add escaping inline with iri to uri mapping outlined here:
 * http://www.ietf.org/rfc/rfc3987.txt
 *
 * $Id: xri.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class xri_Core {

	// xri resolution service
	const PROXY_RESOLVER = 'http://proxy.xri.net/';

	//
	public static $canonical_authorities = array('!', '=', '@', '+', '$', '(');

	/**
	 * Convert an xri to canonical form
	 *
	 * @param  string   xri string
	 * @return mixed    canonical xri string or FALSE if invalid xri
	 */
	public static function to_canonical_form($xri)
	{
		if (strpos($xri, 'xri://$ip*') === 0)
		{
			$xri = substr($xri, strlen('xri://$ip*'));
		}
		elseif (strpos($xri, 'xri://$dns*') === 0)
		{
			$xri = substr($xri, strlen('xri://$dns*'));
		}
		elseif (strpos($xri, 'xri://') === 0)
		{
			$xri = substr($xri, strlen('xri://'));
		}

		if ( ! xri::valid_canonical($xri))
			return FALSE;

		return $xri;
	}

	/**
	 * Convert an xri to an hxri by prepending the xri with the proxy xri resolver url.
	 * (For example =iname would become 'http://proxy.xri.net/=iname')
	 *
	 * @param  string   xri string
	 * @return string   hxri string
	 */
	public static function to_hxri($xri)
	{
		return self::PROXY_RESOLVER.$xri;
	}

	/**
	 * Ensure that the passed xri starts with 'xri://'
	 *
	 * @param  string   xri string
	 * @return string   xri string with scheme
	 */
	public static function prepend_xri_scheme_if_absent($xri)
	{
		$pos = strpos($xri, 'xri://');

		if ($pos === FALSE)
		{
			$xri = 'xri://'.$xri;
		}
		elseif ($pos !== 0)
		{
			return FALSE;
		}

		return $xri;
	}

	/**
	 * Validate Canonical xri
	 *
	 * @param   string   canonical xri
	 * @return  boolean
	 */
	public static function valid_canonical($xri)
	{
		return in_array($xri[0], xri::$canonical_authorities);
	}
}