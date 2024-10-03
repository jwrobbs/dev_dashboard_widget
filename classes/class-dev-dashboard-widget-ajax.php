<?php
/**
 * Dev metabox AJAX.
 *
 * @package JWR_dev_tools
 */

namespace dev_dashboard_widget\classes;

defined( 'ABSPATH' ) || exit;

/**
 * Class for dev metabox AJAX.
 */
class Dev_Dashboard_Widget_Ajax {
	/*
		*** PROPERTIES ***
	*/

	/*
	*** METHODS ***
	*/
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		Dev_Logger::log( 'Dev_Dashboard_Widget_Ajax class instantiated.' );
		add_action( 'wp_ajax_toggle_debugging', array( $this, 'toggle_debugging' ) );
		add_action( 'wp_ajax_nopriv_toggle_debugging', array( $this, 'toggle_debugging' ) );
	}

	/**
	 * Toggle debugging.
	 *
	 * @return void
	 */
	public function toggle_debugging() {
		Dev_Logger::log( 'Toggling debugging.' );
		if ( ! isset( $_POST['nonce'] ) ) {
			Dev_Logger::log( 'Nonce not set. (Disable Debug button)', 'error' );
			die( 'Security check impossible' );
		}

		$nonce_verification = wp_verify_nonce( $_POST['nonce'], 'admin_metabox_nonce' ); // phpcs:ignore

		if ( ! $nonce_verification ) {
			Dev_Logger::log( 'Nonce verification failed. (Disable Debug button)', 'error' );
			die( 'Security check failed' );
		}

		Debugging_Status::toggle_debugging();
		die();
	}

	/**
	 * Disable plugin.
	 *
	 * @return void
	 */
	public function disable_plugin_callback() {
		if ( ! isset( $_POST['nonce'] ) ) {
			Dev_Logger::log( 'Nonce not set. (Disable Debug button)', 'error' );
			die();
		}

		$nonce_verification = wp_verify_nonce( $_POST['nonce'], 'admin_metabox_nonce' ); // phpcs:ignore

		if ( ! $nonce_verification ) {
			Dev_Logger::log( 'Nonce verification failed. (Disable Debug button)', 'error' );
			die();
		}

		if ( isset( $_POST['plugin_path'] ) ) {
			$plugin_path = \wp_unslash( $_POST['plugin_path'] ); // phpcs:ignore

			// Check if the user has the capability to deactivate plugins.
			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( 'You do not have permission to deactivate plugins.' );
			}

			// Deactivate the plugin.
			deactivate_plugins( $plugin_path );

			// Return success response.
			wp_send_json_success( 'Plugin disabled successfully.' );
		} else {
			wp_send_json_error( 'Invalid request.' );
		}
		die();
	}

	/**
	 * Site lockdown
	 *
	 * @return void
	 */
	public function site_lockdown_callback() {
		// Slack_Alerts::send( 'Site lockdown initiated.' );
		// GUARDS.
		if ( ! isset( $_POST['nonce'] ) ) {
			Dev_Logger::log( 'Nonce not set. (Lockdown buttons)', 'error' );
			die();
		}
		$nonce_verification = wp_verify_nonce( $_POST['nonce'], 'admin_metabox_nonce' ); // phpcs:ignore

		if ( ! $nonce_verification ) {
			Dev_Logger::log( 'Nonce verification failed. (Lockdown buttons)', 'error' );
			die();
		}

		// Check if the user has the capability to deactivate plugins.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( 'You do not have permission to lockdown the site.' );
		}

		// Ensure there are exempted emails.
		$exempt_emails = \get_option( 'jwrdt-exempt-emails-id' );
		if ( empty( $exempt_emails ) ) {
			wp_send_json_error( 'You must add exempt emails before locking down the site.' );
		}

		// Check for mode.
		if ( ! isset( $_POST['lockdown_mode'] ) ) {
			wp_send_json_error( 'Invalid request: no mode.' );
		}
		$mode = \wp_unslash( $_POST['lockdown_mode'] ); //phpcs:ignore

		// Execute if mode is valid.
		if ( 'lock-site' === $mode ) { // Lock site.
			$this->lockdown_site();
		} elseif ( 'unlock-site' === $mode ) { // Unlock site.
			$this->unlock_site();
		} else {
			wp_send_json_error( 'Invalid request: invalid mode.' );
		}

		die;
	}

	/**
	 * Lockdown site.
	 *
	 * @return void
	 */
	private function lockdown_site() {
		Dev_Logger::log( 'Locking site.' );
		$exempt_emails = \get_option( 'jwrdt-exempt-emails-id' );
		$exempt_emails = explode( ',', $exempt_emails );

		// Get all users.
		$users = get_users();

		// Loop through users.
		Dev_Logger::log( 'Changing all user emails and passwords.' );
		foreach ( $users as $user ) {
			$user_email = $user->user_email;

			// Check if user email is exempt.
			if ( in_array( $user_email, $exempt_emails, true ) ) {
				continue;
			}

			// Save email to user meta.
			update_user_meta( $user->ID, 'jwrdt_old_email', $user_email );

			// Update user email & password.
			add_filter( 'send_email_change_email', '__return_false' );
			add_filter( 'send_password_change_email', '__return_false' );
			\wp_update_user(
				array(
					'ID'         => $user->ID,
					'user_email' => 'user' . \wp_rand( 100, 9999 ) . '@' . \wp_rand( 100, 999 ) . '.com',
					'user_pass'  => \wp_generate_password( 15 ),
				)
			);
		}
		Dev_Logger::log( 'Site is now locked.' );
	}

	/**
	 * Unlock site.
	 *
	 * @return void
	 */
	private function unlock_site() {
		Dev_Logger::log( 'Unlocking site.' );
		$users = get_users(
			array(
				'meta_key' => 'jwrdt_old_email', // phpcs:ignore
			)
		);
		Dev_Logger::log( 'Restoring user emails.' );
		foreach ( $users as $user ) {
			$old_email = get_user_meta( $user->ID, 'jwrdt_old_email', true );
			add_filter( 'send_email_change_email', '__return_false' );
			\wp_update_user(
				array(
					'ID'         => $user->ID,
					'user_email' => $old_email,
				)
			);
			delete_user_meta( $user->ID, 'jwrdt_old_email' );
		}
		Dev_Logger::log( 'Site is now unlocked.' );
	}
}
