<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Lndr_Activator {
	/**
	 * Plugin installation
	 * perform installation task when the plugin is activated.
	 *
	 * @since    1.0
	 */
	public function activate() {
    global $lndr_version;
    add_option('lndr_version', $lndr_version);
    flush_rewrite_rules();
    wp_schedule_event(time(), 'two_minutes', 'lndr_cron');
	}
}
