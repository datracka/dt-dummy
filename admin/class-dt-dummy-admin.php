<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    DT_Dummy
 * @subpackage DT_Dummy/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    DT_Dummy
 * @subpackage DT_Dummy/admin
 * @author     Dream-Theme
 */
class DT_Dummy_Admin {

	private $pugin_pages = array();
	private $theme_name;
	private $images_url;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function choose_dummy_content() {
		$this->theme_name = defined( 'PRESSCORE_THEME_NAME' ) ? PRESSCORE_THEME_NAME : sanitize_key( wp_get_theme()->get( 'Name' ) );
		$this->images_url = plugin_dir_url( __FILE__ ) . '../includes/dummy-content/' . $this->theme_name . '/images/';
	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

		if ( $this->plugin_page['import_dummy'] == $hook ) {
			wp_enqueue_style( $this->plugin_name . '-import', plugin_dir_url( __FILE__ ) . 'css/dt-dummy-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name . '-alertify-core', plugin_dir_url( __FILE__ ) . 'css/alertify-themes/alertify.core.css', array(), '0.3.11', 'all' );
			wp_enqueue_style( $this->plugin_name . '-alertify-default', plugin_dir_url( __FILE__ ) . 'css/alertify-themes/alertify.default.css', array(), '0.3.11', 'all' );
		}

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		if ( $this->plugin_page['import_dummy'] == $hook ) {
			wp_enqueue_script( $this->plugin_name . '-import', plugin_dir_url( __FILE__ ) . 'js/dt-dummy-admin.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->plugin_name . '-alertify', plugin_dir_url( __FILE__ ) . 'js/alertify.min.js', array( 'jquery' ), '0.3.11', false );
		}

	}

	public function add_plugin_action_links( $links, $file ) {
		$links['import-content'] = '<a href="' . esc_url( 'tools.php?page=dt-dummy-import' ) . '">' . __( 'Import content', $this->plugin_name ) . '</a>';
		return $links;
	}

	public function add_admin_notices() {
		global $current_screen;

		if ( ! get_option( 'dt_dummy_first_run_message' ) ) {
			$link = '<a href="' . esc_url( 'tools.php?page=dt-dummy-import' ) . '">' . __( 'Tools > Import Dummy', $this->plugin_name ) . '</a>';
			$msg = sprintf( __( 'You can import The7 demo content on %s page.' ), $link );

			add_settings_error( 'dt-dummy-activate-notice', 'dt-dummy-activate-notice', $msg, 'updated' );

			if ( ! in_array( $current_screen->parent_base, array( 'options-general', 'options-framework' ) ) ) {
				settings_errors( 'dt-dummy-activate-notice' );
			}

			update_option( 'dt_dummy_first_run_message', true );
		}
	}

	public function ajax_response() {

		if ( empty( $_POST['dummy'] ) ) {
			echo 'Unable to find dummy content.';
			exit();
		}

		$plugin_path = plugin_dir_path( dirname( __FILE__ ) ) . '/includes/dummy-content/' . $this->theme_name . '/';
		$dummy_content_obj = new DT_Dummy_Content( $this->get_dummy_list(), $this->theme_name );

		$content_part_id = empty( $_POST['content_part_id'] ) ? '0': sanitize_key( $_POST['content_part_id'] );
		$dummy_content = array_merge( $dummy_content_obj->get_main_content( $content_part_id )->as_array(), $dummy_content_obj->get_wc_content( $content_part_id )->as_array() );
		foreach( explode( ',', $_POST['dummy'] ) as $dummy_id ) {

			$dummy_id = sanitize_key( $dummy_id );
			if ( ! array_key_exists( $dummy_id, $dummy_content ) ) {
				continue;
			}

			$import_options = array();
			if ( isset( $dummy_content[ $dummy_id ]['replace_attachments'] ) ) {
				$import_options['replace_attachments'] = $dummy_content[ $dummy_id ]['replace_attachments'];
			}

			$file_name = $plugin_path . $dummy_content[ $dummy_id ]['file_name'];
			$this->import_file( $file_name, $import_options );

			echo '<p>' . dt_dummy_get_content_nice_name( $dummy_id, $dummy_content[ $dummy_id ] ) . ' ' . __( 'dummy content was imported.', $this->plugin_name ) . '</p>';
		}

		exit();
	}

	private function import_file( $file_name, $options = array() ) {
		$default_options = array(
			'replace_attachments' => true,
			'fetch_attachments' => true
		);
		$options = wp_parse_args( $options, $default_options );

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		$import_filepath = apply_filters( 'dt_dummy_filepath', $file_name );

		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';
		$import_error = false;

		//check if wp_importer, the base importer class is available, otherwise include it
		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) ) {
				require_once $class_wp_importer;
			} else {
				$import_error = true;
			}
		}

		//check if the wp import class is available, this class handles the wordpress XML files. If not include it
		//make sure to exclude the init function at the end of the file in kriesi_importer
		if ( ! class_exists( 'WP_Import' ) ) {
			$class_wp_import = plugin_dir_path( dirname( __FILE__ ) ) . '/includes/wordpress-importer/wordpress-importer.php';
			if ( file_exists( $class_wp_import ) ) {
				require_once $class_wp_import;
			} else {
				$import_error = true;
			}
		}

