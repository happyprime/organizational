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
	 * The default name.
	 *
	 * Used to refer to the content type in the admin settings even after the
	 * name has been overridden.
	 *
	 * @var string
	 */
	public string $default_name = 'Projects';

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

	/**
	 * The slug for an additional taxonomy associated with the content type.
	 *
	 * @var string
	 */
	public string $taxonomy = 'og_project_group';

	/**
	 * The plural name of an additional taxonomy for the content type.
	 *
	 * @var string
	 */
	public string $taxonomy_plural_name = 'Project Groups';

	/**
	 * The singular name of an additional taxonomy for the content type.
	 *
	 * @var string
	 */
	public string $taxonomy_singular_name = 'Project Group';
}
