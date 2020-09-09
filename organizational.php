<?php
/*
Plugin Name: Organizational
Plugin URI: https://github.com/happyprime/organizational
Description: Associate people, projects, organizations, and publications.
Author: happyprime
Version: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Include handling of meta for default content types.
require_once __DIR__ . '/includes/organizational-meta.php';

class Organizational {
	/**
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @var string
	 */
	public $plugin_version = '1.0.0';

	/**
	 * The slug used to register the project custom content type.
	 *
	 * @var string
	 */
	public $project_content_type = 'org_project';

	/**
	 * The slug used to register the people custom content type.
	 *
	 * @var string
	 */
	public $people_content_type = 'org_person';

	/**
	 * The slug used to register the publication custom content type.
	 *
	 * @var string
	 */
	public $publication_content_type = 'org_publication';

	/**
	 * The slug used to register the entity custom content type.
	 *
	 * @var string
	 */
	public $entity_content_type = 'org_entity';

	/**
	 * The slug used to register the entity type taxonomy.
	 *
	 * @var string
	 */
	public $entity_type_taxonomy = 'org_entity_type';

	/**
	 * The slug used to register a taxonomy for center topics.
	 *
	 * @var string
	 */
	public $topics_taxonomy = 'org_topics';

	/**
	 * Whether a nonce has been output for associated objects.
	 *
	 * @var bool
	 */
	public $nonce_output = false;

	/**
	 * Setup the hooks used by the plugin.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'set_default_support' ), 10 );
		add_action( 'init', array( $this, 'register_project_content_type' ), 11 );
		add_action( 'init', array( $this, 'register_people_content_type' ), 11 );
		add_action( 'init', array( $this, 'register_publication_content_type' ), 11 );
		add_action( 'init', array( $this, 'register_entity_content_type' ), 11 );
		add_action( 'init', array( $this, 'register_entity_type_taxonomy' ), 11 );
		add_action( 'init', array( $this, 'register_topic_taxonomy' ), 11 );

		add_action( 'init', array( $this, 'process_upgrade_routine' ), 12 );

		add_action( 'save_post', array( $this, 'assign_unique_id' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_associated_data' ), 11, 2 );

		add_action( 'admin_init', array( $this, 'display_settings' ), 11 );
		add_action( 'organizational_flush_rewrite_rules', array( $this, 'flush_rewrite_rules' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 1 );

		add_filter( 'the_content', array( $this, 'add_object_content' ), 999, 1 );

		add_action( 'init', array( $this, 'add_query_vars' ), 10 );
		add_action( 'pre_get_posts', array( $this, 'filter_rest_query' ), 10 );
		add_action( 'pre_get_posts', array( $this, 'filter_query' ), 10 );
	}

	/**
	 * Provide a list slugs for all registered content types.
	 *
	 * @return array
	 */
	public function get_object_type_slugs() {
		$slugs = array( $this->people_content_type, $this->project_content_type, $this->entity_content_type, $this->publication_content_type );

		return $slugs;
	}

	/**
	 * Process any upgrade routines between versions or on initial activation.
	 */
	public function process_upgrade_routine() {
		$db_version = get_option( 'organizational_version', '0.0.0' );

		// Flush rewrite rules if on an early or non existing DB version.
		if ( version_compare( $db_version, '1.0.0', '<' ) ) {
			flush_rewrite_rules();
		}

		update_option( 'organizational_version', $this->plugin_version );
	}

	/**
	 * If a theme does not provide explicit support for one or more portions of this plugin
	 * when the plugin is activated, we should assume that intent is to use all functionality.
	 *
	 * If at least one portion has been declared as supported, we leave the decision with the theme.
	 */
	public function set_default_support() {
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
	 * Register the settings fields that will be output for this plugin.
	 */
	public function display_settings() {
		register_setting( 'general', 'organizational_names', array( $this, 'sanitize_names' ) );
		add_settings_field(
			'organizational-names',
			'Organizational Names',
			array( $this, 'general_settings_names' ),
			'general',
			'default',
			array(
				'label_for' => 'organizational_names',
			)
		);
	}

	/**
	 * Sanitize the names assigned to object types before saving to the database.
	 *
	 * @param array $names Names being saved.
	 *
	 * @return array Clean data.
	 */
	public function sanitize_names( $names ) {
		$clean_names = array();
		foreach ( $names as $name => $data ) {
			if ( ! in_array( $name, array( 'project', 'people', 'entity', 'publication' ), true ) ) {
				continue;
			}

			$clean_names[ $name ]['singular'] = sanitize_text_field( $data['singular'] );
			$clean_names[ $name ]['plural']   = sanitize_text_field( $data['plural'] );
		}

		wp_schedule_single_event( time() + 1, 'organizational_flush_rewrite_rules' );

		return $clean_names;
	}

	/**
	 * Flush the rewrite rules on the site.
	 */
	public function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Display a settings area to capture modified names for object types.
	 */
	public function general_settings_names() {
		$names = get_option( 'organizational_names', false );

		$display_names = array();
		if ( ! isset( $names['project'] ) ) {
			$names['project'] = array();
		}
		if ( ! isset( $names['people'] ) ) {
			$names['people'] = array();
		}
		if ( ! isset( $names['entity'] ) ) {
			$names['entity'] = array();
		}
		if ( ! isset( $names['publication'] ) ) {
			$names['publication'] = array();
		}

		$display_names['project']     = wp_parse_args(
			$names['project'],
			array(
				'singular' => 'Project',
				'plural'   => 'Projects',
			)
		);
		$display_names['people']      = wp_parse_args(
			$names['people'],
			array(
				'singular' => 'Person',
				'plural'   => 'People',
			)
		);
		$display_names['entity']      = wp_parse_args(
			$names['entity'],
			array(
				'singular' => 'Entity',
				'plural'   => 'Entities',
			)
		);
		$display_names['publication'] = wp_parse_args(
			$names['publication'],
			array(
				'singular' => 'Publication',
				'plural'   => 'Publications',
			)
		);
		?>
		<div class="organizational-settings-names">
			<p>Changing the settings here will override the default labels for the content types provided by the Organizational plugin. The default labels are listed to the left of each field. The <strong>singular</strong> label will also be used as a slug in URLs.</p>
			<p class="description"></p>
			<label for="organizational_names_project_singular">Project (Singular)</label>
			<input id="organizational_names_project_singular" name="organizational_names[project][singular]" value="<?php echo esc_attr( $display_names['project']['singular'] ); ?>" type="text" class="regular-text" />
			<label for="organizational_names_project_plural">Projects (Plural)</label>
			<input id="organizational_names_project_plural" name="organizational_names[project][plural]" value="<?php echo esc_attr( $display_names['project']['plural'] ); ?>" type="text" class="regular-text" />
			<p class="description"></p>
			<label for="organizational_names_people_singular">Person (Singular)</label>
			<input id="organizational_names_people_singular" name="organizational_names[people][singular]" value="<?php echo esc_attr( $display_names['people']['singular'] ); ?>" type="text" class="regular-text" />
			<label for="organizational_names_people_plural">People (Plural)</label>
			<input id="organizational_names_people_plural" name="organizational_names[people][plural]" value="<?php echo esc_attr( $display_names['people']['plural'] ); ?>" type="text" class="regular-text" />
			<p class="description"></p>
			<label for="organizational_names_entity_singular">Entity (Singular)</label>
			<input id="organizational_names_entity_singular" name="organizational_names[entity][singular]" value="<?php echo esc_attr( $display_names['entity']['singular'] ); ?>" type="text" class="regular-text" />
			<label for="organizational_names_entity_plural">Entities (Plural)</label>
			<input id="organizational_names_entity_plural" name="organizational_names[entity][plural]" value="<?php echo esc_attr( $display_names['entity']['plural'] ); ?>" type="text" class="regular-text" />
			<p class="description"></p>
			<label for="organizational_names_publication_singular">Publication (Singular)</label>
			<input id="organizational_names_publication_singular" name="organizational_names[publication][singular]" value="<?php echo esc_attr( $display_names['publication']['singular'] ); ?>" type="text" class="regular-text" />
			<label for="organizational_names_publication_plural">Publications (Plural)</label>
			<input id="organizational_names_publication_plural" name="organizational_names[publication][plural]" value="<?php echo esc_attr( $display_names['publication']['plural'] ); ?>" type="text" class="regular-text" />
		</div>
		<?php
	}

	/**
	 * Build labels for a custom content type based on passed names.
	 *
	 * @param array $names Array of singular and plural forms for label names.
	 *
	 * @return array List of labels.
	 */
	private function build_labels( $names ) {
		$labels = array(
			'name'               => $names['plural'],
			'singular_name'      => $names['singular'],
			'all_items'          => 'All ' . $names['plural'],
			'add_new_item'       => 'Add ' . $names['singular'],
			'edit_item'          => 'Edit ' . $names['singular'],
			'new_item'           => 'New ' . $names['singular'],
			'view_item'          => 'View ' . $names['singular'],
			'search_items'       => 'Search ' . $names['plural'],
			'not_found'          => 'No ' . $names['plural'] . ' found',
			'not_found_in_trash' => 'No ' . $names['plural'] . ' found in trash',
		);
		return $labels;
	}

	/**
	 * Build a description string for a content type based on the passed naming data.
	 *
	 * @param string $plural Plural form of the string being set.
	 *
	 * @return string
	 */
	private function build_description( $plural ) {
		return esc_html( $plural ) . ' belonging to the center';
	}

	/**
	 * Retrieve object type names from a previously saved names option.
	 *
	 * @param string $object_type The type of object for which we need names.
	 *
	 * @return array|bool A list of singular and plural names. False if not available.
	 */
	private function get_object_type_names( $object_type ) {
		$names = get_option( 'organizational_names', false );

		// If an option is not provided, do not provide names.
		if ( false === $names || ! isset( $names[ $object_type ] ) ) {
			return false;
		}

		// The data must match our structure before we can depend on it.
		if ( ! isset( $names[ $object_type ]['singular'] ) || ! isset( $names[ $object_type ]['plural'] ) ) {
			return false;
		}

		return array(
			'singular' => esc_html( $names[ $object_type ]['singular'] ),
			'plural'   => esc_html( $names[ $object_type ]['plural'] ),
		);
	}

	/**
	 * Register the project content type.
	 */
	public function register_project_content_type() {
		// Only register the project content type if supported by the theme.
		if ( false === current_theme_supports( 'organizational_project' ) ) {
			return;
		}

		$existing_content_type = apply_filters( 'organizational_project_content_type', false );
		if ( $existing_content_type ) {
			$this->project_content_type = $existing_content_type;
			return;
		}

		$default_labels      = array(
			'name'               => __( 'Projects', 'organizational' ),
			'singular_name'      => __( 'Project', 'organizational' ),
			'all_items'          => __( 'All Projects', 'organizational' ),
			'add_new_item'       => __( 'Add Project', 'organizational' ),
			'edit_item'          => __( 'Edit Project', 'organizational' ),
			'new_item'           => __( 'New Project', 'organizational' ),
			'view_item'          => __( 'View Project', 'organizational' ),
			'search_items'       => __( 'Search Projects', 'organizational' ),
			'not_found'          => __( 'No Projects found', 'organizational' ),
			'not_found_in_trash' => __( 'No Projects found in trash', 'organizational' ),
		);
		$default_description = __( 'Projects belonging to the center.', 'organizational' );
		$default_slug        = 'project';

		$names = $this->get_object_type_names( 'project' );
		$names = apply_filters( 'organizational_project_type_names', $names );

		if ( false !== $names && isset( $names['singular'] ) && isset( $names['plural'] ) ) {
			$labels      = $this->build_labels( $names );
			$description = $this->build_description( $names['plural'] );
			$slug        = sanitize_title( strtolower( $names['singular'] ) );
		} else {
			$labels      = $default_labels;
			$description = $default_description;
			$slug        = $default_slug;
		}

		$args = array(
			'labels'       => $labels,
			'description'  => $description,
			'public'       => true,
			'hierarchical' => false,
			'menu_icon'    => 'dashicons-analytics',
			'supports'     => array(
				'title',
				'editor',
				'revisions',
				'thumbnail',
				'excerpt',
			),
			'taxonomies'   => array( 'category', 'post_tag' ),
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => $slug,
				'with_front' => false,
			),
			'show_in_rest' => true,
			'rest_base'    => 'projects', // Note that this can be different from the post type slug.
		);

		register_post_type( $this->project_content_type, $args );
	}

	/**
	 * Register the people content type.
	 */
	public function register_people_content_type() {
		// Only register the people content type if supported by the theme.
		if ( false === current_theme_supports( 'organizational_person' ) ) {
			return;
		}

		$existing_content_type = apply_filters( 'organizational_people_content_type', false );
		if ( $existing_content_type ) {
			$this->people_content_type = $existing_content_type;
			return;
		}

		$default_labels      = array(
			'name'               => __( 'People', 'organizational' ),
			'singular_name'      => __( 'Person', 'organizational' ),
			'all_items'          => __( 'All People', 'organizational' ),
			'add_new_item'       => __( 'Add Person', 'organizational' ),
			'edit_item'          => __( 'Edit Person', 'organizational' ),
			'new_item'           => __( 'New Person', 'organizational' ),
			'view_item'          => __( 'View Person', 'organizational' ),
			'search_items'       => __( 'Search People', 'organizational' ),
			'not_found'          => __( 'No People found', 'organizational' ),
			'not_found_in_trash' => __( 'No People found in trash', 'organizational' ),
		);
		$default_description = __( 'People involved with the center.', 'organizational' );
		$default_slug        = 'people';

		$names = $this->get_object_type_names( 'people' );
		$names = apply_filters( 'organizational_people_type_names', $names );

		if ( false !== $names && isset( $names['singular'] ) && isset( $names['plural'] ) ) {
			$labels      = $this->build_labels( $names );
			$description = $this->build_description( $names['plural'] );
			$slug        = sanitize_title( strtolower( $names['singular'] ) );
		} else {
			$labels      = $default_labels;
			$description = $default_description;
			$slug        = $default_slug;
		}

		$args = array(
			'labels'       => $labels,
			'description'  => $description,
			'public'       => true,
			'hierarchical' => false,
			'menu_icon'    => 'dashicons-id-alt',
			'supports'     => array(
				'title',
				'author',
				'editor',
				'revisions',
				'thumbnail',
				'excerpt',
			),
			'taxonomies'   => array( 'category', 'post_tag' ),
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => $slug,
				'with_front' => false,
			),
			'show_in_rest' => true,
			'rest_base'    => 'people',
		);

		register_post_type( $this->people_content_type, $args );
	}

	/**
	 * Register the publication content type.
	 */
	public function register_publication_content_type() {
		// Only register the publication content type if supported by the theme.
		if ( false === current_theme_supports( 'organizational_publication' ) ) {
			return;
		}

		$existing_content_type = apply_filters( 'organizational_publication_content_type', false );
		if ( $existing_content_type ) {
			$this->publication_content_type = $existing_content_type;
			return;
		}

		$default_labels      = array(
			'name'               => __( 'Publications', 'organizational' ),
			'singular_name'      => __( 'Publications', 'organizational' ),
			'all_items'          => __( 'All Publications', 'organizational' ),
			'add_new_item'       => __( 'Add Publication', 'organizational' ),
			'edit_item'          => __( 'Edit Publication', 'organizational' ),
			'new_item'           => __( 'New Publication', 'organizational' ),
			'view_item'          => __( 'View Publication', 'organizational' ),
			'search_items'       => __( 'Search Publications', 'organizational' ),
			'not_found'          => __( 'No Publications found', 'organizational' ),
			'not_found_in_trash' => __( 'No Publications found in trash', 'organizational' ),
		);
		$default_description = __( 'Publications involved with the center.', 'organizational' );
		$default_slug        = 'publication';

		$names = $this->get_object_type_names( 'publication' );
		$names = apply_filters( 'organizational_publication_type_names', $names );

		if ( false !== $names && isset( $names['singular'] ) && isset( $names['plural'] ) ) {
			$labels      = $this->build_labels( $names );
			$description = $this->build_description( $names['plural'] );
			$slug        = sanitize_title( strtolower( $names['singular'] ) );
		} else {
			$labels      = $default_labels;
			$description = $default_description;
			$slug        = $default_slug;
		}

		$args = array(
			'labels'       => $labels,
			'description'  => $description,
			'public'       => true,
			'hierarchical' => false,
			'menu_icon'    => 'dashicons-book',
			'supports'     => array(
				'title',
				'editor',
				'revisions',
				'thumbnail',
				'excerpt',
			),
			'taxonomies'   => array( 'category', 'post_tag' ),
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => $slug,
				'with_front' => false,
			),
			'show_in_rest' => true,
			'rest_base'    => 'publications',
		);

		register_post_type( $this->publication_content_type, $args );
	}

	/**
	 * Register the entity content type.
	 */
	public function register_entity_content_type() {
		// Only register the entity content type if supported by the theme.
		if ( false === current_theme_supports( 'organizational_entity' ) ) {
			return;
		}

		$existing_content_type = apply_filters( 'organizational_entity_content_type', false );
		if ( $existing_content_type ) {
			$this->entity_content_type = $existing_content_type;
			return;
		}

		$default_labels      = array(
			'name'               => __( 'Organizations', 'organizational' ),
			'singular_name'      => __( 'Organization', 'organizational' ),
			'all_items'          => __( 'All Organizations', 'organizational' ),
			'add_new_item'       => __( 'Add Organization', 'organizational' ),
			'edit_item'          => __( 'Edit Organization', 'organizational' ),
			'new_item'           => __( 'New Organization', 'organizational' ),
			'view_item'          => __( 'View Organization', 'organizational' ),
			'search_items'       => __( 'Search Organizations', 'organizational' ),
			'not_found'          => __( 'No Organizations found', 'organizational' ),
			'not_found_in_trash' => __( 'No Organizations found in trash', 'organizational' ),
		);
		$default_description = __( 'Organizations involved with the center.', 'organizational' );
		$default_slug        = 'entity';

		$names = $this->get_object_type_names( 'entity' );
		$names = apply_filters( 'organizational_entity_type_names', $names );

		if ( false !== $names && isset( $names['singular'] ) && isset( $names['plural'] ) ) {
			$labels      = $this->build_labels( $names );
			$description = $this->build_description( $names['plural'] );
			$slug        = sanitize_title( strtolower( $names['singular'] ) );
		} else {
			$labels      = $default_labels;
			$description = $default_description;
			$slug        = $default_slug;
		}

		$args = array(
			'labels'       => $labels,
			'description'  => $description,
			'public'       => true,
			'hierarchical' => false,
			'menu_icon'    => 'dashicons-groups',
			'supports'     => array(
				'title',
				'editor',
				'revisions',
				'thumbnail',
				'excerpt',
			),
			'taxonomies'   => array( 'category', 'post_tag' ),
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => $slug,
				'with_front' => false,
			),
			'show_in_rest' => true,
			'rest_base'    => 'organizations',
		);

		register_post_type( $this->entity_content_type, $args );
	}

	/**
	 * Register a taxonomy to track types of entities.
	 */
	public function register_entity_type_taxonomy() {
		// Only register the entity type taxonomy if the theme supports the entity content type.
		if ( false === current_theme_supports( 'organizational_entity' ) ) {
			return;
		}

		if ( false === apply_filters( 'organizational_entity_type_taxonomy_enabled', true ) ) {
			return;
		}

		$args = array(
			'labels'            => array(
				'name'              => __( 'Organization Types', 'organizational' ),
				'singular_name'     => __( 'Organization Type', 'organizational' ),
				'search_items'      => __( 'Search Organization Types', 'organizational' ),
				'all_items'         => __( 'All Organization Types', 'organizational' ),
				'parent_item'       => __( 'Parent Organization Type', 'organizational' ),
				'parent_item_colon' => __( 'Parent Organization Type:', 'organizational' ),
				'edit_item'         => __( 'Edit Organization Type', 'organizational' ),
				'update_item'       => __( 'Update Organization Type', 'organizational' ),
				'add_new_item'      => __( 'Add New Organization Type', 'organizational' ),
				'new_item_name'     => __( 'New Organization Type Name', 'organizational' ),
				'menu_name'         => __( 'Organization Type', 'organizational' ),
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug' => 'entity-type',
			),
		);

		register_taxonomy( $this->entity_type_taxonomy, $this->entity_content_type, $args );
	}

	/**
	 * Register a taxonomy to track topics for projects. This can then be used to determine
	 * what topics people and entities are associated with through their relationship with
	 * projects.
	 */
	public function register_topic_taxonomy() {
		// Only register the topic taxonomy if projects are supported.
		if ( false === current_theme_supports( 'organizational_project' ) ) {
			return;
		}

		if ( false === apply_filters( 'organizational_topic_taxonomy_enabled', true ) ) {
			return;
		}

		$args = array(
			'labels'            => array(
				'name'              => __( 'Topics', 'organizational' ),
				'singular_name'     => __( 'Topic', 'organizational' ),
				'search_items'      => __( 'Search Topics', 'organizational' ),
				'all_items'         => __( 'All Topics', 'organizational' ),
				'parent_item'       => __( 'Parent Topic', 'organizational' ),
				'parent_item_colon' => __( 'Parent Topic:', 'organizational' ),
				'edit_item'         => __( 'Edit Topic', 'organizational' ),
				'update_item'       => __( 'Update Topic', 'organizational' ),
				'add_new_item'      => __( 'Add New Topic', 'organizational' ),
				'new_item_name'     => __( 'New Topic Name', 'organizational' ),
				'menu_name'         => __( 'Topic', 'organizational' ),
			),
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug' => 'topic',
			),
		);

		register_taxonomy( $this->topics_taxonomy, array( $this->project_content_type ), $args );
	}

	/**
	 * Assign the object a unique ID to be used for maintaining relationships.
	 *
	 * @param int     $post_id The ID of the post being saved.
	 * @param WP_Post $post    The full post object being saved.
	 */
	public function assign_unique_id( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Do not overwrite existing unique IDs during an import.
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			return;
		}

		// Only assign a unique id to content from our registered types.
		if ( ! in_array( $post->post_type, $this->get_object_type_slugs(), true ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		$unique_id = get_post_meta( $post_id, '_organizational_unique_id', true );

		// Generate an ID if it does not yet exist.
		if ( empty( $unique_id ) ) {
			$unique_id = uniqid( 'organizational_id_' );
			update_post_meta( $post_id, '_organizational_unique_id', $unique_id );
		}

		$this->flush_all_object_cache( $post->post_type );
	}

	/**
	 * Save data to an individual post type object about the other objects that are being
	 * associated with it.
	 *
	 * @param int     $post_id ID of the post being saved.
	 * @param WP_Post $post    Post object being saved.
	 */
	public function save_associated_data( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Do not overwrite existing information during an import.
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			return;
		}

		// Only assign a unique id to content from our registered types.
		if ( ! in_array( $post->post_type, $this->get_object_type_slugs(), true ) ) {
			return;
		}

		if ( ! isset( $_POST['_organizational_object_associations_nonce'] ) || false === wp_verify_nonce( $_POST['_organizational_object_associations_nonce'], 'save_object_associations' ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		$post_unique_id = get_post_meta( $post_id, '_organizational_unique_id', true );

		if ( isset( $_POST['assign_people_ids'] ) ) {
			$people_ids = explode( ',', $_POST['assign_people_ids'] );
			$people_ids = $this->clean_posted_ids( $people_ids );

			$this->maintain_object_association( $people_ids, $this->people_content_type, $post, $post_unique_id );

			update_post_meta( $post_id, '_' . $this->people_content_type . '_ids', $people_ids );
			$this->flush_all_object_cache( $this->people_content_type );
		}

		if ( isset( $_POST['assign_projects_ids'] ) ) {
			$projects_ids = explode( ',', $_POST['assign_projects_ids'] );
			$projects_ids = $this->clean_posted_ids( $projects_ids );

			$this->maintain_object_association( $projects_ids, $this->project_content_type, $post, $post_unique_id );

			update_post_meta( $post_id, '_' . $this->project_content_type . '_ids', $projects_ids );
			$this->flush_all_object_cache( $this->project_content_type );
		}

		if ( isset( $_POST['assign_entities_ids'] ) ) {
			$entities_ids = explode( ',', $_POST['assign_entities_ids'] );
			$entities_ids = $this->clean_posted_ids( $entities_ids );

			$this->maintain_object_association( $entities_ids, $this->entity_content_type, $post, $post_unique_id );

			update_post_meta( $post_id, '_' . $this->entity_content_type . '_ids', $entities_ids );
			$this->flush_all_object_cache( $this->entity_content_type );
		}

		if ( isset( $_POST['assign_publications_ids'] ) ) {
			$publications_ids = explode( ',', $_POST['assign_publications_ids'] );
			$publications_ids = $this->clean_posted_ids( $publications_ids );

			$this->maintain_object_association( $publications_ids, $this->publication_content_type, $post, $post_unique_id );

			update_post_meta( $post_id, '_' . $this->publication_content_type . '_ids', $publications_ids );
			$this->flush_all_object_cache( $this->publication_content_type );
		}

		$this->flush_all_object_cache( $post->post_type );
	}

	/**
	 * Clean posted object ID data so that any IDs passed are sanitized and validated as not empty.
	 *
	 * @param array  $object_ids    List of object IDs being associated.
	 * @param string $strip_from_id Text to strip from an ID.
	 *
	 * @return array Cleaned list of object IDs.
	 */
	public function clean_posted_ids( $object_ids, $strip_from_id = '' ) {
		if ( ! is_array( $object_ids ) || empty( $object_ids ) ) {
			return array();
		}

		foreach ( $object_ids as $key => $id ) {
			$id = sanitize_key( ( trim( $id ) ) );

			if ( '' !== $strip_from_id ) {
				$id = str_replace( $strip_from_id, '', $id );
			}

			if ( '' === $id ) {
				unset( $object_ids[ $key ] );
			} else {
				$object_ids[ $key ] = $id;
			}
		}

		return $object_ids;
	}

	/**
	 * Maintain the association between objects when one is added or removed to the other. This ensures that
	 * if one type of object is added to another, that relationship is also established as meta for the
	 * original type of object.
	 *
	 * @param $object_ids
	 * @param $object_content_type
	 * @param $post
	 * @param $post_unique_id
	 */
	private function maintain_object_association( $object_ids, $object_content_type, $post, $post_unique_id ) {
		if ( empty( $object_ids ) ) {
			$object_ids = array();
		}

		$current_object_ids = get_post_meta( $post->ID, '_' . $object_content_type . '_ids', true );

		if ( $current_object_ids ) {
			$added_object_ids   = array_diff( $object_ids, $current_object_ids );
			$removed_object_ids = array_diff( $current_object_ids, $object_ids );
		} else {
			$added_object_ids   = $object_ids;
			$removed_object_ids = array();
		}

		$all_objects = $this->get_all_object_data( $object_content_type );

		foreach ( $added_object_ids as $add_object ) {
			$object_post_id = $all_objects[ $add_object ]['id'];
			$objects        = get_post_meta( $object_post_id, '_' . $post->post_type . '_ids', true );

			if ( empty( $objects ) ) {
				$objects = array();
			}

			if ( ! in_array( $add_object, $objects, true ) ) {
				$objects[] = $post_unique_id;
			}
			update_post_meta( $object_post_id, '_' . $post->post_type . '_ids', $objects );
		}

		foreach ( $removed_object_ids as $remove_object ) {
			if ( ! isset( $all_objects[ $remove_object ] ) ) {
				continue;
			}

			$object_post_id = $all_objects[ $remove_object ]['id'];
			$objects        = get_post_meta( $object_post_id, '_' . $post->post_type . '_ids', true );

			if ( empty( $objects ) ) {
				$objects = array();
			}

			// @codingStandardsIgnoreStart
			$key = array_search( $post_unique_id, $objects );
			// @codingStandardsIgnoreEnd

			if ( false !== $key ) {
				unset( $objects[ $key ] );
			}

			update_post_meta( $object_post_id, '_' . $post->post_type . '_ids', $objects );
		}
	}

	/**
	 * Enqueue the scripts and styles used in the admin interface.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'organizational-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery-ui-autocomplete' ), false, true );
		wp_enqueue_style( 'organizational-admin-style', plugins_url( 'css/admin-style.css', __FILE__ ) );
	}

	/**
	 * Add the meta boxes used to maintain relationships between our content types.
	 *
	 * @param string $post_type The slug of the current post type.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( ! in_array( $post_type, $this->get_object_type_slugs(), true ) ) {
			return;
		}

		if ( $this->project_content_type !== $post_type && current_theme_supports( 'organizational_project' ) ) {
			$labels = get_post_type_object( $this->project_content_type );
			add_meta_box( 'organizational_assign_projects', 'Assign ' . $labels->labels->name, array( $this, 'display_assign_projects_meta_box' ), null, 'normal', 'default' );
		}

		if ( $this->entity_content_type !== $post_type && current_theme_supports( 'organizational_entity' ) ) {
			$labels = get_post_type_object( $this->entity_content_type );
			add_meta_box( 'organizational_assign_entities', 'Assign ' . $labels->labels->name, array( $this, 'display_assign_entities_meta_box' ), null, 'normal', 'default' );
		}

		if ( $this->people_content_type !== $post_type && current_theme_supports( 'organizational_person' ) ) {
			$labels = get_post_type_object( $this->people_content_type );
			add_meta_box( 'organizational_assign_people', 'Assign ' . $labels->labels->name, array( $this, 'display_assign_people_meta_box' ), null, 'normal', 'default' );
		}

		if ( $this->publication_content_type !== $post_type && current_theme_supports( 'organizational_publication' ) ) {
			$labels = get_post_type_object( $this->publication_content_type );
			add_meta_box( 'organizational_assign_publications', 'Assign ' . $labels->labels->name, array( $this, 'display_assign_publications_meta_box' ), null, 'normal', 'default' );
		}
	}

	/**
	 * Display a meta box used to assign projects to other content types.
	 *
	 * @param WP_Post $post Currently displayed post object.
	 */
	public function display_assign_projects_meta_box( $post ) {
		$current_projects = get_post_meta( $post->ID, '_' . $this->project_content_type . '_ids', true );
		$all_projects     = $this->get_all_object_data( $this->project_content_type );
		$this->display_autocomplete_input( $all_projects, $current_projects, 'projects' );
	}

	/**
	 * Display a meta box used to assign entities to other content types.
	 *
	 * @param WP_Post $post Currently displayed post object.
	 */
	public function display_assign_entities_meta_box( $post ) {
		$current_entities = get_post_meta( $post->ID, '_' . $this->entity_content_type . '_ids', true );
		$all_entities     = $this->get_all_object_data( $this->entity_content_type );
		$this->display_autocomplete_input( $all_entities, $current_entities, 'entities' );
	}

	/**
	 * Display a meta box used to assign people to other content types.
	 *
	 * @param WP_Post $post Currently displayed post object.
	 */
	public function display_assign_people_meta_box( $post ) {
		$current_people = get_post_meta( $post->ID, '_' . $this->people_content_type . '_ids', true );
		$all_people     = $this->get_all_object_data( $this->people_content_type );
		$this->display_autocomplete_input( $all_people, $current_people, 'people' );
	}

	/**
	 * Display a meta box used to assign publications to other content types.
	 *
	 * @param WP_Post $post Currently displayed post object.
	 */
	public function display_assign_publications_meta_box( $post ) {
		$current_publications = get_post_meta( $post->ID, '_' . $this->publication_content_type . '_ids', true );
		$all_publications     = $this->get_all_object_data( $this->publication_content_type );
		$this->display_autocomplete_input( $all_publications, $current_publications, 'publications' );
	}

	/**
	 * Display the HTML used for the autocomplete area when associated objects with
	 * other objects in a meta box.
	 *
	 * @param array  $all_object_data     All objects of this object type.
	 * @param array  $current_object_data Objects of this object type currently associated with this post.
	 * @param string $object_type         The object type.
	 */
	public function display_autocomplete_input( $all_object_data, $current_object_data, $object_type ) {
		$base_object_types = array( 'people', 'projects', 'entities', 'publications' );
		// If we're autocompleting an object that is not part of our base, we append
		// the object type to each objects ID to avoid collision.
		if ( ! in_array( $object_type, $base_object_types, true ) ) {
			$id_append = esc_attr( $object_type );
		} else {
			$id_append = '';
		}

		if ( $current_object_data ) {
			$match_objects = array();
			foreach ( $current_object_data as $current_object ) {
				$match_objects[ $current_object ] = true;
			}
			$objects_for_adding = array_diff_key( $all_object_data, $match_objects );
			$objects_to_display = array_intersect_key( $all_object_data, $match_objects );
		} else {
			$objects_for_adding = $all_object_data;
			$objects_to_display = array();
		}

		$objects = array();
		foreach ( $objects_for_adding as $id => $object ) {
			$objects[] = array(
				'value' => $id . $id_append,
				'label' => $object['name'],
			);
		}

		$objects = wp_json_encode( $objects );

		$objects_to_display_clean = array();
		foreach ( $objects_to_display as $id => $object ) {
			$objects_to_display_clean[ $id . $id_append ] = $object;
		}

		// @codingStandardsIgnoreStart
		?>

		<script> var organizational = organizational || {}; organizational.<?php echo esc_js( $object_type ); ?> = <?php echo $objects; ?>; </script>

		<?php
		// @codingStandardsIgnoreEnd

		$current_objects_html = '';
		$current_objects_ids  = implode( ',', array_keys( $objects_to_display_clean ) );
		foreach ( $objects_to_display_clean as $key => $current_object ) {
			$current_objects_html .= '<div class="added-' . esc_attr( $object_type ) . ' added-object" id="' . esc_attr( $key ) . '" data-name="' . esc_attr( $current_object['name'] ) . '">' . esc_html( $current_object['name'] ) . '<span class="organizational-object-close dashicons-no-alt"></span></div>';
		}

		if ( false === $this->nonce_output ) {
			$this->nonce_output = true;
			wp_nonce_field( 'save_object_associations', '_organizational_object_associations_nonce' );
		}

		// @codingStandardsIgnoreStart
		?>
		<input id="<?php echo esc_attr( $object_type ); ?>-assign">
		<input type="hidden" id="<?php echo esc_attr( $object_type ); ?>-assign-ids" name="assign_<?php echo esc_attr( $object_type ); ?>_ids" value="<?php echo esc_attr( $current_objects_ids ); ?>">
		<div id="<?php echo esc_attr( $object_type ); ?>-results" class="organizational-objects-results"><?php echo $current_objects_html; ?></div>
		<div class="clear"></div>
		<?php
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Retrieve all of the items from a specified content type with their unique ID,
	 * current post ID, and name.
	 *
	 * @param string $post_type The custom post type slug.
	 *
	 * @return array|bool Array of results or false if incorrectly called.
	 */
	public function get_all_object_data( $post_type ) {
		$all_object_data = wp_cache_get( 'organizational_all_' . $post_type );

		if ( ! $all_object_data ) {

			if ( ! in_array( $post_type, $this->get_object_type_slugs(), true ) ) {
				return false;
			}

			$all_object_data = array();
			$all_data        = get_posts(
				array(
					'post_type'      => $post_type,
					'posts_per_page' => 1000,
				)
			);

			foreach ( $all_data as $data ) {
				$unique_data_id = get_post_meta( $data->ID, '_organizational_unique_id', true );
				if ( $unique_data_id ) {
					$all_object_data[ $unique_data_id ]['id']   = $data->ID;
					$all_object_data[ $unique_data_id ]['name'] = $data->post_title;
					$all_object_data[ $unique_data_id ]['url']  = esc_url_raw( get_permalink( $data->ID ) );
				}
			}

			if ( ! empty( $all_object_data ) ) {
				wp_cache_add( 'organizational_all_' . $post_type, $all_object_data, '', 7200 );
			}
		}

		return $all_object_data;
	}

	/**
	 * Clear the "all data" cache associated with this content type so that any autocomplete
	 * lists are populated correctly.
	 *
	 * @param string $post_type Slug for the post type being saved.
	 */
	private function flush_all_object_cache( $post_type ) {
		wp_cache_delete( 'organizational_all_' . $post_type );
		$this->get_all_object_data( $post_type );
	}

	/**
	 * Get a list of objects from an object type which are associated with the requested object.
	 *
	 * @param int         $post_id          ID of the object currently being used.
	 * @param string      $object_type      Slug of the object type to find.
	 * @param bool|string $base_object_type Slug of the object type to use as the base of this
	 *                                      query to support additional fabricated object types.
	 *                                      False if base object type should inherit the passed
	 *                                      object_type parameter.
	 *
	 * @return array List of objects associated with the requested object.
	 */
	public function get_object_objects( $post_id, $object_type, $base_object_type = false ) {
		$post = get_post( $post_id );

		// Return false if the requested object type is the same object type as the post object.
		if ( $post->post_type === $object_type ) {
			return false;
		}

		if ( null === $post ) {
			return array();
		}

		if ( false === $base_object_type ) {
			$base_object_type = $object_type;
		}

		$all_objects        = $this->get_all_object_data( $base_object_type );
		$associated_objects = get_post_meta( $post->ID, '_' . $object_type . '_ids', true );

		if ( is_array( $associated_objects ) && ! empty( $associated_objects ) ) {
			$objects = array_flip( $associated_objects );
			$objects = array_intersect_key( $all_objects, $objects );
		} else {
			$objects = array();
		}

		return $objects;
	}

	/**
	 * Add content areas for entities, projects, and people by default when a piece of
	 * content of these types is being displayed.
	 *
	 * @param string $content Current object content.
	 *
	 * @return string Modified content.
	 */
	public function add_object_content( $content ) {
		if ( false === is_singular( $this->get_object_type_slugs() ) ) {
			return $content;
		}

		if ( current_theme_supports( 'organizational_entity' ) ) {
			$entities = $this->get_object_objects( get_the_ID(), $this->entity_content_type );
		} else {
			$entities = false;
		}

		if ( current_theme_supports( 'organizational_project' ) ) {
			$projects = $this->get_object_objects( get_the_ID(), $this->project_content_type );
		} else {
			$projects = false;
		}

		if ( current_theme_supports( 'organizational_person' ) ) {
			$people = $this->get_object_objects( get_the_ID(), $this->people_content_type );
		} else {
			$people = false;
		}

		if ( current_theme_supports( 'organizational_publication' ) ) {
			$publications = $this->get_object_objects( get_the_ID(), $this->publication_content_type );
		} else {
			$publications = false;
		}

		$added_html = '';

		if ( false !== $entities && ! empty( $entities ) ) {
			$labels      = get_post_type_object( $this->entity_content_type );
			$added_html .= '<div class="organizational-entities"><h3>' . $labels->labels->name . '</h3><ul>';
			foreach ( $entities as $entity ) {
				$added_html .= '<li><a href="' . esc_url( $entity['url'] ) . '">' . esc_html( $entity['name'] ) . '</a></li>';
			}
			$added_html .= '</ul></div>';

		}

		$projects = apply_filters( 'organizational_projects_to_add_to_content', $projects, get_the_ID() );
		if ( false !== $projects && ! empty( $projects ) ) {
			$labels      = get_post_type_object( $this->project_content_type );
			$added_html .= '<div class="organizational-projects"><h3>' . $labels->labels->name . '</h3><ul>';
			foreach ( $projects as $project ) {
				$added_html .= '<li><a href="' . esc_url( $project['url'] ) . '">' . esc_html( $project['name'] ) . '</a></li>';
			}
			$added_html .= '</ul></div>';
		}

		$people = apply_filters( 'organizational_people_to_add_to_content', $people, get_the_ID() );
		if ( false !== $people && ! empty( $people ) ) {
			$labels      = get_post_type_object( $this->people_content_type );
			$added_html .= '<div class="organizational-people"><h3>' . $labels->labels->name . '</h3><ul>';
			foreach ( $people as  $person ) {
				$added_html .= '<li><a href="' . esc_url( $person['url'] ) . '">' . esc_html( $person['name'] ) . '</a></li>';
			}
			$added_html .= '<ul></div>';
		}

		if ( false !== $publications && ! empty( $publications ) ) {
			$labels      = get_post_type_object( $this->publication_content_type );
			$added_html .= '<div class="organizational-publications"><h3>' . $labels->labels->name . '</h3><ul>';
			foreach ( $publications as $publication ) {
				$added_html .= '<li><a href="' . esc_url( $publication['url'] ) . '">' . esc_html( $publication['name'] ) . '</a></li>';
			}
			$added_html .= '</ul></div>';
		}

		return $content . apply_filters( 'organizational_objects', $added_html );
	}

	/**
	 * Adds custom query vars to allow the filtering of REST API results by
	 * organization, person, publication, or project.
	 *
	 * @since 0.8.0
	 */
	public function add_query_vars() {
		global $wp;

		$wp->add_query_var( 'org_organization' );
		$wp->add_query_var( 'org_person' );
		$wp->add_query_var( 'org_publication' );
		$wp->add_query_var( 'org_project' );
	}

	/**
	 * Restricts a REST API request result to a set of IDs when a
	 * corresponding query var is provided.
	 *
	 * @param WP_Query $query
	 */
	public function filter_rest_query( $query ) {
		if ( ! defined( 'REST_REQUEST' ) || true !== REST_REQUEST ) {
			return;
		}

		// If we don't remove this filter, we'll start an infinite loop.
		remove_filter( 'pre_get_posts', array( $this, 'filter_rest_query' ) );

		if ( isset( $query->query['org_organization'] ) && ! empty( $query->query['org_organization'] ) ) {
			$slug = sanitize_title( $query->query['org_organization'] );
			$type = $this->entity_content_type;
		} elseif ( isset( $query->query['org_person'] ) && ! empty( $query->query['org_person'] ) ) {
			$slug = sanitize_title( $query->query['org_person'] );
			$type = $this->people_content_type;
		} elseif ( isset( $query->query['org_publication'] ) && ! empty( $query->query['org_publication'] ) ) {
			$slug = sanitize_title( $query->query['org_publication'] );
			$type = $this->publication_content_type;
		} elseif ( isset( $query->query['org_project'] ) && ! empty( $query->query['org_project'] ) ) {
			$slug = sanitize_title( $query->query['org_project'] );
			$type = $this->project_content_type;
		} else {
			return;
		}

		$posts = get_posts(
			array(
				'post_type' => $type,
				'name'      => $slug,
			)
		);

		if ( 0 === count( $posts ) ) {
			$query->set( 'post__in', array( 0 ) );
			return;
		}

		$objects = organizational_get_object_objects( $posts[0]->ID, $query->query['post_type'] );

		if ( empty( $objects ) ) {
			$query->set( 'post__in', array( 0 ) );
			return;
		}

		$ids = array_values( wp_list_pluck( $objects, 'id' ) );

		$query->set( 'post__in', $ids );
		$query->set( 'per_page', 100 );
	}

	/**
	 * Filter post type archive view queries.
	 *
	 * - Projects and entities are sorted by title.
	 * - People are sorted by last name.
	 * - Publications are left to a default sort by date.
	 * - All posts_per_page limits are bumped to 2000.
	 *
	 * @param WP_Query $query
	 */
	public function filter_query( $query ) {
		if ( ! $query->is_main_query() || is_admin() ) {
			return;
		}

		$post_types = $this->get_object_type_slugs();

		// Avoid paginating without intent by maxing out at 2000 per archive.
		if ( $query->is_post_type_archive( $post_types ) ) {
			$query->set( 'posts_per_page', 2000 );
		}

		// Avoid pagination without intent by maxing out at 2000 per taxonomy archive.
		if ( $query->is_tax( $this->entity_type_taxonomy ) || $query->is_tax( $this->topics_taxonomy ) ) {
			$query->set( 'posts_per_page', 2000 );
		}

		// Entities and projects are sorted by their titles in archive views.
		if ( $query->is_tax( $this->topics_taxonomy ) || $query->is_tax( $this->entity_type_taxonomy ) || $query->is_post_type_archive( $this->entity_content_type ) || $query->is_post_type_archive( $this->project_content_type ) ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
		}

		// People are sorted by their last names in archive views.
		if ( $query->is_post_type_archive( $post_types ) && $query->is_post_type_archive( $this->people_content_type ) ) {
			$query->set( 'meta_key', '_organizational_person_last_name' );
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'order', 'ASC' );
		}
	}
}
global $organizational;
$organizational = new Organizational();

/**
 * Return the content type slug for the object type being queried.
 *
 * @param string $content_type Should be one of people, publication, entity, or project.
 *
 * @return string
 */
function organizational_get_object_type_slug( $content_type ) {
	global $organizational;

	if ( 'people' === $content_type ) {
		return $organizational->people_content_type;
	}

	if ( 'publication' === $content_type ) {
		return $organizational->publication_content_type;
	}

	if ( 'entity' === $content_type ) {
		return $organizational->entity_content_type;
	}

	if ( 'project' === $content_type ) {
		return $organizational->project_content_type;
	}

	return '';
}

/**
 * Retrieve a list of content type slugs for registered content types by this plugin.
 *
 * @return array
 */
function organizational_get_object_type_slugs() {
	global $organizational;
	return $organizational->get_object_type_slugs();
}

/**
 * Retrieve all of the items from a specified content type with their unique ID,
 * current post ID, and name.
 *
 * @param string $object_type The custom post type slug.
 *
 * @return array|bool Array of results or false if incorrectly called.
 */
function organizational_get_all_object_data( $object_type ) {
	global $organizational;
	return $organizational->get_all_object_data( $object_type );
}

/**
 * Clean posted object ID data so that any IDs passed are sanitized and validated as not empty.
 *
 * @param array  $object_ids    List of object IDs being associated.
 * @param string $strip_from_id Text to strip from an object's ID.
 *
 * @return array Cleaned list of object IDs.
 */
function organizational_clean_post_ids( $object_ids, $strip_from_id = '' ) {
	global $organizational;
	return $organizational->clean_posted_ids( $object_ids, $strip_from_id );
}

/**
 * Retrieve the list of projects associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function organizational_get_object_projects( $post_id = 0 ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $organizational->project_content_type );
}

/**
 * Retrieve the list of people associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function organizational_get_object_people( $post_id = 0 ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $organizational->people_content_type );
}

/**
 * Retrieve the list of entities associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function organizational_get_object_entities( $post_id = 0 ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $organizational->entity_content_type );
}

/**
 * Retrieve the list of publications associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function organizational_get_object_publications( $post_id = 0 ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $organizational->publication_content_type );
}

/**
 * Wrapper method to retrieve a list of objects from an object type associated with the requested object.
 *
 * @param int         $post_id          ID of the object currently being used.
 * @param string      $object_type      Slug of the object type to find.
 * @param bool|string $base_object_type Slug of the object type to use as the base of this
 *                                      query to support additional fabricated object types.
 *                                      False if base object type should inherit the passed
 *                                      object_type parameter.
 *
 * @return array List of objects associated with the requested object.
 */
function organizational_get_object_objects( $post_id, $object_type, $base_object_type = false ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $object_type, $base_object_type );
}
