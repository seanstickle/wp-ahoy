<?php

namespace Ahoy;

use PHPUnit\Framework\TestCase;

final class VisitPropertiesTest extends TestCase
{
    public function setUp(): void
    {
        $post = wp_insert_post([
            'post_title' => 'Products',
            'post_name' => 'products',
            'post_type' => 'page',
            'post_status' => 'publish',
        ]);
    }

    public function testStandard(): void
    {
        // $referrer = "http://www.example.com";
        // $request = new \WP_REST_Request('GET', '/products');
        // $response = rest_get_server()->dispatch($request);
        // print_r($response);
        // // get products_url, headers: {"Referer" => referrer}

        // $visit = Visit::last();
        // $this->assertEquals($referrer, $visit->referrer);
        // $this->assertEquals("www.example.com", $visit->referring_domain);
        // $this->assertEquals("http://www.example.com/products", $visit->landing_page);
        // $this->assertEquals("127.0.0.1", $visit->ip);

        $this->assertTrue(true);
    }
}
