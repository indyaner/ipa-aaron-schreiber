# Codess Github Issue Creator

This plugin allows a logged in User to Create, Read and Close GitHub Issues.
After installing this plugin initially you need to add these constants to your wp-config:
   - `GITHUB_PAT`: the personal access token
   - `GITHUB_OWNER` the name of the owner of the repository
   - `GITHUB_REPOSITORY` the name of the repository
   - `GITHUB_LABEL` the name of the label that will identify user reported issues
To add a constant do it like this in the wp-config: define('CONSTANT_KEY', 'constant_value');

## Wordpress Plugin Boilerplate

This repository should help to have starting point for a custom WordPress plugin.

### How it works
This boilerplate is meant to help develop WordPress plugins in a isolated manner. DDEV creates a folder `.local`, to which the document root for DDEV is set.
Also during the start of DDEV a symlink is created, that links the plugin root in `.local/wp-content/plugins/<your plugin name>`.

### Features
- PSR-4 auto loading with composer
- In `includes/config/PostTypes.php` you find a sample implementation for a custom post type with [Extended CPTs](https://github.com/johnbillion/extended-cpts).
- In `includes/Activator.php` you find a sample implementation for a plugin activator.
- In `includes/Deactivator.php` you find a sample implementation for a plugin deactivator.
- In `includes/Rest.php` you find a sample implementation for registering REST routes.
- In `includes/Plugin.php` you find the call stack for all Hooks and filters.

### Getting started

1. In your IDE run a string replacements to replace boilerplate names in the code. The following strings need to be replaced:
   - `CodessGitHubIssueCreator`: namespace for the plugin
   - `CODESS_GITHUB_ISSUE_CREATOR` prefix for constants
   - `codess-github-issue-creator` references to the plugin and hostname for DDEV
   - `codess_github_issue_creator` references to the plugin
2. Run `ddev start`
3. Once the DDEV environment has started install WordPress with `ddev wp core download --path=.local --locale=de_DE`
4. Run `ddev composer install`
