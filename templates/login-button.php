<?php
/**
 * The template for displaying the Appwrite login button.
 *
 * @package Appwrite_Login
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="login-toggle">
	<input type="hidden" name="login_type" value="wordpress" />
	<button type="button" id="toggle-login-type" class="toggle-button button button-secondary">
		Use Appwrite Credentials
	</button>
</div>

<script>
	document.addEventListener("DOMContentLoaded", function() {
		var toggleButton = document.getElementById("toggle-login-type");
		var usingAppwrite = false;

		// Handle the toggle button click event.
		toggleButton.addEventListener("click", function() {
			if (usingAppwrite) {
				// Switch back to WordPress mode.
				toggleButton.textContent = "Use Appwrite Credentials";
				usingAppwrite = false;

				// Update the hidden input value.
				document.querySelector("input[name=login_type]").value = "WordPress";

				// Change the label for user_login.
				document.querySelector("label[for=user_login]").textContent = "Username or Email";
			} else {
				// Switch to Appwrite mode.
				toggleButton.textContent = "Use WordPress Credentials";
				usingAppwrite = true;

				// Update the hidden input value.
				document.querySelector("input[name=login_type]").value = "appwrite";

				// Change the label for user_login.
				document.querySelector("label[for=user_login]").textContent = "Appwrite Email";
			}
		});
	});
</script>
