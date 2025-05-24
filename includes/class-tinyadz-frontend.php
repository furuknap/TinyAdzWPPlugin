<?php
/**
 * TinyAdz Frontend Class
 *
 * Handles the frontend functionality for the TinyAdz WordPress plugin,
 * including footer script injection and filtering logic.
 *
 * @package TinyAdzWPPlugin
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TinyAdz Frontend Class
 *
 * @since 1.0.0
 */
class TinyAdz_Frontend {

    /**
     * Settings option name
     *
     * @var string
     * @since 1.0.0
     */
    private $option_name = 'tinyadz_settings';

    /**
     * Plugin settings
     *
     * @var array
     * @since 1.0.0
     */
    private $settings;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->settings = get_option($this->option_name, array());
        $this->migrate_legacy_settings();
        $this->init_hooks();
    }

    /**
     * Migrate legacy settings for backward compatibility
     *
     * @since 1.0.0
     */
    private function migrate_legacy_settings() {
        // Check if we have the old script_enabled setting but not the new script_location
        if (isset($this->settings['script_enabled']) && !isset($this->settings['script_location'])) {
            // Migrate the old setting
            if ($this->settings['script_enabled']) {
                $this->settings['script_location'] = 'footer';
            } else {
                $this->settings['script_location'] = 'none';
            }
            
            // Update the database with the migrated setting
            update_option($this->option_name, $this->settings);
        }
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Add script injection hook based on location setting
        $this->init_script_injection_hooks();
        
        // Only add content filter hook if inline ads are enabled
        if ($this->is_inline_ads_enabled()) {
            add_filter('the_content', array($this, 'insert_inline_ads'));
        }
    }

    /**
     * Initialize script injection hooks based on location setting
     *
     * @since 1.0.0
     */
    private function init_script_injection_hooks() {
        $script_location = $this->get_script_location();
        
        // Only add hooks if script injection is enabled and site ID is set
        if ($script_location !== 'none' && !empty($this->settings['site_id'])) {
            if ($script_location === 'header') {
                add_action('wp_head', array($this, 'add_script'));
            } elseif ($script_location === 'footer') {
                add_action('wp_footer', array($this, 'add_script'));
            }
        }
    }

    /**
     * Get the script injection location setting
     *
     * @return string The script location ('none', 'header', or 'footer')
     * @since 1.0.0
     */
    private function get_script_location() {
        return isset($this->settings['script_location']) ? $this->settings['script_location'] : 'footer';
    }

    /**
     * Check if script injection is enabled in settings
     *
     * @return bool
     * @since 1.0.0
     */
    private function is_script_enabled() {
        $script_location = $this->get_script_location();
        return $script_location !== 'none' && !empty($this->settings['site_id']);
    }

    /**
     * Check if inline ads are enabled in settings
     *
     * @return bool
     * @since 1.0.0
     */
    private function is_inline_ads_enabled() {
        return !empty($this->settings['inline_ads_enabled']) && !empty($this->settings['site_id']);
    }

    /**
     * Add TinyAdz script to header or footer
     *
     * @since 1.0.0
     */
    public function add_script() {
        // Check if current page/post should display ads
        if (!$this->should_display_ads()) {
            return;
        }

        $site_id = sanitize_text_field($this->settings['site_id']);
        
        // Output the script tag
        echo sprintf(
            '<script src="https://app.tinyadz.com/scripts/ads.js?siteId=%s" type="module" async></script>',
            esc_attr($site_id)
        );
    }

    /**
     * Determine if ads should be displayed on current page/post
     *
     * @return bool
     * @since 1.0.0
     */
    private function should_display_ads() {
        global $post;

        // If no post object, check if we're on a page that should show ads
        if (!$post) {
            return false;
        }

        $post_type = get_post_type($post);
        
        // Handle posts
        if ($post_type === 'post') {
            return $this->should_display_on_post($post);
        }
        
        // Handle pages
        if ($post_type === 'page') {
            return $this->should_display_on_page($post);
        }

        // For other post types, don't display ads
        return false;
    }

    /**
     * Check if ads should be displayed on a specific post
     *
     * @param WP_Post $post The post object
     * @return bool
     * @since 1.0.0
     */
    private function should_display_on_post($post) {
        // Check if "All Posts" is enabled
        if (!empty($this->settings['filter_all_posts'])) {
            // If all posts are enabled, check additional filters
            return $this->check_post_filters($post);
        }

        // If "All Posts" is not enabled, don't show on posts
        return false;
    }

    /**
     * Check if ads should be displayed on a specific page
     *
     * @param WP_Post $post The page object
     * @return bool
     * @since 1.0.0
     */
    private function should_display_on_page($post) {
        // Check if "All Pages" is enabled
        if (!empty($this->settings['filter_all_pages'])) {
            return true;
        }

        // Check if this specific page is selected
        $specific_pages = isset($this->settings['filter_specific_pages']) ? (array) $this->settings['filter_specific_pages'] : array();
        return in_array($post->ID, $specific_pages);
    }

    /**
     * Check additional post filters (title contains, age filters)
     *
     * @param WP_Post $post The post object
     * @return bool
     * @since 1.0.0
     */
    private function check_post_filters($post) {
        // Check title contains filter
        if (!$this->check_title_contains_filter($post)) {
            return false;
        }

        // Check post age filters
        if (!$this->check_post_age_filters($post)) {
            return false;
        }

        return true;
    }

    /**
     * Check if post title contains specified keywords
     *
     * @param WP_Post $post The post object
     * @return bool
     * @since 1.0.0
     */
    private function check_title_contains_filter($post) {
        $title_contains = isset($this->settings['filter_title_contains']) ? trim($this->settings['filter_title_contains']) : '';
        
        // If no title filter is set, pass the check
        if (empty($title_contains)) {
            return true;
        }

        $post_title = strtolower($post->post_title);
        $keywords = array_map('trim', explode(',', strtolower($title_contains)));
        
        // Check if any of the keywords are found in the title
        foreach ($keywords as $keyword) {
            if (!empty($keyword) && strpos($post_title, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check post age filters
     *
     * @param WP_Post $post The post object
     * @return bool
     * @since 1.0.0
     */
    private function check_post_age_filters($post) {
        $post_date = strtotime($post->post_date);
        $current_time = current_time('timestamp');
        $post_age_days = floor(($current_time - $post_date) / DAY_IN_SECONDS);

        // Check "older than" filter
        $older_than = isset($this->settings['filter_post_age_older']) ? absint($this->settings['filter_post_age_older']) : 0;
        if ($older_than > 0 && $post_age_days < $older_than) {
            return false;
        }

        // Check "younger than" filter
        $younger_than = isset($this->settings['filter_post_age_younger']) ? absint($this->settings['filter_post_age_younger']) : 0;
        if ($younger_than > 0 && $post_age_days > $younger_than) {
            return false;
        }

        return true;
    }

    /**
     * Insert inline ads into content based on position setting
     *
     * @param string $content The post/page content
     * @return string Modified content with inline ads
     * @since 1.0.0
     */
    public function insert_inline_ads($content) {
        // Check if current page/post should display ads
        if (!$this->should_display_ads()) {
            return $content;
        }

        // Only process content on single posts and pages
        if (!is_single() && !is_page()) {
            return $content;
        }

        // Get the ad container HTML
        $ad_container = $this->get_ad_container_html();

        // Get the position setting
        $position = isset($this->settings['inline_ads_position']) ? $this->settings['inline_ads_position'] : 'bottom';

        // Insert ad based on position
        switch ($position) {
            case 'top':
                return $ad_container . $content;

            case 'random':
                return $this->insert_ad_random_position($content, $ad_container);

            case 'bottom':
            default:
                return $content . $ad_container;
        }
    }

    /**
     * Get the HTML for the ad container
     *
     * @return string Ad container HTML
     * @since 1.0.0
     */
    private function get_ad_container_html() {
        return '<!-- Add this div where you want the ad to appear -->' . "\n" .
               '<div id="TA_AD_CONTAINER">' . "\n" .
               '  <!-- It will be replaced with an ad -->' . "\n" .
               '</div>' . "\n";
    }

    /**
     * Insert ad at a random position after a paragraph
     *
     * @param string $content The post/page content
     * @param string $ad_container The ad container HTML
     * @return string Modified content with ad inserted
     * @since 1.0.0
     */
    private function insert_ad_random_position($content, $ad_container) {
        // Find all paragraph closing tags
        $paragraphs = preg_split('/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

        // If we don't have enough paragraphs, fall back to bottom insertion
        if (count($paragraphs) < 3) {
            return $content . $ad_container;
        }

        // Find paragraph pairs (content + closing tag)
        $paragraph_pairs = array();
        for ($i = 0; $i < count($paragraphs) - 1; $i += 2) {
            if (isset($paragraphs[$i + 1])) {
                $paragraph_pairs[] = $paragraphs[$i] . $paragraphs[$i + 1];
            }
        }

        // If we don't have enough paragraph pairs, fall back to bottom insertion
        if (count($paragraph_pairs) < 2) {
            return $content . $ad_container;
        }

        // Choose a random paragraph (not the first or last)
        $random_index = rand(1, count($paragraph_pairs) - 2);

        // Rebuild content with ad inserted after the random paragraph
        $result = '';
        for ($i = 0; $i < count($paragraph_pairs); $i++) {
            $result .= $paragraph_pairs[$i];
            if ($i === $random_index) {
                $result .= $ad_container;
            }
        }

        // Add any remaining content
        if (isset($paragraphs[count($paragraphs) - 1])) {
            $result .= $paragraphs[count($paragraphs) - 1];
        }

        return $result;
    }
}
