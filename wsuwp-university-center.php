<?php
/*
Plugin Name: University Center
Plugin URI: http://web.wsu.edu/wordpress/plugins/university-center/
Description: Provide custom content types and taxonomies for a university center or organization.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Include handling of meta for default content types.
include_once __DIR__ . '/includes/wsuwp-university-center-meta.php';

class WSUWP_University_Center {
	/**
	 * The plugin version number, used to break caches and trigger
	 * upgrade routines.
	 *
	 * @var string
	 */
	var $plugin_version = '0.2.2';

	/**
	 * The slug used to register the project custom content type.
	 *
	 * @var string
	 */
	var $project_content_type = 'wsuwp_uc_project';

	/**
	 * The slug used to register the people custom content type.
	 *
	 * @var string
	 */
	var $people_content_type = 'wsuwp_uc_person';

	/**
	 * The slug used to register the publication custom content type.
	 *
	 * @var string
	 */
	var $publication_content_type = 'wsuwp_uc_publication';

	/**
	 * The slug used to register the entity custom content type.
	 *
	 * @var string
	 */
	var $entity_content_type = 'wsuwp_uc_entity';

	/**
	 * The slug used to register the entity type taxonomy.
	 *
	 * @var string
	 */
	var $entity_type_taxonomy = 'wsuwp_uc_entity_type';

	/**
	 * The slug used to register a taxonomy for center topics.
	 *
	 * @var string
	 */
	var $topics_taxonomy = 'wsuwp_uc_topics';

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

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 1 );

		add_filter( 'the_content', array( $this, 'add_object_content' ), 999, 1 );
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
		$db_version = get_option( 'wsuwp_uc_version', '0.0.0' );

		// Flush rewrite rules if on an early or non existing DB version.
		if ( version_compare( $db_version, '0.2.0', '<' ) ) {
			flush_rewrite_rules();
		}

		update_option( 'wsuwp_uc_version', $this->plugin_version );
	}

	/**
	 * If a theme does not provide explicit support for one or more portions of this plugin
	 * when the plugin is activated, we should assume that intent is to use all functionality.
	 *
	 * If at least one portion has been declared as supported, we leave the decision with the theme.
	 */
	public function set_default_support() {
		if ( false === current_theme_supports( 'wsuwp_uc_project' ) &&
			 false === current_theme_supports( 'wsuwp_uc_person' )  &&
			 false === current_theme_supports( 'wsuwp_uc_entity' )  &&
			 false === current_theme_supports( 'wsuwp_uc_publication' ) ) {
			add_theme_support( 'wsuwp_uc_project' );
			add_theme_support( 'wsuwp_uc_person' );
			add_theme_support( 'wsuwp_uc_entity' );
			add_theme_support( 'wsuwp_uc_publication' );
		}
	}

	/**
	 * Register the project content type.
	 */
	public function register_project_content_type() {
		// Only register the project content type if supported by the theme.
		if ( false === current_theme_supports( 'wsuwp_uc_project' ) ) {
			return;
		}

		$args = array(
			'labels' => array(
				'name' => __( 'Projects', 'wsuwp_uc' ),
				'singular_name' => __( 'Project', 'wsuwp_uc' ),
				'all_items' => __( 'All Projects', 'wsuwp_uc' ),
				'add_new_item' => __( 'Add Project', 'wsuwp_uc' ),
				'edit_item' => __( 'Edit Project', 'wsuwp_uc' ),
				'new_item' => __( 'New Project', 'wsuwp_uc' ),
				'view_item' => __( 'View Project', 'wsuwp_uc' ),
				'search_items' => __( 'Search Projects', 'wsuwp_uc' ),
				'not_found' => __( 'No Projects found', 'wsuwp_uc' ),
				'not_found_in_trash' => __( 'No Projects found in trash', 'wsuwp_uc' ),
			),
			'description' => __( 'Projects belonging to the center.', 'wsuwp_uc' ),
			'public' => true,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-analytics',
			'supports' => array (
				'title',
				'editor',
				'revisions',
				'thumbnail',
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'project',
				'with_front' => false
			),
		);
		register_post_type( $this->project_content_type, $args );
	}

	/**
	 * Register the people content type.
	 */
	public function register_people_content_type() {
		// Only register the people content type if supported by the theme.
		if ( false === current_theme_supports( 'wsuwp_uc_person' ) ) {
			return;
		}

		$args = array(
			'labels' => array(
				'name' => __( 'People', 'wsuwp_uc' ),
				'singular_name' => __( 'Person', 'wsuwp_uc' ),
				'all_items' => __( 'All People', 'wsuwp_uc' ),
				'add_new_item' => __( 'Add Person', 'wsuwp_uc' ),
				'edit_item' => __( 'Edit Person', 'wsuwp_uc' ),
				'new_item' => __( 'New Person', 'wsuwp_uc' ),
				'view_item' => __( 'View Person', 'wsuwp_uc' ),
				'search_items' => __( 'Search People', 'wsuwp_uc' ),
				'not_found' => __( 'No People found', 'wsuwp_uc' ),
				'not_found_in_trash' => __( 'No People found in trash', 'wsuwp_uc' ),
			),
			'description' => __( 'People involved with the center.', 'wsuwp_uc' ),
			'public' => true,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-id-alt',
			'supports' => array (
				'title',
				'author',
				'editor',
				'revisions',
				'thumbnail',
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'people',
				'with_front' => false
			),
		);

		register_post_type( $this->people_content_type, $args );
	}

	/**
	 * Register the publication content type.
	 */
	public function register_publication_content_type() {
		// Only register the publication content type if supported by the theme.
		if ( false === current_theme_supports( 'wsuwp_uc_publication' ) ) {
			return;
		}

		$args = array(
			'labels' => array(
				'name' => __( 'Publications', 'wsuwp_uc' ),
				'singular_name' => __( 'Publications', 'wsuwp_uc' ),
				'all_items' => __( 'All Publications', 'wsuwp_uc' ),
				'add_new_item' => __( 'Add Publication', 'wsuwp_uc' ),
				'edit_item' => __( 'Edit Publication', 'wsuwp_uc' ),
				'new_item' => __( 'New Publication', 'wsuwp_uc' ),
				'view_item' => __( 'View Publication', 'wsuwp_uc' ),
				'search_items' => __( 'Search Publications', 'wsuwp_uc' ),
				'not_found' => __( 'No Publications found', 'wsuwp_uc' ),
				'not_found_in_trash' => __( 'No Publications found in trash', 'wsuwp_uc' ),
			),
			'description' => __( 'Publications involved with the center.', 'wsuwp_uc' ),
			'public' => true,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-book',
			'supports' => array (
				'title',
				'editor',
				'revisions',
				'thumbnail',
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'publication',
				'with_front' => false
			),
		);

		register_post_type( $this->publication_content_type, $args );
	}

	/**
	 * Register the entity content type.
	 */
	public function register_entity_content_type() {
		// Only register the entity content type if supported by the theme.
		if ( false === current_theme_supports( 'wsuwp_uc_entity' ) ) {
			return;
		}

		$args = array(
			'labels' => array(
				'name' => __( 'Entities', 'wsuwp_uc' ),
				'singular_name' => __( 'Entity', 'wsuwp_uc' ),
				'all_items' => __( 'All Entities', 'wsuwp_uc' ),
				'add_new_item' => __( 'Add Entity', 'wsuwp_uc' ),
				'edit_item' => __( 'Edit Entity', 'wsuwp_uc' ),
				'new_item' => __( 'New Entity', 'wsuwp_uc' ),
				'view_item' => __( 'View Entity', 'wsuwp_uc' ),
				'search_items' => __( 'Search Entities', 'wsuwp_uc' ),
				'not_found' => __( 'No Entities found', 'wsuwp_uc' ),
				'not_found_in_trash' => __( 'No Entities found in trash', 'wsuwp_uc' ),
			),
			'description' => __( 'Entities involved with the center.', 'wsuwp_uc' ),
			'public' => true,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-groups',
			'supports' => array (
				'title',
				'editor',
				'revisions',
				'thumbnail',
			),
			'has_archive' => true,
			'rewrite' => array(
				'slug' => 'entity',
				'with_front' => false
			),
		);

		register_post_type( $this->entity_content_type, $args );
	}

	/**
	 * Register a taxonomy to track types of entities.
	 */
	public function register_entity_type_taxonomy() {
		// Only register the entity type taxonomy if the theme supports the entity content type.
		if ( false === current_theme_supports( 'wsuwp_uc_entity' ) ) {
			return;
		}

		$args = array(
			'labels' => array(
				'name' => __( 'Entity Types', 'wsuwp_uc' ),
				'singular_name' => __( 'Entity Type', 'wsuwp_uc' ),
				'search_items' => __( 'Search Entity Types', 'wsuwp_uc' ),
				'all_items' => __( 'All Entity Types', 'wsuwp_uc' ),
				'parent_item' => __( 'Parent Entity Type', 'wsuwp_uc' ),
				'parent_item_colon' => __( 'Parent Entity Type:', 'wsuwp_uc' ),
				'edit_item' => __( 'Edit Entity Type', 'wsuwp_uc' ),
				'update_item' => __( 'Update Entity Type', 'wsuwp_uc' ),
				'add_new_item' => __( 'Add New Entity Type', 'wsuwp_uc' ),
				'new_item_name' => __( 'New Entity Type Name', 'wsuwp_uc' ),
				'menu_name' => __( 'Entity Type', 'wsuwp_uc' ),
			),
			'hierarchical' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'entity-type' ),
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
		if ( false === current_theme_supports( 'wsuwp_uc_project' ) ) {
			return;
		}

		$args = array(
			'labels' => array(
				'name' => __( 'Topics', 'wsuwp_uc' ),
				'singular_name' => __( 'Topic', 'wsuwp_uc' ),
				'search_items' => __( 'Search Topics', 'wsuwp_uc' ),
				'all_items' => __( 'All Topics', 'wsuwp_uc' ),
				'parent_item' => __( 'Parent Topic', 'wsuwp_uc' ),
				'parent_item_colon' => __( 'Parent Topic:', 'wsuwp_uc' ),
				'edit_item' => __( 'Edit Topic', 'wsuwp_uc' ),
				'update_item' => __( 'Update Topic', 'wsuwp_uc' ),
				'add_new_item' => __( 'Add New Topic', 'wsuwp_uc' ),
				'new_item_name' => __( 'New Topic Name', 'wsuwp_uc' ),
				'menu_name' => __( 'Topic', 'wsuwp_uc' ),
			),
			'hierarchical' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'topic' ),
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

		// Only assign a unique id to content from our registered types.
		if ( ! in_array( $post->post_type, $this->get_object_type_slugs() ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		$unique_id = get_post_meta( $post_id, '_wsuwp_uc_unique_id', true );

		// Generate an ID if it does not yet exist.
		if ( empty( $unique_id ) ) {
			$unique_id = uniqid( 'wsuwp_uc_id_' );
			update_post_meta( $post_id, '_wsuwp_uc_unique_id', $unique_id );
		}

		$this->_flush_all_object_data_cache( $post->post_type );
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

		// Only assign a unique id to content from our registered types.
		if ( ! in_array( $post->post_type, $this->get_object_type_slugs() ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		$post_unique_id = get_post_meta( $post_id, '_wsuwp_uc_unique_id', true );

		if ( isset( $_POST['assign_people_ids'] ) ) {
			$people_ids = explode( ',', $_POST['assign_people_ids'] );
			$people_ids = $this->clean_posted_ids( $people_ids );

			$this->_maintain_object_association( $people_ids, $this->people_content_type, $post, $post_unique_id );

			update_post_meta( $post_id, '_' . $this->people_content_type . '_ids', $people_ids );
			$this->_flush_all_object_data_cache( $this->people_content_type );
		}

		if ( isset( $_POST['assign_projects_ids'] ) ) {
			$projects_ids = explode( ',', $_POST['assign_projects_ids'] );
			$projects_ids = $this->clean_posted_ids( $projects_ids );

			$this->_maintain_object_association( $projects_ids, $this->project_content_type, $post, $post_unique_id );

			update_post_meta( $post_id, '_' . $this->project_content_type . '_ids', $projects_ids );
			$this->_flush_all_object_data_cache( $this->project_content_type );
		}

		if ( isset( $_POST['assign_entities_ids'] ) ) {
			$entities_ids = explode( ',', $_POST['assign_entities_ids'] );
			$entities_ids = $this->clean_posted_ids( $entities_ids );

			$this->_maintain_object_association( $entities_ids, $this->entity_content_type, $post, $post_unique_id );

			update_post_meta( $post_id, '_' . $this->entity_content_type . '_ids', $entities_ids );
			$this->_flush_all_object_data_cache( $this->entity_content_type );
		}

		if ( isset( $_POST['assign_publications_ids'] ) ) {
			$publications_ids = explode( ',', $_POST['assign_publications_ids'] );
			$publications_ids = $this->clean_posted_ids( $publications_ids );

			$this->_maintain_object_association( $publications_ids, $this->publication_content_type, $post, $post_unique_id );

			update_post_meta( $post_id, '_' . $this->publication_content_type . '_ids', $publications_ids );
			$this->_flush_all_object_data_cache( $this->publication_content_type );
		}

		$this->_flush_all_object_data_cache( $post->post_type );
	}

	/**
	 * Clean posted object ID data so that any IDs passed are sanitized and validated as not empty.
	 *
	 * @param array $object_ids List of object IDs being associated.
	 *
	 * @return array Cleaned list of object IDs.
	 */
	public function clean_posted_ids( $object_ids ) {
		if ( ! is_array( $object_ids ) || empty( $object_ids ) ) {
			return array();
		}

		foreach( $object_ids as $key => $id ) {
			$id = sanitize_key( ( trim( $id ) ) ) ;

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
	private function _maintain_object_association( $object_ids, $object_content_type, $post, $post_unique_id ) {
		if ( empty( $object_ids ) ) {
			$object_ids = array();
		}

		$current_object_ids = get_post_meta( $post->ID, '_' . $object_content_type . '_ids', true );

		if ( $current_object_ids ) {
			$added_object_ids = array_diff( $object_ids, $current_object_ids );
			$removed_object_ids = array_diff( $current_object_ids, $object_ids );
		} else {
			$added_object_ids = $object_ids;
			$removed_object_ids = array();
		}

		$all_objects = $this->_get_all_object_data( $object_content_type );

		foreach( $added_object_ids as $add_object ) {
			$object_post_id = $all_objects[ $add_object ]['id'];
			$objects = get_post_meta( $object_post_id, '_' . $post->post_type . '_ids', true );

			if ( empty( $objects ) ) {
				$objects = array();
			}

			if ( ! in_array( $add_object, $objects ) ) {
				$objects[] = $post_unique_id;
			}
			update_post_meta( $object_post_id, '_' . $post->post_type . '_ids', $objects );
		}

		foreach( $removed_object_ids as $remove_object ) {
			if ( ! isset( $all_objects[ $remove_object ] ) ) {
				continue;
			}

			$object_post_id = $all_objects[ $remove_object ]['id'];
			$objects = get_post_meta( $object_post_id, '_' . $post->post_type . '_ids', true );

			if ( empty( $objects ) ) {
				$objects = array();
			}

			$key = array_search( $post_unique_id, $objects );

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
		wp_enqueue_script( 'wsuwp-uc-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery-ui-autocomplete' ), false, true );
		wp_enqueue_style( 'wsuwp-uc-admin-style', plugins_url( 'css/admin-style.css', __FILE__ ) );
	}

	/**
	 * Add the meta boxes used to maintain relationships between our content types.
	 *
	 * @param string $post_type The slug of the current post type.
	 */
	public function add_meta_boxes( $post_type ) {
		if ( ! in_array( $post_type, $this->get_object_type_slugs() ) ) {
			return;
		}

		if ( $this->project_content_type !== $post_type && current_theme_supports( 'wsuwp_uc_project' ) ) {
			add_meta_box( 'wsuwp_uc_assign_projects', 'Assign Projects', array( $this, 'display_assign_projects_meta_box' ), null, 'normal', 'default' );
		}

		if ( $this->entity_content_type !== $post_type && current_theme_supports( 'wsuwp_uc_entity' ) ) {
			add_meta_box( 'wsuwp_uc_assign_entities', 'Assign Entities', array( $this, 'display_assign_entities_meta_box' ), null, 'normal', 'default' );
		}

		if ( $this->people_content_type !== $post_type && current_theme_supports( 'wsuwp_uc_person' ) ) {
			add_meta_box( 'wsuwp_uc_assign_people', 'Assign People', array( $this, 'display_assign_people_meta_box' ), null, 'normal', 'default' );
		}

		if ( $this->publication_content_type !== $post_type && current_theme_supports( 'wsuwp_uc_publication' ) ) {
			add_meta_box( 'wsuwp_uc_assign_publications', 'Assign Publications', array( $this, 'display_assign_publications_meta_box' ), null, 'normal', 'default' );
		}
	}

	/**
	 * Display a meta box used to assign projects to other content types.
	 *
	 * @param WP_Post $post Currently displayed post object.
	 */
	public function display_assign_projects_meta_box( $post ) {
		$current_projects = get_post_meta( $post->ID, '_' . $this->project_content_type . '_ids', true );
		$all_projects = $this->_get_all_object_data( $this->project_content_type );
		$this->display_autocomplete_input( $all_projects, $current_projects, 'projects' );
	}

	/**
	 * Display a meta box used to assign entities to other content types.
	 *
	 * @param WP_Post $post Currently displayed post object.
	 */
	public function display_assign_entities_meta_box( $post ) {
		$current_entities = get_post_meta( $post->ID, '_' . $this->entity_content_type . '_ids', true );
		$all_entities = $this->_get_all_object_data( $this->entity_content_type );
		$this->display_autocomplete_input( $all_entities, $current_entities, 'entities' );
	}

	/**
	 * Display a meta box used to assign people to other content types.
	 *
	 * @param WP_Post $post Currently displayed post object.
	 */
	public function display_assign_people_meta_box( $post ) {
		$current_people = get_post_meta( $post->ID, '_' . $this->people_content_type . '_ids', true );
		$all_people = $this->_get_all_object_data( $this->people_content_type );
		$this->display_autocomplete_input( $all_people, $current_people, 'people' );
	}

	/**
	 * Display a meta box used to assign publications to other content types.
	 *
	 * @param WP_Post $post Currently displayed post object.
	 */
	public function display_assign_publications_meta_box( $post ) {
		$current_publications = get_post_meta( $post->ID, '_' . $this->publication_content_type . '_ids', true );
		$all_publications = $this->_get_all_object_data( $this->publication_content_type );
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
		if ( $current_object_data ) {
			$match_objects = array();
			foreach( $current_object_data as $current_object ) {
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
				'value' => $id,
				'label' => $object['name'],
			);
		}

		$objects = json_encode( $objects );
		?>

		<script> var wsu_uc = wsu_uc || {}; wsu_uc.<?php echo esc_js( $object_type ); ?> = <?php echo $objects; ?>; </script>

		<?php
		$current_objects_html = '';
		$current_objects_ids = implode( ',', array_keys( $objects_to_display ) );
		foreach( $objects_to_display as $key => $current_object ) {
			$current_objects_html .= '<div class="added-' . esc_attr( $object_type ) . ' added-object" id="' . esc_attr( $key ) . '" data-name="' . esc_attr( $current_object['name'] ) . '">' . esc_html( $current_object['name'] ) . '<span class="uc-object-close dashicons-no-alt"></span></div>';
		}
		?>
		<input id="<?php echo esc_attr( $object_type ); ?>-assign">
		<input type="hidden" id="<?php echo esc_attr( $object_type ); ?>-assign-ids" name="assign_<?php echo esc_attr( $object_type ); ?>_ids" value="<?php echo $current_objects_ids; ?>">
		<div id="<?php echo esc_attr( $object_type ); ?>-results" class="wsu-uc-objects-results"><?php echo $current_objects_html; ?></div>
		<div class="clear"></div>
	<?php
	}

	/**
	 * Retrieve all of the items from a specified content type with their unique ID,
	 * current post ID, and name.
	 *
	 * @param string $post_type The custom post type slug.
	 *
	 * @return array|bool Array of results or false if incorrectly called.
	 */
	private function _get_all_object_data( $post_type ) {
		$all_object_data = wp_cache_get( 'wsuwp_uc_all_' . $post_type );

		if ( ! $all_object_data ) {

			if ( ! in_array( $post_type, $this->get_object_type_slugs() ) ) {
				return false;
			}

			$all_object_data = array();
			$all_data = get_posts( array( 'post_type' => $post_type, 'posts_per_page' => 1000 ) );

			foreach( $all_data as $data ) {
				$unique_data_id = get_post_meta( $data->ID, '_wsuwp_uc_unique_id', true );
				if ( $unique_data_id ) {
					$all_object_data[ $unique_data_id ]['id'] = $data->ID;
					$all_object_data[ $unique_data_id ]['name'] = $data->post_title;
					$all_object_data[ $unique_data_id ]['url'] = esc_url_raw( get_permalink( $data->ID ) );
				}
			}

			if ( ! empty( $all_object_data ) ) {
				wp_cache_add( 'wsuwp_uc_all_' . $post_type, $all_object_data, '', 7200 );
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
	private function _flush_all_object_data_cache( $post_type ) {
		wp_cache_delete( 'wsuwp_uc_all_' . $post_type );
		$this->_get_all_object_data( $post_type );
	}

	/**
	 * Get a list of objects from an object type which are associated with the requested object.
	 *
	 * @param int    $post_id     ID of the object currently being used.
	 * @param string $object_type Slug of the object type to find.
	 *
	 * @return array List of objects associated with the requested object.
	 */
	public function get_object_objects( $post_id, $object_type ) {
		$post = get_post( $post_id );

		// Return false if the requested object type is the same object type as the post object.
		if ( $post->post_type === $object_type ) {
			return false;
		}

		if ( null === $post ) {
			return array();
		}

		$all_objects = $this->_get_all_object_data( $object_type );
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

		if ( current_theme_supports( 'wsuwp_uc_entity' ) ) {
			$entities = $this->get_object_objects( get_the_ID(), $this->entity_content_type );
		} else {
			$entities = false;
		}

		if ( current_theme_supports( 'wsuwp_uc_project' ) ) {
			$projects = $this->get_object_objects( get_the_ID(), $this->project_content_type );
		} else {
			$projects = false;
		}

		if ( current_theme_supports( 'wsuwp_uc_person' ) ) {
			$people = $this->get_object_objects( get_the_ID(), $this->people_content_type );
		} else {
			$people = false;
		}

		if ( current_theme_supports( 'wsuwp_uc_publication' ) ) {
			$publications = $this->get_object_objects( get_the_ID(), $this->publication_content_type );
		} else {
			$publications = false;
		}

		$added_html = '';

		if ( false !== $entities ) {
			$added_html .= '<div class="wsuwp-uc-entities"><h3>Organizations:</h3><ul>';
			foreach( $entities as $entity ) {
				$added_html .= '<li><a href="' . esc_url( $entity['url'] ) . '">' . esc_html( $entity['name'] ) . '</a></li>';
			}
			$added_html .= '</ul></div>';

		}

		if ( false !== $projects ) {
			$added_html .= '<div class="wsuwp-uc-projects"><h3>Projects:</h3><ul>';
			foreach ( $projects as $project ) {
				$added_html .= '<li><a href="' . esc_url( $project['url'] ) . '">' . esc_html( $project['name'] ) . '</a></li>';
			}
			$added_html .= '</ul></div>';
		}

		if ( false !== $people ) {
			$added_html .= '<div class="wsuwp-uc-people"><h3>People:</h3><ul>';
			foreach( $people as  $person ) {
				$added_html .= '<li><a href="' . esc_url( $person['url'] ) . '">' . esc_html( $person['name'] ) . '</a></li>';
			}
			$added_html .= '<ul></div>';
		}

		if ( false !== $publications ) {
			$added_html .= '<div class="wsuwp-uc-publications"><h3>Publications:</h3><ul>';
			foreach( $publications as $publication ) {
				$added_html .= '<li><a href="' . esc_url( $publication['url'] ) . '">' . esc_html( $publication['name'] ) . '</a></li>';
			}
			$added_html .= '</ul></div>';
		}

		return $content . $added_html;
	}
}
$wsuwp_university_center = new WSUWP_University_Center();

/**
 * Return the content type slug for the object type being queried.
 *
 * @param string $content_type Should be one of people, publication, entity, or project.
 *
 * @return string
 */
function wsuwp_uc_get_object_type_slug( $content_type ) {
	global $wsuwp_university_center;

	if ( 'people' === $content_type ) {
		return $wsuwp_university_center->people_content_type;
	}

	if ( 'publication' === $content_type ) {
		return $wsuwp_university_center->publication_content_type;
	}

	if ( 'entity' === $content_type ) {
		return $wsuwp_university_center->entity_content_type;
	}

	if ( 'project' === $content_type ) {
		return $wsuwp_university_center->project_content_type;
	}

	return '';
}

/**
 * Retrieve a list of content type slugs for registered content types by this plugin.
 *
 * @return array
 */
function wsuwp_uc_get_object_type_slugs() {
	global $wsuwp_university_center;
	return $wsuwp_university_center->get_object_type_slugs();
}

/**
 * Retrieve the list of projects associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function wsuwp_uc_get_object_projects( $post_id = 0 ) {
	global $wsuwp_university_center;
	return $wsuwp_university_center->get_object_objects( $post_id, $wsuwp_university_center->project_content_type );
}

/**
 * Retrieve the list of people associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function wsuwp_uc_get_object_people( $post_id = 0 ) {
	global $wsuwp_university_center;
	return $wsuwp_university_center->get_object_objects( $post_id, $wsuwp_university_center->people_content_type );
}

/**
 * Retrieve the list of entities associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function wsuwp_uc_get_object_entities( $post_id = 0 ) {
	global $wsuwp_university_center;
	return $wsuwp_university_center->get_object_objects( $post_id, $wsuwp_university_center->entity_content_type );
}

/**
 * Retrieve the list of publications associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function wsuwp_uc_get_object_publications( $post_id = 0 ) {
	global $wsuwp_university_center;
	return $wsuwp_university_center->get_object_objects( $post_id, $wsuwp_university_center->publication_content_type );
}