<?php
/** 
 * HNGamers Core
 * 
 * @package HNGamers Atavism User Verification
 * @author thevisad
 * @copyright 2022 HNGamers
 * @license GPL 
 * 
 * @wordpress-plugin 
 * Plugin Name: HNGamers Atavism User Verification
 * Plugin URI: https://hngamers.com/courses/atavism/atavism-wordpress-cms/
 * Description:  This is the user verification plugin for the HNG Core Atavism series and allows users to verify and log into the game server from the wordpress logins.
 * Version: 0.0.12
 * Author: thevisad
 * Author URI: https://hngamers.com/
 * Text Domain: hngamers-atavism-user-verify
 * License: GPL 
 * Lic
 **/

// the main plugin class
class hngamers_atavism_user_verify_plugin extends hngamers_atavism_core {
	
	private $source;
	private $dest;

	// build plugin and install all default items
	public function __construct() {
		// Inside the constructor or activation hook
		if (!class_exists('hngamers_atavism_core')) {
			// Display an admin notice if inside the WordPress admin area
			if (is_admin()) {
                add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
            }
			return; // Exit the constructor or activation method
		}
		$my_theme = wp_get_theme();
		if (!$my_theme->exists()) {
			// Handle the error, e.g., log it or display an admin notice
			error_log('Selected theme does not exist.');
		}
		$this->source = dirname(__FILE__) . '/templates/hngamers-atavism-verify-user.php';
        $this->dest = get_template_directory() . '/hngamers-atavism-verify-user.php';
		add_action( 'init', array( $this, 'hngamers_atavism_user_verify_sidebar_init') );
		
		register_activation_hook( __FILE__, array( $this,  'hngamers_atavism_user_verify_plugin_activate') );
		register_deactivation_hook( __FILE__, array( $this,  'hngamers_atavism_user_verify_plugin_remove') );

		add_action( 'admin_menu', array( $this,'hngamers_atavism_user_verify_admin_menu'), 99  );
		add_action('admin_init', array( $this,'hngamers_atavism_user_verify_admin_init'));
		add_filter('query_vars', array( $this,'hngamers_atavism_user_verify_plugin_query_vars'));
	}


	function hngamers_atavism_user_verify_plugin_dependency_check($plugin, $network_deactivating) {
		if (!class_exists('hngamers_atavism_core')) {
			deactivate_plugins('hngamers_atavism_informer');
		}
	}
	
