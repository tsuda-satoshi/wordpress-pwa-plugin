<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class Plugin_Name_Admin {

	/**
	 * @since    1.0.0
	 * @access   private
	 * @var      Plugin_Name_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	private $loader;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	public function define_hooks() {
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $this, 'setup_admin_menu' );
		$this->loader->add_action( 'admin_init', $this, 'pwa_plugin_admin_init' );
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $loader ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->loader      = $loader;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function setup_admin_menu() {

		add_options_page( $this->plugin_name, $this->plugin_name, 'manage_options', $this->plugin_name, array(
			$this,
			'render_view',
		) );
	}

	public function render_view() {
		require_once( plugin_dir_path( __FILE__ ) . 'class-plugin-name-admin-view.php' );
		$manifestSettings = get_option( 'pwa_manifest' );
		$cacheSettings    = get_option( 'pwa_cache_settings' );
		$view             = new Plugin_name_Admin_View();
		$view->render( [ 'manifestSettings' => $manifestSettings, 'cacheSettings' => $cacheSettings ] );
	}

	public function pwa_plugin_admin_init() {
		if ( isset( $_POST['my-submenu'] ) && $_POST['my-submenu'] && check_admin_referer( 'my-nonce-key', 'my-submenu' ) ) {
			// マニフェスト編集
//			echo '<pre>'; var_dump([$_POST, $_FILES]); echo'</pre>';
			$icons    = $this->makeAppIcons( $_POST, $_FILES['icon_file'] );
			$manifest = $this->makeManifest( $_POST, $icons );
			$this->saveAndGenerateManifestFile( $manifest );
		} else if ( isset( $_POST['my-submenu2'] ) && $_POST['my-submenu2'] && check_admin_referer( 'my-nonce-key2', 'my-submenu2' ) ) {
			// キャッシュ設定
			$data = [
				'exclusions'     => array_filter($_POST['exclusions'], function($pattern) {
					return !empty($pattern);
				}),
				'initial-caches' => array_filter($_POST['initial-caches'], function($url) {
					return !empty($url);
				}),
				'ttl'            => $_POST['ttl'],
				'offline_url'    => $_POST['offline_url'],
			];
			$this->generateServiceWorker( $data );
		}
	}

	private function saveAndGenerateManifestFile( $manifest ) {
		update_option( 'pwa_manifest', $manifest );
		$manifestJson = json_encode( $manifest );
		file_put_contents( get_home_path() . 'pwa-plugin-manifest.json', $manifestJson );
	}

	private function makeAppIcons( $data, $uploadFiles ) {
		$savedIcons  = get_option( 'pwa_plugin_app_icons' );
		$icons       = [];
		$iconDirPath = plugin_dir_path( dirname( __FILE__ ) ) . 'public/assets/images/';

		foreach ( $uploadFiles["name"] as $i => $name ) {
			if ( ! isset( $data['icon_size'][ $i ] ) || $data['icon_size'][ $i ] === '' ) {
				continue;
			}
			if ( $name ) {
				$icons[] = [
					'filename' => $uploadFiles['name'][ $i ],
					'type'     => $uploadFiles['type'][ $i ],
					'sizes'    => $data['icon_size'][ $i ],
				];
				move_uploaded_file(
					$uploadFiles['tmp_name'][ $i ],
					$iconDirPath . $uploadFiles['name'][ $i ] );
			} else if ( $savedIcons[ $i ] ) {
				$icon          = $savedIcons[ $i ];
				$icon['sizes'] = $data['icon_size'][ $i ];
				$icons[]       = $icon;
			}
		}
		update_option( 'pwa_plugin_app_icons', $icons );

		return $icons;
	}

	private function makeManifest( $data, $icons ) {

		return [
			'name'             => $data['name'],
			'short_name'       => $data['short_name'],
			'icons'            => array_map( function ( $icon ) {
				return [
					'src'   => plugin_dir_url( dirname( __FILE__ ) ) . 'public/assets/images/' . $icon['filename'],
					'type'  => $icon['type'],
					'sizes' => $icon['sizes'],
				];
			}, $icons ),
			'start_url'        => $data['start_url'],
			'display'          => $data['display'],
			'background_color' => $data['background_color'],
			'description'      => $data['description'],
			'theme_color'      => $data['theme_color'],
			'orientation'      => $data['orientation'],
		];
	}

	private function generateServiceWorker( $data ) {
		require_once plugin_dir_path( __FILE__ ) . 'class-pwa-plugin-service-worker-generator.php';
		update_option( 'pwa_cache_settings', $data );
		$generator = new Pwa_Plugin_Service_Worker_Generator( plugin_dir_url( dirname( __FILE__ ) ) );
		$script    = $generator->generate( $data );
		file_put_contents( get_home_path() . 'pwa-plugin-service-worker.js', $script );
	}
}
