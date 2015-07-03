<?php
/*
Plugin Name: 	R3DF - Copyright Message
Description:    Inserts a customizable copyright message in the theme footer.
Plugin URI:		http://r3df.com/
Version: 		1.0.0
Text Domain:	r3df_copyright_message
Domain Path: 	/lang/
Author:         R3DF
Author URI:     http://r3df.com
Author email:   plugin-support@r3df.com
Copyright: 		R-Cubed Design Forge
*/


/*  Copyright 2015 R-Cubed Design Forge

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// TODO
// option for hook points
//  - wp-footer
//  - twenty* depending on theme
//  - custom
// Language files
// Uninstall


// Avoid direct calls to this file where wp core files not present
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$r3df_copyright_message = new R3DF_Copyright_Message();


/**
 * Class R3DF_Dashboard_Language
 *
 */
class R3DF_Copyright_Message {

	private $twenty_astric_themes = array(
		'twentyten',
		'twentyeleven',
		'twentytwelve',
		'twentythirteen',
		'twentyfourteen',
		'twentyfifteen',
	);

	/**
	 * Class constructor
	 *
	 */
	function __construct() {

		// Add plugin text domain hook
		add_action( 'plugins_loaded', array( $this, '_text_domain' ) );

		// Add hook for customizer
		add_action( 'customize_register', array( $this, 'customizer_options' ) );

		if ( is_admin() ) {
			// load admin css and javascript
			//add_action( 'admin_enqueue_scripts', array( $this, '_load_admin_scripts_and_styles' ) );
		} else {
			// add body classes to assist css
			add_filter( 'body_class', array( $this, 'add_body_class' ) );

			if ( in_array( wp_get_theme()->template, $this->twenty_astric_themes )  ) {
				add_action( wp_get_theme()->template . '_credits', array( $this, 'copyright_html' ) );
			} else {
				add_action( 'wp_footer', array( $this, 'copyright_html' ) );
			}

			// load css and javascript
			add_action( 'wp_enqueue_scripts', array( $this, '_load_scripts_and_styles' ) );
		}
	}

	/**
	 * Add theme name to body classes
	 *
	 * @param $body_classes - array, setting to be returned
	 *
	 * @return array
	 */
	function add_body_class( $body_classes ) {
		if ( in_array( wp_get_theme()->template, $this->twenty_astric_themes )  ) {
			$body_classes[] = 'r3df-cm-' . wp_get_theme()->template;
		}
		$options = get_option( 'r3df_copyright_message', null );
		if ( ! empty( $options['hide-pbw'] ) ) {
			$body_classes[] = 'r3df-cm-hide-pbw';
		}
		return ( $body_classes );
	}


	/**
	 * Return values for defaults, false if not set
	 *
	 * @param $default_setting - string, setting to be returned
	 *
	 * @return mixed
	 */
	function get_default( $default_setting ) {
		$defaults = apply_filters( 'r3df_copyright_message_defaults', array(
			'copyright' => '&#169; ' . date( 'Y' ) . ', ' . get_bloginfo(),
		));
		return ( isset( $defaults[ $default_setting ] ) ? $defaults[ $default_setting ] : false );
	}


	/**
	 * Add options to customizer
	 *
	 * @param $wp_customize
	 */
	function customizer_options( $wp_customize ) {

		$wp_customize->add_section( 'r3df_copyright_message_settings', array(
			'title'          => 'Copyright Message Settings',
			//'priority'       => 160,
			'description' => '<b>' . __( 'Default copyright message:', 'r3df_copyright_message' ) .'</b><br>' . $this->get_default( 'copyright' ),
		) );

		$wp_customize->add_setting( 'r3df_copyright_message[use_custom]', array(
			'default' => false,
			'type'    => 'option',
		) );

		$wp_customize->add_control( 'r3df_cm_use_custom', array(
			'section' => 'r3df_copyright_message_settings',
			'settings'   => 'r3df_copyright_message[use_custom]',
			'type'    => 'checkbox',
			'label'   => __( 'Use custom copyright message...', 'r3df_copyright_message' ),
		) );

		$wp_customize->add_setting( 'r3df_copyright_message[custom_message]', array(
			'default' => $this->get_default( 'copyright' ),
			'type'    => 'option',
		) );

		$wp_customize->add_control( 'r3df_cm_custom_message', array(
			'section' => 'r3df_copyright_message_settings',
			'settings'   => 'r3df_copyright_message[custom_message]',
			'type'    => 'text',
			'active_callback' => array( $this, 'is_custom_copyright' ),
		) );

	}


	/**
	 * Is custom copyright set
	 *
	 * @return bool
	 */
	function is_custom_copyright() {
		$options = get_option( 'r3df_copyright_message', null );
		if ( ! empty( $options['use_custom'] ) ) {
			return true;
		}
		return false;
	}


	/**
	 * Add copyright to footer
	 *
	 */
	function copyright_html() {
		$options = get_option( 'r3df_copyright_message', null );
		if ( ! empty( $options['use_custom'] ) && ! empty( $options['custom_message'] ) ) { ?>
			<span id="r3df-copyright-message"><?php echo $options['custom_message'] ?></span>
		<?php } else { ?>
			<span id="r3df-copyright-message"><?php echo $this->get_default( 'copyright' ); ?></span>
		<?php }
	}


	/* ****************************************************
	 * Utility functions
     * ****************************************************/

	/**
	 * Plugin language file loader
	 *
	 */
	function _text_domain() {
		// Load language files - files must be r3df_copyright_message-xx_XX.mo
		load_plugin_textdomain( 'r3df_copyright_message', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	}

	/**
	 * Admin scripts and styles loader
	 *
	 * @param $hook
	 *
	 */
	function _load_admin_scripts_and_styles( $hook ) {
		// Get the plugin version (added to files loaded to clear browser caches on change)
		$plugin = get_file_data( __FILE__, array( 'Version' => 'Version' ) );

		// Register and enqueue the admin css files
		wp_register_style( 'r3df_cm_admin_style', plugins_url( '/css/admin-style.css', __FILE__ ), false, $plugin['Version'] );
		wp_enqueue_style( 'r3df_cm_admin_style' );
	}

	/**
	 * Site scripts and styles loader
	 *
	 * @param $hook
	 *
	 */
	function _load_scripts_and_styles( $hook ) {
		// Get the plugin version (added to files loaded to clear browser caches on change)
		$plugin = get_file_data( __FILE__, array( 'Version' => 'Version' ) );

		// Register and enqueue the site css files
		wp_register_style( 'r3df_cm_style', plugins_url( '/css/style.css', __FILE__ ), false, $plugin['Version'] );
		wp_enqueue_style( 'r3df_cm_style' );
	}
}
