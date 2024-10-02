<?php
/**
 * The file that has the Appwrite admin class, responsible for adding the settings page.
 *
 * @package Appwrite_Login
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Appwrite_Admin class.
 */
class Appwrite_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add the admin menu.
	 */
	public function add_admin_menu() {
		add_options_page(
			'Appwrite Login Settings',
			'Appwrite Login',  
			'manage_options',        
			'appwrite-login-settings',  
			array( $this, 'settings_page_html' )
		);
	}

	/**
	 * Register the settings.
	 */
	public function register_settings() {
		register_setting( 'appwrite-login-settings-group', 'appwrite_endpoint' );
		register_setting( 'appwrite-login-settings-group', 'appwrite_project_id', array( $this, 'sanitize_secret_input' ) );
		register_setting( 'appwrite-login-settings-group', 'appwrite_api_key', array( $this, 'sanitize_secret_input' ) );
		register_setting( 'appwrite-login-settings-group', 'appwrite_enable_login' );
	}

	/**
	 * Sanitize the secret input fields.
	 *
	 * @param string $input The input value.
	 * @return string The sanitized input value.
	 */
	public function sanitize_secret_input( $input ) {
		// Check if the input is empty. If it is, keep the current value.
		$current_value = get_option( current_filter() );

		if ( empty( $input ) ) {
			return $current_value;
		}

		// Otherwise, sanitize and return the input.
		return sanitize_text_field( $input );
	}

	/**
	 * Settings page HTML.
	 */
	public function settings_page_html() {
		?>
		<div class="wrap">
			<h1>Appwrite Login Settings</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'appwrite-login-settings-group' ); ?>
				<?php do_settings_sections( 'appwrite-login-settings-group' ); ?>

				<table class="form-table">
					<!-- Appwrite Endpoint -->
					<tr>
						<th scope="row">Appwrite Endpoint</th>
						<td><input type="text" name="appwrite_endpoint" value="<?php echo esc_attr( get_option( 'appwrite_endpoint' ) ); ?>" />
							<p class="description">Enter the Appwrite endpoint URL.</p>
						</td>
					</tr>

					<!-- Appwrite Project ID -->
					<tr>
						<th scope="row">Appwrite Project ID</th>
						<td>
							<input type="password" name="appwrite_project_id" placeholder="Enter to update" value="<?php echo esc_attr( get_option( 'appwrite_project_id' ) ); ?>" />
							<p class="description">Current value will not be displayed for security reasons.</p>
						</td>
					</tr>

					<!-- Appwrite API Key -->
					<tr>
						<th scope="row">Appwrite API Key</th>
						<td>
							<input type="password" name="appwrite_api_key" placeholder="Enter to update" value="<?php echo esc_attr( get_option( 'appwrite_api_key' ) ); ?>" />
							<p class="description">Current value will not be displayed for security reasons.</p>
						</td>
					</tr>

					<!-- Enable/Disable Appwrite Login -->
					<tr>
						<th scope="row">Enable Appwrite Login</th>
						<td>
							<input type="checkbox" name="appwrite_enable_login" value="1" <?php checked( get_option( 'appwrite_enable_login' ), '1' ); ?> />
							<p class="description">Enable or disable the "Use Appwrite Credentials" button on the login page.<br/>
							Note: Make sure to set the Appwrite Endpoint, Project ID, and API Key above,<br /> otherwise, the button will not be displayed.
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Check if Appwrite login is enabled.
	 *
	 * @return bool True if Appwrite login is enabled, false otherwise.
	 */
	public static function is_appwrite_login_enabled() {
		$enabled    = get_option( 'appwrite_enable_login' ) === '1';
		$endpoint   = get_option( 'appwrite_endpoint' );
		$project_id = get_option( 'appwrite_project_id' );
		$api_key    = get_option( 'appwrite_api_key' );

		// Return true only if all the fields are set and login is enabled.
		return $enabled && ! empty( $endpoint ) && ! empty( $project_id ) && ! empty( $api_key );
	}
}
