<?php

namespace Codess\CodessGitHubIssueCreator;

use Exception;
use Github\AuthMethod;
use Github\Client;
use Github\Exception\InvalidArgumentException;
use Github\Exception\MissingArgumentException;
use Github\Exception\RuntimeException;

/**
 * IssueManager class
 *
 * This class provides a simple interface to interact with the GitHub API using a Personal Access Token (PAT).
 * It allows various GitHub-related operations, including authentication, issue creation, fetching user details,
 * and listing issues for a specific repository. The class leverages the `knplabs/github-api` package to make requests
 * to the GitHub API and handle responses.
 *
 * Authentication is done via the `authenticate` method using the provided PAT, and this class expects the
 * GitHub repository owner and repository name to be defined as constants (`GITHUB_OWNER`, `GITHUB_REPOSITORY`).
 *
 * @since 1.0.0
 */
class IssueManager {

    /**
     * @var string The GitHub Personal Access Token (PAT) used for authentication.
     * This token should have appropriate permissions to access the repository.
     */
    private string $token = GITHUB_PAT;

    /**
     * @var string The GitHub repository owner's username.
     * This property represents the user or organization who owns the repository.
     */
    private string $owner = GITHUB_OWNER;

    /**
     * @var string The name of the GitHub repository.
     * This should be the name of the repository you are working with.
     */
    private string $repo = GITHUB_REPOSITORY;

    /**
     * @var object The instance of the GitHub API client.
     * This client is used to interact with the GitHub API and perform various operations such as
     * fetching user information, creating issues, etc.
     */
    private object $client;

    /**
     * Constructor for initializing the GitHub API client and authenticating it.
     *
     * This constructor creates a new instance of the GitHub API client and attempts to authenticate
     * the client using a personal access token. If authentication fails, it catches various exceptions
     * and logs them to an internal log for debugging purposes.
     *
     * Exceptions caught:
     * @throws InvalidArgumentException If a argument is Invalid (token).
     * @throws RuntimeException If there is a GitHub API issue.
     * @throws Exception If an unexpected error occurs.
     *
     * @since 1.0.0-dev
     */
    public function __construct() {
        try {
            // Initialize the GitHub API client
            $this->client = new Client();

            // Authenticate using the personal access token
            $this->client->authenticate($this->token, '', AuthMethod::ACCESS_TOKEN);
        } catch (InvalidArgumentException $e) {
            // Logs invalid argument errors (incorrect token format)
            error_log("Authentication failed: " . $e->getMessage());
        } catch (RuntimeException $e) {
            // Logs any GitHub API-related errors
            error_log("GitHub API error: " . $e->getMessage());
        } catch (Exception $e) {
            // Logs any unexpected errors
            error_log("Unexpected error: " . $e->getMessage());
        }
    }

    /**
     * Fetches the currently authenticated GitHub user.
     *
     * This method attempts to retrieve the information about the currently authenticated user
     * from GitHub. If successful, it returns the login name of the authenticated user. If an error occurs
     * during the API request, the error is logged, and the method returns `false`.
     *
     * @return string|false The login name of the authenticated user as a string,
     *                      or `false` if the request fails or an error occurs.
     *
     * @throws RuntimeException If there is an issue with the GitHub API request.
     * @throws Exception If any unexpected error occurs while fetching user details.
     *
     * @since 1.0.0-dev
     */
    public function get_authenticated_user(): string|false {
        try {
            // Fetches the authenticated users details
            return $this->client->api('current_user')->show()['login'];
        } catch (RuntimeException $e) {
            // Logs any GitHub API-related errors
            error_log("GitHub API error: " . $e->getMessage());
        } catch (Exception $e) {
            // Logs any unexpected errors
            error_log("Unexpected error: " . $e->getMessage());
        }
        // Return false if an error occurs
        return false;
    }

    /**
     * Creates a new issue in a specified GitHub repository.
     *
     * This method interacts with the GitHub API to create a new issue on the specified repository.
     * It accepts parameters for the issue's title, body, labels, and assignees, and handles any
     * exceptions that occur during the API request. If an error occurs, it will be logged for debugging.
     *
     * @param string $title The title of the issue.
     * @param string $body The body of the issue.
     * @param array $labels An array of labels to assign to the issue.
     * @param array $assignees An array of assignees to assign to the issue.
     *
     * @return bool Returns true if the issue was created successfully, or false if an error occurred.
     *
     * @throws MissingArgumentException If there is a GitHub API issue.
     * @throws RuntimeException If there is a GitHub API issue.
     * @throws Exception If an unexpected error occurs.
     *
     * @since 1.0.0-dev
     */
    public function create_issue(string $title, string $body, array $labels = [], array $assignees = []): bool {
        // Exception handling
        try {
            // Creates a new GitHub Issue
            $this->client->api('issue')->create($this->owner, $this->repo, [
                'title' => $title,
                'body' => $body,
                'labels' => $labels,
                'assignees' => $assignees,
            ]);
            return true; // Return true if no exceptions were thrown above
        } catch (MissingArgumentException $e) {
            error_log("Missing Argument error: " . $e->getMessage());
        } catch (RuntimeException $e) {
            error_log("GitHub API error: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
        }
        return false; // always return false if an error occurs
    }

    /**
     * Closes an issue in the specified GitHub repository.
     *
     * This method interacts with the GitHub API to close an issue by its ID in the current repository.
     * It returns `true` if the issue is successfully closed, or `false` if an error occurs.
     *
     * @param int $issueId The ID of the issue to be closed.
     *
     * @return bool Returns `true` if the issue was successfully closed, or `false` if an error occurs.
     *
     * @throws RuntimeException If the GitHub API returns a runtime error.
     * @throws Exception If an unexpected error occurs.
     *
     * @since 1.0.0-dev
     */
    public function close_issue(int $issueId): bool {
        try {
            // Attempt to close the issue using the GitHub API
            $this->client->api('issue')->update($this->owner, $this->repo, $issueId, [
                'state' => 'closed', // Set the issue state to 'closed'
            ]);
            return true;
        } catch (RuntimeException $e) {
            error_log("GitHub API error: " . $e->getMessage()); // logs the API error internally
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage()); // catches any other issues
        }
        return false; // return false if an error occurs
    }

    /**
     * Retrieves all open issues from the specified GitHub repository.
     *
     * This method interacts with the GitHub API to fetch open issues for the current repository.
     * It returns an array of issues if the request is successful. If an error occurs (either
     * a GitHub API error or an unexpected issue), it logs the error and returns false.
     *
     * @return array|false Returns an array of issues if successful, or false if an error occurs.
     *
     * @throws RuntimeException If the GitHub API returns a runtime error.
     * @throws Exception If an unexpected error occurs.
     *
     * @since 1.0.0-dev
     */
    public function get_open_issues(string $label): array|false {
        // exception handling
        try {
            // Fetch issues filtered by the provided label
            return $this->client->api('issue')->all($this->owner, $this->repo, [
                'labels' => $label, // passing the label as a query parameter
            ]);
        } catch (RuntimeException $e) {
            error_log("GitHub API error: " . $e->getMessage()); // logs the API error internally
        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage()); // catches any other issues
        }
        return false; // always return false if an error occurs
    }

}