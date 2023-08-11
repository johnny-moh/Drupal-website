# INTRODUCTION

This module extends the admin toolbar for a better content administration experience. This includes:
* Extending the top "Content" menu by listing all content types.
* Allows for grouping content types into "collections".
* Adds a "Categories" top menu item, that lists the vocabularies.
* Adds a "Media" top menu item, that lists the different media types and direct access to the media library.
* Allows for customised content views exposed filters per content type.
* List recent items per content type (number of items configurable).

Works with GIN admin theme (see recommended modules)

# REQUIREMENTS

This module requires the following modules:

* Admin Toolbar (https://www.drupal.org/project/admin_toolbar)

# RECOMMENDED MODULES

- Gin theme (https://www.drupal.org/project/gin)

# INSTALLATION

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/node/1897420 for further information.

## Upgrading to version 2.x:
* hook_collections now replaces and combines:
  * hook_content_type_collections => handled by 'content' plugin
  * hook_vocabularies_collections => handled by 'categories' plugin
  * hook_menus_collections => handled by 'menus' plugin
* css classes have changed and are more uniformly named:
  * collection link:
    * admin-toolbar-content--collection
    * admin-toolbar-content--collection--\<plugin-id>
    * admin-toolbar-content--collection--\<plugin-id>-\<collection>
  * item link
    * admin-toolbar-content--edit-item
    * admin-toolbar-content--edit-item--\<item-id>
  * item add link
    * admin-toolbar-content--add-new-item
    * admin-toolbar-content--add-new-item--\<item-id>
  * recent items
    * admin-toolbar-content--recent-items
    * admin-toolbar-content--recent-items--\<item-id>
  * recent item link
    * admin-toolbar-content--recent-item
    * admin-toolbar-content--recent-item--\<item-id>
  * more recent items link
    * admin-toolbar-content--more-recent-items
    * admin-toolbar-content--more-recent-items--\<item-id>
* admin_toolbar_content.settings.yml structure has changed, so check configuration settings and save them again.

# CONFIGURATION

## General usage

After installing the module, menu's should be updated. If you experience performance issues,
you can alter the amount of recent items shown or disable the feature
by setting the config to 0 (zero).

Configuration is found under the standard "User Interface" configuration item.
/admin/config/user-interface/admin-toolbar-content

# MAINTAINERS

Current maintainers:
* Kris Booghmans (kriboogh) - https://www.drupal.org/u/kriboogh
* Stef De Waele (Stefdewa) - https://www.drupal.org/u/stefdewa

This project has been sponsored by:
* Calibrate
  In the past fifteen years, we have gained large expertise in
  consulting organizations in various industries.
  https://www.calibrate.be
