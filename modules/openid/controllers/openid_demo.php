<?php defined('SYSPATH') or die('No direct script access.');
/**
 * OpenID module demo controller.
 *
 * $Id: openid_demo.php 2008-08-12 09:28:34 BST Atomless $
 *
 * This example controller should NOT be used in production,
 * it is for demonstration purposes only!
 *
 * @package    Openid
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Openid_Demo_Controller extends Template_Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	public function __construct()
	{
		parent::__construct();

		//$this->profiler = new Profiler;

		$this->template->title = 'Kohana OpenID Demo';
	}

	/**
	 * Displays the auth install instructions
	 */
	public function index()
	{
		// Display the install page
		$this->template->content =  View::factory('openid/install');
	}

	/**
	 * Example openID user registration page
	 */
	public function register()
	{
		// It has become a convention that Openid form fields are named 'openid_url'
		// this is helpfull for browser form history and auto-complete.
		// Note: It's important that you don't add any more validation rules to the openid_url
		// partly because the Openid library will perform validity checks and normalization anyway
		// and also because Openids come in many different flavours!
		$_POST = Validation::factory($_POST)
			   ->pre_filter('trim')
			   ->add_rules('openid_url', 'required', 'length[2,64]');

		if ($_POST->validate())
		{
			// Sanitize $_POST data removing all inptus without rules
			$postdata_array = $_POST->safe_array();

			// NOTE: return_to url MUST fall under the same domain as the url set for 'realm' in config/openid.php
			$return_to = url::base().'openid_demo/complete_registration';

			// If no value is passed to the constructor for return_to url it will default to the config setting.
			$openid_auth = new Openid_Auth(array('return_to' => $return_to));

			// Boolean passed to the RP start_authentication method to determine whether user attributes are
			// requested from the OpenID Provider or not. See settings for user_attributes_required and
			// user_attributes_optional in config/openid.php
			$request_attributes = TRUE;

			if ($openid_auth->start_authentication($postdata_array['openid_url'], $request_attributes))
			{
				// Due to the way the OpenID protocol works we've had to wait until after the discovery
				// phase has been completed to determine if the id already exists in the db.
				// Because OpenIDs can get reassigned to different users over time. The discovery
				// phase resolves the entered id into a url that will never be reassigned. This is the id
				// we should store in the db. This will ensure that the wrong user never mistakenly
				// inherits access to the accounts set up by whoever previously had use of the OpenID
				// in question.
				if (ORM::factory('openid_user_identity')->exists(array('claimed_id' => $openid_auth->claimed_id)) === FALSE)
				{
					$remember = (isset($_REQUEST['remember']))? TRUE : FALSE;

					// Store the state of the 'remember me' checkbox in the session
					Session::instance()->set('remember', $remember);

					// The Discovery and Association phases are complete so time to do redirect so the user
					// can authenticate with their OpenID Provider - they will then be redirected back
					// to the complete_authentication method.
					$openid_auth->redirect_to_openid_provider();
				}
				else
				{
					$_POST->add_error('openid_url', 'claimed_id_exists');
				}
			}
			else
			{
				$_POST->add_error('openid_url', 'claimed_id_invalid');

				// echo KOHANA::debug($openid_auth->internal_log);
			}
		}

		// Pass a view object to the template->content
		// Setting the error messages to those listed in i18n/openid.php
		$this->template->content = View::factory('openid/form')
								 ->set('title', 'Register A New User Account')
								 ->bind('formerrors', $_POST->errors('openid'));
	}

	/**
	 * Complete the registration process - save user info to the db
	 */
	public function complete_registration()
	{
		$openid_auth = Openid_Auth::instance();

		$openid_auth->load_from_session();

		if ($openid_auth->complete_authentication())
		{
			// Were any extra user attributes requested from the OpenID Provider?
			if ($openid_auth->attributes_requested)
			{
				url::redirect('openid_demo/attributes');
			}
			else
			{
				$openid_user = new Openid_User_Model;

				// If no attributes were requested then there's nothing to use as the user_name
				// When no string is passed to the user->create method the user_name defaults to the id field.
				$openid_user->create();

				$openid_user->add_identity($openid_auth->claimed_id, $openid_auth->display_id);

				// Authentication & registration complete so no reason not to go ahead and login
				if ($openid_auth->login($openid_user, Session::instance()->get('remember', FALSE)))
				{
					url::redirect('openid_demo/user/'.$openid_user->user_name);
				}
			}
		}
		else
		{
			$this->template->content = KOHANA::debug($openid_auth->internal_log);
		}
	}

	/**
	 * If attributes other than an OpenID are required for registration then ask the user to fill in
	 * any required attributes not returned by the OpenID Provider.
	 * NOTE: This method currently assumes that the extra required attributes are fullname and email.
	 * (see config/openid.php user_attributes_required)
	 */
	public function attributes()
	{
		$openid_auth = Openid_Auth::instance();

		// should not happen is redirected here from complete_registration but just incase!
		if ($openid_auth->load_from_session() === FALSE OR $openid_auth->claimed_id == '' OR $openid_auth->display_id == '')
			url::redirect('openid_demo/authentication_error/');

		// Merge already aquired attributes with any contained in $_POST.
		// Those in $_POST take precedence.
		$_POST = array_merge($openid_auth->user_attributes, $_POST);

		// Set up the validation object.
		// Because we are using the fullname retrieved from the OpenID Provider as the user_name
		// we call the POST field fullname, but label it on the page 'Username'
		// and later pass it to the user create method as 'user_name'.
		$_POST = Validation::factory($_POST)
			   ->pre_filter('trim')
			   ->add_rules('email', 'required', 'length[5,64]', 'email')
			   ->add_rules('fullname', 'required', 'length[4,32]', 'standard_text');

		if ($_POST->validate())
		{
			// Sanitize $_POST data removing all inptus without rules
			$postdata_array = $_POST->safe_array();

			$openid_user = new Openid_User_Model;

			if ($openid_user->exists(array('user_name' => $postdata_array['fullname'])) === FALSE)
			{
				// REGISTER
				// Create a new user using the fullname as the user_name
				$openid_user->create($postdata_array['fullname']);

				$openid_user->add_identity($openid_auth->claimed_id, $openid_auth->display_id, array('email' => $postdata_array['email']));

				// LOGIN
				// Authentication & registration complete so no reason not to go ahead and set this user as logged in.
				if ($openid_auth->login($openid_user, Session::instance()->get('remember', FALSE)))
				{
					url::redirect('openid_demo/user/'.$openid_user->user_name);
				}
			}
			else
			{
				$_POST->add_error('fullname', 'exists');
			}
		}

		// Pass a view object to the template->content
		// Setting the error messages to the ones listed in the i18n directory
		// inside openid.php
		$this->template->content = View::factory('openid/attribute_form')
								 ->bind('title', Kohana::lang('openid.attributes_page_title'))
								 ->bind('subtitle', Kohana::lang('openid.attributes_page_subtitle'))
								 ->bind('user_attributes', $_POST->safe_array())
								 ->bind('formerrors', $_POST->errors('openid'));
	}

	/**
	 * login form
	 */
	public function login()
	{
		// Check for valid user in the current session
		if (Openid_Auth::instance()->logged_in())
		{
			if ($logged_in_openid_user = Session::instance()->get('openid_user',FALSE))
			{
				url::redirect('openid_demo/user/'.$logged_in_openid_user->user_name);
			}
		}

		// Attempt to auto login with cookie.
		// Set when user previously logged in and ticked 'stay logged in'.
		if (Openid_Auth::instance()->auto_login())
		{
			if ($logged_in_openid_user = Session::instance()->get('openid_user',FALSE))
			{
				url::redirect('openid_demo/user/'.$logged_in_openid_user->user_name);
			}
		}

		// It has become a convention that Openid form fields are named 'openid_url'
		// this is helpful for browser form history & auto-complete.
		// Note: It's important that you don't add any more validation rules to the openid_url
		// partly because the Openid library will perform validity checks and normalization anyway
		// and also because Openids come in many different flavours!
		$_POST = Validation::factory($_POST)
			   ->pre_filter('trim')
			   ->add_rules('openid_url', 'required', 'length[2,64]');

		if ($_POST->validate())
		{
			// Sanitize $_POST data removing all inptus without rules
			$postdata_array = $_POST->safe_array();

			// NOTE: return_to url MUST fall under the same domain as the url set for 'realm' in config/openid.php
			$return_to = url::base().'openid_demo/complete_login';

			// If no value is passed to the constructor for return_to url it will default to the config/openid.php setting.
			$openid_auth = new Openid_Auth(array('return_to' => $return_to));

			if ($openid_auth->start_authentication($postdata_array['openid_url']))
			{
				// Again, it's important we wait til after the discovery phase before we check if the id
				// exists in the db or not. See explaination for this in register method above.
				// However, If the user entered a Provider type uri like yahoo.com
				// (in order to then select their OpenID from a list on their Provider site after being
				// redirected) then the entered ID doesn't need to exist to proceed with login.
				// In such cases the existance of the ID will be checked in the complete_login method below.
				if (ORM::factory('openid_user_identity', $openid_auth->claimed_id)->loaded
					OR $openid_auth->current_openid_service_endpoint->is_openid_provider_type())
				{
					$remember = (isset($_REQUEST['remember']))? TRUE : FALSE;

					// Store the state of the 'remember me' checkbox in the session
					Session::instance()->set('remember', $remember);

					// The Discovery and Association phases are complete so time to do redirect so the user
					// can authenticate with their OpenID Provider - they will then be redirected back
					// to the complete_authentication method.
					$openid_auth->redirect_to_openid_provider();
				}
				else
				{
					$_POST->add_error('openid_url', 'claimed_id_not_found');
				}
			}
			else
			{
				url::redirect('openid_demo/authentication_error');
			}
		}

		// If redirected back here from the complete_login method below because no user account was found
		// for the claimed_id. This only happens if the user initially enters an OpenID Provider type uri
		// like yahoo.com - which means we could not check the claimed_id til after the user had been
		// redirected back.
		if ($error = Session::instance()->get('openid_url_error', FALSE))
		{
			$_POST->add_error('openid_url', $error);
		}

		// Pass a view object to the template->content
		// Setting the error messages to those listed in i18n/openid.php
		$this->template->content = View::factory('openid/form')
								 ->set('title', 'Login')
								 ->bind('formerrors', $_POST->errors('openid'));
	}

	/**
	 * Complete the login process
	 */
	public function complete_login()
	{
		$openid_auth = Openid_Auth::factory()->load_from_session();

		if ($openid_auth->complete_authentication())
		{
			// Load the user identity matching the current claimed_id
			$openid_user_identity = new Openid_User_Identity_Model($openid_auth->claimed_id);

			// Because we already checked for the existance of an account for the entered OpenID in the login
			// method above, baring some db failure the only time the $openid_user->loaded will equal FALSE
			// here is when the user intially entered an OpenID Provider type uri like yahoo.com
			// and in such cases the claimed_id does not get set until we receive the response after
			// the user is redirected back from the Provider site.
			if ( ! $openid_user_identity->loaded)
			{
				Session::instance()->set_flash('openid_url_error', 'claimed_id_not_found');

				url::redirect('openid_demo/login');
			}

			// Load the Openid_User that 'owns' the current identity
			$openid_user = new Openid_User_Model($openid_user_identity->openid_user_id);

			// Baring some horrible db error this should not happen! but just incase...
			if ( ! $openid_user->loaded)
			{
				$openid_auth->log('authentication_error', 'openid_demo', 'complete_login', 'no Openid_User found with id='.$openid_user_identity->openid_user_id);

				url::redirect('openid_demo/authentication_error');
			}

			// Perform login - Authentication is already done so it's really just a case of saving the
			// required settings to set the state of this user to 'logged in'
			if ($openid_auth->login($openid_user, Session::instance()->get('remember', FALSE)))
			{
				url::redirect('openid_demo/user/'.$openid_user->user_name);
			}
			else
			{
				$openid_auth->log('authentication_error', 'openid_demo', 'complete_login', 'login method of Openid_Auth failed to return TRUE');

				url::redirect('openid_demo/authentication_error');
			}
		}
		else
		{
			$openid_auth->log('authentication_error', 'openid_demo', 'complete_login', 'login failed to complete');

			url::redirect('openid_demo/authentication_error');
		}
	}

	/**
	 * log out the currently logged in user
	 */
	public function logout()
	{
		// Load auth and log out
		Openid_Auth::instance()->logout(TRUE);

		// Redirect back to the login page
		url::redirect('openid_demo/login');
	}

	/**
	 * Private user page
	 */
	public function user($user_name = FALSE)
	{
		$logged_in_openid_user = Session::instance()->get('openid_user', FALSE);

		if ($user_name === FALSE OR $logged_in_openid_user === FALSE)
			url::redirect('openid_demo/login/');

		if ($logged_in_openid_user->user_name != $user_name)
			url::redirect('openid_demo/access_denied/');

		$openid_user = new Openid_User_Model($user_name);

		// Pass a view object to the template->content
		// Setting the error messages to those listed in i18n/openid.php
		$this->template->content = View::factory('openid/user_page')
								 ->set('user_name', $user_name)
								 ->bind('user_identities', $openid_user->openid_user_identities);

	}

	/**
	 *
	 */
	public function access_denied()
	{
		$this->template->content = '<h1>Access Denied</h1><p>You are not authorized to view this page.</p>';
	}

	/**
	 * Error page
	 */
	public function authentication_error()
	{
		$openid_auth = Openid_Auth::instance()->load_from_session();

		$this->template->content = '<h1>Authentication Error</h1><p>Here is the internal log:</p>'.KOHANA::debug($openid_auth->internal_log);
	}

} // End Auth Controller