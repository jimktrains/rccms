<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

/**
 * Auth library configuration. By default, Auth will use the controller
 * database connection. If Database is not loaded, it will use use the default
 * database group.
 *
 * In order to log a user in, a user must have the `login` role. You may create
 * and assign any other role to your users.
 */

/**
 * Driver to use for authentication.
 */
'driver'=> 'Local',

/**
 * Type of hash to use for passwords. Any algorithm supported by the hash function
 * can be used here. Note that the length of your password is determined by the
 * hash type + the number of salt characters.
 * @see http://php.net/hash
 * @see http://php.net/hash_algos
 */
'hash_method'=> 'sha1',

/**
 * Defines the hash offsets to insert the salt at. The password hash length
 * will be increased by the total number of offsets.
 */
'salt_pattern'=> '1, 3, 5, 9, 14, 15, 20, 21, 28, 30',

/**
 * Set the auto-login (remember me) cookie lifetime, in seconds. The default
 * lifetime is two weeks.
 */
'lifetime'=> 1209600,

/**
 * Set the session key that will be used to store the current user.
 */
'session_key'=> 'auth_user'

);