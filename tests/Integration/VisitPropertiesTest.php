<?php

namespace Tests;

use WP_Query;

class VisitPropertiesTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    public function test_track_page_view(): void
    {
        // add trigger for server-side page:view event
        ahoy_add_server_side_page_view_event();

        // create a fresh page
        $post = static::factory()->post->create_and_get();

        // visit the page to trigger the page:view event
        $the_query = new WP_Query(['post_id' => $post->ID]);
        while ($the_query->have_posts()) {
            $the_query->the_post();
        }

        $lastEvent = \Ahoy\Event::last();
        $this->assertEquals('page:view', $lastEvent->name);
        $this->assertEquals($post->ID, $lastEvent->properties->postId);
    }
}
