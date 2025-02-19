<?php

namespace Codess\CodessGitHubIssueCreator;

use \WP_REST_Response;

/**
 * Defines the REST functionalities for the plugin.
 *
 */
class Rest {

    /**
     * The rest base for the plugin.
     *
     * @var string
     */
    protected string $rest_base;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        $this->rest_base = 'codess-github-issue-creator';

    }

    /**
     * A wrapper function to register all REST routes for the plugin.
     *
     * @return void
     */
    public function add_rest_routes(): void{
        register_rest_route( $this->rest_base.'/v1', 'version', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_version'),
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            }
        ) );
    }

    /**
     * Get the plugin version and wrap it in a WP_REST_Response.
     *
     * @return WP_REST_Response
     */
    public function get_version(): WP_REST_Response
    {
        return new WP_REST_Response(array('version' => CODESS_GITHUB_ISSUE_CREATOR_VERSION), 200);
    }
}
