/**
 * WC Serial Numbers
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

 /*global woocommerce_admin_meta_boxes, wc_serial_numbers_meta_boxes_order_i10n */

jQuery( document ).ready( function ( $ ) {
	var wcsn_meta_boxes_order_serial_number = {
		modal: null,

		init: function() {
			$( document.body )
				.on( 'wc_backbone_modal_loaded', this.backbone.init )
				.on( 'wcsn_backbone_modal_response', this.backbone.response );

			$( document ).ajaxSend( this.filter_sn_products );
			$( document ).ajaxSend( this.close_add_products_modal );
			$( document ).ajaxComplete( this.may_be_refresh_serial_numbers );
		},

		block: function( el ) {
			el.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},

		unblock: function( el ) {
			el.unblock();
		},

		backbone: {
			init: function (e, target) {
				if ( 'wcsn-modal-add-products' === target ) {
					$( document.body ).trigger( 'wc_backbone_modal_loaded', 'wc-modal-add-products' );
				}
			},

			response: function ( e, modal ) {
				wcsn_meta_boxes_order_serial_number.modal = modal;

				// Build a object of unique product ids and their quantities
				var item_table      = $( this ).find( 'table.widefat' ),
					item_table_body = item_table.find( 'tbody' ),
					rows            = item_table_body.find( 'tr' ),
					add_items       = {};

				$( rows ).each( function() {
					var item_id = $( this ).find( ':input[name="item_id"]' ).val(),
						item_qty = $( this ).find( ':input[name="item_qty"]' ).val() || 1;

					if ( ! item_id ) {
						return;
					}

					if ( ! add_items[ item_id ] ) {
						add_items[ item_id ] = 0;
					}

					add_items[ item_id ] += item_qty;
				} );

				if ( ! Object.keys( add_items ).length ) {
					modal.closeButton(e);
					return alert( wc_serial_numbers_meta_boxes_order_i10n.i18n.no_product_selected );
				}

				wcsn_meta_boxes_order_serial_number.backbone.validate_items( add_items );
			},

			validate_items: function( add_items ) {
				var modal_content = $( '#wcsn-modal-add-products > .wc-backbone-modal-content' );
				wcsn_meta_boxes_order_serial_number.block( modal_content );

				$.ajax({
					url: woocommerce_admin_meta_boxes.ajax_url,
					method: 'post',
					data: {
						action: 'wcsn_validate_add_order_items',
						security : woocommerce_admin_meta_boxes.order_item_nonce,
						items: add_items
					}
				})
				.done(function (response) {
					if ( ! response.success && response.data ) {
						return alert( response.data );
					}

					$( document.body ).trigger( 'wc_backbone_modal_response', [ 'wc-modal-add-products' ] );
				})
				.fail(function () {
					alert( wc_serial_numbers_meta_boxes_order_i10n.i18n.something_went_wrong );
				})
				.always(function () {
					wcsn_meta_boxes_order_serial_number.unblock( modal_content );
				});
			}
		},

		refresh_serial_numbers: function() {
			var wcsn_item_table = $( '#wcsn-admin-order-serial-numbers' );

			wcsn_meta_boxes_order_serial_number.block( wcsn_item_table );

			$.ajax( {
				type: 'get',
				url: woocommerce_admin_meta_boxes.ajax_url,
				dataType: 'json',
				data: {
					action: 'wcsn_get_order_metabox_table_items',
					security : woocommerce_admin_meta_boxes.order_item_nonce,
					order_id: woocommerce_admin_meta_boxes.post_id
				}
			} ).done( function ( response ) {
				if ( response.success && response.data && response.data.tbody ) {
					wcsn_item_table.children( 'tbody' ).html( response.data.tbody );
				}

				wcsn_meta_boxes_order_serial_number.unblock( wcsn_item_table );
			} );
		},

		filter_sn_products: function ( event, jqxhr, settings ) {
			if ( ! ( settings.url && typeof settings.url === 'string' && settings.url.indexOf( 'action=wcsn_json_search_products_and_variations' ) >= 0 ) ) {
				return;
			}

			if ( $( '#wcsn-modal-add-products-chkbox' ).is( ':checked' ) ) {
				settings.url += '&wcsn_product_only=true';
			}
		},

		close_add_products_modal: function ( event, jqxhr, settings ) {
			if ( ! ( settings.data && typeof settings.data === 'string' && settings.data.indexOf( 'action=woocommerce_add_order_item' ) >= 0 ) ) {
				return;
			}

			if ( wcsn_meta_boxes_order_serial_number.modal ) {
				wcsn_meta_boxes_order_serial_number.modal.closeButton( new Event( '' ) );
				wcsn_meta_boxes_order_serial_number.modal = null;
			}
		},

		may_be_refresh_serial_numbers: function ( event, xhr, settings ) {
			if ( ! ( settings.data && typeof settings.data === 'string' ) ) {
				return;
			}

			// Add an item from
			if ( settings.data.indexOf( 'action=woocommerce_add_order_item' ) >= 0 ) {
				wcsn_meta_boxes_order_serial_number.refresh_serial_numbers();
			}

			// Deleted an item
			if ( settings.data.indexOf( 'action=woocommerce_remove_order_item' ) >= 0 ) {
				wcsn_meta_boxes_order_serial_number.refresh_serial_numbers( 'remove_item' );
			}
		}
	};

	var wc_backbone_modal_default_view = $.extend({}, $.WCBackboneModal.View.prototype);
	var wc_backbone_modal_mutate_view = $.extend($.WCBackboneModal.View.prototype, {
		initialize: function( data ) {
			if ( data.target === 'wc-modal-add-products' ) {
				data.target = 'wcsn-modal-add-products';
			}

			return wc_backbone_modal_default_view.initialize.call( this, data );
		},

		addButton: function( e ) {
			if ( 'wcsn-modal-add-products' === this._target ) {
				$( document.body ).trigger( 'wcsn_backbone_modal_response', [ this ] );
			} else {
				$( document.body ).trigger( 'wc_backbone_modal_response', [ this._target, this.getFormData() ] );
				this.closeButton( e );
			}
		}
	});

	$.WCBackboneModal.View = $.WCBackboneModal.View.extend(wc_backbone_modal_mutate_view);
	wcsn_meta_boxes_order_serial_number.init();
} );
