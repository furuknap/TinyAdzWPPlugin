# TinyAdz WordPress Plugin

A WordPress plugin that integrates TinyAdz advertising services into WordPress sites with comprehensive ad placement controls, filtering options, and both footer script injection and inline ad management.

# Author

Bjorn Furuknap (https://linkedin.com/in/furuknap/)

No affiliation with TinyAdz. I do not get any benefits or have any advantages beyond wanting to make this for myself.

Please contact TinyAdz.com for any questions about their service.

Made with the loving support of RooCode, Gemini, and Claude.

## Description

The TinyAdz WordPress Plugin provides seamless integration with [TinyAdz](https://tinyadz.com) advertising services, allowing you to:

- Inject TinyAdz scripts into your WordPress site's footer
- Manage inline ad placements within your content
- Control ad display with advanced filtering options based on post titles, age, and specific pages
- Configure ad positioning and display preferences through an intuitive admin interface

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Tested up to WordPress 6.4

## Manual Installation

Follow these step-by-step instructions to manually install the TinyAdz WordPress Plugin:

### Step 1: Download the Plugin Files

1. Download the plugin files from the source (GitHub repository, zip file, etc.)
2. If you received a zip file, extract it to reveal the plugin folder

### Step 2: Upload Plugin Files to WordPress

#### Option A: Using FTP/SFTP Client

1. Connect to your website using an FTP/SFTP client (such as FileZilla, WinSCP, or Cyberduck)
2. Navigate to your WordPress installation directory
3. Go to the `/wp-content/plugins/` folder
4. Upload the entire `tinyadz-wp-plugin` folder to the `/wp-content/plugins/` directory
5. Ensure all files are uploaded correctly, including:
   - `tinyadz-wp-plugin.php` (main plugin file)
   - `includes/` folder with class files
   - `admin/` folder with CSS files
   - `LICENSE` file

#### Option B: Using WordPress File Manager (if available)

1. Log in to your web hosting control panel (cPanel, Plesk, etc.)
2. Open the File Manager
3. Navigate to your WordPress installation directory
4. Go to the `/wp-content/plugins/` folder
5. Upload the `tinyadz-wp-plugin` folder or upload a zip file and extract it

#### Option C: Using WordPress Admin Dashboard (Upload Method)

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins** → **Add New**
3. Click **Upload Plugin** at the top of the page
4. Click **Choose File** and select the plugin zip file
5. Click **Install Now**
6. Wait for the upload and installation to complete

### Step 3: Activate the Plugin

1. In your WordPress admin dashboard, navigate to **Plugins** → **Installed Plugins**
2. Locate "TinyAdz WordPress Plugin" in the list
3. Click the **Activate** link under the plugin name
4. You should see a confirmation message that the plugin has been activated

### Step 4: Configure the Plugin

1. After activation, navigate to **Settings** → **TinyAdz** in your WordPress admin menu
2. Configure your TinyAdz settings:
   - Enter your TinyAdz Site ID
   - Choose script injection location (footer recommended)
   - Configure inline ad settings if needed
   - Set up content filtering options
   - Configure page and post display preferences
3. Click **Save Changes** to apply your settings

## Plugin Structure

The plugin consists of the following main files:

```
tinyadz-wp-plugin/
├── tinyadz-wp-plugin.php          # Main plugin file
├── LICENSE                        # MIT License file
├── README.md                      # This documentation
├── includes/
│   ├── class-tinyadz-admin.php    # Admin functionality
│   └── class-tinyadz-frontend.php # Frontend functionality
└── admin/
    └── css/
        └── admin-styles.css       # Admin interface styles
```

## Configuration Options

Once installed and activated, you can configure the following options:

- **Site ID**: Your unique TinyAdz site identifier
- **Script Location**: Choose between footer injection or inline placement
- **Content Filtering**: Control which posts and pages display ads
- **Post Age Filtering**: Filter ads based on post publication date
- **Page-Specific Settings**: Choose specific pages for ad display

## Troubleshooting

### Plugin Not Appearing in Admin

- Ensure all plugin files were uploaded correctly
- Check that the main plugin file `tinyadz-wp-plugin.php` is in the correct location
- Verify file permissions are set correctly (typically 644 for files, 755 for folders)

### Activation Errors

- Check that your WordPress version meets the minimum requirements (5.0+)
- Ensure your PHP version is 7.4 or higher
- Look for any PHP error messages in your error logs

### Ads Not Displaying

- Verify your TinyAdz Site ID is entered correctly
- Check your filtering settings to ensure ads are enabled for the content you're viewing
- Ensure the plugin is activated and configured properly

## Support

For support and questions about this plugin, please refer to the plugin documentation or contact the plugin developer.

## License

This plugin is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Version

Current version: 1.0.0

---

**Note**: This plugin requires a valid TinyAdz account and Site ID to function properly. Please ensure you have registered with TinyAdz and obtained your Site ID before configuring the plugin.
