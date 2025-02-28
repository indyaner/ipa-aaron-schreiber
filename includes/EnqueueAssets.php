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
        wp_enqueue_style('codess-github-issue-creator-custom-styles', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/custom_styles.css', array(), $this->version);
        wp_enqueue_style('codess-github-issue-creator-bootstrap-styles', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/custom_bootstrap.css', array(), $this->version);
        wp_enqueue_script('codess-github-issue-creator-scripts', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/js/issue_create_modal.js', array('jquery'), $this->version, true);

        // Localize scripts with translation strings
        wp_localize_script('codess-github-issue-creator-scripts', 'codess_github_issue_creator',
            [
                'confirm_close_message' => __('Are you sure you want to close this issue?', 'codess-github-issue-creator'),
                'title_error' => __('Title must be between 3 and 30 characters.', 'codess-github-issue-creator'),
                'description_error' => __('Description must be between 3 and 300 characters.', 'codess-github-issue-creator'),
                'field_warning' => __('Please fill out the Form', 'codess-github-issue-creator'),
                'title_warning' => __('Please fill out the Title field!', 'codess-github-issue-creator'),
                'description_warning' => __('Please fill out the Description field!', 'codess-github-issue-creator'),
                'title_success' => __('Title is correct!', 'codess-github-issue-creator'),
                'description_success' => __('Description is correct!', 'codess-github-issue-creator'),
                'malicious_error'       => __('Your input contains invalid or malicious content.', 'codess-github-issue-creator'),
            ]
        );


        wp_localize_script('codess-github-issue-creator-scripts', 'modal_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('modal_report_nonce')
        ));
    }

    /**
     * Enqueue all admin assets.
     *
     * @return void
     */
    public function enqueue_admin_assets(): void {
        // Enqueue styles
        wp_enqueue_style('codess-github-issue-creator-admin-bootstrap', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/custom_bootstrap.css', [], $this->version);
        wp_enqueue_style('codess-github-issue-creator-admin-custom', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/css/custom_styles.css', [], $this->version);

        // Enqueue scripts
        wp_enqueue_script('codess-github-issue-creator-admin-scripts', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/js/issue_create_modal.js', ['jquery'], $this->version, true);
        wp_enqueue_script('codess-github-issue-creator-close-entry', CODESS_GITHUB_ISSUE_CREATOR_URL . 'assets/js/issue_close_entry.js', ['jquery'], $this->version, true);

        // Localized script data
        $localized_data = ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('modal_report_nonce')];

        // Localize scripts with translation strings
        wp_localize_script('codess-github-issue-creator-admin-scripts', 'codess_github_issue_creator',
            [
                'confirm_close_message' => __('Are you sure you want to close this issue?', 'codess-github-issue-creator'),
                'title_error' => __('Title must be between 3 and 30 characters.', 'codess-github-issue-creator'),
                'description_error' => __('Description must be between 3 and 300 characters.', 'codess-github-issue-creator'),
                'field_warning' => __('Please fill out the Form', 'codess-github-issue-creator'),
                'title_warning' => __('Please fill out the Title field!', 'codess-github-issue-creator'),
                'description_warning' => __('Please fill out the Description field!', 'codess-github-issue-creator'),
                'title_success' => __('Title is correct!', 'codess-github-issue-creator'),
                'description_success' => __('Description is correct!', 'codess-github-issue-creator'),
                'malicious_error'       => __('Your input contains invalid or malicious content.', 'codess-github-issue-creator'),
            ]
        );

        // Localize scripts with translation strings
        wp_localize_script('codess-github-issue-creator-admin-scripts', 'codess_github_issue_close',
            [
                'confirm_close_message' => __('Are you sure you want to close this issue?', 'codess-github-issue-creator'),
            ]
        );

        wp_localize_script('codess-github-issue-creator-admin-scripts', 'modal_ajax', $localized_data);
        wp_localize_script('codess-github-issue-creator-admin-scripts', 'close_ajax', $localized_data);
    }
}
