# Changelog

## 2.1.0 (October 17, 2024)

* Register a block binding source to render a `mailto:` link for a person's email address.

## 2.0.1 (September 24, 2024)

* Add support for Shadow Terms.

## 2.0.0 (September 5, 2024)

* Everything is new again.

## 1.0.0 (September 8, 2020)

* Forked as "Organizational" from the WSUWP University Center plugin.

## 0.9.3 (August 8, 2017)

* Ad filter to disable project output on other objects.

## 0.9.2 (August 3, 2017)

* Add filters to disable project and person meta boxes.

## 0.9.1 (July 26, 2017)

* Add a filter to enable/disable built in taxonomies.
* Remove unused taxonomy registration filters.

## 0.9.0 (July 26, 2017)

* Provide ability to filter the post type used for each center object.

## 0.8.1 (February 20, 2017)

* Add support for the `offset` attribute in shortcodes.
* Add support for `excerpt` and `full` output.

## 0.8.0 (November 10, 2016)

* Provide ability to filter by object association in shortcodes.
* Wrap name in anchor/link when headlines output is used.
* Add an automatic fallback for site URL to the current site.
* Provide more explicit nonce handling for object URL.
* Add a nonce to the save of object associations.

## 0.7.1 (November 8, 2016)

* Bug: Ensure class global is `$global` before starting.

## 0.7.0 (November 1, 2016)

* Add `wsuwp_uc_people` shortcode, extending WSUWP Content Syndicate.
* Add `wsuwp_uc_entities` shortcode, extending WSUWP Content Syndicate.
* Add `wsuwp_uc_publications` shortcode, extending WSUWP Content Syndicate.
* Add `wsuwp_uc_projects` shortcode, extending WSUWP Content Syndicate.

## 0.6.6 (July 1, 2015)

* Use the proper label for publications when adding default content to another post type.

## 0.6.5 (July 1, 2015)

* Ensure the proper labels assigned to each post type are used when displaying meta boxes.

## 0.6.4 (June 2, 2015)

* Provide a filter to modify the list of people output during content generation on another post type.
* Avoid recreating unique identifiers during an import.

## 0.6.3 (April 30, 2015)

* Provide a wrapper to retrieve an object's associated objects.
* Allow for fabricated object types to be pulled as associated objects.

## 0.6.2 (April 29, 2015)

* Avoid pagination on taxonomy archive pages.
* Sort topics and entity types by title (by default).

## 0.6.1 (April 29, 2015)

* Avoid collision between object IDs when displaying previously saved object associations.

## 0.6.0 (April 29, 2015)

* Make more methods public so that plugins and themes are able to extend object relationships using the logic provided by this plugin.

## 0.5.4 (April 16, 2015)

* Provide support for categories and tags on all object types.

## 0.5.3 (February 4, 2015)

* Fix bug where publicly used `save_object_url()` method was private.

## 0.5.2 (January 16, 2015)

* Capture an ID for projects.
* Move URL capture into the standard "information" area on people and projects.

## 0.5.1 (January 14, 2015)

* Sort people by last name in an archive view. This requires that every person has a last name entered.

## 0.5.0 (January 6, 2015)

* Provide `wsuwp_uc_get_meta()` to retrieve meta values for university objects using friendly field names.
* Capture suffix as part of a person's name details.
* Capture a secondary title as part of a person's information.

## 0.4.0 (January 6, 2015)

* Capture more information about people.

## 0.3.1 (December 8, 2014)

* Sort archives for people, entities, and projects by title. Publications sort by date.
* Set `posts_per_page` to 2000 for people, entities, projects, and publications.

## 0.3.0 (December 5, 2014)

* Introduce publications as a content type. Publications can be assigned to people, entities, and projects.
* Add a meta box to capture URLs for all content types.
* Add a meta box to capture email address for people.
* Add support for author to the people content type.
* Hide the automatic display of related object headings of related objects of that type are not assigned.
* Add filters to support the renaming of object type and taxonomy labels.
* Add settings fields to general settings to change the singular and plural names for all object types.
	* Note that changes here may break existing URL structures as the singular name will become a part of the URL.

## 0.2.2 (November 10, 2014)

* Introduce an upgrade routine to handle tasks such as rewrite rules flushing.
* Resolve issue with missing array index.

## 0.2.1 (October 9, 2014)

* Check for theme support for `wsuwp_uc_person`, `wsuwp_uc_entity`, and `wsuwp_uc_project` before registering post types and taxonomies.
    * If one of these is registered, we leave it in the hands of the theme to decide what is supported.
    * If none of these are registered, we assume that intent is to support all.
