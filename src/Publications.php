<?php
/**
 * Define the Publications post type.
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

/**
 * Define the Publications post type.
 */
class Publications extends ContentType {
	/**
	 * The post type.
	 *
	 * @var string
	 */
	public string $post_type = 'og_publication';

	/**
	 * The singular name.
	 *
	 * @var string
	 */
	public string $singular_name = 'Publication';

	/**
	 * The plural name.
	 *
	 * @var string
	 */
	public string $plural_name = 'Publications';

	/**
	 * The menu icon.
	 *
	 * @var string
	 */
	public string $menu_icon = 'dashicons-book';

	/**
	 * Meta keys automatically registered for the post type.
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	public function get_meta(): array {
		return [
			'organizational_publication_name'    => [
				'title'            => 'Journal Name',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_publication_issue'   => [
				'title'            => 'Issue',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_publication_authors' => [
				'title'            => 'Authors',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_publication_url'     => [
				'title'            => 'URL',
				'type'             => 'string',
				'show_in_rest'     => true,
				'bindings_sources' => [],
			],
			'organizational_publication_date'    => [
				'title'            => 'Date',
				'type'             => 'number',
				'show_in_rest'     => true,
				'bindings_sources' => [
					'organizational/publication-date' => [
						'label'              => __( 'Publication date', 'organizational' ),
						'get_value_callback' => [ $this, 'get_date_source' ],
						'uses_context'       => array( 'postId' ),
					],
				],
			],
		];
	}

	/**
	 * Get binding source data for displaying a publication date.
	 *
	 * @since 2.2.0
	 *
	 * @param array     $source_args     Array containing source arguments.
	 * @param \WP_Block $block_instance  The block instance.
	 * @return string The publication date or an empty string if not available.
	 */
	public function get_date_source( array $source_args, \WP_Block $block_instance ): string {
		$post_id = $block_instance->context['postId'] ?? false;

		if ( $post_id ) {
			$date = get_post_meta( $post_id, 'organizational_publication_date', true );

			if ( ! $date ) {
				return '';
			}

			// Convert to date format set in WordPress settings.
			$date = date_i18n( get_option( 'date_format' ), $date );

			return $date;
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

		$asset_data = require_once ORGANIZATIONAL_PLUGIN_DIR . '/js/build/publication-meta/index.asset.php';

		wp_enqueue_script(
			'organizational-publication-meta',
			plugins_url( '/js/build/publication-meta/index.js', ORGANIZATIONAL_PLUGIN_FILE ),
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);
	}
}
