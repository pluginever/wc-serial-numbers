<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Class Tools.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Tools extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_filter( 'wc_serial_numbers_tools_tabs', array( $this, 'add_status_tab' ), PHP_INT_MAX );
		add_action( 'wc_serial_numbers_tools_tab_status', array( $this, 'status_tab' ) );
		add_action( 'wc_serial_numbers_tools_tab_api', array( $this, 'api_validation_section' ) );
		add_action( 'wc_serial_numbers_tools_tab_api', array( $this, 'api_activation_deactivation_section' ) );
	}

	/**
	 * Output tools page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_page() {
		wp_verify_nonce( '_nonce' );
		$tabs = array(
			'generators' => __( 'Generators', 'wc-serial-numbers' ),
			'api'        => __( 'API Toolkit', 'wc-serial-numbers' ),
			'import'     => __( 'Import', 'wc-serial-numbers' ),
			'export'     => __( 'Export', 'wc-serial-numbers' ),
		);

		// If software support is disabled, remove the activations tab.
		if ( ! wcsn_is_software_support_enabled() ) {
			unset( $tabs['api'] );
		}

		$tabs        = apply_filters( 'wc_serial_numbers_tools_tabs', $tabs );
		$tab_ids     = array_keys( $tabs );
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : reset( $tab_ids );
		$page        = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		$this->app->template->view(
			'admin.tools',
			array(
				'tabs'        => $tabs,
				'current_tab' => $current_tab,
				'page'        => $page,
			)
		);
	}

	/**
	 * Add status tab.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @return array
	 */
	public function add_status_tab( $tabs ) {
		$tabs['status'] = __( 'Status', 'wc-serial-numbers' );

		return $tabs;
	}

	/**
	 * Import tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function import_tab() {
		?>
		<div class="wcsn-feature-promo-banner">
			<div class="wcsn-feature-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-serial-numbers' ); ?></h3>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=import-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-serial-numbers' ); ?></a>
			</div>
			<img src="<?php echo esc_url( $this->app->assets_url( 'images/csv-import.png' ) ); ?>" alt="<?php esc_attr_e( 'Import Serial Numbers', 'wc-serial-numbers' ); ?>"/>
		</div>
		<div class="wcsn-feature-promo-banner">
			<div class="wcsn-feature-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-serial-numbers' ); ?></h3>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=import-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-serial-numbers' ); ?></a>
			</div>
			<img src="<?php echo esc_url( $this->app->assets_url( 'images/txt-import.png' ) ); ?>" alt="<?php esc_attr_e( 'Import Serial Numbers', 'wc-serial-numbers' ); ?>"/>
		</div>
		<?php
	}

	/**
	 * Export tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function export_tab() {
		?>
		<div class="wcsn-feature-promo-banner">
			<div class="wcsn-feature-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-serial-numbers' ); ?></h3>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=export-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-serial-numbers' ); ?></a>
			</div>
			<img src="<?php echo esc_url( $this->app->assets_url( 'images/csv-export.png' ) ); ?>" alt="<?php esc_attr_e( 'Export Serial Numbers', 'wc-serial-numbers' ); ?>"/>
		</div>
		<?php
	}

	/**
	 * Generators tab content.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public function generators_tab() {
		?>
		<div class="wcsn-feature-promo-banner">
			<div class="wcsn-feature-promo-banner__content">
				<h3><?php esc_html_e( 'Available in Pro Version', 'wc-serial-numbers' ); ?></h3>
				<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=generators-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-serial-numbers' ); ?></a>
			</div>
			<img src="<?php echo esc_url( $this->app->assets_url( 'images/add-generator.png' ) ); ?>" alt="<?php esc_attr_e( 'Generators', 'wc-serial-numbers' ); ?>"/>
		</div>
		<?php
	}

	/**
	 * Debug tab content.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public function status_tab() {
		$statuses = array(
			'Serial Numbers version' => $this->app->version,
		);
		if ( $this->app->is_pro_active() && function_exists( 'wc_serial_numbers_pro' ) ) {
			$statuses['Serial Numbers Pro version'] = WCSN_PRO()->get_version();
		}

		// Check if required tables exist.
		$required_tables = array(
			'serial_numbers',
			'serial_numbers_activations',
		);
		foreach ( $required_tables as $table ) {
			$exists = $GLOBALS['wpdb']->get_var( $GLOBALS['wpdb']->prepare( 'SHOW TABLES LIKE %s', $GLOBALS['wpdb']->prefix . $table ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			if ( $exists ) {
				$statuses[ $table ] = __( 'Table exists', 'wc-serial-numbers' );
			} else {
				$statuses[ $table ] = __( 'Table does not exist', 'wc-serial-numbers' );
			}
		}

		// Cron jobs.
		$cron_jobs = array(
			'wc_serial_numbers_hourly_event' => __( 'Hourly cron', 'wc-serial-numbers' ),
			'wc_serial_numbers_daily_event'  => __( 'Daily cron', 'wc-serial-numbers' ),
		);
		foreach ( $cron_jobs as $cron_job => $cron_job_name ) {
			$next_scheduled = wp_next_scheduled( $cron_job );
			if ( $next_scheduled ) {
				// translators: %s: Next scheduled time.
				$statuses[ $cron_job_name ] = sprintf( __( 'Next run: %s', 'wc-serial-numbers' ), esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_scheduled ) ) );
			} else {
				$statuses[ $cron_job_name ] = __( 'Not scheduled', 'wc-serial-numbers' );
			}
		}
		$statuses = apply_filters( 'wc_serial_numbers_plugin_statuses', $statuses );
		?>
		<table class="widefat wcsn-status" cellspacing="0" id="wcsn-status">
			<thead>
			<tr>
				<th colspan="3" data-export-label="Serial Numbers"><h2><?php esc_html_e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h2></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $statuses as $name => $value ) : ?>
				<tr>
					<td data-export-label="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></td>
					<td class="help">&dash;</td>
					<td><?php echo esc_html( $value ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>

		</table>

		<?php
	}

	/**
	 * Validation section.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public function api_validation_section() {
		$this->app->template->view( 'admin.api-validation', array( 'products' => $this->get_key_products() ) );
	}

	/**
	 * Activation deactivation section.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public function api_activation_deactivation_section() {
		$this->app->template->view( 'admin.api-actions', array( 'products' => $this->get_key_products() ) );
	}

	/**
	 * Get the key enabled products as id => label pairs.
	 *
	 * @since 1.4.6
	 * @return array
	 */
	protected function get_key_products() {
		$args        = array_merge(
			wcsn_get_products_query_args(),
			array(
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			)
		);
		$the_query   = new \WP_Query( $args );
		$product_ids = $the_query->get_posts();
		$products    = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}
			$products[ $product->get_id() ] = sprintf( '%s (#%d)', $product->get_name(), $product->get_id() );
		}

		return $products;
	}
}
