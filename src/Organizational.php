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
}
