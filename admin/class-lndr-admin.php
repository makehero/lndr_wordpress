<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Lndr
 * @subpackage Lndr/admin
 */
const LNDR_API_VALIDATE_TOKEN = 'http://alpha.makehero.co/v1/validate_token';



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
    register_setting( 'lndr', 'lndr_settings', (array(&$this, 'lndr_settings_validate')));

    add_settings_field(
      'lndr_field_token',
      __( 'API token', 'lndr' ),
      array(&$this, 'lndr_field_api_token'),
      'lndr',
      'default',
      ['label_for' => 'lndr_field_token',]
    );

    add_settings_field(
      'lndr_field_dev',
      __('Development mode', 'lndr'),
      array(&$this, 'lndr_field_dev_mode'),
      'lndr',
      'default',
      ['label_for' => 'lndr_field_dev',]
    );
  }

  /**
   * Validate API token entered by the user
   * @param $input
   * @return mixed
   */
  public function lndr_settings_validate($input) {
    $options = [
      'method' => 'POST',
      'body' => ['token' => $input['api_token']]
    ];
    $response = wp_remote_get(LNDR_API_VALIDATE_TOKEN, $options);
    if (wp_remote_retrieve_response_code($response) != '200') {
      $message = __('You have entered an invalid API token, please copy and paste the API token from your profile in Lndr', 'lndr');
      add_settings_error(
        'API token',
        esc_attr('settings_updated'),
        $message,
        'error'
      );
    }
    return $input;
  }

  /**
   * Admin page html
   */
  public function admin_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
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
  public function lndr_field_api_token($args) {
    // get the value of the setting we've registered with register_setting()
    $settings = get_option('lndr_settings');
    // output the field

    $default = '';
    if (!empty($settings)) {
      $default = $settings['api_token'];
    }
    $output = '<div><input type="text" name="lndr_settings[api_token]" value="' . $default . '" required="true" size="50" /></div>';
    print $output;
  }

  /**
   * Render the settings field for developer mode
   * @param $args
   */
  public function lndr_field_dev_mode($args) {
    $settings = get_option('lndr_settings');

    $default = null;
    if (!empty($settings) && array_key_exists('dev_mode', $settings)) {
      $default = $settings['dev_mode'];
    }

    $output = '<div><input type="checkbox" name="lndr_settings[dev_mode]" value="1" ' . checked(1, $default, false) . '/></div>';
    print $output;
  }

}
