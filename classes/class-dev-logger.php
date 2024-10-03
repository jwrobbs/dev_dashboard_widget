<?php
/**
 * Quick and dirty logger.
 * Uses SimpleLogger if possible (Simple History).
 * Else, logs to error log if enabled.
 *
 * @package dev_dashboard_widget
 */

namespace dev_dashboard_widget\classes;

defined( 'ABSPATH' ) || exit;


/**
 * Class Dev_Logger
 */
class Dev_Logger {

	/**
	 * Static method to log a message.
	 *
	 * @param string $message Message to log.
	 * @param string $level   Log level.
	 * @return void
	 */
	public static function log( $message, $level = 'info' ) {
		if ( function_exists( '\SimpleLogger' ) ) {
			\SimpleLogger()->$level( $message );
		} else {
			error_log( $message );
		}
	}
}
