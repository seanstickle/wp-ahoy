<?php

/**
 *
 * Plugin Name: Ahoy
 * Description: Simple, powerful, first-party analytics for WordPress
 * Version: 1.0
 * Author: Sean Stickle <sean@stickle.net>, Andrew Kane <andrew@ankane.org>
 *
 * Based on the original Ahoy for Rails: https://github.com/ankane/ahoy
 *
 **/

require_once 'vendor/autoload.php';
require_once 'activate.php';
require_once 'lib/ahoy/RestServer.php';
require_once 'lib/ahoy/Tracker.php';
require_once 'lib/ahoy/Visit.php';
require_once 'lib/ahoy/VisitProperties.php';
require_once 'lib/ahoy/Event.php';

//
// enqueue javascript
//
add_action('wp_enqueue_scripts', function () {
    $path = plugin_dir_url(__FILE__) . 'assets/js/ahoy.js';
    wp_enqueue_script('ahoy', $path, [], null, ['strategy' => 'defer']);
}, 100);

//
// add meta tag to pages
//
add_action('wp_head', function () {
    echo '<meta name="ahoy" content="' . wp_create_nonce('wp_rest') . '">';
});
