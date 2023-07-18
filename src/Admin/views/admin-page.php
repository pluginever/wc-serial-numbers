<?php
/**
 * View: Admin Page
 *
 * @since 1.0.0
 * @subpackage Admin/Views
 * @package Serial Numbers
 * @var string $page_hook Page hook.
 */

defined( 'ABSPATH' ) || exit;

$current_tab  = filter_input( INPUT_GET, 'tab' );
$current_page = filter_input( INPUT_GET, 'page' );
$tabs         = isset( $tabs ) ? $tabs : array();
$tabs         = apply_filters( 'wc_serial_numbers_' . $page_hook . '_tabs', $tabs );
$current_tab  = ! empty( $current_tab ) && array_key_exists( $current_tab, $tabs ) ? $current_tab : key( $tabs );
?>
	<div class="wrap pev-admin-page woocommerce">
		<?php if ( ! empty( $tabs ) && count( $tabs ) > 1 ) : ?>
			<nav class="nav-tab-wrapper pev-admin-page__nav">
				<?php
				foreach ( $tabs as $name => $label ) {
					echo sprintf(
						'<a href="%s" class="nav-tab %s">%s</a>',
						esc_url( admin_url( 'admin.php?page=' . $current_page . '&tab=' . $name ) ),
						esc_attr( $current_tab === $name ? 'nav-tab-active' : '' ),
						esc_html( $label )
					);
				}
				?>
				<?php
				/**
				 * Fires after the tabs on the settings page.
				 *
				 * @param string $current_tab Current tab..
				 * @param array $tabs Tabs.
				 *
				 * @since 1.0.0
				 */
				do_action( 'wc_serial_numbers_' . $page_hook . '_nav_items', $current_tab, $tabs );
				?>
			</nav>
		<?php endif; ?>

		<hr class="wp-header-end">

		<?php
		if ( ! empty( $tabs ) && ! empty( $current_tab ) ) {
			/**
			 * Action: Serial Numbers Admin Page Tab
			 *
			 * @param string $current_tab Current tab.
			 *
			 * @since 1.0.0
			 */
			do_action( "wc_serial_numbers_{$page_hook}_{$current_tab}_content", $current_tab );
		}

		/**
		 * Action: Serial Numbers Admin Page
		 *
		 * @param string $current_tab Current tab.
		 *
		 * @since 1.0.0
		 */
		do_action( "wc_serial_numbers_{$page_hook}_content", $current_tab );
		?>
	</div>
<?php
