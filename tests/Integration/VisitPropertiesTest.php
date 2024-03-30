<?php

namespace Tests;

use WP_Query;

class VisitPropertiesTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_track_page_view(): void
    {
        $post = static::factory()->post->create_and_get();

        $the_query = new WP_Query(['post_id' => $post->ID]);

        while ($the_query->have_posts()) {
            $the_query->the_post();
        }

        $last = \Ahoy\Event::last();
        $this->assertEquals('page:view', $last->name);
        $this->assertEquals($post->ID, $last->properties->postId);
    }
}
