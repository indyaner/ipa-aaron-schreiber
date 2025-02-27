<?php

namespace Codess\CodessGitHubIssueCreator;

use Codess\CodessGitHubIssueCreator\admin\Admin;
use Codess\CodessGitHubIssueCreator\config\PostTypes;
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
        $this->define_activation_hooks();


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
     * Register all hooks related to assets.
     *
     * @return void
     */
    public function define_enqueue_hooks(): void {
        $plugin_enqueue = new EnqueueAssets($this->version);

        $this->loader->add_action('wp_enqueue_scripts', $plugin_enqueue, 'enqueue_public_assets');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_enqueue, 'enqueue_admin_assets');

    }


    /**
     *
     */
    private function define_activation_hooks(): void {
            $activator = new Activator();
            $this->loader->add_action('after_setup_theme', $activator, 'register_capabilities');
    }


    /**
     * Run the loader to execute all the hooks with WordPress.
     *
     * @return void
     */
    public function run(): void {
        $this->loader->run();
    }

    /**
     * @param WP_Admin_Bar $admin_bar
     * @return void
     */
    function admin_bar_item(WP_Admin_Bar $admin_bar): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $admin_bar->add_menu(array(
            'id' => 'codess-github-issue-creator-adminbar-btn',
            'title' => __('Report Issue', 'codess-github-issue-creator'),
            'href' => '#', // no redirect
            'meta' => [
                'title' => __('Report Issue', 'codess-github-issue-creator'),
            ]
        ));
    }


    /**
     * @return void
     */
    function add_admin_bar_modal(): void {
        ?>
        <div class="bootstrap_wrapper">
            <dialog id="codess-issue-modal">
                <form class="modal-form" action="">
                    <div class="modal-content codess-modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header d-flex justify-content-between">
                            <h5 class="modal-title mb-3"
                                id="modalTitle"><?= __('Report Issue', 'codess-github-issue-creator') ?></h5>
                            <button type="button" class="btn-close codess-close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body">
                            <p class="text-muted"><?= __('Fill out this form to report a new GitHub Issue to Codess.', 'codess-github-issue-creator') ?></p>

                            <div class="mb-3">
                                <label for="issue_title"
                                       class="form-label"><?= __('Bug Report Title', 'codess-github-issue-creator') ?></label>
                                <input id="issue_title" type="text" name="title" class="form-control"
                                       placeholder="Enter the issue title" required>
                            </div>

                            <div class="mb-3">
                                <label for="issue_description"
                                       class="form-label"><?= __('Description', 'codess-github-issue-creator') ?></label>
                                <textarea id="issue_description" name="description" class="form-control" rows="4"
                                          placeholder="Describe the issue..." required></textarea>
                            </div>

                            <!-- Hidden Inputs -->
                            <input id="issue_operating_system" type="hidden" name="operating_system" value="">
                            <input id="issue_view_port_size" type="hidden" name="view_port_size" value="">
                            <input id="issue_current_page_url" type="hidden" name="current_page_url" value="">
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer mb-3">
                            <button type="submit" id="submit_btn_modal"
                                    class="btn btn-primary"><?= __('Report Issue', 'codess-github-issue-creator') ?></button>
                        </div>

                    </div>
                </form>
            </dialog>
        </div>
        <?php
    }


    function codess_add_admin_menu(): void {

        $git_hub_issue = new GitHubIssue();

        add_menu_page(
            __('Reported Issues', 'codess-github-issue-creator'), // Page title
            __('Reported Issues', 'codess-github-issue-creator'), // Menu title
            'manage_options', // Capability (only for admins)
            'reported-issues',// Menu slug
            array($git_hub_issue, 'codess_backend_page'),// Callback function
            'dashicons-admin-generic', // Menu icon
            80 // Position in the menu
        );
    }
}
