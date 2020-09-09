# Organizational

A WordPress plugin to associate people, projects, organizations, and publications.

People from organizations work together on projects and publish their research. This plugin makes it easy to associate these 4 content types with each other. Through a set of filters it's possible to customize this completely to change the included content types and redefine them entirely.

By default, Organizational provides 4 custom post types that are common to a center, institute, or other organization at a university:

* People
* Organization (entity)
* Project
* Publication

Once associated with each other, lists of these objects will appear on their respective individual front-end views that show the association.

## Theme support

All content types will show up in all themes by default. No explicit theme support is required. It is possible to limit the number of content types supported by the theme by explicitly registering theme support. If at least one content type is explicitly added, the content types not explicitly added will no longer appear.

* `add_theme_support( 'organizational_person' )`
* `add_theme_support( 'organizational_project' )`
* `add_theme_support( 'organizational_entity' )`
* `add_theme_support( 'organizational_publication' )`

## Filters

Content type filters are `false` by default. Use the filter to return a `string` containing a post type's slug to replace that content type with one of your own.

* `organizational_people_content_type`
* `organizational_project_content_type`
* `organizational_entity_content_type`
* `organizational_publication_content_type`

Other filters are provided to modify the names used when registering the plugin's built in content types. For example, the word "Publication" is used by default for the publication post type. This can be changed to "Paper" or "Abstract" using the provided filter.

* `organizational_project_type_names`
* `organizational_people_type_names`
* `organizational_entity_type_names`
* `organizational_publication_type_names`

The plugin provides 2 taxonomies by default, topics and entity types. Filters can be used to disable these taxonomies:

* `organizational_topic_taxonomy_enabled`
* `organizational_entity_type_taxonomy_enabled`

When a list of associated objects is displayed on another object's view, a filter can be used to determine which of those associated objects should be listed (if any at all).

* `organizational_people_to_add_to_content`
