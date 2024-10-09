<?php
/**
 * be-blue-astra-child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package be-blue-astra-child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_BE_BLUE_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'be-blue-astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_BE_BLUE_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );



require_once 'api.php';

require_once 'shortcodes.php';

require_once 'automations.php';

add_action( 'wp_head', 'add_header_custom_scripts' );

function add_header_custom_scripts() {

	?>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
	<?php
	
}



add_action( 'wp_footer', 'add_footer_custom_scripts' );

function add_footer_custom_scripts() {

	?>
		<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
	<?php

	echo '<script type="module" src="' . get_stylesheet_directory_uri() . '/assets/js/dashboard.js"></script>';

}




