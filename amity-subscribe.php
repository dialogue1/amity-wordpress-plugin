<?php
/*
Plugin Name: amity Subscribe
Plugin URI: http://www.dialogue1.de/
Description: Allows to let people subscribe to amity-managed contact lists.
Version: 1.0.0
Author: dialogue1 GmbH
License: MIT
*/

if (!function_exists('add_action')) {
	exit;
}

define('DIALOGUE1_AMITY_ROOT', plugin_dir_path(__FILE__));
require DIALOGUE1_AMITY_ROOT.'vendor/autoload.php';

add_action('admin_init', function() {
	add_settings_section(
		'amity_section',
		'amity Subscribe Configuration',
		function() { print '<p>To make subscriptions work, you need to configure your amity API credentials here.</p>'; },
		'general'
	);

	add_settings_field(
		'amity_hostname',
		'<label for="amity_hostname">amity Installation:</label>',
		function() { print '<input name="amity_hostname" type="text" id="amity_hostname" value="'.esc_attr(get_option('amity_hostname')).'" placeholder="news.mydomain.net" class="regular-text ltr">'; },
		'general',
		'amity_section'
	);

	add_settings_field(
		'amity_ssl',
		'',
		function() { print '<label><input name="amity_ssl" type="checkbox" id="amity_ssl" value="1"'.(get_option('amity_ssl') ? ' checked="checked"' : '').'> use SSL (if you use HTTPS to access amity, enable this)</label>'; },
		'general',
		'amity_section'
	);

	add_settings_field(
		'amity_client_id',
		'<label for="amity_client_id">Client ID:</label>',
		function() { print '<input name="amity_client_id" type="text" id="amity_client_id" value="'.esc_attr(get_option('amity_client_id')).'" class="small-text ltr">'; },
		'general',
		'amity_section'
	);

	add_settings_field(
		'amity_api_key',
		'<label for="amity_api_key">API Key:</label>',
		function() { print '<input name="amity_api_key" type="text" id="amity_api_key" value="'.esc_attr(get_option('amity_api_key')).'" placeholder="1a2b3c4d5e6f7e8d9c0b9a8b7c6d5e4f3e2d1c0b" class="regular-text ltr">'; },
		'general',
		'amity_section'
	);

	register_setting('general', 'amity_hostname');
	register_setting('general', 'amity_ssl');
	register_setting('general', 'amity_client_id');
	register_setting('general', 'amity_api_key');
});

add_action('widgets_init', function() {
	register_widget('dialogue1\amity\SubscribeWidget');
});
