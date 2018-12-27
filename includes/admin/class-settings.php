<?php

namespace Pluginever\WCSerialNumbers\Admin;
class Settings {
    private $settings_api;

    function __construct() {
        $this->settings_api = new \Ever_Settings_API();
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    }

    function admin_init() {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        add_submenu_page( 'tools', 'Settings', 'Settings', 'manage_options', 'wc_serial_numbers-settings', array(
            $this,
            'settings_page'
            ) );
    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'wc_serial_numbers_settings',
                'title' => __( 'Settings', 'wc-serial-numbers' )
            ),
        );
        return apply_filters( 'wc_serial_numbers_settings_sections', $sections );
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        $settings_fields = array(
            'wc_serial_numbers_settings' => array(
                array(
                    'name'        => 'text',
                    'label'       => __( 'Text Field', 'wc-serial-numbers' ),
                    'desc'        => __( 'Text Field Desc', 'wc-serial-numbers' ),
                    'placeholder' => __( 'Place Holder', 'wc-serial-numbers' ),
                    'type'        => 'text',
                ),
            )
            );
        return apply_filters( 'wc_serial_numbers_settings_fields', $settings_fields );
    }
    function settings_page() {
        ?>
        <?php
        echo '<div class="wrap">';
        echo sprintf( "<h2>%s</h2>", __( 'WC Serial Numbers Settings', 'wc-serial-numbers' ) );
        $this->settings_api->show_settings();
        echo '</div>';
    }
    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages         = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_options[ $page->ID ] = $page->post_title;
            }
        }
        return $pages_options;
    }
}
