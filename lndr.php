<?php
/*
Plugin Name: Lndr page builder
Description: This plugin allows you to publish pages from Lndr to Wordpress websites directly.
Author: Incc.io
Version: 1.0
Author URI: http://alpha.makehero.co
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

Copyright 2017 Incc.io
*/

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_lndr() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-lndr-activator.php';
  $activator = new Lndr_Activator();
  rewrite_api_route();
  $activator->activate();
  // lndr_custom_post_type();
}

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
function rewrite_api_route() {
  // Matching anything service/ to the rest route
  add_rewrite_rule('^service/(.*)?', 'index.php?rest_route=/service/$matches[1]', 'top');
}

/**
 * Registering a new post type for Lndr pages
 */
//function lndr_custom_post_type() {
//  register_post_type('lndr_page',
//    [
//      'labels'      => [
//        'name'          => __('Lndr pages'),
//        'singular_name' => __('Lndr page'),
//      ],
//      'public'      => true,
//      'has_archive' => false,
//      'rewrite'     => ['slug' => '/'],
//    ]
//  );
//}

add_action('init', 'rewrite_api_route');
// add_action('init', 'lndr_custom_post_type');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_lndr() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-lndr-deactivator.php';
  Lndr_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'activate_lndr' );
register_deactivation_hook( __FILE__, 'deactivate_lndr' );

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
function run_lndr() {
  $plugin = new Lndr();
  $plugin->run();
}

run_lndr();