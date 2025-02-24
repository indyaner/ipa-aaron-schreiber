<?php


/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://codess.media
 *
 * @wordpress-plugin
 * Plugin Name:       Codess GitHub Issue Creator
 * Plugin URI:        https://codess.media
 * Description:       Für das erstellen und die ansicht von GitHub issues, über die GitHub API.
 * Version:           1.0.0-dev
 * Author:            Codess media GmbH
 * Author URI:        https://codess.media
 * License:           Copyright
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       codess-github-issue-creator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include the composer autoloader
require_once( __DIR__ . '/vendor/autoload.php');

use Codess\CodessGitHubIssueCreator\Activator;
use Codess\CodessGitHubIssueCreator\Deactivator;
use Codess\CodessGitHubIssueCreator\Plugin;
use Github\Client;

// Define the URL to the plugin root.
define( 'CODESS_GITHUB_ISSUE_CREATOR_URL', plugin_dir_url(__FILE__) );

// Define constant for plugin name.
define( 'CODESS_GITHUB_ISSUE_CREATOR_NAME', 'codess-github-issue-creator' );

// Define constant for current plugin version.
define( 'CODESS_GITHUB_ISSUE_CREATOR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 *
 * @return void
 */
function activate_codess_github_issue_creator(): void {
	Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * @return void
 */
function deactivate_codess_github_issue_creator(): void {
	Deactivator::deactivate();
}

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'activate_codess_github_issue_creator');
register_deactivation_hook( __FILE__, 'deactivate_codess_github_issue_creator');

/**
 * Begin plugin execution.
 *
 * @return void
 */
function run_plugin(): void{
	$plugin = new Plugin();
	$plugin->run();
}

run_plugin();



$token = GITHUB_PAT;
$owner = GITHUB_OWNER;
$repo = GITHUB_REPOSITORY;

$client = new Client();
$client->authenticate($token, '', Client::AUTH_ACCESS_TOKEN);

$issues = $client->api('issue')->all($owner, $repo);

foreach ($issues as $issue) {
    echo "Issue #" . $issue['number'] . ": " . $issue['title'] . "\n";
    echo "URL: " . $issue['html_url'] . "\n\n";
}
