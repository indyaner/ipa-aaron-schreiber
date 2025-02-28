<?php

namespace Codess\CodessGitHubIssueCreator;

use Exception;
use Github\Exception\MissingArgumentException;
use JetBrains\PhpStorm\NoReturn;

/**
 *
 */
class GitHubIssue {


    /**
     *
     *
     * @return void
     * @throws MissingArgumentException
     * @throws Exception
     */
    #[NoReturn] public function create(): void {

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'modal_report_nonce')) {
            // If the nonce is invalid, do not proceed
            $response = array(
                'status' => 'error',
                'message' => __('There was an error with the security check!', 'codess-github-issue-creator'),
            );

        } else {

            $validation = new Validation();
            $post = $validation->sanitize_post($_POST);

            // check for user input
            if ($post !== false) {
                // Initialize the response variable
                $response = [];


                // Initialize a new GitHubApi object
                $github = new GitHubApi();
                $current_user = wp_get_current_user();
                $username = $current_user->user_login;
                $email = $current_user->user_email;

                $description_content = $post['description'] . "\n\n";
                $description_content .= __('Operating System and Browser:', 'codess-github-issue-creator') . ' ' . $post['operating_system'] . "\n";
                $description_content .= __('Viewport Size:', 'codess-github-issue-creator') . ' ' . $post['view_port_size'] . "\n";
                $description_content .= __('WP User Username:', 'codess-github-issue-creator') . ' ' . $username . "\n";
                $description_content .= __('WP User Email:', 'codess-github-issue-creator') . ' ' . $email . "\n";
                $description_content .= __('Called up Page Url:', 'codess-github-issue-creator') . ' ' . $post['current_page_url'] . "\n";

                // Check the conditions for different scenarios
                switch (true) {
                    case empty($post['title']) && empty($post['description']):
                        $response = [
                            'status' => 'warning',
                            'message' => __('Please fill out the Form', 'codess-github-issue-creator'),
                        ];
                        break;

                    case empty($post['title']):
                        $response = [
                            'status' => 'warning',
                            'message' => __('Please fill out the Title field!', 'codess-github-issue-creator'),
                        ];
                        break;

                    case empty($post['description']):
                        $response = [
                            'status' => 'warning',
                            'message' => __('Please fill out the Description field!', 'codess-github-issue-creator'),
                        ];
                        break;

                    case strlen($post['title']) < 3 || strlen($post['title']) > 30:
                        $response = [
                            'status' => 'warning',
                            'message' => __('Title must be between 3 and 30 characters!', 'codess-github-issue-creator'),
                        ];
                        break;

                    case strlen($post['description']) < 3 || strlen($post['description']) > 300:
                        $response = [
                            'status' => 'warning',
                            'message' => __('Description must be between 3 and 300 characters!', 'codess-github-issue-creator'),
                        ];
                        break;

                    default:
                        // Check for user authentication
                        $loggedInUser = $github->getAuthenticatedUser();
                        if ($loggedInUser === false) {
                            $response = [
                                'status' => 'error',
                                'message' => __('Authentication failed. Please try again.', 'codess-github-issue-creator'),
                            ];
                        } else {
                            // Create the issue using the provided title, description, label, and assignees
                            $result = $github->createIssue($post['title'], $description_content, [GITHUB_LABEL], [$loggedInUser]);

                            // Prepare the response based on the result of the issue creation
                            $response = [
                                'status' => $result ? 'success' : 'error',
                                'message' => $result ? __('Issue created successfully!', 'codess-github-issue-creator') : __('There was an error creating the issue!', 'codess-github-issue-creator'),
                            ];
                        }
                        break;
                }
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => __('There was an error with your inputs!', 'codess-github-issue-creator'),
                );
            }
        }


        $this->send_json($response);

    }

    #[NoReturn] private function send_json($response): void {
        // Send JSON response
        wp_send_json($response);
        // Always exit to prevent extra output
        wp_die();
    }

    /**
     *
     *
     * @throws Exception
     */
    public function close(): void {

        $github = new GitHubApi();

        // get the authenticated user
        $loggedInUser = $github->getAuthenticatedUser();

        // check if the user is authenticated
        if ($loggedInUser === false) {
            // handle the error: User authentication failed
            $response = [
                'status' => 'error',
                'message' => __('Authentication failed. Please try again.', 'codess-github-issue-creator'),
            ];
        } else {
            $result = $github->closeIssue($_POST['issue_id']);

            $response = [
                'status' => $result ? 'success' : 'error',
                'message' => $result ? __('Issue closed successfully!', 'codess-github-issue-creator') : __('There was an error closing the issue!', 'codess-github-issue-creator'),
            ];
        }

        $this->send_json($response);
    }

    /**
     * Renders the backend page for displaying reported issues from GitHub.
     *
     * This function fetches open issues from GitHub based on the specified label and displays
     * them in a responsive grid format. Each issue includes a title, body, and a delete button to remove it.
     *
     * @return void
     * @throws Exception
     */
    public function codess_backend_page(): void {
        $github = new GitHubApi();
        $issues = $github->getOpenIssues(GITHUB_LABEL); // Fetch issues with a specific label
        if (current_user_can('manage_github_api_issues')) {
            ?>
            <div class="wrap bootstrap_wrapper container">
                <h1><?= __('Reported Issues', 'codess-github-issue-creator'); ?></h1>

                <div id="issuesAccordion">
                    <?php
                    // Check if there are any issues
                    if ($issues && is_array($issues)) {
                        foreach ($issues as $index => $issue) {

                            // Generate unique IDs for each issue
                            $accordion_id = 'issue-' . $issue['number'];
                            $collapse_id = 'collapse-' . $issue['number'];
                            $heading_id = 'heading-' . $issue['number'];
                            ?>
                            <div class="accordion bg-light mb-3" id="<?= $collapse_id; ?>">
                                <h2 class="accordion-header bg-info" id="<?= $heading_id; ?>">
                                    <button class="accordion-button collapsed" type="button"
                                            aria-expanded="<?= $index === 0 ? 'true' : 'false'; ?>"
                                            aria-controls="<?= $collapse_id; ?>" data-bs-toggle="collapse"
                                            data-bs-target="#<?= $collapse_id; ?>">
                                        <?= $issue['title']; ?>
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse " aria-labelledby="<?= $heading_id; ?>"
                                     data-bs-parent="#issuesAccordion">
                                    <div class="accordion-body">
                                        <p class="card-text"><?= $issue['body']; ?></p> <!-- Display truncated body -->
                                        <button class="btn btn-danger delete-issue"
                                                data-issue-id="<?= $issue['number']; ?>"><?= __('Delete Issue', 'codess-github-issue-creator') ?></button>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p>' . __('No issues found with the specified label.', 'codess-github-issue-creator') . '</p>';
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }

}