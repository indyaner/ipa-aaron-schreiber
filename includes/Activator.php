<?php

namespace Codess\CodessGitHubIssueCreator;

/**
 * Fired during plugin activation
 */
class Activator {

    /**
     * Load the dependencies.
     */
    public function __construct() {
        $this->load_dependencies();
    }

    /**
     * Load the required dependencies for the plugin activation.
     *
     * @return void
     */
    private function load_dependencies(): void {
        // require_once plugin_dir_path(dirname(__FILE__)) . 'includes/Utils.php';
    }

    /**
     * Run activation code
     */
    public static function activate(): void {
        $plugin_activator = new Activator();
        $current_version = $plugin_activator->get_version();

        self::register_capabilities();

        if (!$current_version) {
            // Set plugin version in options
            $plugin_activator->set_version();
        } else if (version_compare(CODESS_GITHUB_ISSUE_CREATOR_VERSION, $current_version, '>')) {
            //Run code for update maintenance if current version is higher than the installed

            // Update plugin version in options
            $plugin_activator->set_version();
        }
    }

    /**
     * Get the version saved in the WordPress options.
     *
     * @return string|bool
     */
    private function get_version(): string|bool {
        $plugin_options = get_option('codess_github_issue_creator_options');

        if (!$plugin_options) {
            return false;
        }

        if (array_key_exists('version', $plugin_options)) {
            return $plugin_options['version'];
        }

        return false;
    }

    /**
     * Save the plugin version in the WordPress options
     *
     * @return bool
     */
    private function set_version(): bool {
        $current_version = $this->get_version();

        if (!$current_version) {
            // Add new option for plugin
            $plugin_options = array(
                'version' => CODESS_GITHUB_ISSUE_CREATOR_VERSION,
            );

            return add_option('codess_github_issue_creator_options', $plugin_options);
        } else {
            // Update version in current options
            $plugin_options = get_option('codess_github_issue_creator_options');
            $plugin_options['version'] = CODESS_GITHUB_ISSUE_CREATOR_VERSION;

            return add_option('codess_github_issue_creator_options', $plugin_options);
        }
    }

    /**
     *
     *
     * @return void
     */
    public static function register_capabilities(): void {
        // list of roles you want to add capabilities to
        $roles = ['editor', 'administrator']; // add more roles if needed
        // loop through each role
        foreach ($roles as $role_name) {
            $role = get_role($role_name); // get role object
            if(!$role->has_cap('manage_github_api_issues')) {
                $role->add_cap('manage_github_api_issues');
            }
        }
    }
}
