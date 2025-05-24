<?php
/**
 * TinyAdz Admin Settings Class
 *
 * Handles the admin settings page functionality for the TinyAdz WordPress plugin.
 *
 * @package TinyAdzWPPlugin
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TinyAdz Admin Settings Class
 *
 * @since 1.0.0
 */
class TinyAdz_Admin {

    /**
     * Settings page slug
     *
     * @var string
     * @since 1.0.0
     */
    private $page_slug = 'tinyadz-settings';

    /**
     * Settings option name
     *
     * @var string
     * @since 1.0.0
     */
    private $option_name = 'tinyadz_settings';

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * Add settings page to WordPress admin menu
     *
     * @since 1.0.0
     */
    public function add_settings_page() {
        add_options_page(
            __('TinyAdz Settings', 'tinyadz-wp-plugin'),
            __('TinyAdz', 'tinyadz-wp-plugin'),
            'manage_options',
            $this->page_slug,
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings using WordPress Settings API
     *
     * @since 1.0.0
     */
    public function register_settings() {
        // Register the main settings group
        register_setting(
            'tinyadz_settings_group',
            $this->option_name,
            array($this, 'sanitize_settings')
        );

        // Add main settings section
        add_settings_section(
            'tinyadz_main_section',
            __('General Settings', 'tinyadz-wp-plugin'),
            array($this, 'render_main_section'),
            $this->page_slug
        );

        // Site ID field
        add_settings_field(
            'site_id',
            __('Site ID', 'tinyadz-wp-plugin'),
            array($this, 'render_site_id_field'),
            $this->page_slug,
            'tinyadz_main_section'
        );

        // Script location field
        add_settings_field(
            'script_location',
            __('Script Injection Location', 'tinyadz-wp-plugin'),
            array($this, 'render_script_location_field'),
            $this->page_slug,
            'tinyadz_main_section'
        );

        // Inline ads enabled field
        add_settings_field(
            'inline_ads_enabled',
            __('Enable Inline Ads', 'tinyadz-wp-plugin'),
            array($this, 'render_inline_ads_enabled_field'),
            $this->page_slug,
            'tinyadz_main_section'
        );

        // Inline ads position field
        add_settings_field(
            'inline_ads_position',
            __('Inline Ad Position', 'tinyadz-wp-plugin'),
            array($this, 'render_inline_ads_position_field'),
            $this->page_slug,
            'tinyadz_main_section'
        );

        // Add filtering section
        add_settings_section(
            'tinyadz_filtering_section',
            __('Filtering Options', 'tinyadz-wp-plugin'),
            array($this, 'render_filtering_section'),
            $this->page_slug
        );

        // Posts filtering fields
        add_settings_field(
            'filter_all_posts',
            __('Show on All Posts', 'tinyadz-wp-plugin'),
            array($this, 'render_filter_all_posts_field'),
            $this->page_slug,
            'tinyadz_filtering_section'
        );

        add_settings_field(
            'filter_title_contains',
            __('Post Title Contains', 'tinyadz-wp-plugin'),
            array($this, 'render_filter_title_contains_field'),
            $this->page_slug,
            'tinyadz_filtering_section'
        );

        add_settings_field(
            'filter_post_age_older',
            __('Posts Older Than (days)', 'tinyadz-wp-plugin'),
            array($this, 'render_filter_post_age_older_field'),
            $this->page_slug,
            'tinyadz_filtering_section'
        );

        add_settings_field(
            'filter_post_age_younger',
            __('Posts Younger Than (days)', 'tinyadz-wp-plugin'),
            array($this, 'render_filter_post_age_younger_field'),
            $this->page_slug,
            'tinyadz_filtering_section'
        );

        // Pages filtering fields
        add_settings_field(
            'filter_all_pages',
            __('Show on All Pages', 'tinyadz-wp-plugin'),
            array($this, 'render_filter_all_pages_field'),
            $this->page_slug,
            'tinyadz_filtering_section'
        );

        add_settings_field(
            'filter_specific_pages',
            __('Specific Pages', 'tinyadz-wp-plugin'),
            array($this, 'render_filter_specific_pages_field'),
            $this->page_slug,
            'tinyadz_filtering_section'
        );
    }

    /**
     * Render the main settings page
     *
     * @since 1.0.0
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add error/update messages
        settings_errors('tinyadz_messages');
        ?>
        <div class="wrap tinyadz-settings-page">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="tinyadz-help-text">
                <h4><?php esc_html_e('How to use TinyAdz', 'tinyadz-wp-plugin'); ?></h4>
                <p><?php esc_html_e('Configure your TinyAdz integration by following these steps:', 'tinyadz-wp-plugin'); ?></p>
                <ul>
                    <li><?php esc_html_e('Enter your TinyAdz Site ID (required)', 'tinyadz-wp-plugin'); ?></li>
                    <li><?php esc_html_e('Choose script injection location (Header, Footer, or None) and/or enable inline ads', 'tinyadz-wp-plugin'); ?></li>
                    <li><?php esc_html_e('Configure filtering options to control where ads appear', 'tinyadz-wp-plugin'); ?></li>
                    <li><?php esc_html_e('Save settings and test on your site', 'tinyadz-wp-plugin'); ?></li>
                </ul>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('tinyadz_settings_group');
                do_settings_sections($this->page_slug);
                submit_button(__('Save Settings', 'tinyadz-wp-plugin'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Enqueue admin styles and scripts
     *
     * @param string $hook_suffix The current admin page
     * @since 1.0.0
     */
    public function enqueue_admin_styles($hook_suffix) {
        // Only load on our settings page
        if ('settings_page_' . $this->page_slug !== $hook_suffix) {
            return;
        }

        wp_enqueue_style(
            'tinyadz-admin-styles',
            TINYADZ_PLUGIN_URL . 'admin/css/admin-styles.css',
            array(),
            TINYADZ_PLUGIN_VERSION
        );

        // Enqueue admin JavaScript for exclusivity logic
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $this->get_admin_javascript());
    }

    /**
     * Get admin JavaScript for exclusivity logic
     *
     * @return string JavaScript code
     * @since 1.0.0
     */
    private function get_admin_javascript() {
        return "
        jQuery(document).ready(function($) {
            // Handle Pages exclusivity
            function handlePagesExclusivity() {
                var allPagesCheckbox = $('#tinyadz_filter_all_pages');
                var specificPagesSelect = $('#tinyadz_filter_specific_pages');
                var specificPagesRow = specificPagesSelect.closest('tr');
                
                if (allPagesCheckbox.is(':checked')) {
                    specificPagesRow.hide();
                    specificPagesSelect.prop('disabled', true);
                } else {
                    specificPagesRow.show();
                    specificPagesSelect.prop('disabled', false);
                }
            }
            
            // Handle Posts exclusivity
            function handlePostsExclusivity() {
                var allPostsCheckbox = $('#tinyadz_filter_all_posts');
                var titleContainsField = $('#tinyadz_filter_title_contains');
                var postAgeOlderField = $('#tinyadz_filter_post_age_older');
                var postAgeYoungerField = $('#tinyadz_filter_post_age_younger');
                
                var subConfigRows = [
                    titleContainsField.closest('tr'),
                    postAgeOlderField.closest('tr'),
                    postAgeYoungerField.closest('tr')
                ];
                
                if (allPostsCheckbox.is(':checked')) {
                    // Hide and disable sub-configuration options
                    subConfigRows.forEach(function(row) {
                        row.hide();
                    });
                    titleContainsField.prop('disabled', true);
                    postAgeOlderField.prop('disabled', true);
                    postAgeYoungerField.prop('disabled', true);
                } else {
                    // Show and enable sub-configuration options
                    subConfigRows.forEach(function(row) {
                        row.show();
                    });
                    titleContainsField.prop('disabled', false);
                    postAgeOlderField.prop('disabled', false);
                    postAgeYoungerField.prop('disabled', false);
                }
            }
            
            // Handle Inline Ads Position visibility
            function handleInlineAdsPositionVisibility() {
                var inlineAdsEnabledCheckbox = $('#tinyadz_inline_ads_enabled');
                var inlineAdsPositionField = $('#tinyadz_inline_ads_position');
                var inlineAdsPositionRow = inlineAdsPositionField.closest('tr');
                
                if (inlineAdsEnabledCheckbox.is(':checked')) {
                    inlineAdsPositionRow.show();
                    inlineAdsPositionField.prop('disabled', false);
                } else {
                    inlineAdsPositionRow.hide();
                    inlineAdsPositionField.prop('disabled', true);
                }
            }
            
            // Initialize on page load
            handlePagesExclusivity();
            handlePostsExclusivity();
            handleInlineAdsPositionVisibility();
            
            // Bind event handlers
            $('#tinyadz_filter_all_pages').on('change', handlePagesExclusivity);
            $('#tinyadz_filter_all_posts').on('change', handlePostsExclusivity);
            $('#tinyadz_inline_ads_enabled').on('change', handleInlineAdsPositionVisibility);
        });
        ";
    }

    /**
     * Render main section description
     *
     * @since 1.0.0
     */
    public function render_main_section() {
        echo '<p>' . esc_html__('Configure your TinyAdz integration settings below.', 'tinyadz-wp-plugin') . '</p>';
    }

    /**
     * Render filtering section description
     *
     * @since 1.0.0
     */
    public function render_filtering_section() {
        echo '<p>' . esc_html__('Control where TinyAdz ads are displayed on your site. Posts/pages must match ALL enabled criteria to display ads.', 'tinyadz-wp-plugin') . '</p>';
    }

    /**
     * Render Site ID field
     *
     * @since 1.0.0
     */
    public function render_site_id_field() {
        $options = get_option($this->option_name, array());
        $site_id = isset($options['site_id']) ? $options['site_id'] : '';
        ?>
        <input type="text"
               id="tinyadz_site_id"
               name="<?php echo esc_attr($this->option_name); ?>[site_id]"
               value="<?php echo esc_attr($site_id); ?>"
               class="regular-text"
               placeholder="<?php esc_attr_e('e.g., 6831f5d777c736ac56e07ea1', 'tinyadz-wp-plugin'); ?>" />
        <p class="description">
            <?php esc_html_e('Enter your TinyAdz Site ID. This is required for the ads to display properly.', 'tinyadz-wp-plugin'); ?>
            <br>
            <?php
            printf(
                esc_html__('To get your Site ID: Log in to %s and find your Site ID in your account dashboard.', 'tinyadz-wp-plugin'),
                '<a href="https://app.tinyadz.com" target="_blank" rel="noopener noreferrer">app.tinyadz.com</a>'
            );
            ?>
            <br>
            <?php
            printf(
                esc_html__('For more information about TinyAdz, visit %s', 'tinyadz-wp-plugin'),
                '<a href="https://tinyadz.com" target="_blank" rel="noopener noreferrer">tinyadz.com</a>'
            );
            ?>
        </p>
        <?php
    }

    /**
     * Render Script Location field
     *
     * @since 1.0.0
     */
    public function render_script_location_field() {
        $options = get_option($this->option_name, array());
        $script_location = isset($options['script_location']) ? $options['script_location'] : 'footer';
        ?>
        <select id="tinyadz_script_location"
                name="<?php echo esc_attr($this->option_name); ?>[script_location]">
            <option value="none" <?php selected($script_location, 'none'); ?>>
                <?php esc_html_e('None (Disabled/Manual)', 'tinyadz-wp-plugin'); ?>
            </option>
            <option value="header" <?php selected($script_location, 'header'); ?>>
                <?php esc_html_e('Header', 'tinyadz-wp-plugin'); ?>
            </option>
            <option value="footer" <?php selected($script_location, 'footer'); ?>>
                <?php esc_html_e('Footer', 'tinyadz-wp-plugin'); ?>
            </option>
        </select>
        <p class="description">
            <?php esc_html_e('Choose where to inject the TinyAdz script on filtered pages. Select "None" to disable the plugin or if you want to place the script tag yourself.', 'tinyadz-wp-plugin'); ?>
        </p>
        <?php
    }

    /**
     * Render Inline Ads Enabled field
     *
     * @since 1.0.0
     */
    public function render_inline_ads_enabled_field() {
        $options = get_option($this->option_name, array());
        $inline_ads_enabled = isset($options['inline_ads_enabled']) ? $options['inline_ads_enabled'] : false;
        ?>
        <input type="checkbox" 
               id="tinyadz_inline_ads_enabled" 
               name="<?php echo esc_attr($this->option_name); ?>[inline_ads_enabled]" 
               value="1" 
               <?php checked($inline_ads_enabled, 1); ?> />
        <label for="tinyadz_inline_ads_enabled">
            <?php esc_html_e('Enable inline ad containers in content', 'tinyadz-wp-plugin'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, ad containers will be inserted into post/page content.', 'tinyadz-wp-plugin'); ?>
        </p>
        <?php
    }

    /**
     * Render Inline Ads Position field
     *
     * @since 1.0.0
     */
    public function render_inline_ads_position_field() {
        $options = get_option($this->option_name, array());
        $inline_ads_position = isset($options['inline_ads_position']) ? $options['inline_ads_position'] : 'bottom';
        ?>
        <select id="tinyadz_inline_ads_position"
                name="<?php echo esc_attr($this->option_name); ?>[inline_ads_position]">
            <option value="top" <?php selected($inline_ads_position, 'top'); ?>>
                <?php esc_html_e('Top of content', 'tinyadz-wp-plugin'); ?>
            </option>
            <option value="bottom" <?php selected($inline_ads_position, 'bottom'); ?>>
                <?php esc_html_e('Bottom of content', 'tinyadz-wp-plugin'); ?>
            </option>
            <option value="random" <?php selected($inline_ads_position, 'random'); ?>>
                <?php esc_html_e('Random position (after paragraph)', 'tinyadz-wp-plugin'); ?>
            </option>
        </select>
        <p class="description">
            <?php esc_html_e('Choose where to insert the inline ad container within the content.', 'tinyadz-wp-plugin'); ?>
        </p>
        <?php
    }

    /**
     * Render Filter All Posts field
     *
     * @since 1.0.0
     */
    public function render_filter_all_posts_field() {
        $options = get_option($this->option_name, array());
        $filter_all_posts = isset($options['filter_all_posts']) ? $options['filter_all_posts'] : true; // Default to true
        ?>
        <input type="checkbox"
               id="tinyadz_filter_all_posts"
               name="<?php echo esc_attr($this->option_name); ?>[filter_all_posts]"
               value="1"
               <?php checked($filter_all_posts, 1); ?> />
        <label for="tinyadz_filter_all_posts">
            <?php esc_html_e('Display ads on all blog posts', 'tinyadz-wp-plugin'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, ads will show on all posts. When disabled, use the options below to filter posts.', 'tinyadz-wp-plugin'); ?>
        </p>
        <?php
    }

    /**
     * Render Filter Title Contains field
     *
     * @since 1.0.0
     */
    public function render_filter_title_contains_field() {
        $options = get_option($this->option_name, array());
        $filter_title_contains = isset($options['filter_title_contains']) ? $options['filter_title_contains'] : '';
        ?>
        <input type="text" 
               id="tinyadz_filter_title_contains" 
               name="<?php echo esc_attr($this->option_name); ?>[filter_title_contains]" 
               value="<?php echo esc_attr($filter_title_contains); ?>" 
               class="regular-text" 
               placeholder="<?php esc_attr_e('Enter keywords', 'tinyadz-wp-plugin'); ?>" />
        <p class="description">
            <?php esc_html_e('Only show ads on posts whose titles contain these keywords (comma-separated).', 'tinyadz-wp-plugin'); ?>
        </p>
        <?php
    }

    /**
     * Render Filter Post Age Older field
     *
     * @since 1.0.0
     */
    public function render_filter_post_age_older_field() {
        $options = get_option($this->option_name, array());
        $filter_post_age_older = isset($options['filter_post_age_older']) ? $options['filter_post_age_older'] : '';
        ?>
        <input type="number" 
               id="tinyadz_filter_post_age_older" 
               name="<?php echo esc_attr($this->option_name); ?>[filter_post_age_older]" 
               value="<?php echo esc_attr($filter_post_age_older); ?>" 
               min="0" 
               class="small-text" />
        <p class="description">
            <?php esc_html_e('Only show ads on posts older than this many days. Leave empty to disable.', 'tinyadz-wp-plugin'); ?>
        </p>
        <?php
    }

    /**
     * Render Filter Post Age Younger field
     *
     * @since 1.0.0
     */
    public function render_filter_post_age_younger_field() {
        $options = get_option($this->option_name, array());
        $filter_post_age_younger = isset($options['filter_post_age_younger']) ? $options['filter_post_age_younger'] : '';
        ?>
        <input type="number" 
               id="tinyadz_filter_post_age_younger" 
               name="<?php echo esc_attr($this->option_name); ?>[filter_post_age_younger]" 
               value="<?php echo esc_attr($filter_post_age_younger); ?>" 
               min="0" 
               class="small-text" />
        <p class="description">
            <?php esc_html_e('Only show ads on posts younger than this many days. Leave empty to disable.', 'tinyadz-wp-plugin'); ?>
        </p>
        <?php
    }

    /**
     * Render Filter All Pages field
     *
     * @since 1.0.0
     */
    public function render_filter_all_pages_field() {
        $options = get_option($this->option_name, array());
        $filter_all_pages = isset($options['filter_all_pages']) ? $options['filter_all_pages'] : true; // Default to true
        ?>
        <input type="checkbox"
               id="tinyadz_filter_all_pages"
               name="<?php echo esc_attr($this->option_name); ?>[filter_all_pages]"
               value="1"
               <?php checked($filter_all_pages, 1); ?> />
        <label for="tinyadz_filter_all_pages">
            <?php esc_html_e('Display ads on all static pages', 'tinyadz-wp-plugin'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, ads will show on all pages. When disabled, use the specific pages selection below.', 'tinyadz-wp-plugin'); ?>
        </p>
        <?php
    }

    /**
     * Render Filter Specific Pages field
     *
     * @since 1.0.0
     */
    public function render_filter_specific_pages_field() {
        $options = get_option($this->option_name, array());
        $filter_specific_pages = isset($options['filter_specific_pages']) ? $options['filter_specific_pages'] : array();
        
        // Get all pages
        $pages = get_pages(array(
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'post_status' => 'publish'
        ));
        
        if (!empty($pages)) {
            echo '<select multiple="multiple" id="tinyadz_filter_specific_pages" name="' . esc_attr($this->option_name) . '[filter_specific_pages][]" size="5" style="width: 300px;">';
            foreach ($pages as $page) {
                $selected = in_array($page->ID, (array) $filter_specific_pages) ? 'selected="selected"' : '';
                echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
            }
            echo '</select>';
            echo '<p class="description">' . esc_html__('Hold Ctrl (Cmd on Mac) to select multiple pages. Only selected pages will display ads.', 'tinyadz-wp-plugin') . '</p>';
        } else {
            echo '<p>' . esc_html__('No pages found. Create some pages first.', 'tinyadz-wp-plugin') . '</p>';
        }
    }

    /**
     * Sanitize and validate settings
     *
     * @param array $input Raw input data
     * @return array Sanitized settings
     * @since 1.0.0
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        // Sanitize Site ID
        if (isset($input['site_id'])) {
            $sanitized['site_id'] = sanitize_text_field($input['site_id']);
            
            // Validate Site ID format (basic validation for MongoDB ObjectId format)
            if (!empty($sanitized['site_id']) && !preg_match('/^[a-f0-9]{24}$/i', $sanitized['site_id'])) {
                add_settings_error(
                    'tinyadz_messages',
                    'tinyadz_message',
                    __('Site ID must be a valid 24-character hexadecimal string.', 'tinyadz-wp-plugin'),
                    'error'
                );
            }
        }

        // Sanitize script location
        if (isset($input['script_location'])) {
            $allowed_locations = array('none', 'header', 'footer');
            $sanitized['script_location'] = in_array($input['script_location'], $allowed_locations)
                ? $input['script_location']
                : 'footer';
        } else {
            $sanitized['script_location'] = 'footer';
        }

        // Sanitize checkboxes
        $sanitized['inline_ads_enabled'] = isset($input['inline_ads_enabled']) ? 1 : 0;
        $sanitized['filter_all_posts'] = isset($input['filter_all_posts']) ? 1 : 0;
        $sanitized['filter_all_pages'] = isset($input['filter_all_pages']) ? 1 : 0;

        // Enforce exclusivity rules for posts filtering
        if ($sanitized['filter_all_posts']) {
            // If "Show on All Posts" is enabled, clear the sub-configuration options
            $sanitized['filter_title_contains'] = '';
            $sanitized['filter_post_age_older'] = '';
            $sanitized['filter_post_age_younger'] = '';
        }

        // Enforce exclusivity rules for pages filtering
        if ($sanitized['filter_all_pages']) {
            // If "Show on All Pages" is enabled, clear specific pages selection
            $sanitized['filter_specific_pages'] = array();
        }

        // Sanitize inline ads position
        if (isset($input['inline_ads_position'])) {
            $allowed_positions = array('top', 'bottom', 'random');
            $sanitized['inline_ads_position'] = in_array($input['inline_ads_position'], $allowed_positions)
                ? $input['inline_ads_position']
                : 'bottom';
        } else {
            $sanitized['inline_ads_position'] = 'bottom';
        }

        // Sanitize text fields (only if not overridden by exclusivity rules)
        if (!$sanitized['filter_all_posts']) {
            $sanitized['filter_title_contains'] = isset($input['filter_title_contains']) ? sanitize_text_field($input['filter_title_contains']) : '';
        }

        // Sanitize and validate numeric fields (only if not overridden by exclusivity rules)
        if (!$sanitized['filter_all_posts']) {
            if (isset($input['filter_post_age_older'])) {
                $sanitized['filter_post_age_older'] = absint($input['filter_post_age_older']);
            }

            if (isset($input['filter_post_age_younger'])) {
                $sanitized['filter_post_age_younger'] = absint($input['filter_post_age_younger']);
            }
        }

        // Sanitize specific pages array (only if not overridden by exclusivity rules)
        if (!$sanitized['filter_all_pages']) {
            if (isset($input['filter_specific_pages']) && is_array($input['filter_specific_pages'])) {
                $sanitized['filter_specific_pages'] = array_map('absint', $input['filter_specific_pages']);
            } else {
                $sanitized['filter_specific_pages'] = array();
            }
        }

        return $sanitized;
    }
}