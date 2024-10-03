<?php
/**
 * Enable or disable debugging.
 *
 * @package JWR_admin_metabox
 */

namespace dev_dashboard_widget\classes;

defined( 'ABSPATH' ) || die();

/**
 * Class Debugging_Mode
 */
class Debugging_Status {

	/**
	 * Debug strings.
	 *
	 * @var array
	 */
	private static $debug_strings = array(
		'debug_enabled'       => "define( 'WP_DEBUG', true );",
		'debug_disabled'      => "define( 'WP_DEBUG', false );",
		'logging_enabled'     => "define( 'WP_DEBUG_LOG', true );",
		'logging_disabled'    => "define( 'WP_DEBUG_LOG', false );",
		'displaying_enabled'  => "define( 'WP_DEBUG_DISPLAY', true );",
		'displaying_disabled' => "define( 'WP_DEBUG_DISPLAY', false );",
		'error_reporting'     => 'error_reporting( E_ALL & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE );',
	);

	/**
	 * Get debugging status.
	 * Possible values: 'enabled', 'disabled', 'unset'.
	 *
	 * @return string
	 */
	public static function get_debugging_status() {
		return self::evaluate_constant( 'WP_DEBUG' );
	}

	/**
	 * Get logging status.
	 * Possible values: 'enabled', 'disabled', 'unset'.
	 *
	 * @return string
	 */
	public static function get_logging_status() {
		return self::evaluate_constant( 'WP_DEBUG_LOG' );
	}

	/**
	 * Get displaying status.
	 * Possible values: 'enabled', 'disabled', 'unset'.
	 *
	 * @return string
	 */
	public static function get_displaying_status() {
		return self::evaluate_constant( 'WP_DEBUG_DISPLAY' );
	}

	/**
	 * Evaluate the constant's status.
	 * Possible values: 'enabled', 'disabled', 'unset'.
	 *
	 * @param string $constant The constant to evaluate.
	 * @return string
	 */
	private static function evaluate_constant( $constant ) {
		if ( ! \defined( $constant ) ) {
			return 'unset';
		} elseif ( \constant( $constant ) ) {
			return 'enabled';
		} else {
			return 'disabled';
		}
	}

	/**
	 * Add symbols to the status.
	 *
	 * @param string $status The status to add symbols to.
	 * @return string
	 */
	public static function add_symbol( $status ) {
		if ( 'disabled' === $status ) {
			return '&#x1f7e2; disabled';
		} else {
			return '&#10060; ' . $status;
		}
	}

	/**
	 * Toggle debugging.
	 *
	 * @return void
	 */
	public static function toggle_debugging() {

		$debugging = self::get_debugging_status();

		if ( 'enabled' === $debugging ) {
			self::disable_debugging();
		} else {
			self::enable_debugging();
		}
	}

	/**
	 * Disable debugging.
	 *
	 * @return void
	 */
	private static function disable_debugging() {
		$config_file_path = ABSPATH . 'wp-config.php';

		global $wp_filesystem;
		if ( null === $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$config_file = $wp_filesystem->get_contents( $config_file_path );

		// DEBUG constants.
		if ( ! \str_contains( $config_file, self::$debug_strings['debug_disabled'] )
		&& ! \str_contains( $config_file, self::$debug_strings['debug_enabled'] ) ) {
			$config_file = \str_replace( '/* Add any custom values between this line and the "stop editing" line. */', '/* Add any custom values between this line and the "stop editing" line. */' . "\n" . self::$debug_strings['debug_disabled'], $config_file );

			$res = $wp_filesystem->put_contents( $config_file_path, $config_file );
		}
		if ( ! \str_contains( $config_file, self::$debug_strings['debug_enabled'] ) ) {
			return;
		}

		$config_file = \str_replace( self::$debug_strings['debug_enabled'], self::$debug_strings['debug_disabled'], $config_file );

		// PHP error reporting.
		$config_file = \str_replace(
			self::$debug_strings['error_reporting'] . "\n",
			'',
			$config_file
		);

		$res = $wp_filesystem->put_contents( $config_file_path, $config_file );
	}

	/**
	 * Enable debugging.
	 *
	 * @return void
	 */
	private static function enable_debugging() {

		$config_file_path = ABSPATH . 'wp-config.php';

		global $wp_filesystem;
		if ( null === $wp_filesystem ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$config_file = $wp_filesystem->get_contents( $config_file_path );

		if ( ! \str_contains( $config_file, self::$debug_strings['debug_disabled'] ) ) {
			return;
		}

		// Enable debugging.
		$config_file = \str_replace( self::$debug_strings['debug_disabled'], self::$debug_strings['debug_enabled'], $config_file );

		// Check for logging. Enable or add.
		if ( \str_contains( $config_file, self::$debug_strings['logging_enabled'] ) ) {
			$config_file = $config_file;
		} elseif ( \str_contains( $config_file, self::$debug_strings['logging_disabled'] ) ) {
			$config_file = \str_replace( self::$debug_strings['logging_disabled'], self::$debug_strings['logging_enabled'], $config_file );
		} else {
			$config_file = \str_replace( self::$debug_strings['debug_enabled'], self::$debug_strings['debug_enabled'] . "\n" . self::$debug_strings['logging_enabled'], $config_file );
		}

		// Check for displaying. Enable or add.
		if ( \str_contains( $config_file, self::$debug_strings['displaying_enabled'] ) ) {
			$config_file = $config_file;
		} elseif ( \str_contains( $config_file, self::$debug_strings['displaying_disabled'] ) ) {
			$config_file = \str_replace( self::$debug_strings['displaying_disabled'], self::$debug_strings['displaying_enabled'], $config_file );
		} else {
			$config_file = \str_replace( self::$debug_strings['logging_enabled'], self::$debug_strings['logging_enabled'] . "\n" . self::$debug_strings['displaying_enabled'], $config_file );
		}

		// PHP error reporting.
		if ( ! \str_contains( $config_file, self::$debug_strings['error_reporting'] ) ) {
			$config_file = \str_replace(
				'/* Add any custom values between this line and the "stop editing" line. */' . "\n",
				'/* Add any custom values between this line and the "stop editing" line. */' . "\n" . self::$debug_strings['error_reporting'] . "\n",
				$config_file
			);
		}

		$res = $wp_filesystem->put_contents( $config_file_path, $config_file );
	}
}
