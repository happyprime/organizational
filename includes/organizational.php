<?php

global $organizational, $organizational_meta;

$organizational_meta = new Organizational_Meta();
$organizational      = new Organizational();

/**
 * Provides a helper function for grabbing the meta data stored for people.
 *
 * @param int    $post_id ID of the person.
 * @param string $field   Friendly field name for the meta being requested.
 *
 * @return bool|mixed
 */
function organizational_get_meta( $post_id, $field ) {
	global $organizational_meta;
	return $organizational_meta->get_meta( $post_id, $field );
}

/**
 * Return the content type slug for the object type being queried.
 *
 * @param string $content_type Should be one of people, publication, entity, or project.
 *
 * @return string
 */
function organizational_get_object_type_slug( $content_type ) {
	global $organizational;

	if ( 'people' === $content_type ) {
		return $organizational->people_content_type;
	}

	if ( 'publication' === $content_type ) {
		return $organizational->publication_content_type;
	}

	if ( 'entity' === $content_type ) {
		return $organizational->entity_content_type;
	}

	if ( 'project' === $content_type ) {
		return $organizational->project_content_type;
	}

	return '';
}

/**
 * Retrieve a list of content type slugs for registered content types by this plugin.
 *
 * @return array
 */
function organizational_get_object_type_slugs() {
	global $organizational;
	return $organizational->get_object_type_slugs();
}

/**
 * Retrieve all of the items from a specified content type with their unique ID,
 * current post ID, and name.
 *
 * @param string $object_type The custom post type slug.
 *
 * @return array|bool Array of results or false if incorrectly called.
 */
function organizational_get_all_object_data( $object_type ) {
	global $organizational;
	return $organizational->get_all_object_data( $object_type );
}

/**
 * Clean posted object ID data so that any IDs passed are sanitized and validated as not empty.
 *
 * @param array  $object_ids    List of object IDs being associated.
 * @param string $strip_from_id Text to strip from an object's ID.
 *
 * @return array Cleaned list of object IDs.
 */
function organizational_clean_post_ids( $object_ids, $strip_from_id = '' ) {
	global $organizational;
	return $organizational->clean_posted_ids( $object_ids, $strip_from_id );
}

/**
 * Retrieve the list of projects associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function organizational_get_object_projects( $post_id = 0 ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $organizational->project_content_type );
}

/**
 * Retrieve the list of people associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function organizational_get_object_people( $post_id = 0 ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $organizational->people_content_type );
}

/**
 * Retrieve the list of entities associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function organizational_get_object_entities( $post_id = 0 ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $organizational->entity_content_type );
}

/**
 * Retrieve the list of publications associated with an object.
 *
 * @param int $post_id
 *
 * @return array
 */
function organizational_get_object_publications( $post_id = 0 ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $organizational->publication_content_type );
}

/**
 * Wrapper method to retrieve a list of objects from an object type associated with the requested object.
 *
 * @param int         $post_id          ID of the object currently being used.
 * @param string      $object_type      Slug of the object type to find.
 * @param bool|string $base_object_type Slug of the object type to use as the base of this
 *                                      query to support additional fabricated object types.
 *                                      False if base object type should inherit the passed
 *                                      object_type parameter.
 *
 * @return array List of objects associated with the requested object.
 */
function organizational_get_object_objects( $post_id, $object_type, $base_object_type = false ) {
	global $organizational;
	return $organizational->get_object_objects( $post_id, $object_type, $base_object_type );
}
