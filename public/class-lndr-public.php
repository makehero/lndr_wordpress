<?php
const LNDR_API_GET_PROJECT = 'http://alpha.makehero.co/v1/projects';


/**
 * If running nginx, implement getallheaders ourself.
 *
 * Code is taken from http://php.net/manual/en/function.getallheaders.php
 */
if (!function_exists('getallheaders')) {
  function getallheaders() {
    foreach ($_SERVER as $name => $value) {
      if (substr($name, 0, 5) == 'HTTP_') {
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
      }
    }
    return $headers;
  }
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Lndr
 * @subpackage Lndr/public
 */
class Lndr_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

  /**
   * rewrite our API endpoint routes to standardize them
   */
  public function rewrite_api_route() {
    // Matching anything service/ to the rest route
    add_rewrite_rule('^service/(.*)?', 'index.php?rest_route=service/$matches[1]', 'top');
  }

  /**
   * Facilitating cron run
   */
  public function lndr_cron_run() {
    if (!wp_next_scheduled('lndr_cron')) {
      wp_schedule_event(time(), 'hourly', 'lndr_cron');
    }
  }

  /**
   * Register our service path
   */
  public function service_routes() {
    register_rest_route('service/lndr', '/validate_path', array(
      'methods' => 'GET',
      'callback' => array(&$this, 'validate_path_by_post'),
    ));

    register_rest_route('service/lndr', '/reserve_path', array(
      'methods' => 'GET',
      'callback' => array(&$this, 'create_post'),
    ));
  }

  /**
   * Add CORS headers to allow access to endpoints
   */
  public function add_cors_header() {
    // Send our own CORS headers
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    // @todo: to make this more secure, only apply for certain endpoints?
    add_filter( 'rest_pre_serve_request', function( $value ) {
      header( 'Access-Control-Allow-Origin: *');
      header( 'Access-Control-Allow-Methods: POST, GET');
      header( 'Access-Control-Allow-Credentials: true');
      header('Access-Control-Allow-Headers: Authorization');
      header('Content-Type: application/json; charset=utf-8');
      return $value;
    });
  }

  /**
   * validate whether the path requested is available
   */
  public function validate_path_by_post(WP_REST_Request $request) {
    $response = $this->service_authorization($request);
    // If token doesn't check out or others, we exit
    if ($response['response']['type'] === 'error') {
      return json_encode($response);
    }

    // path is stored in response message if everything is fine
    $path = $response['response']['message'];

    // 1. Let's check to see if the path is available in the system (Assuming path validation is done on Lndr side)
    $path = rtrim($path, '/');

    $lndr_public = new Lndr_Public('lndr', '1.0');
    $existing_post = $lndr_public->get_post_by_path($path);

    if (!isset($existing_post)) {
      $response = array(
        'response' => array(
          'type' => 'path_valid',
          'message' => 'The chosen path is available',
          'code' => '200',
        ),
      );
      return json_encode($response);
    }
    else {
      $response = array(
        'response' => array(
          'type' => 'path_taken',
          'message' => 'The requested path is not available for Lndr',
          'code' => '403',
        ),
      );
      return json_encode($response);
    }
  }

  /**
   * Reserve path API call, in wordpress we are actually
   * creating an unpublished lndr_page post
   */
  public function create_post(WP_REST_Request $request) {
    $response = $this->service_authorization($request);
    // If token doesn't check out or others, we exit
    if ($response['response']['type'] === 'error') {
      return json_encode($response);
    }
    // path is stored in response message if everything is fine
    $path = $response['response']['message'];
    $path = rtrim($path, '/');

    // Because WP is special and our path slug can only be /[slug] and
    // not anything we want, we need to check (Assuming path passed here does not have forwarding slash
    if (count(explode('/', $path)) > 1) {
      $response = array(
        'type' => 'error',
        'message' => 'The path you entered is invalid, you can only enter a base path such as http://example.com/page, not http://example.com/page/subpage',
        'code' => '500',
      );
      return json_encode($response);
    }

    // Create a new unpublished lndr_page
    $new_post = [
      'post_title' => $path,
      'post_type' => 'lndr_page',
      'post_content' => '',
      'post_status' => 'draft',
      'post_name' => $path,
    ];
    $post_saved = wp_insert_post($new_post, true);
    if (is_wp_error($post_saved)) {
      $response = array(
        'type' => 'error',
        'message' => $post_saved->get_error_message(),
        'code' => '500',
      );
      return json_encode($response);
    }

    $response = array(
      'response' => array(
        'type' => 'path_valid',
        'message' => 'The path has been successfully reserved',
        'code' => '200',
      ),
    );
    return json_encode($response);
  }

  /**
   * @param WP_REST_Request $request
   * perform various checks on the web service.
   * @return array
   */
  public function service_authorization(WP_REST_Request $request) {
    // Check if the request has the appropriate API token in the header
    $headers = getallheaders();

    if (!array_key_exists('Authorization', $headers)) {
      // no token exist
      $response = array(
        'response' => array(
          'type' => 'error',
          'message' => 'No token in the request header',
          'code' => '403',
        ),
      );
      return $response;
    }
    $settings = get_option('lndr_settings');
    if(!array_key_exists('api_token', $settings)) {
      // no token configured in the system
      $response = array(
        'response' => array(
          'type' => 'error',
          'message' => 'No API token configured in Wordpress',
          'code' => '403',
        ),
      );
      return $response;
    }

    $authorization = str_replace('Token token=', '', $headers['Authorization']);
    if ($settings['api_token'] != $authorization) {
      // invalid token given
      $response = array(
        'response' => array(
          'type' => 'error',
          'message' => 'Invalid token given',
          'code' => '403',
        ),
      );
      return $response;
    }

    // Get the path parameter
    $path = $request->get_param('path');
    if (!isset($path)) {
      $response = array(
        'response' => array(
          'type' => 'error',
          'message' => 'Required parameter path not given',
          'code' => '403',
        ),
      );
      return $response;
    }

    // if everything checks out, we just pass the query back
    $response = array(
      'response' => array(
        'type' => 'status',
        'message' => $path,
        'code' => '200',
      ),
    );
    return $response;
  }

  /**
   * Overriding the post template with our own in the plugin
   * @param $single
   * @return string
   */
  public function lndr_page_template($single) {
    global $post;
    /* Checks for single template by post type */
    if ($post->post_type == "lndr_page"){
      if(file_exists(plugin_dir_path( __FILE__ ) . 'lndr-page-template.php')) {
        return plugin_dir_path( __FILE__ ) . 'lndr-page-template.php';
      }
    }
    return $single;
  }

  /**
   * Syncing posts from Lndr
   */
  public function sync_posts() {
    $settings = get_option('lndr_settings');

    // If API token is not set
    if (!array_key_exists('api_token', $settings)) {
      return;
    }
    if ($settings['api_token'] == '') {
      return;
    }

    // If developer mode is set, let's load the file
    if (array_key_exists('dev_mode', $settings)) {
      $response_body = file_get_contents(plugin_dir_path( __FILE__ ) . 'lndr_test.json');
    } else {
      // Let's reach out to Lndr to get list of contents
      $options = array(
        'headers' => array(
          'Authorization' => 'Token token=' . $settings['api_token'],
        ),
      );
      $response = wp_remote_get(LNDR_API_GET_PROJECT, $options);
      if (wp_remote_retrieve_response_code($response) != '200') {
        // @todo: log some type of error?
        return;
      }
      $response_body = wp_remote_retrieve_body($response);
    }
    $data = json_decode($response_body, true);
    if (!$data || empty($data)) {
      return;
    }
    $data = $data['projects'];

    // our syncing CRUD functions
    $this->upsert_posts($data);
    $this->remove_posts($data);
  }

  /**
   * Insert or update Lndr pages in WP
   * @param $projects
   */
  public function upsert_posts($projects) {
    // Base URL without trailing slash
    $base_url = home_url();

    $wp_pages = array();
    foreach ($projects as $project) {
      if (strstr($project['publish_url'], $base_url)) {
        $wp_pages[] = $project;
      }
    }
    // Nothing to process
    if (empty($wp_pages)) {
      return;
    }
    // Going through all the pages that are published to this URL
    foreach ($wp_pages as $page) {
      $path = substr($page['publish_url'], strlen($base_url));
      $path = ltrim($path, '/');

      // Load all of the posts which has this url path.
      $existing_post_by_alias = $this->get_post_by_path($path);
      if (isset($existing_post_by_alias)) {
        // case 1. this post was created (reserved) for this page, however it unpublished, let's just udpate it
        if ($existing_post_by_alias->post_status != 'publish') {
          $update_post = [
            'ID' => $existing_post_by_alias->ID,
            'post_status' => 'publish',
          ];
          // Update the post into the database
          // @todo: catch error?
          wp_update_post( $update_post );
          // create a new postmeta
          add_post_meta($existing_post_by_alias->ID, 'lndr_project_id', $page['id'], true);
        }
      }
      else
      {
        // case 3. post was previously created, but the path is updated from Lndr
        $existing_post_by_project_id = $this->get_posts_by_project_id($page['id']);
        if (isset($existing_post_by_project_id)) {
          // Making sure it is still on the same domain
          if (substr($page['publish_url'], 0, strlen($base_url)) == $base_url) {
            // $lndr_path = ltrim(substr($page['publish_url'], strlen($base_url)), '/');
            if ($path != $existing_post_by_project_id->post_name) {
              $update_post = [
                'ID' => $existing_post_by_project_id->ID,
                'post_name' => $path,
              ];
              // @todo: catch error?
              wp_update_post( $update_post );
            }
          }
        }
        else
        {
          // case 2. No post was previously created, this Lndr page was changed from non-WP URL to a WP URL
          $new_post = [
            'post_title' => $page['title'],
            'post_type' => 'lndr_page',
            'post_content' => '',
            'post_status' => 'publish',
            'post_name' => $path,
            'meta_input' => [
              'lndr_project_id' => $page['id'],
            ],
          ];
          wp_insert_post($new_post);
        }
      }
    }
  }

  /**
   * Remove posts when Lndr page is deleted
   * @param $projects
   */
  public function remove_posts($projects) {
    $base_url = home_url();

    // Re-format the projects a bit to give them keys as project id
    $_projects = array();
    foreach ($projects as $project) {
      $_projects[$project['id']] = $project;
    }

    // Get all of the posts that has a post meta for lndr project id
    $existing_posts = $this->get_posts_by_project_id();
    if (empty($existing_posts)) {
      return;
    }

    foreach ($existing_posts as $post_id => $project_id) {
      // Case 5. Remove any local path not present in the web service
      if (!array_key_exists($project_id, $_projects)) {
        // delete the post permenantly
        // @todo: future there can be an archived stage = trash or archive
        wp_delete_post($post_id, true);
      }
      else
      {
        // Case 4. There is a local post, however remotely it has been changed to another domain URL
       if (substr($_projects[$project_id]['publish_url'], 0, strlen($base_url)) != $base_url) {
         // delete the post permenantly
         // @todo: future there can be an archived stage = trash or archive
         wp_delete_post($post_id, true);
       }
      }
    }
  }

  /**
   * Fetch a post based on Lndr Project ID
   * @param $project_id (Lndr project ID)
   * @return null
   */
  public function get_posts_by_project_id($project_id = null) {
    $data = null;
    // load all lndr posts
    $args = [
      'post_type' => 'lndr_page',
      'post_status' => 'any',
      'meta_key' => 'lndr_project_id',
    ];

    if (isset($project_id)) {
      $args['meta_value'] = $project_id;
    }
    $posts = get_posts($args);

    // For single post query
    if (isset($project_id)) {
      if (!empty($posts)) {
        return $posts[0];
      }
    }
    else
    {
      // For multiple post, we attach the post meta
      // @todo: this is not most efficient right now
      foreach ($posts as $post) {
        $lndr_project_id = get_post_meta($post->ID, 'lndr_project_id', true);
        $data[$post->ID] = $lndr_project_id;
      }
      return $data;
    }
  }

  /**
   * Register our query parameter for WP to use
   * @param $vars
   * @return array
   */
  function add_query_vars_filter($vars){
    $vars[] = "path";
    return $vars;
  }

  /**
   * Finds a custom lndr_page post with the matching path in its permalink
   * @param $path
   * @return null
   */
  public function get_post_by_path($path) {
    $data = null;
    // load all lndr posts
    $args = [
      'post_type' => 'lndr_page',
      'post_status' => 'any',
    ];
    $posts = get_posts($args);
    if (empty($posts)) {
      return $data;
    }

    foreach ($posts as $post) {
      // post name is the slug (permalink path part)
      if ($post->post_name == $path) {
        $data = $post;
      }
    }
    return $data;
  }

}
