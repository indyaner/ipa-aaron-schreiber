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
		wp_enqueue_style('codess-github-issue-creator-styles', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/custom_bootstrap.css', array(), $this->version );
        wp_enqueue_style('codess-github-issue-creator-styles', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/custom_styles.css', array(), $this->version );
        wp_enqueue_script('codess-github-issue-creator-scripts', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/js/issue_create_modal.js', array('jquery'), $this->version, true );

        $nonce = wp_create_nonce('modal_report_nonce');

        wp_localize_script( 'codess-github-issue-creator-scripts', 'modal_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => $nonce
        ));
	}

	/**
	 * Enqueue all admin assets.
     *
     * @return void
	 */
	public function enqueue_admin_assets(): void {
        wp_enqueue_style('codess-github-issue-creator-admin-styles', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/custom_bootstrap.css', array(), $this->version );
        wp_enqueue_style('codess-github-issue-creator-admin-styles', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/custom_styles.css', array(), $this->version );
        wp_enqueue_script('codess-github-issue-creator-admin-scripts', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/js/issue_create_modal.js', array('jquery'), $this->version, true );
        wp_enqueue_script('codess-github-issue-creator-scripts', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/js/issue_close_entry.js', array('jquery'), $this->version, true );

        $modal_nonce = wp_create_nonce('modal_report_nonce');
        $close_nonce = wp_create_nonce('modal_report_nonce');


        wp_localize_script( 'codess-github-issue-creator-admin-scripts', 'modal_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => $modal_nonce
        ));


        wp_localize_script( 'codess-github-issue-creator-admin-scripts', 'close_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => $close_nonce
        ));
    }
}
