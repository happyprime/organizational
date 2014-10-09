# Changelog

## 0.2.1 (October 9, 2014)

* Check for theme support for `wsuwp_uc_person`, `wsuwp_uc_entity`, and `wsuwp_uc_project` before registering post types and taxonomies.
    * If one of these is registered, we leave it in the hands of the theme to decide what is supported.
    * If none of these are registered, we assume that intent is to support all.