<?php
/**
 * Define the Projects post type.
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

/**
 * Define the Projects post type.
 */
class Projects extends ContentType {
	/**
	 * The post type.
	 *
	 * @var string
	 */
	public string $post_type = 'og_project';

	/**
	 * The singular name.
	 *
	 * @var string
	 */
	public string $singular_name = 'Project';

	/**
	 * The plural name.
	 *
	 * @var string
	 */
	public string $plural_name = 'Projects';

	/**
	 * The menu icon.
	 *
	 * @var string
	 */
	public string $menu_icon = 'dashicons-analytics';
}
