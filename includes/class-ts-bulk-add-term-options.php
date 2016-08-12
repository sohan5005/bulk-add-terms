<?php

class TS_Bulk_Add_Terms_Options {
	
	function __construct() {
		
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'options' ) );
		
	}
	
	function register_page() {
		
        add_options_page(
            __( 'Bulk Add Terms Settings', 'ts_bat_domain' ),
            __( 'Bulk Add Terms', 'ts_bat_domain' ),
            'manage_options',
            'bulk_add_terms_settings',
			array( $this, 'page_callback' )
        );
		
	}
	
	function page_callback() {
		
        echo sprintf( '<h1>%s</h1>', __( 'Bulk Add Terms Settings', 'ts_bat_domain' ) );
        ?>
        <form action="options.php" method="post">
        <?php settings_fields('bulk_add_terms_settings'); ?>
        <?php do_settings_sections('bulk_add_terms_settings'); ?>

        <input name="Submit" type="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form></div>
        <?php
		
	}
	
	function options() {

        add_settings_section(
            'ts_bat_setting_section',
            __( 'Use these options to control the visual and interaction of the Bulk Add Term screen', 'ts_bat_domain' ),
            array( $this, 'section_callback' ),
            'bulk_add_terms_settings'
        );

        $options = array(
            array(
                'name' => 'ts_bat_hide_nonhirearchicals',
                'title' => __( 'Hide Non-hierarchical taxonomies', 'ts_bat_domain' ),
                'desc' => __( 'This option will hide all taxonomies that can not have children. A great example is "post_tags".', 'ts_bat_domain' ),
            ),
            array(
                'name' => 'ts_bat_select_multiple',
                'title' => __( 'Multiple taxonomies at a time', 'ts_bat_domain' ),
                'desc' => __( 'Enabling this option will give you a chance to select multiple taxonomies to add same terms at same time.', 'ts_bat_domain' ),
            ),
            array(
                'name' => 'ts_bat_hide_real_name',
                'title' => __( 'Hide the real name of taxonomy', 'ts_bat_domain' ),
                'desc' => __( 'Enable this option to hide the register name of taxonomies on the add screen. For example, without this option you will see "Tags (post_tag)" wich is default. On the other hand after turning this option on, you will see only actual label "Tags"', 'ts_bat_domain' ),
            ),
            array(
                'name' => 'ts_bat_hide_get',
                'title' => __( 'Hide "Get terms" button', 'ts_bat_domain' ),
                'desc' => __( 'This button is extremely helpful when you need to copy a lot of terms from one taxonomy to another. You\'ll get a full list of available terms from the selected taxonomy.', 'ts_bat_domain' ),
            ),
            array(
                'name' => 'ts_bat_hide_remove',
                'title' => __( 'Hide "Remove terms" button', 'ts_bat_domain' ),
                'desc' => __( 'This button is extremely helpful when you need to copy a remove all the terms that were added by this plugin. This will work on a selected taxonomy.', 'ts_bat_domain' ),
            ),
            array(
                'name' => 'ts_bat_dont_empty',
                'title' => __( 'Do not empty the form please!', 'ts_bat_domain' ),
                'desc' => __( 'Enable this option to keep all the texts (terms) in the field after a successful addition of terms', 'ts_bat_domain' ),
            ),
        );

        foreach( $options as $option ) {

            add_settings_field(
                $option['name'],
                $option['title'],
                array( $this, 'field_callback' ),
                'bulk_add_terms_settings',
                'ts_bat_setting_section',
                array(
                    $option['name'],
                    $option['desc'],
                )
            );

            register_setting( 'bulk_add_terms_settings', $option['name'] );

        }
		
	}
	
    function section_callback() {
        echo '<hr>';
    }
	
    function field_callback( $args ) {
        $val = checked( 1, get_option( $args[0] ), false );
        echo sprintf( '<input name="%1$s" id="%1$s" type="checkbox" value="1" class="code" %2$s /><p class="description">%3$s</p>', $args[0], $val, $args[1] );
    }
	
}

new TS_Bulk_Add_Terms_Options;