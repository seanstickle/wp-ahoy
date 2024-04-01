<?php

/**
 *
 * Plugin Name: Ahoy
 * Description: Simple, powerful, first-party analytics for WordPress
 * Version: 0.1
 * Author: Sean Stickle <sean@stickle.net>
 *
 **/

require 'vendor/autoload.php';
require 'activate.php';
require 'lib/ahoy/RestServer.php';
require 'lib/ahoy/Ahoy.php';
require 'lib/ahoy/Tracker.php';
require 'lib/ahoy/Visit.php';
require 'lib/ahoy/VisitProperties.php';
require 'lib/ahoy/Event.php';

//
// enqueue javascript
//
add_action('wp_enqueue_scripts', function () {
    $path = plugin_dir_url(__FILE__) . 'vendor/assets/javascripts/ahoy.js';
    wp_enqueue_script('ahoy', $path, [], null, ['strategy' => 'defer']);
}, 100);

//
// add meta tag to pages
//
add_action('wp_head', function () {
    echo '<meta name="ahoy" content="' . wp_create_nonce('wp_rest') . '">';
});
