<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class Lndr_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

  /**
   * Adding admin page
   */
  public function admin_page() {
    add_submenu_page(
      'options-general.php',
      'Lndr Configuration',
      'Lndr',
      'manage_options',
      'lndr',
      array(&$this, 'admin_page_html')
    );
  }

  /**
   * Custom setting for Lndr
   */
  public function settings_init() {
    // register a new setting for "lndr" page
    register_setting( 'lndr', 'lndr_settings' );

    add_settings_field(
      'lndr_field_token',
      __( 'API token', 'lndr' ),
      array(&$this, 'lndr_field_api_token'),
      'lndr',
      'default',
      ['label_for' => 'lndr_field_token',]
    );
  }

  /**
   * Admin page html
   */
  public function admin_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }

    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
      // add settings saved message with the class of "updated"
      add_settings_error( 'lndr_messages', 'lndr_message', __( 'Settings Saved', 'lndr' ), 'updated' );
    }

    // show error/update messages
    settings_errors( 'lndr_messages' );

    print '<div class="wrap">';
    print '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
    print '<form action="options.php" method="post">';
     // output security fields for the registered setting "lndr"
    settings_fields('lndr');
    do_settings_fields('lndr', 'default');
    submit_button( 'Save Settings' );
    print '</form>';
    print'</div>';
  }

  /**
   * Render the setting field for API token
   * @param $args
   */
  public function lndr_field_api_token( $args ) {
    // get the value of the setting we've registered with register_setting()
    $setting = get_option('lndr_settings');
    // output the field

    $default = '';
    if (isset($setting)) {
      $default = $setting;
    }

    $output = '<input type="text" name="lndr_settings" value="' . $default . '" required="true" value="" />';
    print $output;
  }
}
