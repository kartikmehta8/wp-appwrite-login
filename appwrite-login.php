<?php
/**
 * Plugin Name: Appwrite Login
 * Description: Adds a "Use Appwrite Credentials" button to WordPress login and integrates with Appwrite user authentication.
 * Plugin URI: https://appwrite.io
 * Version: 1.0
 * Author: Kartik Mehta
 * Author URI: https://mrmehta.in
 * License: GPLv2 or later
 * Text Domain: appwrite-login
 * 
 * @package Appwrite_Login
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Composer's autoload file to include Appwrite SDK (composer require appwrite/appwrite).
if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
} else {
	wp_die( 'Composer autoload file not found. Please run "composer install".' );
}

// Include necessary files.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-appwrite-auth.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-appwrite-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/class-appwrite-admin-users.php';

/**
 * Initialize the plugin.
 */
function appwrite_login_init() {
	if ( is_admin() && current_user_can( capability: 'manage_options' ) ) {
		new Appwrite_Admin();
		new Appwrite_Admin_Users();
	}
}

add_action( 'plugins_loaded', 'appwrite_login_init' );

/**
 * Add the Appwrite login button to the login form.
 */
function appwrite_add_login_button() {

	// Check if Appwrite login is enabled.
	if ( ! Appwrite_Admin::is_appwrite_login_enabled() ) {
		return;
	}

	ob_start();
	// Include the login button template.
	require_once plugin_dir_path( __FILE__ ) . 'templates/login-button.php';
	ob_flush();

	// Enqueue the custom CSS file.
	wp_enqueue_style( 'appwrite-style', plugin_dir_url( __FILE__ ) . 'assets/css/appwrite-style.css', array(), filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/appwrite-style.css' ) );
}

add_action( 'login_form', 'appwrite_add_login_button' );

/**
 * Handle the Appwrite login.
 */
function appwrite_handle_login() {
	// Check if the login form is submitted.
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['login_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		// Check if "Use Appwrite Credentials" is selected.
		if ( 'appwrite' === $_POST['login_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Process Appwrite login.
			if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				// check if it is valid email.
				if ( ! filter_var( $_POST['log'], FILTER_VALIDATE_EMAIL ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					wp_die( 'Invalid email address.' );
				}

				// Handle the login.
				$auth = new Appwrite_Auth();
				$auth->login_user();
				exit;
			}
		}
	}
}

add_action( 'init', 'appwrite_handle_login' );
