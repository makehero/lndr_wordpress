<?php

const LNDR_BASE = 'http://alpha.makehero.co/';

/**
 * Fetch a Lndr page and render it as a post template
 */
function import_page() {
  global $post;
  // Get current post id
  $lndr_project_id = get_post_meta($post->ID, 'lndr_project_id', true);
  $url = LNDR_BASE . 'projects/' . $lndr_project_id;

  $response = wp_remote_get($url);
  if ($response['response']['code'] == '200') {
    require_once plugin_dir_path( __FILE__ ) . 'simple_html_dom.inc';
    $body = wp_remote_retrieve_body($response);
    $html = str_get_html($body);
    // Because we are issuing redirect
    $http_response = $response['http_response']->get_response_object();
    $uri = $http_response->url;
    $html = parse_page($html, $uri);
    print $html;
  } else {
    // Render some type of Wordpress 404
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
  }
}

/**
 * Parse an input Lndr HTML page
 * @param $html
 * @return mixed
 */
function parse_page($html, $url) {
  // prepend the url of the page to all of the images
  foreach($html->find('img') as $key => $element) {
    $src= $element->src;
    $html->find('img', $key)->src = $url . $src;
  }

  // prepend url to stylesheet, assuming we only have one stylesheet so far
  $html->find('link[rel="stylesheet"]', 0)->href = $url . $html->find('link[rel="stylesheet"]', 0)->href;

  // prepend javascripts
  foreach($html->find('script') as $key => $element) {
    $src = $element->src;
    if (isset($src)) {
      $html->find('script', $key)->src = $url . $src;
    }
  }

  $elements = array(
    'div',
    'a',
    'section',
  );

  foreach ($elements as $element) {
    foreach ($html->find($element . '[data-background-image]') as $key => $_element) {
      $bg_image = $_element->{'data-background-image'};
      $html->find($element . '[data-background-image]', $key)->{'data-background-image'} = $url . $bg_image;
    }
  }

  return $html;
}

add_action( 'wp', 'import_page' );
import_page();