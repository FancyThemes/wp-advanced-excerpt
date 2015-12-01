<?php
/**
 * The Advanced Excerpt plugin loader.
 *
 * Control the appearance of WordPress post excerpts.
 *
 * @package Advanced_Excerpt
 */

/**
 * Plugin Name: Advanced Excerpt
 * Plugin URI: http://wordpress.org/plugins/advanced-excerpt/
 * Description: Control the appearance of WordPress post excerpts.
 * Version: 4.3
 * Author: Chris Aprea
 * Author URI: http://twitter.com/chrisaprea
 * License: GNU General Public License v3 or later
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Advanced Excerpt loader class.
 */
final class Advanced_Excerpt_Loader {

	/** Magic *****************************************************************/

	/**
	 * Advanced Excerpt uses many variables, several of which can be filtered to
	 * customize the way it operates. Most of these variables are stored in a
	 * private array that gets updated with the help of PHP magic methods.
	 *
	 * This is a precautionary measure, to avoid potential errors produced by
	 * unanticipated direct manipulation of Advanced Excerpt's run-time data.
	 *
	 * @see Advanced_Excerpt::setup_globals()
	 * @var array
	 */
	private $data;

	/** Singleton *************************************************************/

	/**
	 * Main Advanced Excerpt Instance
	 *
	 * Insures that only one instance of Advanced Excerpt exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @staticvar object $instance
	 * @return The one true Advanced Excerpt Loader
	 */
	public static function instance() {

		// Store the instance locally to avoid private static replication.
		static $instance = null;

		// Only run these methods if they haven't been ran previously.
		if ( null === $instance ) {
			$instance = new Advanced_Excerpt_Loader;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions();
		}

		// Always return the instance.
		return $instance;
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent Advanced Excerpt from being loaded more than once.
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent Advanced Excerpt from being cloned
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'advanced-excerpt' ), '1.0.0' ); }

	/**
	 * A dummy magic method to prevent Advanced Excerpt from being unserialized
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'advanced-excerpt' ), '1.0.0' ); }

	/**
	 * Magic method for checking the existence of a certain custom field
	 */
	public function __isset( $key ) { return isset( $this->data[$key] ); }

	/**
	 * Magic method for getting Advanced Excerpt variables
	 */
	public function __get( $key ) { return isset( $this->data[$key] ) ? $this->data[$key] : null; }

	/**
	 * Magic method for setting Advanced Excerpt variables
	 */
	public function __set( $key, $value ) { $this->data[$key] = $value; }

	/**
	 * Magic method for unsetting Advanced Excerpt variables
	 */
	public function __unset( $key ) { if ( isset( $this->data[$key] ) ) unset( $this->data[$key] ); }

	/**
	 * Magic method to prevent notices and errors from invalid method calls
	 */
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/** Private Methods *******************************************************/

	/**
	 * Set some smart defaults to class variables.
	 */
	private function setup_globals() {

		$this->version    = '4.3.0';

		// Setup some base path and URL information.
		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url( $this->file );

		// Includes.
		$this->inc_dir    = $this->plugin_dir . 'inc/';

		// Classes.
		$this->classes    = $this->inc_dir    . 'classes/';

		// Functions.
		$this->functions  = $this->inc_dir    . 'functions/';

		// Assets.
		$this->assets_dir = $this->plugin_dir . 'assets/';
		$this->assets_url = $this->plugin_url . 'assets/';

		// Templates.
		$this->templates  = $this->plugin_dir . 'template/';

		// CSS folder.
		$this->css_dir    = $this->assets_dir  . 'css/';
		$this->css_url    = $this->assets_url  . 'css/';

		// Images folder.
		$this->image_dir  = $this->assets_dir  . 'img/';
		$this->image_url  = $this->assets_url  . 'img/';

		// JS folder.
		$this->js_dir     = $this->assets_dir  . 'js/';
		$this->js_url     = $this->assets_url  . 'js/';
	}

	/**
	 * Include required files.
	 */
	private function includes() {

		// Main plugin class.
		$this->main = require $this->classes . 'class-advanced-excerpt.php';

		// Global functions.
		require $this->functions . 'functions.php';
	}

	/**
	 * Setup the default hooks and actions.
	 */
	private function setup_actions() {

		// Attach functions to the activate / deactive hooks.
		add_action( 'activate_'   . $this->basename, array( $this, 'activate' ) );
		add_action( 'deactivate_' . $this->basename, array( $this, 'deactivate' ) );
	}

	/** Public Methods *******************************************************/

	/**
	 * Plugin activation.
	 */
	public function activate() {

	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {

	}
}

/**
 * The main function responsible for returning the one true Advanced Excerpt Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $advanced_excerpt = advanced_excerpt(); ?>
 *
 * @return The one true Advanced Excerpt Instance
 */
function advanced_excerpt() {
	return Advanced_Excerpt_Loader::instance();
}

$GLOBALS['advanced_excerpt'] = advanced_excerpt();
