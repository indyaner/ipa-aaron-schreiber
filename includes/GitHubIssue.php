<?php

namespace Codess\CodessGitHubIssueCreator;

/**
 *
 */
class GitHubIssue {

    /**
     * @return void
     */
    function codess_backend_page(): void { // Todo let this generate content from the github issues
        ?>
        <div class="wrap">
            <h1><?= __('Codess Plugin Settings', 'codess-github-issue-creator'); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields('codess_settings_group'); // Security fields
                do_settings_sections('codess-settings'); // Display sections
                submit_button(); // Save button
                ?>
            </form>
        </div>
        <?php
    }

}