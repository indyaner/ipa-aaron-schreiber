# Wordpress Plugin Boilerplate

This repository should help to have starting point for a custom WordPress plugin.

## How it works
This boilerplate is meant to help develop WordPress plugins in a isolated manner. DDEV creates a folder `.local`, to which the document root for DDEV is set.
Also during the start of DDEV a symlink is created, that links the plugin root in `.local/wp-content/plugins/<your plugin name>`.

## Features
- PSR-4 auto loading with composer
- In `includes/config/PostTypes.php` you find a sample implementation for a custom post type with [Extended CPTs](https://github.com/johnbillion/extended-cpts).
- In `includes/Activator.php` you find a sample implementation for a plugin activator.
- In `includes/Deactivator.php` you find a sample implementation for a plugin deactivator.
- In `includes/Rest.php` you find a sample implementation for registering REST routes.
- In `includes/Plugin.php` you find the call stack for all Hooks and filters.

## Getting started

1. In your IDE run a string replacements to replace boilerplate names in the code. The following strings need to be replaced:
   - `WPPluginBoilerplate`: namespace for the plugin
   - `CODESS_WP_PLUGIN_BOILERPLATE` prefix for constants
   - `wp-plugin-boilerplate` references to the plugin and hostname for DDEV
   - `wp_plugin_boilerplate` references to the plugin
2. Run `ddev start`
3. Once the DDEV environment has started install WordPress with `ddev wp core download --path=.local --locale=de_DE`
4. Run `ddev composer install`