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
}
