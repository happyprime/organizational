<?php
/**
 * Define the ContentType class.
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

/**
 * Define the ContentType class.
 */
class ContentType {
	/**
	 * The post type.
	 *
	 * @var string
	 */
	public string $post_type = '';

	/**
	 * The post type description.
	 *
	 * @var string
	 */
	public string $description = '';

	/**
	 * The menu icon.
	 *
	 * @var string
	 */
	public string $menu_icon = '';

	/**
	 * The singular name used for the post type.
	 *
	 * @var string
	 */
	public string $singular_name = '';

	/**
	 * The plural name used for the post type.
	 *
	 * @var string
	 */
	public string $plural_name = '';

	/**
	 * Meta fields automatically registered for the post type.
	 *
	 * @var array
	 */
	public array $meta = [];

	/**
	 * Register the post type.
	 *
	 * @return void
	 */
	public function register(): void {
		register_post_type( $this->post_type, $this->get_args() );

		$this->register_meta();
	}

	/**
	 * Register meta fields for the post type.
	 *
	 * @return void
	 */
	public function register_meta(): void {
		foreach ( $this->meta as $key => $args ) {
			register_post_meta(
				$this->post_type,
				$key,
				array(
					'single'       => isset( $args['single'] ) ? $args['single'] : true,
					'show_in_rest' => isset( $args['show_in_rest'] ) ? $args['show_in_rest'] : false,
					'type'         => $args['type'],
				)
			);
		}
	}

	/**
	 * Retrieve the labels for the post type.
	 *
	 * @return array
	 */
	public function get_labels(): array {
		$names = $this->get_object_type_names();

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
	 * Retrieve the description for the post type.
	 *
	 * @return string
	 */
	public function get_description(): string {
		return apply_filters( "organizational_{$this->post_type}_description", $this->description );
	}

	/**
	 * Retrieve the arguments for post type registration.
	 *
	 * @return array
	 */
	public function get_args(): array {
		$names = $this->get_object_type_names();

		$args = array(
			'labels'       => $this->get_labels(),
			'description'  => $this->get_description(),
			'public'       => true,
			'hierarchical' => false,
			'menu_icon'    => $this->menu_icon,
			'supports'     => array(
				'title',
				'editor',
				'revisions',
				'thumbnail',
				'excerpt',
				'custom-fields',
			),
			'taxonomies'   => apply_filters( "organizational_{$this->post_type}_taxonomies", array() ),
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => sanitize_title( strtolower( $names['singular'] ) ),
				'with_front' => false,
			),
			'show_in_rest' => true,
			'rest_base'    => sanitize_title( strtolower( $names['plural'] ) ), // Note this is different from the post type slug.
		);

		$args = apply_filters( "organizational_{$this->post_type}_args", $args );

		return $args;
	}

	/**
	 * Retrieve object type names from a previously saved names option.
	 *
	 * @return array A list of singular and plural names.
	 */
	private function get_object_type_names(): array {
		$names = get_option( 'organizational_names', false );

		if ( false !== $names && isset( $names[ $this->post_type ] ) && isset( $names[ $this->post_type ]['singular'] ) ) {
			$this->singular_name = $names[ $this->post_type ]['singular'] ? $names[ $this->post_type ]['singular'] : $this->singular_name;
		}

		if ( false !== $names && isset( $names[ $this->post_type ] ) && isset( $names[ $this->post_type ]['plural'] ) ) {
			$this->plural_name = $names[ $this->post_type ]['plural'] ? $names[ $this->post_type ]['plural'] : $this->plural_name;
		}

		return apply_filters(
			"organizational_{$this->post_type}_type_names",
			array(
				'singular' => $this->singular_name,
				'plural'   => $this->plural_name,
			)
		);
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_block_editor_assets(): void {}
}
