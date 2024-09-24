<?php
/**
 * Initialize the plugin.
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

/**
 * Initialize the plugin.
 */
class Init {

	/**
	 * Initialize the plugin.
	 */
	public static function init(): void {
		add_action( 'plugins_loaded', array( __CLASS__, 'init_plugin' ) );
		add_action( 'init', array( __CLASS__, 'process_upgrade_routine' ), 5 );
		add_action( 'init', array( __CLASS__, 'set_default_theme_support' ), 10 );
		add_action( 'init', array( __CLASS__, 'register_content_types' ), 15 );
		add_action( 'init', array( __CLASS__, 'init_admin' ), 100 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ) );
		add_action( 'organizational_flush_rewrite_rules', 'flush_rewrite_rules' );
	}

	/**
	 * Initialize a global object to track organizational state.
	 */
	public static function init_plugin(): void {
		global $organizational;

		$organizational = new Organizational();
	}

	/**
	 * Initialize the admin portion of the plugin.
	 */
	public static function init_admin(): void {
		if ( is_admin() ) {
			Admin::init();
		}
	}

	/**
	 * Apply any available upgrade routines between plugin versions or on
	 * initial activation.
	 */
	public static function process_upgrade_routine(): void {
		$db_version = get_option( 'organizational_version', '0.0.0' );

		// Flush rewrite rules if on an early or non existing DB version.
		if ( version_compare( $db_version, '1.0.0', '<' ) ) {
			flush_rewrite_rules();
		}

		update_option( 'organizational_version', ORGANIZATIONAL_VERSION );
	}

	/**
	 * If a theme does not provide explicit support for one or more portions of
	 * this plugin when the plugin is activated, assume the intent is to use
	 * all functionality.
	 *
	 * If at least one portion has been declared as supported, we leave the
	 * decision with the theme.
	 */
	public static function set_default_theme_support(): void {
		if ( false === current_theme_supports( 'organizational_project' ) &&
			false === current_theme_supports( 'organizational_person' ) &&
			false === current_theme_supports( 'organizational_entity' ) &&
			false === current_theme_supports( 'organizational_publication' ) ) {
			add_theme_support( 'organizational_project' );
			add_theme_support( 'organizational_person' );
			add_theme_support( 'organizational_entity' );
			add_theme_support( 'organizational_publication' );
		}
	}

	/**
	 * Register the supported content types.
	 */
	public static function register_content_types(): void {
		global $organizational;

		if ( current_theme_supports( 'organizational_person' ) ) {
			$organizational->people = new People();
			$organizational->people->register();
		}

		if ( current_theme_supports( 'organizational_project' ) ) {
			$organizational->projects = new Projects();
			$organizational->projects->register();
		}

		if ( current_theme_supports( 'organizational_entity' ) ) {
			$organizational->entities = new Entities();
			$organizational->entities->register();
		}

		if ( current_theme_supports( 'organizational_publication' ) ) {
			$organizational->publications = new Publications();
			$organizational->publications->register();
		}

		$organizational->register_relationships();
	}

	/**
	 * Enqueue block editor assets for supported content types.
	 */
	public static function enqueue_block_editor_assets(): void {
		global $organizational;

		if ( ! isset( $organizational ) ) {
			return;
		}

		if ( current_theme_supports( 'organizational_person' ) ) {
			$organizational->people->enqueue_block_editor_assets();
		}

		if ( current_theme_supports( 'organizational_project' ) ) {
			$organizational->projects->enqueue_block_editor_assets();
		}

		if ( current_theme_supports( 'organizational_entity' ) ) {
			$organizational->entities->enqueue_block_editor_assets();
		}

		if ( current_theme_supports( 'organizational_publication' ) ) {
			$organizational->publications->enqueue_block_editor_assets();
		}
	}
}
