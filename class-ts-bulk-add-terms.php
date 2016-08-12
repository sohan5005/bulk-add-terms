<?php

class TS_Bulk_Add_Terms {
	
	function __construct() {
		
		add_action( 'plugins_loaded', array( $this, 'load_textdoamin' ) );
		add_action( 'admin_menu', array( $this, 'menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_ajax_ts_bat_add_new_terms', array( $this, 'ajax_callback' ) );
		add_action( 'wp_ajax_ts_bat_show_the_terms', array( $this, 'show_ajax_callback' ) );
		add_action( 'wp_ajax_ts_bat_remove_the_terms', array( $this, 'delete_ajax_callback' ) );
		
	}
	
	function load_textdoamin() {
		load_plugin_textdomain( 'ts-bulk-add-terms', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
	
    function menu_page() {

        $this->hook_suffix = add_menu_page(
            __( 'Add bulk terms', 'ts_bat_domain' ),
            __( 'Add bulk terms', 'ts_bat_domain' ),
            'manage_categories',
            'add-bulk-terms',
            array( $this, 'admin_page_view' ),
            'dashicons-tickets-alt',
            58
        );

    }
	
    function admin_page_view() {
        
        $hide_tags = ( get_option('ts_bat_hide_nonhirearchicals') == 1 ? true : false );
        $multiple = ( get_option('ts_bat_select_multiple') == 1 ? true : false );
        $hide_real = ( get_option('ts_bat_hide_real_name') == 1 ? true : false );
        $hide_get = ( get_option('ts_bat_hide_get') == 1 ? true : false );
        $hide_remove = ( get_option('ts_bat_hide_remove') == 1 ? true : false );
        $keep_txt = ( get_option('ts_bat_dont_empty') == 1 ? ' class="keep-txt"' : '' );
        
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
                <label for="bulk_term_input"><h2><?php _e( 'Enter your terms:', 'ts_bat_domain' ); ?></h2></label>
                <textarea<?php echo $keep_txt; ?> name="bulk_term_input" id="bulk_term_input" rows="15"></textarea>
                <button type="button" id="submit_bulk_terms" class="button button-primary button-large"><?php _e( 'Add now', 'ts_bat_domain' ) ?></button>
                <?php if( ! $hide_get ) : ?>
                <button type="button" id="get_bulk_terms" class="button button-primary button-large"><?php _e( 'Get terms', 'ts_bat_domain' ) ?></button>
                <?php endif; ?>
                <?php if( ! $hide_remove ) : ?>
                <button type="button" id="remove_bulk_terms" class="button button-large"><?php _e( 'Remove terms', 'ts_bat_domain' ) ?></button>
                <?php endif; ?>
                <button type="button" id="reset_bulk_terms" class="button button-large"><?php _e( 'Reset', 'ts_bat_domain' ) ?></button>
            </div>
        </div>
        <div id="ts_bat_notice_holder"><span></span></div>
        <?php
    }
	
    function admin_scripts( $hook ) {
        if( $this->hook_suffix !== $hook ) {
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
            'confirmrem' => __( 'Are you sure you want to remove terms from selected taxonomy? (Only those terms will be deleted which are added via this plug-in)', 'ts_bat_domain' ),
            'selonetax' => __( 'Please select only one taxonomy to perform this action.', 'ts_bat_domain' ),
            'successdel' => __( 'Terms deleted successfully.', 'ts_bat_domain' ),
        );

        wp_localize_script( 'ts-bat-scripts', 'locale_strings', $locale );
    }
	
	function remove_terms( $taxonomy ) {
			
		$args = array(
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'meta_key' => '_ts_bulk_add_term',
			'meta_value' => true,
		);
		
		$terms = get_terms( $args );
		
		foreach( $terms as $term ) {
			wp_delete_term( $term->term_id, $taxonomy );
		}
		
	}
	
	function recursive_terms( $taxonomy, $parent = 0 ) {
		
		if( !isset( $this->done_terms ) ) {
			$this->done_terms = array();
		}
			
		$args = array(
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'orderby' => 'term_order',
			'parent' => $parent,
		);
		
		$terms = get_terms( $args );
		
		$str = '';

		foreach( $terms as $term ) {
			
			if( in_array( $term->term_id, $this->done_terms ) ) {
				continue;
			}
			
			$ancestors = get_ancestors( $term->term_id, $taxonomy );
			$str .= str_repeat( '-', count( $ancestors ) );
			$str .= $term->name . '~~' . $term->slug . ( $i == end( $terms ) ? "\n" : "\n" );
			
			$this->done_terms[] = $term->term_id;
			
			$str .= $this->recursive_terms( $taxonomy, $term->term_id );

		}
		
		return $str;
		
	}
	
	function r( $p ) {
		echo '<pre>';
		print_r($p);
		echo '</pre>';
	}
	
	function show_ajax_callback() {
        
        if ( isset( $_POST ) ) {

            if( !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], 'ts_bat_add_terms_ajax' ) ) {
                die();
            }
            
            $taxonomy = $_POST['taxonomy'];
			
			$data = $this->recursive_terms( $taxonomy );
			
			wp_send_json_success( $data );
            
        }
        
        die();
		
	}
	
	function delete_ajax_callback() {
        
        if ( isset( $_POST ) ) {

            if( !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], 'ts_bat_add_terms_ajax' ) ) {
                die();
            }
            
            $taxonomy = $_POST['taxonomy'];
			
			$this->remove_terms( $taxonomy );
			
			wp_send_json_success();
            
        }
        
