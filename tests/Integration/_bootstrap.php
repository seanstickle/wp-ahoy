<?php
/*
 * Integration suite bootstrap file.
 *
 * This file is loaded AFTER the suite modules are initialized, WordPress, plugins and themes are loaded.
 *
 * If you need to load plugins or themes, add them to the Integration suite configuration file, in the
 * "modules.config.WPLoader.plugins" and "modules.config.WPLoader.theme" settings.
 *
 * If you need to load one or more database dump file(s) to set up the test database, add the path to the dump file to
 * the "modules.config.WPLoader.dump" setting.
 *
 */

// trigger server-side page:view event
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
