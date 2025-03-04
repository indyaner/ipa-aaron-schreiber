<?php

namespace Codess\CodessGitHubIssueCreator;

/**
 * Fired during plugin deactivation
 */

class Deactivator {

	/**
	 * Short Description.
	 */
	public static function deactivate():void {
        self::deregister_capabilities();
	}

    /**
     *
     * D
     *
     * @return void
     */
    public static function deregister_capabilities(): void {
        // List of roles you want to add capabilities to
        $roles = ['editor', 'administrator']; // more roles can be added as needed

        // Loop through each role
        foreach ($roles as $role_name) {
            $role = get_role($role_name); // Get role object
            $role->remove_cap('manage_github_api_issues');
        }
    }

}
