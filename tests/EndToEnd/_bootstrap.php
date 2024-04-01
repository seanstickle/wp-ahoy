<?php
/*
 * EndToEnd suite bootstrap file.
 *
 * This file is loaded AFTER the suite modules are initialized and WordPress has been loaded by the WPLoader module.
 *
 * The initial state of the WordPress site is the one set up by the dump file(s) loaded by the WPDb module, look for the
 * "modules.config.WPDb.dump" setting in the suite configuration file. The database will be dropped after each test
 * and re-created from the dump file(s).
 *
 */

// add trigger for server-side page:view event
function ahoy_add_server_side_page_view_event()
{
    add_filter('the_post', 'ahoy_track_page_view');
    function ahoy_track_page_view($post)
    {
        $ahoy = new \Ahoy\Tracker();
        $ahoy->track("page:view", ['postId' => $post->ID]);
        return $post;
    }
}
