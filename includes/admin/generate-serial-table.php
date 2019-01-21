<?php

namespace Pluginever\WCSerialNumberPro\Admin;


// WP_List_Table is not loaded automatically so we need to load it in our application
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Generate_Serial_Table extends \WP_List_Table {

	/** Class constructor */
	public function __construct() {

		$GLOBALS['hook_suffix'] = null;

		parent::__construct( [
			'singular' => __( 'Generate Serial Number', 'wc-serial-number-pro' ), //singular name of the listed records
			'plural'   => __( 'Generate Serial Numbers', 'wc-serial-number-pro' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		] );

	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */

	public function prepare_items() {

		$this->process_bulk_action();
		$this->process_filter();

		$columns     = $this->get_columns();
		$sortable    = $this->get_sortable_columns();
		$data        = $this->table_data();
		$perPage     = 5;
		$currentPage = $this->get_pagenum();
		$totalItems  = count( $data );

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

		$data                  = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );
		$this->_column_headers = array( $columns, array(), $sortable );
		$this->items           = $data;
	}

	public function process_bulk_action() {

		// security check!
		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'No, Cheating!' );
			}

			$bulk_deletes = ! empty( $_REQUEST['bulk-delete'] ) && is_array( $_REQUEST['bulk-delete'] ) ? array_map( 'intval', $_REQUEST['bulk-delete'] ) : '';

			if ( ! empty( $bulk_deletes ) ) {

				foreach ( $bulk_deletes as $bulk_delete ) {

					$bulk_delete = intval( $bulk_delete );

					if ( current_user_can( 'delete_posts' ) && get_post_status( $bulk_delete ) ) {

						wp_delete_post( $bulk_delete, true );

					}

				}

			}

		}

	}

	function process_filter() {
		if ( isset( $_REQUEST['wsn-filter-table-generate'] ) && ! empty( $_REQUEST['filter-product'] ) ) {

		}
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'product'       => __( 'Product', 'wc-serial-number-pro' ),
			'variation'     => __( 'Variation', 'wc-serial-number-pro' ),
			'prefix'        => __( 'Prefix. ', 'wc-serial-number-pro' ),
			'chunks_number' => __( 'Chunks', 'wc-serial-number-pro' ),
			'chunks_length' => __( 'Chunks', 'wc-serial-number-pro' ),
			'suffix'        => __( 'Suffix', 'wc-serial-number-pro' ),
			'deliver_times' => __( 'Deliver times', 'wc-serial-number-pro' ),
			'instance'      => __( 'Instance', 'wc-serial-number-pro' ),
			'validity'      => __( 'Validity', 'wc-serial-number-pro' ),
			'generate'      => __( 'Generate', 'wc-serial-number-pro' ),
		);

		return $columns;
	}

	/**
	 * Define the sortable columns
	 *

	 */
	public function get_sortable_columns() {

		$shortable = array();

		return $shortable;
	}

	/**
	 * Get the table data
	 *
	 * @return array
	 */
	private function table_data() {

		$data = array();

		$query = array();

		if ( isset( $_REQUEST['wsn-filter-table-generate'] ) && ! empty( $_REQUEST['filter-product'] ) ) {
			$query['meta_key']   = 'product';
			$query['meta_value'] = intval( $_REQUEST['filter-product'] );
		}

		$posts = wsnp_get_generator_rules( $query );

		foreach ( $posts as $post ) {

			setup_postdata( $post );

			$product       = get_post_meta( $post->ID, 'product', true );
			$variation     = get_post_meta( $post->ID, 'variation', true );
			$prefix        = get_post_meta( $post->ID, 'prefix', true );
			$chunks_number = get_post_meta( $post->ID, 'chunks_number', true );
			$chunk_length  = get_post_meta( $post->ID, 'chunk_length', true );
			$suffix        = get_post_meta( $post->ID, 'suffix', true );
			$deliver_times = get_post_meta( $post->ID, 'deliver_times', true );
			$instance      = get_post_meta( $post->ID, 'max_instance', true );
			$validity      = get_post_meta( $post->ID, 'validity', true );
			$generate_num  = wsn_get_settings( 'wsn_generate_number', '', 'wsn_serial_generator_settings' );

			$generate_html = '
			<span class="ever-spinner"></span>
			<input type="number" class="generate_number ever-thumbnail-small" name="generate_number" id="generate_number" value="' . $generate_num . '">
			<button class="button button-primary wsn_generate_btn" data-rule_id="' . $post->ID . '"> ' . __( 'Generate', 'wc-serial-number-pro' ) . '</button>
			';

			$data[] = [
				'ID'            => $post->ID,
				'product'       => '<a href="' . get_edit_post_link( $product ) . '">' . get_the_title( $product ) . '</a>',
				'variation'     => empty( $variation ) ? __( 'Main Product', 'wc-serial-number-pro' ) : get_the_title( $variation ),
				'prefix'        => empty( $prefix ) ? '' : $prefix,
				'chunks_number' => empty( $chunks_number ) ? '' : $chunks_number,
				'chunks_length' => empty( $chunk_length ) ? '' : $chunk_length,
				'suffix'        => empty( $suffix ) ? '' : $suffix,
				'deliver_times' => empty( $deliver_times ) ? '∞' : $deliver_times,
				'instance'      => empty( $instance ) ? '∞' : $instance,
				'validity'      => empty( $validity ) ? '∞' : $validity,
				'generate'      => $generate_html,
			];

		}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array $item Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'ID':
			case 'product':
			case 'variation':
			case 'prefix':
			case 'chunks_number':
			case 'chunks_length':
			case 'suffix':
			case 'deliver_times':
			case 'instance':
			case 'validity':
			case 'generate':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */

	public function get_bulk_actions() {
		$actions = array( 'bulk-delete' => 'Delete' );

		return $actions;
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}

	function column_product( $item ) {
		$actions = array(
			'edit'   => '<a href="' . add_query_arg( [
					'type'           => 'automate',
					'row_action'     => 'edit',
					'generator_rule' => $item['ID']
				], WPWSN_ADD_GENERATE_RULE ) . '">' . __( 'Edit', 'wc-serial-number-pro' ) . '</a>',
			'delete' => '<a href="' . add_query_arg( [
					'row_action'     => 'delete',
					'generator_rule' => $item['ID']
				], WPWSN_GENERATE_SERIAL_PAGE ) . '">' . __( 'Delete', 'wc-serial-number-pro' ) . '</a>',
		);

		return sprintf( '%1$s %2$s', $item['product'], $this->row_actions( $actions ) );
	}

	function display_tablenav( $which ) {

		if ( 'bottom' === $which ) {
			return;
		}

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() ) { ?>

				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>

				<?php

				$this->pagination( $which );
				$this->extra_tablenav( $which );
			}

			?>

			<br class="clear"/>

		</div>
		<?php
	}

	/**
	 * Table Filter html
	 *
	 * @since 1.0.0
	 *
	 * @param string $which
	 */

	function extra_tablenav( $which ) {
		echo apply_filters( 'wsn_extra_table_nav', '', 'generate' );
	}


}
