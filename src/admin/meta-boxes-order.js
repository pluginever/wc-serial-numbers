/**
 * External dependencies
 */
import $ from 'jquery';

/**
 * Internal dependencies
 */
import './meta-boxes-order.scss';

const wcsnMetaBoxesOrderSerialNumber = {
	modal: null,

	init() {
		$( document.body )
			.on( 'wc_backbone_modal_loaded', this.backbone.init )
			.on( 'wcsn_backbone_modal_response', this.backbone.response );

		$( document ).ajaxSend( this.filter_sn_products );
		$( document ).ajaxSend( this.close_add_products_modal );
		$( document ).ajaxComplete( this.may_be_refresh_serial_numbers );
	},

	block( el ) {
		el.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6,
			},
		} );
	},

	unblock( el ) {
		el.unblock();
	},

	backbone: {
		init( e, target ) {
			if ( 'wcsn-modal-add-products' === target ) {
				$( document.body ).trigger(
					'wc_backbone_modal_loaded',
					'wc-modal-add-products'
				);
			}
		},

		response( e, modal ) {
			wcsnMetaBoxesOrderSerialNumber.modal = modal;

			// Build a object of unique product ids and their quantities
			const itemTable = $( this ).find( 'table.widefat' ),
				itemTableBody = itemTable.find( 'tbody' ),
				rows = itemTableBody.find( 'tr' ),
				addItems = {};

			$( rows ).each( function () {
				const itemId = $( this ).find( ':input[name="item_id"]' ).val(),
					itemQty =
						$( this ).find( ':input[name="item_qty"]' ).val() || 1;

				if ( ! itemId ) {
					return;
				}

				if ( ! addItems[ itemId ] ) {
					addItems[ itemId ] = 0;
				}

				addItems[ itemId ] += itemQty;
			} );

			if ( ! Object.keys( addItems ).length ) {
				modal.closeButton( e );
				return window.alert(
					wc_serial_numbers_meta_boxes_order_i10n.i18n
						.no_product_selected
				);
			}

			wcsnMetaBoxesOrderSerialNumber.backbone.validate_items( addItems );
		},

		validate_items( addItems ) {
			const modalContent = $(
				'#wcsn-modal-add-products > .wc-backbone-modal-content'
			);
			wcsnMetaBoxesOrderSerialNumber.block( modalContent );

			$.ajax( {
				url: woocommerce_admin_meta_boxes.ajax_url,
				method: 'post',
				data: {
					action: 'wcsn_validate_add_order_items',
					security: woocommerce_admin_meta_boxes.order_item_nonce,
					items: addItems,
				},
			} )
				.done( function ( response ) {
					if ( ! response.success && response.data ) {
						return window.alert( response.data );
					}

					$( document.body ).trigger( 'wc_backbone_modal_response', [
						'wc-modal-add-products',
					] );
				} )
				.fail( function () {
					window.alert(
						wc_serial_numbers_meta_boxes_order_i10n.i18n
							.something_went_wrong
					);
				} )
				.always( function () {
					wcsnMetaBoxesOrderSerialNumber.unblock( modalContent );
				} );
		},
	},

	refresh_serial_numbers() {
		const wcsnItemTable = $( '#wcsn-admin-order-serial-numbers' );

		wcsnMetaBoxesOrderSerialNumber.block( wcsnItemTable );

		$.ajax( {
			type: 'get',
			url: woocommerce_admin_meta_boxes.ajax_url,
			dataType: 'json',
			data: {
				action: 'wcsn_get_order_metabox_table_items',
				security: woocommerce_admin_meta_boxes.order_item_nonce,
				order_id: woocommerce_admin_meta_boxes.post_id,
			},
		} ).done( function ( response ) {
			if ( response.success && response.data && response.data.tbody ) {
				wcsnItemTable.children( 'tbody' ).html( response.data.tbody );
			}

			wcsnMetaBoxesOrderSerialNumber.unblock( wcsnItemTable );
		} );
	},

	filter_sn_products( event, jqxhr, settings ) {
		if (
			! (
				settings.url &&
				typeof settings.url === 'string' &&
				settings.url.indexOf(
					'action=wcsn_json_search_products_and_variations'
				) >= 0
			)
		) {
			return;
		}

		if ( $( '#wcsn-modal-add-products-chkbox' ).is( ':checked' ) ) {
			settings.url += '&wcsn_product_only=true';
		}
	},

	close_add_products_modal( event, jqxhr, settings ) {
		if (
			! (
				settings.data &&
				typeof settings.data === 'string' &&
				settings.data.indexOf( 'action=woocommerce_add_order_item' ) >=
					0
			)
		) {
			return;
		}

		if ( wcsnMetaBoxesOrderSerialNumber.modal ) {
			wcsnMetaBoxesOrderSerialNumber.modal.closeButton( new Event( '' ) );
			wcsnMetaBoxesOrderSerialNumber.modal = null;
		}
	},

	may_be_refresh_serial_numbers( event, xhr, settings ) {
		if ( ! ( settings.data && typeof settings.data === 'string' ) ) {
			return;
		}

		if (
			settings.data.indexOf( 'action=woocommerce_add_order_item' ) >= 0 ||
			settings.data.indexOf( 'action=woocommerce_remove_order_item' ) >=
				0 ||
			settings.data.indexOf( 'action=woocommerce_save_order_items' ) >= 0
		) {
			wcsnMetaBoxesOrderSerialNumber.refresh_serial_numbers();
		}
	},
};

const wcBackboneModalDefaultView = $.extend(
	{},
	$.WCBackboneModal.View.prototype
);
const wcBackboneModalMutateView = $.extend( $.WCBackboneModal.View.prototype, {
	initialize( data ) {
		if ( data.target === 'wc-modal-add-products' ) {
			data.target = 'wcsn-modal-add-products';
		}

		return wcBackboneModalDefaultView.initialize.call( this, data );
	},

	addButton( e ) {
		if ( 'wcsn-modal-add-products' === this._target ) {
			$( document.body ).trigger( 'wcsn_backbone_modal_response', [
				this,
			] );
		} else {
			$( document.body ).trigger( 'wc_backbone_modal_response', [
				this._target,
				this.getFormData(),
			] );
			this.closeButton( e );
		}
	},
} );

$.WCBackboneModal.View = $.WCBackboneModal.View.extend(
	wcBackboneModalMutateView
);
wcsnMetaBoxesOrderSerialNumber.init();
