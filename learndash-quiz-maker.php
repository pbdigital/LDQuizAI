<?php
// create plugin skeleton base from https://github.com/pbdigital/streaks-addon
/**
 * Plugin Name: LearnDash Quiz Maker
 * Plugin URI:
 * Description: Create quizzes for LearnDash
 * Version: 1.0.0
 * Author: PBDigital
 * Author URI: https://pbdigital.com.au
 * License: GPL2
 * Text Domain: learndash-quiz-maker
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/pbdigital/LDQuizAI',
	__FILE__,
	'learndash-ai-quiz-maker'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');



// Define plugin constants
define( 'LDM_VERSION', '1.0.0' );
define( 'LDM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LDM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LDM_PLUGIN_FILE', __FILE__ );

// Include the main LearnDash Quiz Maker class.
if ( ! class_exists( 'LearnDash_Quiz_Maker' ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-learndash-quiz-maker.php';
}

/**
 * Main instance of LearnDash Quiz Maker.
 *
 * Returns the main instance of LearnDash Quiz Maker to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return LearnDash_Quiz_Maker
 */
function LearnDash_Quiz_Maker() {
    return LearnDash_Quiz_Maker::instance();
}

// Global for backwards compatibility.
$GLOBALS['learndash-quiz-maker'] = LearnDash_Quiz_Maker();

// Activation and deactivation hooks
register_activation_hook( __FILE__, array( 'LearnDash_Quiz_Maker', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'LearnDash_Quiz_Maker', 'deactivate' ) );
