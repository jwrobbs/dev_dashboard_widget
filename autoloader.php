<?php
/**
 * Autoloader
 *
 * @package dev_dashboard_widget
 */

namespace dev_dashboard_widget;

defined( 'ABSPATH' ) || exit;

$autoload_map = array(
	'dev_dashboard_widget\\classes\\Dev_Dashboard_Widget' => __DIR__ . '/classes/class-dev-dashboard-widget.php',
	'dev_dashboard_widget\\classes\\Dev_Dashboard_Widget_Ajax' => __DIR__ . '/classes/class-dev-dashboard-widget-ajax.php',
	'dev_dashboard_widget\\classes\\Dev_Logger'           => __DIR__ . '/classes/class-dev-logger.php',
	'dev_dashboard_widget\\classes\\Debugging_Status'     => __DIR__ . '/classes/class-debugging-status.php',
);

/**
 * Autoload
 *
 * @param string $class_name Class name.
 * @return void
 */
function autoload( $class_name ) {
	if ( isset( $autoload_map[ $class_name ] ) ) {
		require $autoload_map[ $class_name ];
	}
}

spl_autoload_register( __NAMESPACE__ . '\autoload' );
