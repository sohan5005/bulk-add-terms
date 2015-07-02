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

require_once( dirname(__FILE__) . '/includes/options.php' );

add_action( 'admin_menu', 'ts_register_add_bulk_term_page' );
add_action( 'admin_enqueue_scripts', 'ts_bat_admin_scripts' );
add_action( 'wp_ajax_ts_bat_add_new_terms', 'ts_bat_add_new_terms_callback' );
add_action( 'plugins_loaded', 'ts_bat_load_text_domain' );

if( !function_exists( 'ts_bat_load_text_domain' ) ):
    function ts_bat_load_text_domain() {
        load_plugin_textdomain( 'ts-bulk-add-terms', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
    }
endif;

if( !function_exists( 'ts_register_add_bulk_term_page' ) ):
    function ts_register_add_bulk_term_page() {

        add_menu_page(
            __( 'Add bulk terms', 'ts_bat_domain' ),
            __( 'Add bulk terms', 'ts_bat_domain' ),
            'manage_categories',
            'add-bulk-terms',
            'ts_visualize_add_bulk_term_page',
            'dashicons-tickets-alt',
            58
        );

    }
endif;

if( !function_exists( 'ts_bat_admin_scripts' ) ):
    function ts_bat_admin_scripts( $hook ) {
        if( 'toplevel_page_add-bulk-terms' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'ts-bat-styles', plugins_url( '/assets/css/ts-bat-main.css', __FILE__ ) );
        wp_enqueue_script( 'ts-bat-scripts', plugins_url( '/assets/js/ts-bat-main.js', __FILE__ ), array( 'jquery' ), 1.0, true );
        $locale = array(
            'success' => __( 'Congrats! The terms are added successfully!', 'ts_bat_domain' ),
            'failed' => __( 'Something went wrong... are you sure you have enough permission to add terms?', 'ts_bat_domain' ),
            'notax' => __( 'Please select a taxonomy first!', 'ts_bat_domain' ),
            'noterm' => __( 'Please input some terms!', 'ts_bat_domain' ),
            'confirm' => __( 'Are you sure you want to add these terms?', 'ts_bat_domain' ),
        );

        wp_localize_script( 'ts-bat-scripts', 'locale_strings', $locale );
    }
endif;

if( !function_exists( 'ts_visualize_add_bulk_term_page' ) ):
    function ts_visualize_add_bulk_term_page() {
        
        $hide_tags = ( get_option('ts_bat_hide_nonhirearchicals') == 1 ? true : false );
        $multiple = ( get_option( 'ts_bat_select_multiple' ) == 1 ? true : false );
        $hide_real = ( get_option( 'ts_bat_hide_real_name' ) == 1 ? true : false );
        $keep_txt = ( get_option( 'ts_bat_dont_empty' ) == 1 ? ' class="keep-txt"' : '' );
        
        $query_args = array(
            'public' => true,
            'show_ui' => true
        );
        
        $all_tax = get_taxonomies( $query_args, 'objects' );
        
        wp_nonce_field( 'ts_bat_add_terms_ajax', 'ts_bat_add_terms_ajax_security' );
        
        ?>
        <div class="ts-bat-wrapper">
            <div class="ts-bat-select-tax-to-add-terms">
                <h2><?php _e( 'Select taxonomy:', 'ts_bat_domain'); ?></h2>
                <?php
                $i = 0;
                foreach( $all_tax as $tax => $args ) {
                    
                    $real = ( $hide_real ? '' : ' (' . $tax  . ')');
                    $type = ( $multiple ? 'checkbox' : 'radio' );
                    $output = sprintf( '<input type="%4$s" name="ts_bat_taxonomy_select" id="%1$s" value="%1$s"><label for="%1$s">%2$s%3$s</label>', $tax, $args->label, $real, $type );
                    if( $i !== count( $all_tax ) - 1 ) {
                        $output .= '<br>';
                    }
                    
                    if( $hide_tags ) {
                        if( $args->hierarchical == 1 ) {
                            echo $output;
                        }
                    } else {
                        echo $output;
                    }
                    
                    $i++;
                    
                }
                ?>
            </div>
            <div class="ts-bat-enter-your-terms">
                <label for="bulk_term_input"><h2><?php _e( 'Enter your terms:', 'ts_bat_domain'); ?></h2></label>
                <textarea<?php echo $keep_txt; ?> name="bulk_term_input" id="bulk_term_input" rows="15"></textarea>
                <button type="button" id="submit_bulk_terms" class="button button-primary button-large">Add now</button>
                <button type="button" id="reset_bulk_terms" class="button button-large">Reset</button>
            </div>
        </div>
        <div id="ts_bat_notice_holder"><span></span></div>
        <?php
    }
endif;

if( !function_exists( 'ts_bat_add_new_terms_callback' ) ) :
    function ts_bat_add_new_terms_callback() {
        
        if ( isset($_REQUEST) ) {

            if( !isset( $_REQUEST['security'] ) || !wp_verify_nonce( $_REQUEST['security'], 'ts_bat_add_terms_ajax' ) ) {
                return;
            }
            
            $taxonomy = $_REQUEST['taxonomy'];
            $terms = $_REQUEST['terms'];
            
            if( is_array( $taxonomy ) ) {
                foreach( $taxonomy as $tax ) {
                    ts_bat_add_this_terms_to_that_tax( $terms, $tax );
                }
            } else {
                ts_bat_add_this_terms_to_that_tax( $terms, $taxonomy );
            }
            
        }
        
        die();
    }
endif;

if( !function_exists( 'ts_bat_add_this_terms_to_that_tax' ) ) :
    function ts_bat_add_this_terms_to_that_tax( $terms, $taxonomy ) {
            
        $lines = split( "\n", $terms );

        $current_lvl = 0;

        $lvl_ids = array();

        foreach( $lines as $line ) {

            $the_line = trim( preg_replace( "![\r\n]+!", '', $line ) );

            $args = array();

            $splits = preg_split( "/^\-+/", $line );

            if( isset( $splits[1] ) ) {

                $sp_line = $splits[1];

                preg_match( "/^\-+/", $line, $indentors );

                $level = strlen( $indentors[0] );

                if( $level - 1 ===  $current_lvl ) {

                    $args = array( 'parent' => $lvl_ids[$level - 1]['term_id'] );

                    $current_lvl++;

                } else {

                    $args = array( 'parent' => $lvl_ids[$level - 1]['term_id'] );
                    
                    $current_lvl = $level;
                    
                }

                $lvl_ids[$current_lvl] = wp_insert_term( $sp_line, $taxonomy, $args );

            } else {

                $lvl_ids[0] = wp_insert_term( $line, $taxonomy, $args );

                $current_lvl = 0;

            }                

        }
        
    }
endif;