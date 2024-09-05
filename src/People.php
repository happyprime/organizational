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
	 * Meta keys automatically registered for the post type.
	 *
	 * @var array
	 */
	public array $meta = [
		'organizational_person_prefix'          => [
			'title'        => 'Prefix',
			'type'         => 'string',
			'show_in_rest' => true,
		],
		'organizational_person_first_name'      => [
			'title'        => 'First Name',
			'type'         => 'string',
			'show_in_rest' => true,
		],
		'organizational_person_last_name'       => [
			'title'        => 'Last Name',
			'type'         => 'string',
			'show_in_rest' => true,
		],
		'organizational_person_suffix'          => [
			'title'        => 'Suffix',
			'type'         => 'string',
			'show_in_rest' => true,
		],
		'organizational_person_title'           => [
			'title'        => 'Title',
			'type'         => 'string',
			'show_in_rest' => true,
		],
		'organizational_person_title_secondary' => [
			'title'        => 'Secondary Title',
			'type'         => 'string',
			'show_in_rest' => true,
		],
		'organizational_person_office'          => [
			'title'        => 'Office',
			'type'         => 'string',
			'show_in_rest' => true,
		],
		'organizational_person_email'           => [
			'title'        => 'Email',
			'type'         => 'string',
			'show_in_rest' => true,
		],
		'organizational_person_phone'           => [
			'title'        => 'Phone',
			'type'         => 'string',
			'show_in_rest' => true,
		],
	];

	/**
	 * Enqueue block editor assets used by this post type.
	 */
	public function enqueue_block_editor_assets(): void {
		if ( 'post' !== get_current_screen()->base || get_current_screen()->post_type !== $this->post_type ) {
			return;
		}

		$asset_data = require_once ORGANIZATIONAL_PLUGIN_DIR . '/js/build/people-meta/index.asset.php';

		wp_enqueue_script(
			'organizational-people-meta',
			plugins_url( '/js/build/people-meta/index.js', ORGANIZATIONAL_PLUGIN_FILE ),
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);
	}
}