        die();
		
	}
	
    function ajax_callback() {
        
        if ( isset( $_POST ) ) {

            if( !isset( $_POST['security'] ) || !wp_verify_nonce( $_POST['security'], 'ts_bat_add_terms_ajax' ) ) {
                die();
            }
            
            $taxonomy = $_POST['taxonomy'];
            $terms = $_POST['terms'];
            
            if( is_array( $taxonomy ) ) {
                foreach( $taxonomy as $tax ) {
                    $this->add_terms( $terms, $tax );
                }
            } else {
                $this->add_terms( $terms, $taxonomy );
            }
            
        }
        
        die();
    }
	
	
    function add_terms( $terms, $taxonomy ) {
            
        $lines = split( "\n", $terms );

        $current_lvl = 0;

        $lvl_ids = array();

        foreach( $lines as $line ) {
			
			if( trim( $line ) === '' ) {
				continue;
			}

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
				
				$slugger = explode( '~~', $sp_line );
				
				if( count( $slugger ) === 2 && trim( $slugger[0] ) !== '' && trim( $slugger[1] ) !== '' ) {
					
                    $args['slug'] = $slugger[1];
					
					$lvl_ids[$current_lvl] = wp_insert_term( $slugger[0], $taxonomy, $args );
					
				} else {
					
					$lvl_ids[$current_lvl] = wp_insert_term( $sp_line, $taxonomy, $args );
					
				}
					
				update_term_meta( $lvl_ids[$current_lvl]['term_id'], '_ts_bulk_add_term', true );

            } else {

                $current_lvl = 0;
				
				$slugger = explode( '~~', $line );
				
				if( count( $slugger ) === 2 && trim( $slugger[0] ) !== '' && trim( $slugger[1] ) !== '' ) {
					
                    $args['slug'] = $slugger[1];
					
					$lvl_ids[$current_lvl] = wp_insert_term( $slugger[0], $taxonomy, $args );
					
				} else {
					
					$lvl_ids[$current_lvl] = wp_insert_term( $line, $taxonomy, $args );
					
				}
					
				update_term_meta( $lvl_ids[$current_lvl]['term_id'], '_ts_bulk_add_term', true );

            }                

        }
        
    }
	
}