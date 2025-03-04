<?php

namespace Codess\CodessGitHubIssueCreator;

use Exception;
use Github\Exception\MissingArgumentException;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class GitHubIssue
 *
 * Handles the creation, retrieval, and closure of GitHub issues.
 * This class interacts with the GitHub API to manage issues, ensuring that
 * WordPress users can report, view, and close issues directly from the admin panel.
 *
 * Features:
 * - Create a new issue on GitHub with user-provided details.
 * - Fetch open issues from GitHub with a specific label.
 * - Close an existing issue from the WordPress admin panel.
 * - Includes nonce verification.
 *
 * @package CodessGitHubIssueCreator
 */
class GitHubIssue {

    /**
     * Handles the creation of a new GitHub issue.
     *
     * This function processes the submitted form data, validates input fields, and
     * creates a new issue on GitHub. It also appends user details such as operating system,
     * viewport size, and WordPress user info for debugging purposes.
     *
     * @return void
     * @throws MissingArgumentException If required form fields are missing.
     * @throws Exception If an unexpected error occurs during execution.
     */
    #[NoReturn] public function create(): void {
        // Security Check: Verify the nonce to prevent CSRF attacks
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'modal_report_nonce')) {
            $response = [
                'status' => 'error',
                'message' => __('There was an error with the security check!', 'codess-github-issue-creator'),
            ];
        } else {
            $validation = new Validation();
            $post = $validation->sanitize_post($_POST); // Sanitize all form inputs

            // Extract sanitized values
            $title = $post['title'] ?? '';
            $description = $post['description'] ?? '';
            $response = [];

            if(empty($title) || strlen($title) < 3 || strlen($title) > 50) {
                $response['field'][] = 'title'; // Append 'title' to 'field'
            }
            if(empty($description) || strlen($description) < 3 || strlen($description) > 50) {
                $response['field'][] = 'description';
            }

            // Input validation checks
            if (empty($title) && empty($description)) {
                $response += [
                    'status' => 'warning',
                    'message' => __('Please fill out the form', 'codess-github-issue-creator'),
                ];
            } elseif (empty($title)) {
                $response += [
                    'status' => 'warning',
                    'message' => __('Please fill out the Title field!', 'codess-github-issue-creator'),
                ];
            } elseif (empty($description)) {
                $response += [
                    'status' => 'warning',
                    'message' => __('Please fill out the Description field!', 'codess-github-issue-creator'),
                ];
            } elseif (strlen($title) < 3 || strlen($title) > 50) {
                $response += [
                    'status' => 'warning',
                    'message' => __('Title must be between 3 and 50 characters!', 'codess-github-issue-creator'),
                ];
            } elseif (strlen($description) < 3 || strlen($description) > 300) {
                $response += [
                    'status' => 'warning',
                    'message' => __('Description must be between 3 and 300 characters!', 'codess-github-issue-creator'),
                ];
            } else {
                // Initialize GitHub API
                $github = new GitHubApi();
                $current_user = wp_get_current_user();

                // Retrieve WordPress user details
                $username = $current_user->user_login;
                $email = $current_user->user_email;

                // Construct the issue description with additional debugging details
                $description_content = $description . "\n\n";
                $description_content .= __('Operating System and Browser:', 'codess-github-issue-creator') . ' ' . ($post['operating_system'] ?? '') . "\n";
                $description_content .= __('Viewport Size:', 'codess-github-issue-creator') . ' ' . ($post['view_port_size'] ?? '') . "\n";
                $description_content .= __('WP User Username:', 'codess-github-issue-creator') . ' ' . $username . "\n";
                $description_content .= __('WP User Email:', 'codess-github-issue-creator') . ' ' . $email . "\n";
                $description_content .= __('Called up Page URL:', 'codess-github-issue-creator') . ' ' . ($post['current_page_url'] ?? '') . "\n";

                // Authenticate GitHub User
                $loggedInUser = $github->get_authenticated_user();
                if ($loggedInUser === false) {
                    $response = [
                        'status' => 'error',
                        'message' => __('Authentication failed. Please try again.', 'codess-github-issue-creator'),
                    ];
                } else {
                    // Create a new issue on GitHub
                    $result = $github->create_issue($title, $description_content, [GITHUB_LABEL], [$loggedInUser]);

                    // Handle response based on GitHub API result
                    $response = [
                        'status' => $result ? 'success' : 'error',
                        'message' => $result ? __('Bug report created successfully!', 'codess-github-issue-creator') : __('There was an error creating the issue!', 'codess-github-issue-creator'),
                    ];
                }
            }
        }

        // Send the response as JSON
        $this->send_json($response);
    }

    #[NoReturn] private function send_json($response): void {
        // Send JSON response
        wp_send_json($response);
        // Always exit to prevent extra output
        wp_die();
    }

    /**
     * Closes a GitHub issue via the API.
     *
     * This function interacts with the GitHub API to close an issue. It first checks if the
     * user is authenticated and then attempts to close the issue using the provided issue ID.
     *
     * @throws Exception If there is an issue with the GitHub API request.
     */
    #[NoReturn] public function close(): void {
        $github = new GitHubApi();

        // Retrieve the authenticated user
        $loggedInUser = $github->get_authenticated_user();

        // Check if the user is authenticated
        if ($loggedInUser === false) {
            // Authentication failed, return an error response
            $response = [
                'status' => 'error',
                'message' => __('Authentication failed. Please try again.', 'codess-github-issue-creator'),
            ];
        } else {
            // Attempt to close the GitHub issue using the provided issue ID
            $result = $github->close_issue($_POST['issue_id']);

            // Prepare response based on success or failure
            $response = [
                'status' => $result ? 'success' : 'error',
                'message' => $result
                    ? ''
                    : __('There was an error closing the bug report!', 'codess-github-issue-creator'),
            ];
        }

        // Send JSON response back to the client
        $this->send_json($response);
    }

    /**
     * Renders the backend page for displaying reported issues from GitHub.
     *
     * This function fetches open issues from GitHub that match a specified label and
     * displays them in a Bootstrap accordion layout. Each issue contains a title,
     * description, and a button to close the issue. Only users with the appropriate
     * permissions can access this page.
     *
     * @return void
     * @throws Exception If there is an error while fetching issues from GitHub.
     */
    public function codess_backend_page(): void {
        $github = new GitHubApi();

        // Fetch open issues with a specific label from GitHub
        $issues = $github->get_open_issues(GITHUB_LABEL);

        // Check if the current user has permission to manage GitHub issues
        if (current_user_can('manage_github_api_issues')) {
            ?>
            <div class="wrap bootstrap_wrapper container">
                <h1><?= __('Reported Bug Reports', 'codess-github-issue-creator'); ?></h1>

                <div id="issuesAccordion">
                    <?php
                    // Verify that issues were retrieved and are in a valid array format
                    if ($issues && is_array($issues)) {
                        foreach ($issues as $index => $issue) {

                            // Generate unique IDs for the Bootstrap accordion components
                            $collapse_id = 'collapse-' . $issue['number'];
                            $heading_id = 'heading-' . $issue['number'];
                            ?>
                            <div class="accordion bg-light mb-3" id="<?= $collapse_id; ?>">
                                <!-- Issue Title -->
                                <h2 class="accordion-header bg-info" id="<?= $heading_id; ?>">
                                    <button class="accordion-button collapsed" type="button"
                                            aria-expanded="<?= $index === 0 ? 'true' : 'false'; ?>"
                                            aria-controls="<?= $collapse_id; ?>" data-bs-toggle="collapse"
                                            data-bs-target="#<?= $collapse_id; ?>">
                                        <?= esc_html($issue['title']); ?>
                                    </button>
                                </h2>

                                <!-- Issue Description & Close Button -->
                                <div class="accordion-collapse collapse" aria-labelledby="<?= $heading_id; ?>"
                                     data-bs-parent="#issuesAccordion">
                                    <div class="accordion-body">
                                        <p class="card-text"><?= esc_html($issue['body']); ?></p>

                                        <!-- Button to close the issue -->
                                        <button class="btn btn-danger close-issue"
                                                data-issue-id="<?= esc_attr($issue['number']); ?>">
                                            <?= __('Close Bug Report', 'codess-github-issue-creator') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        // Display a message if no open issues were found
                        echo '<p>' . __('No open bug reports found.', 'codess-github-issue-creator') . '</p>';
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }

}