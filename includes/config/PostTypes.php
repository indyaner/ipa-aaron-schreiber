<?php

namespace Codess\CodessGitHubIssueCreator\config;

/**
 * The post types for plugins.
 *
 */
class PostTypes {
    /**
     * Wrapper function to register all post types related to this plugin.
     *
     * @return void
     */
    public function register_post_types(): void{
        $this->register_custom_post_type();
    }

    private function register_custom_post_type(){
        $labels = array(
            'name'                  => _x( 'Custom Posts', 'Post type plural name', 'codess-github-issue-creator' ),
            'singular_name'         => _x( 'Custom Post', 'Post type singular name', 'codess-github-issue-creator' ),
            'menu_name'             => _x( 'Custom Posts', 'Admin Menu text', 'codess-github-issue-creator' ),
            'name_admin_bar'        => _x( 'Custom Posts', 'Add New on Toolbar', 'codess-github-issue-creator' ),
            'add_new'               => __( 'Add Custom Post', 'codess-github-issue-creator' ),
            'add_new_item'          => __( 'Add New Custom Post', 'codess-github-issue-creator' ),
            'new_item'              => __( 'New Custom Post', 'codess-github-issue-creator' ),
            'edit_item'             => __( 'Edit Custom Post', 'codess-github-issue-creator' ),
            'view_item'             => __( 'View Custom Post', 'codess-github-issue-creator' ),
            'all_items'             => __( 'All Custom Posts', 'codess-github-issue-creator' ),
            'search_items'          => __( 'Search Custom Posts', 'codess-github-issue-creator' ),
            'parent_item_colon'     => __( 'Parent Custom Post:', 'codess-github-issue-creator' ),
            'not_found'             => __( 'No custom pots found.', 'codess-github-issue-creator' ),
            'not_found_in_trash'    => __( 'No custom posts found in trash.', 'codess-github-issue-creator' ),
        );

        register_extended_post_type( 'custom-post', array(

            'labels' => $labels,

            // Add the post type to the site's main RSS feed:
            'show_in_feed' => true,

            // Use the block editor:
            'show_in_rest' => true,

            'has_archive' => true,

            'menu_icon' => 'dashicons-star-filled',

            'supports' => array(
                'title',
                'editor',
                'revisions',
                'author',
                'excerpt',
                'thumbnail',
                'custom-fields'
            ),

            'admin_cols' => array(
                'title',
                'author',
                'date',
            ),

        ), array(
            // Override the base names used for labels:
            'singular' => __('Event', 'codess-github-issue-creator'),
            'plural'   => __('Events', 'codess-github-issue-creator'),
            'slug'     => __('events', 'codess-github-issue-creator'),
        ) );
    }
}
