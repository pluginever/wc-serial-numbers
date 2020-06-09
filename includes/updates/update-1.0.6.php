<?php
function wcsn_update_1_0_6() {
    global $wpdb;

    WCSN_Install::activate();
    WCSN_Install::create_cron();

    $collate = '';
    if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty( $wpdb->charset ) ) {
            $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if ( ! empty( $wpdb->collate ) ) {
            $collate .= " COLLATE $wpdb->collate";
        }
    }

    $tables = [
        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsn_tmp_serial_numbers(
        id bigint(20) NOT NULL AUTO_INCREMENT,
        serial_key longtext DEFAULT NULL,
        serial_image varchar(200) DEFAULT NULL,
        product_id bigint(20) NOT NULL,
        activation_limit int(9) NULL,
        order_id bigint(20) NOT NULL DEFAULT 0,
        activation_email varchar(200) DEFAULT NULL,
        status varchar(50) DEFAULT 'available',
        validity varchar(200) DEFAULT NULL,
        expire_date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
        order_date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
        created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
        ) $collate;",
    ];

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    foreach ( $tables as $table ) {
        dbDelta( $table );
    }



    $serial_numbers = wcsn_get_serial_numbers( array(
        'number' => -1,
    ) );
    foreach( $serial_numbers as $serial_number ) {
        wcsn()->tmp_serial_number->insert( $serial_number );
    }
    foreach( $serial_numbers as $serial_number ) {
        $serial_number = (array) $serial_number;

        if ( ! empty( $serial_number['serial_key'] ) ) {
            $serial_number['serial_key'] = wcsn_encrypt( $serial_number['serial_key'] );
        }

        wcsn()->serial_number->update( $serial_number['id'], $serial_number );
    }
}

wcsn_update_1_0_6();