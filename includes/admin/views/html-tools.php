<?php
/**
 * Render tools page content.
 *
 * @since 1.3.1
 */

// don't call the file directly.
defined( 'ABSPATH' ) || exit();
?>
<div class="wrap woocommerce wc-serial-numbers">
	<?php if ( count( $tabs ) > 1 ) : ?>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $tab_id => $tab ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wsn-tools&tab=' . $tab_id ) ); ?>"
				   class="nav-tab <?php echo $tab_id === $current_tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $tab ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php do_action( 'wc_serial_numbers_tools_page_content_' . $current_tab ); ?>
</div>
