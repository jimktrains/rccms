<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Library Class for handling OpenID authentication.
 *
 * $Id: Openid_Relying_Party.php 2008-08-12 09:28:34 BST Atomless $
 *
 * Instantiation of this class initiates a chain of extension down to the Openid base class:
 * Openid_Auth.php <- Openid_Relying_Party.php <- Openid_Response.php <-
 * Openid_Association.php <- Openid_Discovery.php <- Openid_Request.php <- Openid.php
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Openid_Auth_Core extends Openid_Relying_Party {

	/**
	 * Singleton instance of Openid_Auth.
	 */
	public static function instance()
	{
		static $instance;

		// Create the instance if it does not exist
		($instance === NULL) AND $instance = new Openid_Auth;

		return $instance;
	}

	/**
	 * Create an instance of Openid_Auth.
	 *
	 * @param   array - openid fields contained in parent Openid class
	 * @return  object
	 */
	public static function factory($fields = array())
	{
		return new Openid_Auth($fields);
	}

	/**
	 * Constructor.
	 *
	 * @param   array - openid fields to be set in the base Openid.php class
	 * @return  void
	 */
	public function __construct($fields = array())
	{
		// See the set_authentication_fields method of the parent Openid library class
		// and the openid_identifier helper for the steps taken when
		// the claimed_id is set to a param passed to the Openid constructor
		parent::__construct($fields);
	}

	/**
	 * Check if there's a user session
	 *
	 * @return boolean
	 */
	public function logged_in()
	{
		$logged_in_openid_user = $this->session->get('openid_user', FALSE);

		// Checks if a user is logged in and valid
		return ( ! empty($logged_in_openid_user)
				 AND is_object($logged_in_openid_user)
				 AND ($logged_in_openid_user instanceof Openid_User_Model)
				 AND $logged_in_openid_user->loaded);
	}

	/**
	 * Logs a user in.
	 *
	 * @param   String   user_name
	 * @param   boolean  enable auto-login
	 * @return  boolean
	 */
	public function login($openid_user, $remember)
	{
		if ($remember)
		{
			// Create a new autologin token
			$token = new Openid_User_Token_Model;

			// Set token data
			$token->openid_user_id = $openid_user->id;
			$token->expires = time() + KOHANA::config('openid.login_token_lifetime');
			$token->save();

			// Set the autologin cookie - links to openid_user_token in the db
			cookie::set('openidautologin', $token->token, KOHANA::config('openid.login_token_lifetime'));
		}

		// Finish the login
		$this->complete_login($openid_user);

		return TRUE;
	}

	/**
	 * Logs a user in, based on stored credentials in authautologin cookie.
	 *
	 * @return  boolean
	 */
	public function auto_login()
	{
		if ($token = cookie::get('openidautologin'))
		{
			// Load the token and user
			$token = new Openid_User_token_Model($token);

			if ($token->id > 0 AND $token->openid_user->id > 0)
			{
				if ($token->user_agent === sha1(Kohana::$user_agent))
				{
					// Save the token to create a new unique token
					$token->save();

					// Set the new token
					cookie::set('openidautologin', $token->token, $token->expires - time());

					// Complete the login with the found data
					$this->complete_login($token->openid_user);

					// Automatic login was successful
					return TRUE;
				}

				// Token is invalid
				$token->delete();
			}
		}

		return FALSE;
	}

	/**
	 * Complete the login for an openid user by incrementing the logins and setting
	 * session data: user_id, user_name, roles
	 *
	 * @param   object   user model object
	 * @return  void
	 */
	protected function complete_login(Openid_User_Model $openid_user)
	{
		// Update the number of logins
		$openid_user->logins += 1;

		// Set the last login date
		$openid_user->last_login = time();

		// Save the user
		$openid_user->save();

		// Regenerate session_id
		$this->session->regenerate();

		// Store session data
		$this->session->set('openid_user', $openid_user);
	}

	/**
	 * Log a user out.
	 *
	 * @param   boolean   completely destroy the session - also delete authautologin cookie
	 * @return  boolean
	 */
	public function logout($destroy)
	{
		// Delete the autologin cookie if it exists
		cookie::get('openidautologin') and cookie::delete('openidautologin');

		if ($destroy === TRUE)
		{
			// Destroy the session completely
			$this->session->destroy();
		}
		else
		{
			// Remove the user object from the session
			$this->session->delete('openid_user');

			// Regenerate session_id
			$this->session->regenerate();
		}

		// Double check
		return ! $this->session->get('openid_user', FALSE);
	}
}