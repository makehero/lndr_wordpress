<?php

const LNDR_BASE = 'https://www.lndr.co/';

/**
 * Fetch a Lndr page and render it as a post template
 */
function lndr_import_page() {
  global $post;
  // Get current post id
  $lndr_project_id = get_post_meta($post->ID, 'lndr_project_id', true);

  // If we simply reserved the page, let's manually trigger a sync to process it
  if ($lndr_project_id == 'reserved') {
    // When user visits a page that has a lndr_project_id of reserved,
    // we send them to a manual processing page, after processing, if the page is published
    // they will be redirected back and page will be shown
    $base_url = home_url();
    wp_safe_redirect($base_url . '/service/lndr/lndr_sync?post_id=' . $post->ID);
  }
  else {
    $url = LNDR_BASE . 'projects/' . $lndr_project_id;

    $response = wp_remote_get($url);
    if ($response['response']['code'] == '200') {
      require_once plugin_dir_path( __FILE__ ) . 'simple_html_dom.inc';
      $body = wp_remote_retrieve_body($response);
      $html = str_get_html($body);
      // Because we are issuing redirect
      $http_response = $response['http_response']->get_response_object();
      $uri = $http_response->url;
      $html = lndr_parse_page($html, $uri);
      print $html;
    } else {
      // Render some type of Wordpress 404
      global $wp_query;
      $wp_query->set_404();
      status_header(404);
    }
  }
}

/**
 * Parse an input Lndr HTML page
 * @param $html
 * @return mixed
 */
function lndr_parse_page($html, $url) {
  // prepend the url of the page to all of the images
  foreach($html->find('img') as $key => $element) {
    $src= $element->src;
    $html->find('img', $key)->src = $url . $src;
  }

  // prepend url to internal stylesheets
  foreach($html->find('link[rel="stylesheet"]') as $key => $element) {
    if (substr($element->href, 0, 4) !== 'http') {
      $html->find('link[rel="stylesheet"]', $key)->href = $url . $element->href;
    }
  }

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

add_action( 'wp', 'lndr_import_page' );
lndr_import_page();