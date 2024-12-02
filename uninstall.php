<?php
// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('bunny_stream_manager_settings');

// Delete any cached data
delete_transient('bunny_videos_cache');
