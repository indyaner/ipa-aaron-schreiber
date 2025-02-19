<?php
namespace Codess\CodessGitHubIssueCreator;

use Codess\CodessGitHubIssueCreator\config\PostTypes;
use Codess\CodessGitHubIssueCreator\admin\Admin;

/**
 * The file that defines the core plugin class
 */
class Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var      HookLoader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected HookLoader $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var      string    $version    The current version of the plugin.
	 */
	protected string $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 */
	public function __construct() {
		if ( defined( 'CODESS_GITHUB_ISSUE_CREATOR_VERSION' ) ) {
			$this->version = CODESS_GITHUB_ISSUE_CREATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}

        if ( defined( 'CODESS_GITHUB_ISSUE_CREATOR_NAME' ) ) {
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
	private function set_locale(): void
    {
		$plugin_i18n = new I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		$this->loader->add_action( 'init', $plugin_i18n, 'set_script_translations', 20 );
	}

    private function define_post_type_hooks(): void{
        $plugin_post_types = new PostTypes();

        $this->loader->add_action( 'init', $plugin_post_types, 'register_post_types' );
    }

    private function define_rest_hooks(): void
    {
        $plugin_rest = new Rest();

        $this->loader->add_action( 'rest_api_init', $plugin_rest, 'add_rest_routes');
    }

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 */
	private function define_admin_hooks(): void
    {
		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );

        // Define admin related hooks
		//$this->loader->add_action( 'before_delete_post', $plugin_admin, 'delete_something', 10, 2 );
	}

    /**
     * Register all hooks related to assets.
     *
     * @return void
     */
    public function define_enqueue_hooks(): void{
        $plugin_enqueue = new EnqueueAssets($this->version);

        $this->loader->add_action( 'enqueue_scripts', $plugin_enqueue, 'enqueue_public_assets' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_enqueue, 'enqueue_admin_assets' );
    }

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
     * @return void
	 */
	public function run(): void
    {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name(): string
    {
		return $this->plugin_name;
	}


	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version(): string
    {
		return $this->version;
	}
}
