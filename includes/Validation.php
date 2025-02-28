<?php

namespace Codess\CodessGitHubIssueCreator;

class Validation {


    function sanitize_post($post): array|false {
        $sanitized_values = [];

        // Loop through each post field
        foreach ($post as $key => $value) {
            if ($key === 'nonce') {
                continue; // Skip nonce field
            }

            // Check if value is empty before proceeding with sanitization
            if (empty($value)) {
                return false; // Empty values get sanitized to empty
            } else {
                // Sanitize by stripping all HTML tags
                $sanitized_value = sanitize_text_field($value);

                // Additional sanitization to remove potential JS attempts
                $sanitized_value = $this->remove_js_content($sanitized_value);

                // Store the sanitized value if it's not empty
                $sanitized_values[$key] = $sanitized_value;

            }
        }

        return $sanitized_values;
    }


    /**
     * Function to remove any potential JavaScript or malicious code.
     * This checks for script tags and other unsafe attributes.
     *
     * @param string $input The input string to sanitize.
     * @return string The sanitized string.
     */
    /**
     * Function to remove any potential JavaScript, malicious code, or dangerous input that could
     * lead to command injection or directory traversal.
     *
     * @param string $input The input string to sanitize.
     * @return string|false The sanitized string.
     */
    function remove_js_content(string $input): string|false {
        // Remove <script> tags and their content
        $input = preg_replace('/<script.*?<\/script>/is', '', $input);

        // Remove JavaScript event attributes like "onclick", "onload", etc.
        $input = preg_replace('/\s*on\w+\s*=\s*[^>]+/is', '', $input);

        // Remove <style> tags and embedded styles
        $input = preg_replace('/<style.*?<\/style>/is', '', $input);

        // Remove inline JavaScript inside href, src attributes, etc.
        $input = preg_replace('/<.*?javascript:.*?>/is', '', $input);

        // Remove <iframe>, <object>, <embed>, <applet>, <form>, <input>, <button> tags
        $input = preg_replace('/<(iframe|object|embed|applet|form|input|button)[^>]*>/is', '', $input);

        // Remove malicious <img> tags with dangerous src attributes
        $input = preg_replace('/<img[^>]+src\s*=\s*["\'](javascript:|data:)[^"\']*["\'][^>]*>/is', '', $input);

        // Remove <a> tags with "javascript:" or "data:" in href
        $input = preg_replace('/<a[^>]+href\s*=\s*["\'](javascript:|data:)[^"\']*["\'][^>]*>/is', '', $input);

        // Remove malicious <link> or <meta> tags
        $input = preg_replace('/<(link|meta)[^>]*>/is', '', $input);

        // Remove directory traversal attempts (e.g., "../", "..\", "/..")
        $input = preg_replace('/\.\.\/|\.\.\\\/is', '', $input);

        // Remove unsafe characters that are often used in injection
        $input = preg_replace('/[\\\|;&%$#@!^*?~`><]/', '', $input);


            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');


    }

}