<?php

namespace Codess\CodessGitHubIssueCreator;

use Exception;
use RuntimeException;

/**
 * Validation class for sanitizing and validating user inputs.
 *
 * This class provides methods for sanitizing incoming POST data to ensure it
 * is safe for further processing. It performs various checks and sanitization
 * techniques such as removing HTML tags, JavaScript, and malicious content
 * from user input.
 */
class Validation {

    /**
     * Sanitizes POST data to ensure all inputs are safe.
     *
     * This function processes each key-value pair from the POST data, sanitizing each value
     * by stripping out harmful content, such as HTML tags and potentially dangerous JavaScript.
     * If the input is empty, it stores it as an empty string. It also skips the nonce field
     * to avoid sanitization of the security token.
     *
     * @param array $post The POST data to be sanitized.
     *
     * @return array The sanitized POST data with all harmful content stripped out.
     *
     * @throws Exception If there is an Exception error.
     * @throws RuntimeException If there is a RuntimeException error.
     */
    function sanitize_post(array $post): array {

        try {
            // Initialize an array to store sanitized values
            $sanitized_values = [];

            // Iterate over each key-value pair in the POST data
            foreach ($post as $key => $value) {

                // Skip the 'nonce' field as it's used for security checks and doesn't require sanitization
                if ($key === 'nonce') {
                    continue;
                }

                // If the value is empty, store it as an empty string to avoid returning 'false'
                if (empty($value)) {
                    $sanitized_values[$key] = ''; // Store empty fields as empty strings
                } else {
                    // Sanitize the value by stripping all HTML tags and removing potential JS content
                    $sanitized_value = sanitize_text_field($value); // Strip HTML tags
                    $sanitized_value = $this->strip_input_content($sanitized_value); // Remove any malicious JS content

                    // Store the sanitized value in the resulting array
                    $sanitized_values[$key] = $sanitized_value;
                }
            }
        } catch (RuntimeException $e) {
            error_log("Runtime error in sanitize_post(): " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Unexpected error in sanitize_post(): " . $e->getMessage());
        }

        // Return the sanitized values array
        return $sanitized_values;
    }

    /**
     * Sanitizes the input string by removing potentially dangerous content.
     *
     * This function performs a series of regex-based replacements to remove malicious
     * content from the input string, such as JavaScript, HTML tags, unsafe attributes,
     * shell commands, and directory traversal attempts. It is intended to help prevent
     * attacks such as XSS, injection, and other forms of exploitation.
     *
     * @param string $input The input string that needs sanitization.
     *
     * @return string The sanitized string, with all dangerous content removed.
     *
     * @throws Exception If there is an Exception error.
     * @throws RuntimeException If there is a RuntimeException error.
     */
    function strip_input_content(string $input): string {
        try {
            // Remove JavaScript event attributes like "onclick", "onload", etc.
            $input = preg_replace('/\s*on\w+\s*=\s*[^>]+/is', '', $input);

            // Remove directory traversal attempts ("../", "..\", "/..") which may allow access to unauthorized files
            $input = preg_replace('/\.\.\/|\.\.\\\/is', '', $input);

            // Remove characters often used in injection attempts (shell special characters)
            $input = preg_replace('/[\\\|;&%$#@!^*?~`><]/', '', $input);

            // Remove dangerous shell command attempts like "rm -rf", "cat", "ls", etc.
            $dangerous_commands = ['rm\s+-\s*rf', 'cat', 'ls', 'mv', 'chmod', 'chown', 'wget', 'curl', 'bash', 'sudo', 'eval', 'sh', 'exec', 'unlink'];

            // Loop through the list of dangerous commands and remove any matches
            foreach ($dangerous_commands as $command) {
                $input = preg_replace('/\b' . $command . '\b/i', '', $input);
            }
        } catch (RuntimeException $e) {
            error_log("Runtime error in strip_input_content(): " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Unexpected error in strip_input_content(): " . $e->getMessage());
        }
        // Escape any remaining harmful characters by converting special characters to HTML entities
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

}