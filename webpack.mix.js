const mix = require( 'laravel-mix' );
const path = require( 'path' );
const pluginName = path.basename( path.dirname( '.' ) );
const pluginPath = './' + pluginName;
const resources = pluginPath + '/assets/src';

/**
 * Override underlying Webpack configuration
 * {@link https://laravel-mix.com/docs/6.0/quick-webpack-configuration}
 */
mix.webpackConfig( {
	watchOptions: {
		ignored: `./node_modules/`,
	},
} );

/**
 * Disable success notifications show only error messages
 * {@link https://laravel-mix.com/docs/6.0/os-notifications}
 */
mix.disableSuccessNotifications();

/**
 * Make jQuery available when encountered
 * {@link https://laravel-mix.com/docs/6.0/autoloading}
 */
mix.autoload( {
	jquery: [ '$', 'window.jQuery', 'jQuery' ],
} );

/**
 * Set the path for your public files
 */
mix.setPublicPath( `${ pluginPath }/assets` );

/**
 * Set your entry points for SCSS compiling
 * {@link https://laravel-mix.com/docs/6.0/sass}
 */
mix.sass( `${ resources }/scss/custom_bootstrap.scss`, `${ pluginPath }/assets/css` )
	.options( { processCssUrls: false } )
	.sourceMaps();
mix.sass( `${ resources }/scss/custom_styles.scss`, `${ pluginPath }/assets/css` )
	.options( { processCssUrls: false } )
	.sourceMaps();

/**
 * Set your entry points for JS bundling
 * {@link https://laravel-mix.com/docs/6.0/mixjs}
 */
mix.js( `${ resources }/js/issue_create_modal.js`, `${ pluginPath }/assets/js` );
mix.js( `${ resources }/js/issue_close_entry.js`, `${ pluginPath }/assets/js` );
