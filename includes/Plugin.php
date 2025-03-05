<?php

namespace Codess\CodessGitHubIssueCreator;

use Codess\CodessGitHubIssueCreator\admin\Admin;
use Codess\CodessGitHubIssueCreator\config\PostTypes;
use Exception;
use WP_Admin_Bar;

/**
 * The file that defines the core plugin class
 */
class Plugin {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var      HookLoader $loader Maintains and registers all hooks for the plugin.
     */
    protected HookLoader $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected string $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var      string $version The current version of the plugin.
     */
    protected string $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @throws Exception
     */
    public function __construct() {
        if (defined('CODESS_GITHUB_ISSUE_CREATOR_VERSION')) {
            $this->version = CODESS_GITHUB_ISSUE_CREATOR_VERSION;
        } else {
            $this->version = '1.0.0';
        }

        if (defined('CODESS_GITHUB_ISSUE_CREATOR_NAME')) {
            $this->plugin_name = CODESS_GITHUB_ISSUE_CREATOR_NAME;
        } else {
            $this->plugin_name = 'codess-github-issue-creator';
        }

        $this->loader = new HookLoader();
        $this->set_locale();
        $this->define_post_type_hooks();
        $this->define_rest_hooks();
        $this->define_admin_hooks();
        $this->define_enqueue_hooks();


    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * @return void
     */
    private function set_locale(): void {
        $plugin_i18n = new I18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
        $this->loader->add_action('init', $plugin_i18n, 'set_script_translations', 20);
    }


    private function define_post_type_hooks(): void {
        $plugin_post_types = new PostTypes();

        $this->loader->add_action('init', $plugin_post_types, 'register_post_types');
    }


    private function define_rest_hooks(): void {
        $plugin_rest = new Rest();

        $this->loader->add_action('rest_api_init', $plugin_rest, 'add_rest_routes');
    }

    /**
     * Register all the hooks related to the admin area functionality
     * of the plugin.
     *
     */
    private function define_admin_hooks(): void {
        $plugin_admin = new Admin($this->get_plugin_name(), $this->get_version());

        // Define admin related hooks
        $this->loader->add_action('admin_bar_menu', $this, 'admin_bar_item', 500);

        // can only be used in the wp frontend
        $this->loader->add_action('wp_footer', $this, 'add_admin_bar_modal');

        // can only be used in the wp backend
        $this->loader->add_action('admin_footer', $this, 'add_admin_bar_modal');

        //
        $this->loader->add_action('admin_menu', $this, 'codess_add_admin_menu');


        $git_hub_issue = new GitHubIssue();
        add_action('wp_ajax_create_issue', array($git_hub_issue, 'create'));

        add_action('wp_ajax_close_issue', array($git_hub_issue, 'close'));

    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name(): string {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version(): string {
        return $this->version;
    }

    /**
     * Register all hooks related to asset enqueuing.
     *
     * This method registers the hooks necessary to enqueue public and admin assets for the plugin.
     * It ensures that the required CSS, JavaScript, and other assets are properly loaded for both the front-end
     * and admin areas of WordPress. The hooks are registered using the plugin's loader class.
     *
     * @return void
     *
     * @throws Exception If an error occurs while registering the enqueue hooks.
     *
     * @since 1.0.0-dev
     */
    public function define_enqueue_hooks(): void {
        try {
            $plugin_enqueue = new EnqueueAssets($this->version);

            // Register public assets enqueue hook
            $this->loader->add_action('wp_enqueue_scripts', $plugin_enqueue, 'enqueue_public_assets');

            // Register admin assets enqueue hook
            $this->loader->add_action('admin_enqueue_scripts', $plugin_enqueue, 'enqueue_admin_assets');
        } catch (Exception $e) {
            // Log the exception and provide helpful debugging information
            error_log('Error registering enqueue hooks: ' . $e->getMessage());
        }
    }


    /**
     * Runs the loader to execute all the registered hooks with WordPress.
     *
     * This method triggers the execution of all hooks that have been registered in the loader. It is responsible
     * for ensuring that all actions and filters are properly hooked into WordPress at the appropriate time.
     * It will be called once all the necessary hooks have been registered in the plugin.
     *
     * @return void
     *
     * @throws Exception If there is an error while running the loader or executing hooks.
     *
     * @since 1.0.0-dev
     */
    public function run(): void {
        try {
            // Execute all the registered hooks with WordPress
            $this->loader->run();
        } catch (Exception $e) {
            // Handle the exception if any error occurs while running the loader
            error_log('Error running the loader: ' . $e->getMessage());
        }
    }

    /**
     * Adds a custom "Report Issue" item to the WordPress admin bar for users with appropriate permissions.
     *
     * This function checks if the current user has the capability to manage options (administrator and editor),
     * and adds a custom admin bar item with the title "Report Issue". The item does not perform a redirect when clicked.
     *
     * @param WP_Admin_Bar $admin_bar The WP_Admin_Bar instance to which the menu item will be added.
     *
     * @return void
     * @throws Exception If an error occurs when adding the item to the admin bar.
     *
     * @since 1.0.0-dev
     */
    function admin_bar_item(WP_Admin_Bar $admin_bar): void {
        try {
            // Check if the user has the 'manage_github_api_issues' capability
            if (!current_user_can('manage_github_api_issues')) {
                return;
            }

            // Add the "Report Issue" menu item to the admin bar
            $admin_bar->add_menu(array(
                'id' => 'codess-github-issue-creator-adminbar-btn', // Unique ID for the item
                'title' => __('Report Bug', 'codess-github-issue-creator'), // Displayed title
                'href' => '#', // No redirect action
                'meta' => [
                    'title' => __('Report Bug', 'codess-github-issue-creator'), // Title for the tooltip
                    'class' => 'codess-github-issue-creator-adminbar-btn', // Custom class
                ]
            ));

        } catch (Exception $e) {
            // Log the exception error for debugging or admin purposes
            error_log('Error adding custom admin bar item: ' . $e->getMessage());
        }
    }

    /**
     * Adds a custom admin menu page to the WordPress admin panel for viewing reported issues.
     *
     * This function adds a menu page under the admin menu to view the reported issues via the `GitHubIssue` class.
     * The page is accessible to users with the 'manage_github_api_issues' capability (administrator and editor). The menu page will
     * use the `codess_backend_page` method of the `GitHubIssue` class to display the issues.
     *
     * @return void
     * @throws Exception If the GitHub issues page cannot be added due to any error.
     *
     * @since 1.0.0-dev
     */
    function codess_add_admin_menu(): void {
        try {
            // Instantiate the GitHubIssue class to use its method as a callback
            $git_hub_issue = new GitHubIssue();

            // Add the custom menu page to the WordPress admin menu
            add_menu_page(
                __('Reported Bug Reports', 'codess-github-issue-creator'), // Page title
                __('Reported Bug Reports', 'codess-github-issue-creator'), // Menu title
                'manage_github_api_issues', // Capability
                'reported-issues', // Menu slug
                array($git_hub_issue, 'codess_backend_page'), // Callback function
                'dashicons-list-view', // Menu icon
                300 // Position in the menu
            );

        } catch (Exception $e) {
            // Log the exception error for debugging purposes
            error_log('Error adding admin menu: ' . $e->getMessage());
        }
    }


    /**
     * Displays the modal form for reporting a new GitHub issue.
     *
     * This function outputs HTML for a Bootstrap-styled modal that allows users to submit bug reports or GitHub issues.
     * The modal contains form fields for the issue title, description, and some hidden fields to capture operating system,
     * viewport size, and the current page URL. The modal is displayed within a wrapper that utilizes Bootstrap's styling.
     *
     * The modal also includes a submit button that will trigger the process of reporting the issue once the form is filled out.
     *
     * @return void
     *
     * @since 1.0.0-dev
     */
    function add_admin_bar_modal(): void {
        ?>
        <div class="bootstrap_wrapper">
            <dialog id="codess-issue-modal">
                <form class="modal-form" action="">
                    <div class="modal-content codess-modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header d-flex justify-content-between align-items-start">
                            <h5 class="modal-title mb-3"
                                id="modalTitle"><?= __('Report Bug', 'codess-github-issue-creator') ?></h5>
                            <button type="button" class="btn-close codess-close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body">
                            <p class="text-muted"><?= __('Fill out this form to report a new Bug to Codess.', 'codess-github-issue-creator') ?></p>

                            <div class="mb-3">
                                <label for="issue_title"
                                       class="form-label form_reuqired_asterisk"><?= __('Bug Report Title', 'codess-github-issue-creator') ?></label>
                                <input id="issue_title" type="text" name="title" class="form-control"
                                       placeholder="<?= __('Enter the bug title', 'codess-github-issue-creator') ?>..." required>
                                <p class="text-muted"><?= __('Please enter between 3 and 50 characters.', 'codess-github-issue-creator') ?></p>

                            </div>

                            <div class="mb-3">
                                <label for="issue_description"
                                       class="form-label form_reuqired_asterisk"><?= __('Description', 'codess-github-issue-creator') ?></label>
                                <textarea id="issue_description" name="description" class="form-control" rows="4"
                                          placeholder="<?= __('Describe the bug', 'codess-github-issue-creator') ?>..." required></textarea>
                                <p class="text-muted"><?= __('Please enter between 3 and 300 characters.', 'codess-github-issue-creator') ?></p>
                            </div>

                            <!-- Hidden Inputs -->
                            <input id="issue_operating_system" type="hidden" name="operating_system" value="">
                            <input id="issue_view_port_size" type="hidden" name="view_port_size" value="">
                            <input id="issue_current_page_url" type="hidden" name="current_page_url" value="">
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer mb-3">
                            <button type="submit" id="submit_btn_modal"
                                    class="btn btn-primary"><?= __('Report Bug', 'codess-github-issue-creator') ?></button>
                        </div>
                    </div>
                </form>
            </dialog>
        </div>
        <?php
    }
}
