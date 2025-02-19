<?php

namespace Codess\CodessGitHubIssueCreator;

/**
 * Class to enqueue and deregister styles and scripts
 */

class EnqueueAssets {

	/**
	 * Current plugin version
	 *
	 * @var string $version
	 */
	protected string $version;

	/**
	 * Set version depending on the environment
	 *
	 * @param $version
	 */
	public function __construct($version) {
        $this->version = match (wp_get_environment_type()) {
            'local', 'development', 'staging' => time(),
            default => $version,
        };

	}

	/**
	 * Enqueue all public assets.
     *
     * @return void
	 */
	public function enqueue_public_assets(): void {
		wp_enqueue_style('codess-github-issue-creator-styles', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/styles.css', array(), $this->version );
        wp_enqueue_script('codess-github-issue-creator-scripts', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/js/scripts.js', array('jquery'), $this->version, true );
	}

	/**
	 * Enqueue all admin assets.
     *
     * @return void
	 */
	public function enqueue_admin_assets(): void {
        wp_enqueue_style('codess-github-issue-creator-admin-styles', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/admin-styles.css', array(), $this->version );
        wp_enqueue_script('codess-github-issue-creator-admin-scripts', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/js/admin-scripts.js', array('jquery'), $this->version, true );
	}
}
