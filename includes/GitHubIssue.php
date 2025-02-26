<?php

namespace Codess\CodessGitHubIssueCreator;

use Github\AuthMethod;
use Github\Client;
use JetBrains\PhpStorm\NoReturn;

/**
 *
 */
class GitHubIssue {

    /**
     * @var string
     */
    private string $token = GITHUB_PAT;

    /**
     * @var string
     */
    private string $owner = GITHUB_OWNER;

    /**
     * @var string
     */
    private string $repo = GITHUB_REPOSITORY;

    /**
     * @var object|Client
     */
    private object $client;


    /**
     *
     */
    public function __construct() {
        $this->client = new Client();
        $this->client->authenticate($this->token, '', AuthMethod::ACCESS_TOKEN);
    }

    /**
     *
     *
     * @return void
     */
    #[NoReturn] public function create(): void {

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'modal_report_nonce')) {
            // If the nonce is invalid, do not proceed
            $response = array(
                'status' => 'error',
                'message' => 'There was an error with the nonce security check!',
            );

        } else {

            //todo add validation here to hook in and check all fields

            // check for user input
            if (!empty($_POST['title']) && !empty($_POST['description'])) {

                $current_user = wp_get_current_user();
                $username = $current_user->user_login;
                $email = $current_user->user_email;

                $description = $_POST['description'] . "\n\n";
                $description .= $_POST['operating_system'] . "\n";
                $description .= $_POST['view_port_size'] . "\n";
                $description .= __('Username:', 'codess-github-issue-creator') . ' ' . $username . "\n";
                $description .= __('Username:', 'codess-github-issue-creator') . ' ' . $email . "\n";
                $description .= __('Called up Page Url:', 'codess-github-issue-creator') . ' ' . $_POST['current_page_url'] . "\n";

                $issueData = [
                    'title' => $_POST['title'],
                    'body' => $description,
                    'labels' => array(GITHUB_LABEL),
                    'assignees' => [$this->client->api('current_user')->show()['login']],
                ];

                // Create the issue using the GitHub API client
                $issue = $this->client->api('issue')->create($this->owner, $this->repo, $issueData);
                $message = "Issue created: " . $issue['html_url']; // Show the URL of the newly created issue

                $response = array(
                    'status' => 'success',
                    'message' => 'Issue created successfully!',
                );
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'There was an error creating the issue!',
                );
            }
        }


        // Send JSON response
        wp_send_json($response);
        // Always exit to prevent extra output
        wp_die();
    }

    /**
     * @return void
     */
    public function codess_backend_page(): void { // Todo let this generate content from the github issues


        $issues = $this->client->api('issue')->all($this->owner, $this->repo);


        foreach ($issues as $issue) {
            //var_dump($issue);
            echo '<div class="bootstrap_wrapper">';
            echo '<div class="container mt-4">';
            echo '<p class="mb-2 btn btn-primary">Issue #' . $issue['number'] . ': ' . $issue['title'] . '</p>';
            echo '<p class="mb-2">Description: ' . $issue['body'] . '</p>';
            echo '</div>';
        }


        ?>
        <div class="wrap">
            <h1><?= __('Reported Issues', 'codess-github-issue-creator'); ?></h1>


        </div>

        <?php
    }

}