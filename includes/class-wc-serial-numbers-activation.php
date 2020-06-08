<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Activation {


	/**
	 * since 1.0.0
	 *
	 * @param $serial_number_id
	 * @param $instance
	 * @param string $platform
	 *
	 * @return bool|int
	 */
	public static function activate( $serial_number_id, $instance, $platform = '' ) {
		global $wpdb;
		$where = $wpdb->prepare( " WHERE serial_id=%d", $serial_number_id );
		$where .= $wpdb->prepare( " AND instance=%s", $instance );
		if ( ! empty( $platform ) ) {
			$where .= $wpdb->prepare( " AND platform=%s", $platform );
		}
		$activation = $wpdb->get_row( "SELECT * FROM $wpdb->wcsn_activations $where" );
		if ( $activation && $activation->active ) {
			return $activation->id;
		} else if ( $activation && false != $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wcsn_activations SET active=1 WHERE id=%d", $activation->id ) ) ) {
			return $activation->id;
		}

		$data = array(
			'serial_id'       => $serial_number_id,
			'instance'        => $instance,
			'active'          => '1',
			'platform'        => $platform,
			'activation_time' => current_time( 'mysql' )
		);

		if ( false === $wpdb->insert( $wpdb->wcsn_activations, $data, array( '%d', '%s', '%s', '%s', '%s' ) ) ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}



	/**
	 * Get total activation.
	 *
	 * @param $serial_number_id
	 *
	 * @return int|null
	 * @since 1.1.5
	 */
	public static function get_activation_count( $serial_number_id ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT count(id) from $wpdb->wcsn_activations WHERE serial_id=%d AND active='1'", $serial_number_id ) );
	}
}
