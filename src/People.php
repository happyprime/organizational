<?php
/**
 * Define the People post type.
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

/**
 * Define the People post type.
 */
class People extends ContentType {
	/**
	 * The post type.
	 *
	 * @var string
	 */
	public string $post_type = 'og_person';

	/**
	 * The default name.
	 *
	 * Used to refer to the content type in the admin settings even after the
	 * name has been overridden.
	 *
	 * @var string
	 */
	public string $default_name = 'People';

	/**
	 * The singular name.
	 *
	 * @var string
	 */
	public string $singular_name = 'Person';

	/**
	 * The plural name.
	 *
	 * @var string
	 */
	public string $plural_name = 'People';

	/**
	 * The menu icon.
	 *
	 * @var string
	 */
	public string $menu_icon = 'dashicons-id-alt';

	/**
	 * The slug for an additional taxonomy associated with the content type.
	 *
	 * @var string
	 */
	public string $taxonomy = 'og_person_group';

	/**
	 * The plural name of an additional taxonomy for the content type.
	 *
	 * @var string
	 */
	public string $taxonomy_plural_name = 'People Groups';

	/**
	 * The singular name of an additional taxonomy for the content type.
	 *
	 * @var string
	 */
	public string $taxonomy_singular_name = 'People Group';

	/**
	 * Meta keys automatically registered for the post type.
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_meta(): array {
		return [
			'organizational_person_prefix'           => [
				'title'            => 'Prefix',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_first_name'       => [
				'title'            => 'First Name',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_last_name'        => [
				'title'            => 'Last Name',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_suffix'           => [
				'title'            => 'Suffix',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_title'            => [
				'title'            => 'Title',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_title_secondary'  => [
				'title'            => 'Secondary Title',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_office'           => [
				'title'            => 'Office',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_email'            => [
				'title'            => 'Email',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [
					'organizational/person-mailto' => [
						'label'              => __( 'Mailto link', 'organizational' ),
						'get_value_callback' => [ $this, 'get_mailto_source' ],
						'uses_context'       => array( 'postId' ),
					],
				],
			],
			'organizational_person_phone'            => [
				'title'            => 'Phone',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_website'          => [
				'title'            => 'Website',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_social_linkedin'  => [
				'title'            => 'LinkedIn',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_social_x'         => [
				'title'            => 'X (Twitter)',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_social_facebook'  => [
				'title'            => 'Facebook',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_person_social_instagram' => [
				'title'            => 'Instagram',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
		];
	}

	/**
	 * Get binding source data for displaying a mailto link.
	 *
	 * @since 2.1.0
	 *
	 * @param array     $source_args     Array containing source arguments.
	 * @param \WP_Block $block_instance  The block instance.
	 * @return string The mailto link or an empty string if not available.
	 */
	public function get_mailto_source( array $source_args, \WP_Block $block_instance ): string {
		$post_id = $block_instance->context['postId'] ?? false;

		if ( $post_id ) {
			$email = get_post_meta( $post_id, 'organizational_person_email', true );
			$email = esc_attr( $email );

			return "mailto:$email";
		}

		return '';
	}

	/**
	 * Enqueue block editor assets used by this post type.
	 */
	public function enqueue_block_editor_assets(): void {
		if ( 'post' !== get_current_screen()->base || get_current_screen()->post_type !== $this->post_type ) {
			return;
		}

		$asset_data = require_once plugin_dir_path( PLUGIN_FILE ) . '/js/build/people-meta/index.asset.php';

		wp_enqueue_script(
			'organizational-people-meta',
			plugins_url( '/js/build/people-meta/index.js', PLUGIN_FILE ),
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);
	}
}
