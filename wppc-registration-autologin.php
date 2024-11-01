<?php
/*
Version: 140520
Text Domain: wppc-registration-autologin
Plugin Name: WPPC Registration Autologin

Author: Cristian Lavaque
Author URI: http://cristianlavaque.com

Plugin URI: http://cristianlavaque.com
Description: WordPress plugin that logs the user in automatically after registration.
*/
if (!defined('WPINC'))
	exit('Do NOT access this file directly: '.basename(__FILE__));

class wppc_registration_autologin
{
	public $version = '140520';

	public function __construct()
	{
		add_action('init', array($this, 'init'));
	}

	public function init()
	{
		if (defined('WS_PLUGIN__S2MEMBER_VERSION'))
			add_action('ws_plugin__s2member_during_configure_user_registration', array($this, 's2_during_configure_user_registration'));
		else 
			add_action('user_register', array($this, 'after_user_registers'));
	}

	public function after_user_registers($user_id)
	{
		if (is_admin())
			return;

		if (!did_action('login_form_register'))
			return;

		wp_set_auth_cookie($user_id, FALSE, FALSE);

		wp_redirect(home_url());
	}

	// Compatibility with s2Member.
	public function s2_during_configure_user_registration($vars)
	{
		if (is_admin()) 
			return;

		wp_set_auth_cookie($vars['user_id'], FALSE, FALSE);

		if (did_action('login_form_register'))
			c_ws_plugin__s2member_login_redirects::login_redirect($vars['login'], $vars['user']);

		$GLOBALS['_s2_during_configure_user_registration'] = $vars;
		add_action('template_redirect', array($this, '_s2_during_configure_user_registration'), 1);
	}

	public function _s2_during_configure_user_registration()
	{
		$vars = $GLOBALS['_s2_during_configure_user_registration'];
		c_ws_plugin__s2member_login_redirects::login_redirect($vars['login'], $vars['user']);
	}
}

$GLOBALS['wppc_registration_autologin'] = new wppc_registration_autologin();
