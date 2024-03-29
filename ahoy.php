<?php

/**
 *
 * Plugin Name: Ahoy
 * Description: Simple, powerful, first-party analytics for WordPress
 * Version: 0.1
 * Author: Sean Stickle <sean@stickle.net>
 *
 **/


require "lib/ahoy/Event.php";
require "lib/ahoy/Store.php";
require "lib/ahoy/Tracker.php";
require "lib/ahoy/Visit.php";
require "lib/ahoy/VisitProperties.php";

require 'inc/functions.php';

use Ahoy\Event;
use Ahoy\Store;
use Ahoy\Tracker;
use Ahoy\Visit;
use Ahoy\VisitProperties;


register_activation_hook(__FILE__, 'ahoy_activation');
register_uninstall_hook(__FILE__, 'ahoy_uninstall');

// enqueue ahoy javascript

add_action('wp_enqueue_scripts', function () {
    $path = plugin_dir_url(__FILE__) . 'vendor/assets/javascripts/ahoy.js';
    wp_enqueue_script('ahoy', $path, [], null, ['strategy' => 'defer']);
}, 100);

// controllers

class Ahoy_Visit_Controller extends WP_REST_Controller
{
    // public function create(WP_REST_Request $request)
    // {
    //     $ahoy = Ahoy::track_visit();

    //     $content = json_encode([
    //         'visit_token' => $ahoy->visit_token,
    //         'visitor_token' => $ahoy->visitor_token,
    //     ]);

    //     return new WP_REST_Response($content, 201);
    // }
}

class Ahoy_Event_Controller extends WP_REST_Controller
{
    public function create(WP_REST_Request $request)
    {
        global $wpdb;
        $props = json_decode($request->get_param("events_json"))[0];
        $result = $wpdb->insert('wp_ahoy_events', [
            'user_id' => wp_get_current_user()->ID,
            'name' => $props->name,
            'properties' => json_encode($props->properties),
            'time' => date("Y-m-d H:i:s", (int) $props->time),
        ]);
        return $result
            ? new WP_REST_Response(['message' => 'event created'], 201)
            : new WP_REST_Response(null, 500);
    }
}

// router

class Ahoy_Router
{
    public function registerRoutes(): void
    {
        $namespace = 'ahoy/v1';

        register_rest_route($namespace, '/visits', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [new Ahoy_Visit_Controller, 'create'],
            'permission_callback' => [$this, 'verifyNonce'],
        ]);

        register_rest_route($namespace, '/events', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [new Ahoy_Event_Controller, 'create'],
            'permission_callback' => [$this, 'verifyNonce'],
        ]);
    }

    public function verifyNonce(WP_REST_Request $request): bool
    {
        // nonce set in header for initial visit
        $headerToken = $request->get_header('X-CSRF-Token');
        $headerValid = wp_verify_nonce($headerToken, 'wp_rest');

        // nonce set in data for events
        $dataToken = $request->get_param('_wpnonce');
        $dataValid = wp_verify_nonce($dataToken, 'wp_rest');

        return $headerValid || $dataValid;
    }
}

add_action('rest_api_init', function () {
    $router = new Ahoy_Router();
    $router->registerRoutes();
});