		if ( $import_error !== false ) {
			echo "The Auto importing script could not be loaded. please use the wordpress importer and import the XML file that is located in your themes folder manually.";
		} else {

			if ( class_exists( 'WP_Import' ) ) {
				include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dt-dummy-import.php';
			}

			if ( ! is_file( $import_filepath ) ) {
				echo "The XML file containing the dummy content is not available or could not be read in <pre>".get_template_directory() ."</pre><br/> You might want to try to set the file permission to chmod 777.<br/>If this doesn't work please use the wordpress importer and import the XML file (should be located in your themes folder: dummy.xml) manually <a href='/wp-admin/import.php'>here.</a>";
			} else {

				if ( $options['replace_attachments'] ) {
					add_filter( 'wp_import_post_data_raw', array( $this, 'replace_attachment_url' ) );
				}

				// woocommerce compatibility
				$this->post_importer_compatibility( $import_filepath );

				$wp_import = new DT_Dummy_Import();
				$wp_import->fetch_attachments = $options['fetch_attachments'];
				$wp_import->import( $import_filepath );
			}
		}
	}

	public function replace_attachment_url( $raw_post ) {
		if ( isset( $raw_post['post_type'] ) && 'attachment' == $raw_post['post_type'] ) {
			$raw_post['attachment_url'] = $raw_post['guid'] = $this->get_noimage_url( $raw_post['attachment_url'] );
		}

		return $raw_post;
	}

	public function add_plugin_page() {
		$this->plugin_page['import_dummy'] = add_management_page(
			__( 'Import Dummy', $this->plugin_name ),
			__( 'Import Dummy', $this->plugin_name ),
			'edit_theme_options',
			$this->plugin_name . '-import',
			array( $this, 'plugin_import_page' )
		);
	}

	public function plugin_import_page() {
		include 'partials/dt-dummy-admin-display-import.php';
	}

	public function allow_export_additional_post_types() {
		$post_types = array(
			'attachment'
		);

		foreach ( $post_types as $post_type ) {
			$post_types = get_post_types( array( 'name' => $post_type ), 'objects' );
			if ( ! empty( $post_types ) ) {
				$post_type = reset( $post_types );
				echo '<p><label><input type="radio" name="content" value="' . esc_attr( $post_type->name ) . '" /> ' . esc_html( $post_type->label ) . '</label></p>';
			}
		}
	}

	public function post_importer_compatibility( $file ) {
		global $wpdb;

		if ( ! $this->wc_is_enabled() || ! class_exists( 'WXR_Parser' ) )
			return;

		$parser = new WXR_Parser();
		$import_data = $parser->parse( $file );

		if ( isset( $import_data['posts'] ) ) {
			$posts = $import_data['posts'];

			if ( $posts && sizeof( $posts ) > 0 ) foreach ( $posts as $post ) {

				if ( $post['post_type'] == 'product' ) {

					if ( $post['terms'] && sizeof( $post['terms'] ) > 0 ) {

						foreach ( $post['terms'] as $term ) {

							$domain = $term['domain'];

							if ( strstr( $domain, 'pa_' ) ) {

								// Make sure it exists!
								if ( ! taxonomy_exists( $domain ) ) {

									$nicename = strtolower( sanitize_title( str_replace( 'pa_', '', $domain ) ) );

									$exists_in_db = $wpdb->get_var( $wpdb->prepare( "SELECT attribute_id FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = %s;", $nicename ) );

									// Create the taxonomy
									if ( ! $exists_in_db )
										$wpdb->insert( $wpdb->prefix . "woocommerce_attribute_taxonomies", array( 'attribute_name' => $nicename, 'attribute_type' => 'select', 'attribute_orderby' => 'menu_order' ), array( '%s', '%s', '%s' ) );

									// Register the taxonomy now so that the import works!
									register_taxonomy( $domain,
										apply_filters( 'woocommerce_taxonomy_objects_' . $domain, array('product') ),
										apply_filters( 'woocommerce_taxonomy_args_' . $domain, array(
											'hierarchical' => true,
											'show_ui' => false,
											'query_var' => true,
											'rewrite' => false,
										) )
									);
								}
							}
						}
					}
				}
			}
		}
	}

	private function get_noimage_url( $origin_img_url ) {
		switch ( pathinfo( $origin_img_url, PATHINFO_EXTENSION ) ) {
			case 'jpg':
			case 'jpeg':
				$ext = 'jpg';
				break;

			case 'png':
				$ext = 'png';
				break;

			case 'gif':
			default:
				$ext = 'gif';
				break;
		}
		$noimage_fname = 'noimage.' . $ext;
		return plugin_dir_url( __FILE__ ) . '/images/' . $noimage_fname;
	}

	private function get_dummy_list() {
		include plugin_dir_path( dirname( __FILE__ ) ) . 'includes/dummy-content/dummy-list.php';

		return $dummy_list;
	}

	private function wc_is_enabled() {
		return class_exists( 'Woocommerce' );
	}

}
