<?php
/**
 * Plugin Name: Dev dashboard widget.
 * Description: Adds a dashboard widget with info and tools for devs.
 * Version: 1.0.0
 * Author: Josh Robbs
 * Author URI: https://joshrobbs.com
 * Plugin URI: https://joshrobbs.com
 * License: GPL2
 *
 * @author Josh Robbs <josh@joshrobbs.com>
 * @package dev_dashboard_widget
 */

namespace dev_dashboard_widget;

defined( 'ABSPATH' ) || exit;

use dev_dashboard_widget\classes\Dev_Dashboard_Widget;
use dev_dashboard_widget\classes\Dev_Dashboard_Widget_Ajax;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/autoloader.php';

add_action( 'wp_dashboard_setup', __NAMESPACE__ . '\\dashboard_setup' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_enqueue_scripts', 10 );

/**
 * Dashboard setup
 *
 * @return void
 */
function dashboard_setup() {
	new Dev_Dashboard_Widget();

	$file_url      = plugin_dir_url( __FILE__ ) . 'js/debug-toggle.js';
		$file_path = __DIR__ . '/js/debug-toggle.js';
		$filetime  = filemtime( $file_path );
		wp_enqueue_script(
			'dev_dashboard_widget',
			$file_url,
			array(),
			$filetime,
			true
		);

	$nonce = \wp_create_nonce( 'admin_metabox_nonce' );

	\wp_localize_script(
		'dev_dashboard_widget',
		'ajax_object',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => $nonce,
		)
	);
}

new Dev_Dashboard_Widget_Ajax();

/**
 * Enqueue admin scripts
 *
 * @return void
 */
function admin_enqueue_scripts() {
	// Metabox style.
	$file_url  = plugin_dir_url( __FILE__ ) . 'css/dev-metabox.css';
	$file_path = __DIR__ . '/css/dev-metabox.css';
	$filetime  = filemtime( $file_path );

	wp_enqueue_style(
		'jwr-admin-metabox-style',
		$file_url,
		array(),
		$filetime
	);
}
