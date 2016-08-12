<?php
/**
 * Plugin Name: Bulk add terms
 * Description: This plugin will help you to add multiple taxonomy terms in one go. Ajax is used to add terms.
 * Version:     1.2
 * Author:      Sohan Zaman
 * Author URI:  https://github.com/sohan5005
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /lang
 * Text Domain: ts_bat_domain
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once( dirname(__FILE__) . '/includes/class-ts-bulk-add-term-options.php' );
require_once( dirname(__FILE__) . '/class-ts-bulk-add-terms.php' );

new TS_Bulk_Add_Terms;