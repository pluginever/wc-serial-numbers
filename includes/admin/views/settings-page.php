<?php defined( 'ABSPATH' ) || exit(); ?>
<?php
$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'settings';
$tabs        = array(
	'settings' => __( 'Settings', 'wc-serial-numbers' ),
	'import'   => __( 'Import', 'wc-serial-numbers' ),
	'export'   => __( 'Export', 'wc-serial-numbers' ),
	'help'     => __( 'Help', 'wc-serial-numbers' ),
);
$tabs        = apply_filters( 'wcsn_settings_tabs', $tabs );
?>

<div class="wrap">
	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php
		foreach ( $tabs as $name => $label ) {
			echo '<a href="' . admin_url( 'admin.php?page=wc-serial-numbers-settings&tab=' . $name ) . '" class="nav-tab ';
			if ( $current_tab == $name ) {
				echo 'nav-tab-active';
			}
			echo '">' . $label . '</a>';
		}
		?>
	</nav>
	<h2><?php echo esc_html( $tabs[ $current_tab ] ); ?></h2>
	<?php do_action( 'wcsn_settings_tab_content_' . $current_tab ); ?>
</div>
