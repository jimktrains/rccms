<h1><a href="http://kohanaphp.com/home">Kohana</a> OpenID Module Installation Instructions And Demo</h1>

<p>Downlod the <a href="openid.zip">zip</a> and extract the openid folder to your Kohana modules folder.</p>

<p>There are just a couple of openid config settings that you need to edit in the file config/openid.php</p>

<p>Then the following tables must be installed in your database: openid_users, openid_user_claimed_identities and openid_user_tokens (see the sql below).</p>

<p>See Openid_Extension_Sreg.php for a list of the other user attributes supported by most OpenID providers. Openid_Extension_Ax.php can potentially support arbitrary attributes but the Ax Extension is not yet widely supported by OpenID providers.</p>

<p>After you've edited the config and the tables have been set up in your database, you can <?php echo html::anchor('openid_demo/register', 'register') ?> then <?php echo html::anchor('openid_demo/login', 'login') ?>.</p>

<h2>SQL</h2>

<pre>
-- ----------------------------
-- User Tables ----------------
-- ----------------------------

-- users

CREATE TABLE IF NOT EXISTS `openid_users` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`user_name` varchar(50) NOT NULL,
	`created` int(10) unsigned NOT NULL,
	`logins` int(10) unsigned NOT NULL default '0',
	`last_login` int(10) unsigned NULL,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `unique_user_name` (`user_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `openid_user_identities` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`openid_user_id` int(11) unsigned NOT NULL,
	`display_id` varchar(255) NOT NULL,
	`claimed_id` varchar(255) NOT NULL,
	`email` varchar(127) NULL,
	`updated` timestamp NOT NULL,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `unique_claimed_id` (`claimed_id`)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `openid_user_tokens` (
	`id` int(11) unsigned NOT NULL auto_increment,
	`openid_user_id` int(11) unsigned NOT NULL,
	`user_agent` varchar(40) NOT NULL,
	`token` varchar(32) NOT NULL,
	`created` int(10) unsigned NOT NULL,
	`expires` int(10) unsigned NOT NULL,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `uniq_token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- database storage for sessions recomended, see config/session.php

CREATE TABLE IF NOT EXISTS `sessions` (
	`session_id` varchar(40) NOT NULL,
	`last_activity` int(10) UNSIGNED NOT NULL,
	`data` text NOT NULL,
	PRIMARY KEY (`session_id`)
)
</pre>

<h3>Aditional Optional setup</h3>

<p>Openid 2.0 provides a way for OpenID Providers to verify a return_to url sent by a Relying Party (that's us!). For security reasons this is a good thing!</p>

<p>Also, from a user experience point of view it means that anyone signing up to your site/service with a yahoo openid will not see the worrying "this is not a trusted relying party" message. And that's got to be a good thing!</p>

<p>To enable the Providers to verify the return_to urls specified in each of your authentication requests you need to edit and upload to your server the yadis.xrdf file found in the root of this module.</p>

<p>Then you just need to edit the URIs in the two service nodes to match the the return_to urls your application will use during authentication.</p>

<p>Next you just need to add this rewrite condition to your htaccess file.</p>

<pre>
# Turn on URL rewriting
RewriteEngine On

# -- for openID Relying Party discovery so the Provider can verify the return_to url
AddType application/xrds+xml .xrdf

RewriteCond %{HTTP_ACCEPT} application/xrds\+xml
RewriteCond %{HTTP_ACCEPT} !application/xrds\+xml\s*;\s*q\s*=\s*0(\.0{1,3})?\s*(,|$)
RewriteRule ^$ yadis.xrdf [L]
# --
</pre>

<p>NOTE: If you have a rewrite condition limiting which files on your server are accessible don't forget to add the yadis.xrdf file to the list of 'allowed' files!</p>

<h4>One final thing...</h4>

<p>When asking for user attributes from the OpenID Provider using the SREG extension you are obliged to include the url to your privacy policy.</p>

<p>The policy url is set in the openid config file.</p>

<p>There are a number of free privacy policy generators online - one can be found <a href="http://www.dmaresponsibility.org/PPG/">here</a>.</p>