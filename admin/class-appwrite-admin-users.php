<?php
/**
 * The file that has the Appwrite admin users class, responsible for adding a custom column to the users list.
 *
 * @package Appwrite_Login
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Appwrite_Admin_Users class.
 */
class Appwrite_Admin_Users {

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Add a custom column and populate it.
		add_filter( 'manage_users_columns', array( $this, 'add_custom_user_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'show_custom_user_column_content' ), 10, 3 );

		// Add sortable column.
		add_filter( 'manage_users_sortable_columns', array( $this, 'make_custom_column_sortable' ) );
		add_action( 'pre_get_users', array( $this, 'custom_column_orderby' ) );

		// Remove password reset link for Appwrite users.
		add_filter( 'user_row_actions', array( $this, 'remove_password_reset_for_appwrite_users' ), 10, 2 );
	}

	/**
	 * Add a custom column to the users list.
	 *
	 * @param array $columns An array of column names.
	 * @return array An array of column names.
	 */
	public function add_custom_user_column( $columns ) {
		$columns['appwrite_user'] = 'Appwrite User';
		return $columns;
	}

	/**
	 * Populate the custom column with content.
	 *
	 * @param string $value The content of the column.
	 * @param string $column_name The name of the column.
	 * @param int    $user_id The ID of the user.
	 * @return string The content of the column.
	 */
	public function show_custom_user_column_content( $value, $column_name, $user_id ) {
		if ( 'appwrite_user' == $column_name ) {
			// Check if the user has a custom meta field 'is_appwrite_user' set to true.
			$is_appwrite_user = get_user_meta( $user_id, 'is_appwrite_user', true );
			return $is_appwrite_user ? 'Yes' : 'No';
		}
		return $value;
	}

	/**
	 * Make the custom column sortable.
	 *
	 * @param array $columns An array of column names.
	 * @return array An array of column names.
	 */
	public function make_custom_column_sortable( $columns ) {
		$columns['appwrite_user'] = 'appwrite_user';
		return $columns;
	}

	/**
	 * Sort the users list by the custom column.
	 *
	 * @param WP_User_Query $query The WP_User_Query instance (passed by reference).
	 */
	public function custom_column_orderby( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( 'appwrite_user' == $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', 'is_appwrite_user' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Remove the "Send password reset" link for Appwrite users.
	 *
	 * @param array   $actions An array of row action links.
	 * @param WP_User $user The WP_User object.
	 * @return array An array of row action links.
	 */
	public function remove_password_reset_for_appwrite_users( $actions, $user ) {
		// Check if the user is an Appwrite user by checking the meta key 'is_appwrite_user'.
		$is_appwrite_user = get_user_meta( $user->ID, 'is_appwrite_user', true );
  
		// If the user is an Appwrite user, remove the "Send password reset" link.
		if ( $is_appwrite_user ) {
			unset( $actions['resetpassword'] );
		}
  
		return $actions;
	}
}
