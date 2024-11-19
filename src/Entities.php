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
	 * The default name.
	 *
	 * Used to refer to the content type in the admin settings even after the
	 * name has been overridden.
	 *
	 * @var string
	 */
	public string $default_name = 'Entities';

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

	/**
	 * The slug for an additional taxonomy associated with the content type.
	 *
	 * @var string
	 */
	public string $taxonomy = 'og_entity_group';

	/**
	 * The plural name of an additional taxonomy for the content type.
	 *
	 * @var string
	 */
	public string $taxonomy_plural_name = 'Entity Groups';

	/**
	 * The singular name of an additional taxonomy for the content type.
	 *
	 * @var string
	 */
	public string $taxonomy_singular_name = 'Entity Group';
}
