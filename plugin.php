<?php
/**
 * Plugin Name: Organizational
 * Plugin URI: https://github.com/happyprime/organizational
 * Description: Associate people, projects, organizations, and publications.
 * Author: Happy Prime
 * Author URI: https://happyprime.co
 * Version: 2.3.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package organizational
 */

namespace HappyPrime\Organizational;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

const VERSION     = '2.3.0';
const PLUGIN_FILE = __FILE__;

require_once __DIR__ . '/vendor/autoload.php';

// Initialize the plugin.
Init::init();
