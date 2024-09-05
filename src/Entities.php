<?php
/**
 * Define the Entities post type.
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

/**
 * Define the Entities post type.
 */
class Entities extends ContentType {
	/**
	 * The post type.
	 *
	 * @var string
	 */
	public string $post_type = 'og_entity';

	/**
	 * The singular name.
	 *
	 * @var string
	 */
	public string $singular_name = 'Entity';

	/**
	 * The plural name.
	 *
	 * @var string
	 */
	public string $plural_name = 'Entities';

	/**
	 * The menu icon.
	 *
	 * @var string
	 */
	public string $menu_icon = 'dashicons-groups';
}
