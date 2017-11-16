<?php
/*
Plugin Name: Lndr page builder
Description: This plugin allows you to publish pages from Lndr to Wordpress websites directly.
Author: Incc.io
Version: 1.1
Author URI: http://www.lndr.co
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

global $lndr_version;
$lndr_version = '1.1';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function lndr_activate() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-lndr-activator.php';
  $activator = new Lndr_Activator();
  lndr_rewrite_api_route();
  $activator->activate();
}

/**
 * check for Lndr version and perform updates accordingly
 */
function lndr_update_check() {
  global $lndr_version;
  if (get_site_option('lndr_version') != $lndr_version) {
    // update for specific versions
    if ($lndr_version == '1.1') {
      // we had added a new service path for manual sync, however, there's no need to do anything
      // as it is automatically added
    }
    update_option( "lndr_version", $lndr_version );
  }
}
add_action( 'plugins_loaded', 'lndr_update_check' );

add_filter( 'cron_schedules', 'lndr_custom_cron_schedule' );

function lndr_custom_cron_schedule( $schedules ) {
  $schedules['two_minutes'] = array(
    'interval' => 120,
    'display'  => esc_html__( 'Every 2 minutes' ),
  );

  return $schedules;
}

/**
 * rewrite our API endpoint routes to standardize them
 */
function lndr_rewrite_api_route() {
  // Matching anything service/ to the rest route
  add_rewrite_rule('^service/(.*)?', 'index.php?rest_route=/service/$matches[1]', 'top');
}

add_action('init', 'lndr_rewrite_api_route');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function lndr_deactivate() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-lndr-deactivator.php';
  Lndr_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'lndr_activate' );
register_deactivation_hook( __FILE__, 'lndr_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-lndr.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function lndr_run() {
  $plugin = new Lndr();
  $plugin->run();
}

lndr_run();