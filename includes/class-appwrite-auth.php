<?php
/**
 * The file that has the Appwrite authentication class.
 *
 * @package Appwrite_Login
 */

use Appwrite\Client;
use Appwrite\Services\Account;

/**
 * Appwrite authentication class.
 */
class Appwrite_Auth {

	/**
	 * Appwrite client.
	 *
	 * @var Client
	 */
	private $client;

	/**
	 * Appwrite account service.
	 *
	 * @var Account
	 */
	private $account;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Get the Appwrite endpoint, project ID, and API key from the options.
		$appwrite_endpoint   = get_option( 'appwrite_endpoint' );
		$appwrite_project_id = get_option( 'appwrite_project_id' );
		$appwrite_api_key    = get_option( 'appwrite_api_key' );

		// Initialize the Appwrite client.
		$this->client = new Client();
		$this->client
			->setEndpoint( $appwrite_endpoint )
			->setProject( $appwrite_project_id )
			->setKey( $appwrite_api_key );

		// Initialize the Appwrite account service.
		$this->account = new Account( $this->client );
	}

	/**
	 * Login the user using Appwrite credentials.
	 */
	public function login_user() {
		// Check if the login form is submitted.
		if ( isset( $_POST['log'] ) && isset( $_POST['pwd'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Sanitize the email and password.
			$email    = sanitize_email( $_POST['log'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$password = sanitize_text_field( $_POST['pwd'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Check if the email or password is empty.
			if ( empty( $email ) || empty( $password ) ) {
				wp_die( 'Email or password is missing.' );
			}

			try {
				// Authenticate the user via Appwrite using email and password.
				$session = $this->account->createEmailPasswordSession( $email, $password );

				// Check if the session is valid.
				if ( $session ) {
					// Check if the user exists in WordPress.
					$user = get_user_by( 'email', $email );

					if ( ! $user ) {
						// Create a new WordPress user if not found.
						$user_id = wp_create_user( $email, wp_generate_password(), $email );
						wp_update_user(
							array(
								'ID'   => $user_id,
								'role' => 'subscriber',
							)
						);

						// Log the user in.
						wp_set_current_user( $user_id );
						wp_set_auth_cookie( $user_id );

						// Set a flag to identify the user as an Appwrite user.
						update_user_meta( $user_id, 'is_appwrite_user', true );
						wp_safe_redirect( admin_url() );
						exit;

					} else {
						// Log the existing user in.
						wp_set_current_user( $user->ID );
						wp_set_auth_cookie( $user->ID );
						wp_safe_redirect( admin_url() );
						exit;

					}
				}
			} catch ( Exception $e ) {
				wp_die( __( 'Appwrite login failed: ', 'appwrite-login' ) . $e->getMessage() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		} else {
			wp_die( __( 'Email or password not provided for Appwrite login.', 'appwrite-login' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
