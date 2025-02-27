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
                'message' => __('There was an error with the nonce security check!', 'codess-github-issue-creator'),
            );

        } else {

            //todo add validation here to hook in and check all fields

            // check for user input
            if (!empty($_POST['title']) && !empty($_POST['description'])) {

                $current_user = wp_get_current_user();
                $username = $current_user->user_login;
                $email = $current_user->user_email;

                $description = $_POST['description'] . "\n\n";
                $description .= __('Operating System and Browser:', 'codess-github-issue-creator') . ' ' . $_POST['operating_system'] . "\n";
                $description .= __('Viewport Size:', 'codess-github-issue-creator') . ' ' . $_POST['view_port_size'] . "\n";
                $description .= __('WP User Username:', 'codess-github-issue-creator') . ' ' . $username . "\n";
                $description .= __('WP User Email:', 'codess-github-issue-creator') . ' ' . $email . "\n";
                $description .= __('Called up Page Url:', 'codess-github-issue-creator') . ' ' . $_POST['current_page_url'] . "\n";

                // initialize a new GitHubApi object
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
                    // create the issue using the provided title, description, label, and assignees
                    $result = $github->createIssue($_POST['title'], $description, [GITHUB_LABEL], [$loggedInUser]);

                    // prepare the response based on the result of the issue creation
                    $response = [
                        'status' => $result ? 'success' : 'error',
                        'message' => $result ? __('Issue created successfully!', 'codess-github-issue-creator') : __('There was an error creating the issue!', 'codess-github-issue-creator'),
                    ];
                }
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => __('There was an error creating the issue!', 'codess-github-issue-creator'),
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
        $issues = $github->getOpenIssues(GITHUB_LABEL); // Fetch issues with specific label
        if (current_user_can('manage_github_api_issues')) {
            ?>
            <div class="wrap bootstrap_wrapper">
                <h1><?= __('Reported Issues', 'codess-github-issue-creator'); ?></h1>

                <div class="row">
                    <?php


                    // Check if there are any issues
                    if ($issues && is_array($issues)) {
                        foreach ($issues as $issue) {
                            // Truncate issue body to a specified length (200 characters)
                            $body = strlen($issue['body']) > 200 ? substr($issue['body'], 0, 200) . '...' : $issue['body'];

                            // Display each issue in a responsive grid layout with multiple breakpoints
                            ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4" id="issue-<?= $issue['number']; ?>">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= $issue['title'] ?></h5>
                                        <p class="card-text"><?= $body ?></p> <!-- Display truncated body -->
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