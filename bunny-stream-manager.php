<?php
/*
Plugin Name: Bunny Stream Manager
Plugin URI: 
Description: Manages and displays Bunny.net video content with grid/list views
Version: 1.1.0
Author: Abiodun Akinbodewa
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BunnyStreamManager {
    private $api_key;
    private $library_id;
    private $plugin_path;
    private $plugin_url;

    public function __construct() {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);
        
        // Initialize plugin
        add_action('init', array($this, 'init'));
    }

    public function init() {
        // Get API credentials
        $this->api_key = getenv('BUNNY_API_KEY');
        $this->library_id = getenv('BUNNY_LIBRARY_ID');

        // Register shortcode
        add_shortcode('bunny_videos', array($this, 'display_videos'));
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets() {
        // Enqueue CSS
        wp_enqueue_style(
            'bunny-videos-style',
            $this->plugin_url . 'assets/css/bunny-videos.css',
            array(),
            '1.0.0'
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'bunny-videos-script',
            $this->plugin_url . 'assets/js/bunny-videos.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }

    public function display_videos($atts) {
        // Default settings
        $settings = shortcode_atts(array(
            'view' => 'grid',
            'columns' => 3
        ), $atts);

        // Get videos from Bunny API
        $videos = $this->get_videos();
        if (empty($videos)) {
            return 'No videos found.';
        }

        // Start output buffer
        ob_start();
        ?>
        <div class="bunny-container">
            <div class="bunny-controls">
                <button class="view-toggle active" data-view="grid">Grid View</button>
                <button class="view-toggle" data-view="list">List View</button>
                
                <select class="sort-videos">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="title">Title A-Z</option>
                </select>
            </div>

            <div class="bunny-videos <?php echo esc_attr($settings['view']); ?>" 
                 data-columns="<?php echo esc_attr($settings['columns']); ?>">
                <?php foreach ($videos as $video): ?>
                    <div class="video-item" data-id="<?php echo esc_attr($video['guid']); ?>">
                        <div class="thumbnail">
                            <img src="<?php echo esc_url($video['thumbnailUrl']); ?>" 
                                 alt="<?php echo esc_attr($video['title']); ?>">
                            <span class="duration">
                                <?php echo $this->format_duration($video['length']); ?>
                            </span>
                        </div>
                        <h3 class="title"><?php echo esc_html($video['title']); ?></h3>
                        <div class="date">
                            <?php echo date('F j, Y', strtotime($video['dateUploaded'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_videos() {
        $url = "https://api.bunny.net/videolibrary/{$this->library_id}/videos";
        $args = array(
            'headers' => array(
                'AccessKey' => $this->api_key
            )
        );

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    private function format_duration($seconds) {
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}

// Initialize the plugin
new BunnyStreamManager();
