<?php defined('SYSPATH') or die('No direct script access.');
/**
 * helper class for working with OpenID identifiers.
 *
 * $Id: openid_identifier.php 2008-08-12 09:28:34 BST Atomless $
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class openid_identifier_Core {

	/**
	 * Normalizes an OpenID identifier that can be a URL or an XRI.
	 * Returns true on success and false on failure.
	 *
	 * Normalization is performed according to the following rules
	 * (see the xri helper for the code for rules 1 and 2 and
	 * the url_openid helper for rules 3 and 4)
	 *
	 * 1. If the user's input starts with one of the "xri://", "xri://$ip*",
	 *    or "xri://$dns*" prefixes, they MUST be stripped off, so that XRIs
	 *    are used in the canonical form, and URI-authority XRIs are further
	 *    considered URL identifiers.
	 *
	 * 2. If the first character of the resulting string is an XRI Global
	 *    Context Symbol ("=", "@", "+", "$", "!"), then the input SHOULD be
	 *    treated as an XRI.
	 *
	 * 3. Otherwise, the input SHOULD be treated as an http URL; if it does
	 *    not include an "http" or "https" scheme, the Identifier MUST be
	 *    prefixed with the string "http://".
	 *
	 * 4. URL identifiers MUST then be further normalized by both following
	 *    redirects when retrieving their content and finally applying the
	 *    rules in Section 6 of [RFC3986] to the final destination URL.
	 *
	 * (see http://www.rfc.net/rfc3986.html)
	 *
	 * TODO: add escaping of special chars (rarely needed but should be added to be complete)
	 *
	 * @param string    reference to identifier to be normalized
	 * @return mixed    False on failure or normailzed identifier string on success
	 */
	public static function normalize($id)
	{
		// RFC 3986 7.2.1 & 7.2.2
		// If identifier is xri and can be converted to valid canonical xri
		// halt normalization and return TRUE
		$xri = xri::to_canonical_form($id);

		if ($xri !== FALSE)
			return $xri;

		// RFC 3986 7.2.3
		if (strpos($id, '://') === FALSE)
		{
			// Identifier appears to be a url so must start with http://
			$id = 'http://'.$id;
		}

		// RFC 3986 7.2.4
		return url_openid::normalize($id);
	}

	/**
	 *
	 */
	public static function detect_basic_type($id)
	{
		if(xri::valid_canonical($id) OR strpos($id, 'xri://') === 0)
			return 'xri';

		return 'uri';
	}

}