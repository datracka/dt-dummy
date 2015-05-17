<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://dream-theme.com
 * @since             1.0.1
 * @package           DT_Dummy
 *
 * @wordpress-plugin
 * Plugin Name:       DT Dummy
 * Description:       Dummy content for Dream-Theme themes.
 * Version:           1.1.1
 * Author:            Dream-Theme
 * Author URI:        http://dream-theme.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dt-dummy
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'DT_DUMMY_PLUGIN_MAIN_FILE', __FILE__ );

/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-dt-dummy-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-dt-dummy-deactivator.php';

/** This action is documented in includes/class-dt-dummy-activator.php */
register_activation_hook( __FILE__, array( 'DT_Dummy_Activator', 'activate' ) );

/** This action is documented in includes/class-dt-dummy-deactivator.php */
register_deactivation_hook( __FILE__, array( 'DT_Dummy_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-dt-dummy.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_dt_dummy_loader() {

	$plugin = new DT_Dummy();
	$plugin->run();

}
run_dt_dummy_loader();
