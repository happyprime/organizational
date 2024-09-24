<?php
/**
 * Provide access to the plugin's organizational state.
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

/**
 * Provide access to the plugin's organizational state.
 */
class Organizational {
	/**
	 * The people post type.
	 *
	 * @var People
	 */
	public People $people;

	/**
	 * The entities post type.
	 *
	 * @var Entities
	 */
	public Entities $entities;

	/**
	 * The projects post type.
	 *
	 * @var Projects
	 */
	public Projects $projects;

	/**
	 * The publications post type.
	 *
	 * @var Publications
	 */
	public Publications $publications;

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Enable Shadow Terms to supported post types.
	 */
	public function register_relationships(): void {

		$relatives = [];

		if ( isset( $this->people ) ) {
			$relatives[] = $this->people->post_type;
		}

		if ( isset( $this->entities ) ) {
			$relatives[] = $this->entities->post_type;
		}

		if ( isset( $this->projects ) ) {
			$relatives[] = $this->projects->post_type;
		}

		if ( isset( $this->publications ) ) {
			$relatives[] = $this->publications->post_type;
		}

		foreach ( $relatives as $post_type ) {
			/**
			 * Filter post types that can be related to a given post type via
			 * its shadow term.
			 *
			 * @param array $relatives The related post types.
			 */
			$related = apply_filters( 'organizational_related_post_types_' . $post_type, array_diff( $relatives, [ $post_type ] ) );

			if ( empty( $related ) ) {
				continue;
			}

			add_post_type_support(
				$post_type,
				'shadow-terms',
				$related
			);
		}
	}
}
