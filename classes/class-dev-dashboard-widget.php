<?php
/**
 * Dev dashboard widget class.
 *
 * @package dev_dashboard_widget
 */

namespace dev_dashboard_widget\classes;

defined( 'ABSPATH' ) || exit;

/**
 * Class Dev_Dashboard_Widget
 */
class Dev_Dashboard_Widget {
	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		if ( \current_user_can( 'manage_options' ) ) {
			\wp_add_dashboard_widget(
				'dev_dashboard_widget', // Widget slug.
				'Dev dashboard widget',   // Widget title.
				array( $this, 'render_widget' ),  // Display function.
			);
		}
	}

	/**
	 * Render the widget.
	 *
	 * @return void
	 */
	public function render_widget() {
		echo '<div id="dev-dashboard-widget-container" class="dev-dashboard-widget-container">';
		$this->render_server_data();
		$this->render_crawlability();
		$this->render_debug_section();
		echo '</div>';
	}

	/**
	 * Render server data.
	 *
	 * @return void
	 */
	private function render_server_data() {
		global $wpdb;
		$php         = phpversion();
		$db_version  = $wpdb->db_version();
		$wp          = get_bloginfo( 'version' );
		$server_info = $wpdb->db_server_info();

		$pattern = '%' . $db_version . '-(\w+)-%';
		$results = \preg_match( $pattern, $server_info, $matches );
		if ( $results ) {
			$database   = $matches[1];
			$db_version = "$database-{$db_version} ";
		}

		$html = <<<HTML
			<div class='dev-dashboard-widget-server-data dev-dashboard-widget-info-section'>
				<h3>Server Data</h3>
				<div class='data-list'>
					<div>PHP</div><div>{$php}</div>
					<div>Database</div><div>{$db_version}</div>
					<div>WordPress</div><div>{$wp}</div>
				</div>
			</div>
		HTML;
		echo $html; // phpcs:ignore
	}

	/**
	 * Render crawlability.
	 *
	 * @return void
	 */
	private function render_crawlability() {
		$crawlability = \get_option( 'blog_public' ) ? '&#x1f7e2; Enabled' : '&#10060; Blocked';
		$html         = <<<HTML
			<div class='dev-dashboard-widget-info-section dev-dashboard-widget-crawlability-section'>
				<h3>Crawlability</h3>
				<div class='data-list'>
					<div>Bot crawling</div><div>{$crawlability}</div>
				</div>
			</div>		
		HTML;
		echo $html; // phpcs:ignore
	}

	/**
	 * Render debug section.
	 *
	 * @return void
	 */
	private function render_debug_section() {
		$debugging = Debugging_Status::get_debugging_status();

		if ( 'enabled' === $debugging ) {

			$logging    = Debugging_Status::get_logging_status();
			$displaying = Debugging_Status::get_displaying_status();

			$logging    = Debugging_Status::add_symbol( $logging );
			$displaying = Debugging_Status::add_symbol( $displaying );
			$debugging  = Debugging_Status::add_symbol( $debugging );

			$debug_data = <<<HTML
			<div class='data-list'>
				<div>Debugging</div><div>{$debugging}</div>
				<div>Logging</div><div>{$logging}</div>
				<div>Displaying</div><div>{$displaying}</div>
			</div>
		HTML;

			$button_text = 'Disable Debugging';
		} else {
			$debugging  = Debugging_Status::add_symbol( $debugging );
			$debug_data = <<<HTML
			<div class='data-list'>
				<div>Debugging</div><div>{$debugging}</div>
			</div>
		HTML;

			$button_text = 'Enable Debugging';
		}

		$button = "<button id='debugging-toggle'>$button_text</button>";

		$full_html = <<<HTML
		<div class='dev-dashboard-widget-info-section dev-dashboard-widget-debugging-section'>
			<h3>Debugging</h3>
			$debug_data
			$button
		</div>
		HTML;
		echo $full_html; // phpcs:ignore
	}
}
