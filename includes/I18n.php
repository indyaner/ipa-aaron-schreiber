<?php

namespace Codess\CodessGitHubIssueCreator;

/**
 * Define the internationalization functionality
 *
 */
class I18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'codess-github-issue-creator',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

    /**
     * Load the javascript translations.
     *
     * @return void
     */
    public function set_script_translations(): void {
        wp_set_script_translations( 'codess-github-issue-creator-scripts', 'codess-github-issue-creator', plugin_dir_path( dirname( __FILE__ ) ) . 'languages/' );

    }
}
