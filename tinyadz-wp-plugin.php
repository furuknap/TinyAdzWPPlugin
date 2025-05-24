<?php
/**
 * Plugin Name: TinyAdz WordPress Plugin
 * Plugin URI: https://github.com/furuknap/TinyAdzWPPlugin
 * Description: Integrates TinyAdz advertising services into WordPress sites with comprehensive ad placement controls, filtering options, and both footer script injection and inline ad management.
 * Version: 1.0.1
 * Author: Bjørn Furuknap
 * Author URI: https://www.linkedin.com/in/furuknap/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tinyadz-wp-plugin
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package TinyAdzWPPlugin
 * @version 1.0.1
 * @author Bjørn Furuknap
 * @copyright Copyright (c) 2025, Bjørn Furuknap
 * @license GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TINYADZ_PLUGIN_VERSION', '1.0.0');
define('TINYADZ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TINYADZ_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TINYADZ_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main TinyAdz Plugin Class
 *
 * @since 1.0.0
 */
class TinyAdzWPPlugin {

    /**
     * Plugin instance
     *
     * @var TinyAdzWPPlugin
     * @since 1.0.0
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return TinyAdzWPPlugin
     * @since 1.0.0
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Initialize plugin
     *
     * @since 1.0.0
     */
    public function init() {
        // Load text domain for internationalization
        load_plugin_textdomain('tinyadz-wp-plugin', false, dirname(TINYADZ_PLUGIN_BASENAME) . '/languages');
        
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize admin functionality
        if (is_admin()) {
            $this->init_admin();
        }
        
        // Initialize frontend functionality
        $this->init_frontend();
    }

    /**
     * Load plugin dependencies
     *
     * @since 1.0.0
     */
    private function load_dependencies() {
        // Load admin class
        require_once TINYADZ_PLUGIN_PATH . 'includes/class-tinyadz-admin.php';
        
        // Load frontend class
        require_once TINYADZ_PLUGIN_PATH . 'includes/class-tinyadz-frontend.php';
    }

    /**
     * Initialize admin functionality
     *
     * @since 1.0.0
     */
    private function init_admin() {
        new TinyAdz_Admin();
    }

    /**
     * Initialize frontend functionality
     *
     * @since 1.0.0
     */
    private function init_frontend() {
        new TinyAdz_Frontend();
    }

    /**
     * Plugin activation hook
     *
     * @since 1.0.0
     */
    public function activate() {
        // Set default options with all settings fields
        $default_options = array(
            'site_id' => '',
            'script_location' => 'footer',  // Default to footer injection
            'inline_ads_enabled' => false,
            'inline_ads_position' => 'bottom',
            'filter_all_posts' => true,  // Default to showing on all posts
            'filter_title_contains' => '',
            'filter_post_age_older' => '',
            'filter_post_age_younger' => '',
            'filter_all_pages' => true,  // Default to showing on all pages
            'filter_specific_pages' => array(),
        );
        
        add_option('tinyadz_settings', $default_options);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook
     *
     * @since 1.0.0
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 */
function tinyadz_wp_plugin_init() {
    return TinyAdzWPPlugin::get_instance();
}

// Initialize the plugin
tinyadz_wp_plugin_init();