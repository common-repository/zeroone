<?php
/*
Plugin Name: Keysender
Description: Digital product distribution and order management. Sell, manage, and distribute your digital products all in one place. Made and built for scale.
Version:     1.2.1
Author:      Keysender
Author URI:  https://www.keysender.com
Text Domain: keysender
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die;

define( 'KEYSENDER_ECOMMERCE_FILE', __FILE__ );
define( 'KEYSENDER_ECOMMERCE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'KEYSENDER_ECOMMERCE_CLASS_PATH', trailingslashit( KEYSENDER_ECOMMERCE_PLUGIN_PATH . 'classes' ) );
define( 'KEYSENDER_ECOMMERCE_INC_PATH', trailingslashit( KEYSENDER_ECOMMERCE_PLUGIN_PATH . 'inc' ) );
define( 'KEYSENDER_ECOMMERCE_NONCE_NAME', 'keysenderec_nonce' );
define( 'KEYSENDER_ECOMMERCE_VER', '1.2.1' );
define( 'KEYSENDER_ECOMMERCE_LOGIN_URL', 'https://panel.keysender.co.uk/api/v1.0/login' );

if ( ! class_exists( 'Keysender_eCommerce' ) ) {
    class Keysender_eCommerce {
        public static function get_instance() {
            if ( self::$instance == null ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private $inc_dir = null;

        private static $instance = null;

        private function __clone() { }

        public function __wakeup() { }

        private function __construct() {
            // Properties
			$this->options = null;
			$this->optsgroup_name = 'keysenderec_optsgroup';
			$this->options_name = 'keysenderec_options';
			$this->welcome_option_name = 'keysenderec_option_welcome';
			$this->ajax_loader_url = plugins_url( 'images/ajax-loader.gif', KEYSENDER_ECOMMERCE_FILE );

			// Action hooks
			add_action( 'admin_init', array( $this, 'check_first_run' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'add_submenu_item' ) );
            add_action( 'plugins_loaded', array( $this, 'check_for_woocommerce' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
			add_action( 'woocommerce_settings_tabs_keysenderec', array( $this, 'settings_tab_content' ) );
			add_action( 'wp_ajax_keysenderec_setup_api', array( $this, 'ajax_setup_api' ) );
			add_action( 'wp_ajax_keysenderec_disconnect_api', array( $this, 'ajax_disconnect_api' ) );

			// Filter hooks
			add_filter( 'plugin_action_links_' . plugin_basename( KEYSENDER_ECOMMERCE_FILE ), array( $this, 'add_settings_link' ) );
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 40 );
        }

		public function check_first_run() {
			if ( get_option( $this->welcome_option_name ) != '' ) return;

			update_option( $this->welcome_option_name, 1 );
			wp_redirect( menu_page_url( 'keysenderec-ecommerce-welcome', false ) );
			die;
		}

		public function check_for_woocommerce() {
            if ( ! class_exists( 'WooCommerce' ) ) {
                add_action( 'admin_notices', array( $this, 'missing_wc_notice' ) );
                return;
            }

            define( 'KEYSENDER_ECOMMERCE_BASE_FILE', __FILE__ );

			load_plugin_textdomain( 'keysender', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        public function missing_wc_notice() {
            echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Keysender requires WooCommerce to be installed and active. You can download %s here.', 'keysender' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
        }

		function add_settings_link( $links ) {
			array_splice( $links, 0, 0, array( '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=keysenderec&section=setup' ) . '">' . __( 'Settings', 'keysender' ) . '</a>' ) );
			return $links;
		}

		public function register_settings() {
			register_setting( $this->optsgroup_name, $this->options_name );
		}

		public function add_submenu_item() {
			add_submenu_page(
				null,
				__( 'Keysender', 'keysender' ),
				__( 'Keysender', 'keysender' ),
				'manage_options',
				'keysenderec-ecommerce-welcome',
				array( $this, 'render_welcome_page' )
			);

			add_menu_page(
					__( 'Keysender', 'keysender' ),
					__( 'Keysender', 'keysender' ),
					'manage_options',
					'wc-settings&tab=keysenderec',
					array( $this, 'render_options_page' ),
					plugins_url( 'images/dashicon.png', KEYSENDER_ECOMMERCE_FILE )
			);
		}

		public function render_welcome_page() {
			require KEYSENDER_ECOMMERCE_INC_PATH . 'welcome.php';			
		}

        public function render_options_page() {
		}

		public function enqueue_admin_assets( $hn ) {
			if ( function_exists( 'WC' ) && preg_match( '/keysenderec/', $_SERVER['REQUEST_URI'] ) ) {
				wp_enqueue_style( 'keysender', plugins_url( 'css/admin.css', __FILE__ ), array(), KEYSENDER_ECOMMERCE_VER, 'all' );
				wp_enqueue_script( 'keysender', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), KEYSENDER_ECOMMERCE_VER, true );
				wp_localize_script( 'keysender', 'WCKeysenderData', array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( KEYSENDER_ECOMMERCE_NONCE_NAME )
				) );
			}
		}

		public function add_settings_tab( $settings_tabs ) {
			$settings_tabs['keysenderec'] = __( 'Keysender', 'keysender' );
			return $settings_tabs;
		}

		public function settings_tab_content( $settings ) {
			global $current_section;
			if ( $current_section == '' && $this->is_authenticated() ) $current_section = 'overview';

			echo '<ul class="subsubsub">';
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=keysenderec&section=setup' ) . '" class="' . ( $current_section == 'setup' || $current_section == '' ? 'current' : '' ) . '">' . __( 'Setup', 'keysender' )  . '</a> | </li>';
			echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=keysenderec&section=overview' ) . '" class="' . ( $current_section == 'overview' ? 'current' : '' ) . '">' . __( 'Overview', 'keysender' )  . '</a></li>';
			echo '</ul>';
			echo '<br class="clear">';

			switch( $current_section ) {
				case 'overview':
					require KEYSENDER_ECOMMERCE_INC_PATH . 'overview.php';
					break;

				default:
					require KEYSENDER_ECOMMERCE_INC_PATH . 'setup.php';
					break;
			}
		}

		public function ajax_setup_api() {
			if ( ! check_admin_referer( KEYSENDER_ECOMMERCE_NONCE_NAME, 'nonce' ) ) wp_send_json_error( __( 'Unauthorized', 'keysender' ) );
			if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Unauthorized', 'keysender' ) );

			$api_key = isset( $_POST['apiKey'] ) ? sanitize_text_field( $_POST['apiKey'] ) : '';
			$api_secret = isset( $_POST['apiSecret'] ) ? sanitize_text_field( $_POST['apiSecret'] ) : '';

			if ( $api_key == '' || $api_secret == '' ) wp_send_json_error( __( 'Valid API Key and Secret must be provided', 'keysender' ) );

			$token = $this->login( $api_key, $api_secret );
			if ( is_wp_error( $token ) ) {
				update_option( $this->options_name, '' );
				wp_send_json_error( array( 'indicator' => $this->get_status(), 'error' => $token->get_error_message() ) );
			}

			update_option( $this->options_name, array(
				'api_key' => $api_key,
				'api_secret' => $api_secret,
				'token' => $token
			) );

			wp_send_json_success( array( 'indicator' => $this->get_status() ) );
		}

		public function ajax_disconnect_api() {
			if ( ! check_admin_referer( KEYSENDER_ECOMMERCE_NONCE_NAME, 'nonce' ) ) wp_send_json_error( __( 'Unauthorized', 'keysender' ) );
			if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Unauthorized', 'keysender' ) );

			update_option( $this->options_name, '' );

			wp_send_json_success( array( 'indicator' => $this->get_status() ) );
		}

		private function is_authenticated() {
			return $this->get_option( 'token' ) != '';
		}

		private function get_option( $option_name, $default = '' ) {
			if ( is_null( $this->options ) ) $this->options = ( array ) get_option( $this->options_name, array() );
			if ( isset( $this->options[$option_name] ) ) return $this->options[$option_name];
			return $default;
		}

		private function get_status() {
			$status = $this->is_authenticated() ? sprintf( __( '%sConnected%sDisconnect%s', 'keysender' ), '<span class="keysenderec-connected">', '</span><span class="keysenderec-disconnect"><a href="#">', '</a></span>' ) : sprintf( __( '%sDisconnected%s', 'keysender' ), '<span class="keysenderec-disconnected">', '</span>' );
			
			return '<span>' . sprintf( __( 'Status: %s', 'keysender' ),  $status ) . '</span>';
		}

		private function login( $api_key, $api_secret ) {
			$result = wp_remote_post( KEYSENDER_ECOMMERCE_LOGIN_URL, array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'headers' => array(
					'Accept' => 'application/json',
					'Content-Type' => 'application/json'
				),
				'args' => array(
					'api_key' =>  $api_key,
					'api_secret' => $api_secret 
				)
			) );

			if ( is_wp_error( $result ) ) return $result;

			$obj = json_decode( $result['body'] );
			return true;
			if ( ! $obj ) return new WP_Error( 'error', __( 'Invalid server response (1)', 'keysender' ) );

			if ( ! property_exists( $obj, 'data' ) || ! property_exists( $obj->data, 'access_token' ) ) {
				if ( property_exists( $obj, 'message' ) ) return new WP_Error( 'error', $obj->message );
				return new WP_Error( 'error', __( 'Invalid server response (2)', 'keysender' ) );
			}

			return $obj->data->access_token;
		}
    }
}
Keysender_eCommerce::get_instance();