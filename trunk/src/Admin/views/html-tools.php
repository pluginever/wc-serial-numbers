<?php
/**
 * Show the tools page.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers\Admin\Views
 */

defined( 'ABSPATH' ) || exit;

$page_url = admin_url( 'admin.php?page=wc-serial-numbers-tools' );
?>
<div class="wrap pev-wrap woocommerce">
	<?php if ( is_array( $tabs ) ) : ?>
		<h2 class="nav-tab-wrapper wcsn-nav-tabs">
			<?php foreach ( $tabs as $tab_id => $tab_title ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => $tab_id ), $page_url ) ); ?>" class="nav-tab <?php echo $tab_id === $current_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $tab_title ); ?></a>
			<?php endforeach; ?>
		</h2>
	<?php endif; ?>
	<hr class="wp-header-end">
	<div class="wcsn-tools-tab-content wcsn-tools-tab-<?php echo esc_attr( $current_tab ); ?>">
		<?php do_action( 'wc_serial_numbers_tools_tab_' . $current_tab ); ?>
	</div>
</div>