	function copy_template_file() {
		// Attempt to copy the file
		if (!copy($this->source, $this->dest)) {
			// Log the error if the copy fails
			error_log('Failed to copy file from ' . $this->source . ' to ' . $this->dest);
		}
	}
    public function display_admin_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e( 'HNGamers Atavism User Verification: The core plugin is not active. Please activate the core plugin first.', 'hngamers-atavism-user-verify' ); ?></p>
        </div>
        <?php
    }
	function hngamers_atavism_user_verify_plugin_query_vars($query_vars){
		$query_vars[] = 'user';
		$query_vars[] = 'password';
		return $query_vars;
	}
	
	// Init plugin
    public function hngamers_atavism_user_verify_sidebar_init() {
        if ( ! file_exists( $this->dest ) ) {
            if ( ! copy( $this->source, $this->dest ) ) {
                error_log( 'Failed to copy file from ' . $this->source . ' to ' . $this->dest );
            }
        }
    }

	//Deactivate plugin
	public function hngamers_atavism_user_verify_sidebar_deactivate(){
		if( file_exists( $this->dest ) ) {
			unlink($this->dest);
		}
	}

	//activate the plugin
	function hngamers_atavism_user_verify_plugin_activate(){
	// Require parent plugin
        if ( ! is_plugin_active( 'hngamers-atavism-core/hngamerscore.php' ) && current_user_can( 'activate_plugins' ) ) {
			// Stop activation redirect and show error
			wp_die('Sorry, but this plugin requires the HNGamers Core Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
		}

		$thisOption_array = array(
			"subscribers_only"         => "1",
			"email_login"         => "1",
			"pmp_subscription_id"         => "1",
			"atavism_loginserver_ip"         => "127.0.0.1"
		);
		
		update_option('hngamers_atavism_user_verify_plugin_options', $thisOption_array);
	}
	
	//remove the plugin
	function hngamers_atavism_user_verify_plugin_remove(){
		delete_option('hngamers_atavism_user_verify_plugin_options');
		remove_filter('query_vars', array( $this,'hngamers_atavism_user_verify_plugin_query_vars'));
        $this->hngamers_atavism_user_verify_sidebar_deactivate(); // Call the sidebar deactivate method
	}

	//admin menu for the plugin
	public function hngamers_atavism_user_verify_admin_menu()  
	{
		add_submenu_page( 'hngamers-core-admin', 'Atavism User Verify', 'User Verify', 'manage_options', 'hngamers_atavism_user_verify_admin_menu', array( $this,'hngamers_atavism_user_verify_options_page'));
	}  


	//Add the Core Settings 
	public function hngamers_atavism_user_verify_admin_init()
	{
		register_setting(
			'hngamers_atavism_user_verify_plugin_options',		// Settings page
			'hngamers_atavism_user_verify_plugin_options',		// Option name
            array( $this, 'hngamers_atavism_user_verify_plugin_options_validate' ) // Validation callback
		);
		add_settings_section(
			'hngamers_atavism_user_verify_plugin',			// Id
			'HNGamers Atavism User Verify Settings',		// Title
			 array( $this,'hngamers_atavism_user_verify_section_text'),		// Callback function
			 __FILE__			// Page
		);
			add_settings_field('subscribers_only', "Restrict to Subscribers?", array( $this,'hngamers_atavism_user_verify_plugin_subscribers_only_dropdown_fn'), __FILE__, 'hngamers_atavism_user_verify_plugin');
			add_settings_field('email_login', "Use Emails as login?", array( $this,'hngamers_atavism_user_verify_plugin_email_login_dropdown_fn'), __FILE__, 'hngamers_atavism_user_verify_plugin');
			add_settings_field('pmp_subscription_id', 'Paid memberships Pro Subscription ID', array( $this,'hngamers_atavism_user_verify_plugin_setting_string'), __FILE__, 'hngamers_atavism_user_verify_plugin', 'pmp_subscription_id');
			add_settings_field('atavism_loginserver_ip', 'Comma Separated Server IP list', array( $this,'hngamers_atavism_user_verify_plugin_setting_string'), __FILE__, 'hngamers_atavism_user_verify_plugin', 'atavism_loginserver_ip');

	}
	
	
	function hngamers_atavism_user_verify_plugin_setting_string($i)
	{
		$thisOption = get_option('hngamers_atavism_user_verify_plugin_options');
		?>
		<input id='<?php esc_attr_e($i);?>' name='hngamers_atavism_user_verify_plugin_options[<?php esc_attr_e($i);?>]' size='32' type='text' value="<?php esc_attr_e($thisOption[$i] ); ?>" />
		<?php
	}

	function hngamers_atavism_user_verify_section_text()
	{
		echo '<p>These are the Atavism User Verify Settings and are used in each of the addon plugins.</p>';
	}

	/* Display the admin options page */
	function hngamers_atavism_user_verify_options_page()
	{
		?>
		<div class ="wrap">
			<div class="icon32" id="icon-plugins"><br></div>
			<h2>HNGamers Atavism User Verify Setup</h2>
			<div> You have properly loaded the verify plugin at this time. </div>
			<?php settings_errors(); ?>
			<form action="options.php" method="post">
				<?php settings_fields('hngamers_atavism_user_verify_plugin_options'); ?>
				<?php do_settings_sections(__FILE__); ?>
				<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /><p>
			</form>
		</div>
		<?php
	}

	function  hngamers_atavism_user_verify_plugin_subscribers_only_dropdown_fn() {
		$thisOption = get_option('hngamers_atavism_user_verify_plugin_options');
		?>
		<select name='hngamers_atavism_user_verify_plugin_options[subscribers_only]'>
			<option value=1 <?php selected( $thisOption['subscribers_only'], 1 ); ?>>No</option>
			<option value=2 <?php selected( $thisOption['subscribers_only'], 2 ); ?>>Yes</option>

		</select>
		<?php
	}
	
		function  hngamers_atavism_user_verify_plugin_email_login_dropdown_fn() {
		$thisOption = get_option('hngamers_atavism_user_verify_plugin_options');
		?>
		<select name='hngamers_atavism_user_verify_plugin_options[email_login]'>
			<option value=1 <?php selected( $thisOption['email_login'], 1 ); ?>>No</option>
			<option value=2 <?php selected( $thisOption['email_login'], 2 ); ?>>Yes</option>

		</select>
		<?php
	}

	function hngamers_atavism_user_verify_plugin_options_validate($input)
	{
		$thisOption = get_option('hngamers_atavism_user_verify_plugin_options');
		$input['subscribers_only'] = wp_filter_nohtml_kses($input['subscribers_only']);
		$input['email_login'] = wp_filter_nohtml_kses($input['email_login']);
		$input['atavism_loginserver_ip'] = wp_filter_nohtml_kses($input['atavism_loginserver_ip']);
		$input['pmp_subscription_id'] = wp_filter_nohtml_kses($input['pmp_subscription_id']);

		return $input;
	}
}
//Initialize plugin
$hngamers_atavism_user_verify_plugin = new hngamers_atavism_user_verify_plugin();