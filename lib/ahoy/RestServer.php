<?php

add_action('rest_api_init', function () {
    $router = new Ahoy_Router();
    $router->registerRoutes();
});

// REST router

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
        $token = $_SERVER['HTTP_X_WP_NONCE'] ?? null;
        $token ??= $request->get_param('_wpnonce');
        return wp_verify_nonce($token, 'wp_rest');
    }
}

// REST controllers

class Ahoy_Visit_Controller extends WP_REST_Controller
{
    public function create(WP_REST_Request $request)
    {
        $ahoy = new \Ahoy\Tracker(["request" => $request->get_params(), "api" => true]);
        $visit = $ahoy->trackVisit();
        return $visit
            ? new WP_REST_Response(null, 200)
            : new WP_REST_Response(null, 500);
    }
}

class Ahoy_Event_Controller extends WP_REST_Controller
{
    public function create(WP_REST_Request $request)
    {
        $ahoy = new \Ahoy\Tracker(["request" => $request->get_params(), "api" => true]);
        $params = $request->get_params();
        $eventsJson = $params['events_json'];
        $props = json_decode($eventsJson, true)[0];
        $event = $ahoy->track($props['name'], $props['properties'], ['time' => $props['time']]);
        return $event
            ? new WP_REST_Response(null, 200)
            : new WP_REST_Response(null, 500);
    }
}
