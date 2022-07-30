( function ( factory ) {
	if ( typeof module === 'object' && typeof module.exports === 'object' ) {
		factory( require( 'jquery' ), window, document );
	} else {
		factory( jQuery, window, document );
	}
} )( function ( $, window, document, undefined ) {
	'use strict';
	const modals = [],
		EVENTS = {
			BEFORE_BLOCK: 'ever_modal:before-block',
			BLOCK: 'ever_modal:block',
			BEFORE_OPEN: 'ever_modal:before-open',
			OPEN: 'ever_modal:open',
			BEFORE_CLOSE: 'ever_modal:before-close',
			CLOSE: 'ever_modal:close',
			AFTER_CLOSE: 'ever_modal:after-close',
			AJAX_SEND: 'ever_modal:ajax:send',
			AJAX_SUCCESS: 'ever_modal:ajax:success',
			AJAX_FAIL: 'ever_modal:ajax:fail',
			AJAX_COMPLETE: 'ever_modal:ajax:complete',
			FORM_SUBMITTED: 'ever_modal:submit',
		};

	const getCurrent = function () {
		return modals.length ? modals[ modals.length - 1 ] : null;
	};

	const selectCurrent = function () {
		let i,
			selected = false;
		for ( i = modals.length - 1; i >= 0; i-- ) {
			if ( modals[ i ].$container ) {
				modals[ i ].$container
					.toggleClass( 'current', ! selected )
					.toggleClass( 'behind', selected );
				selected = true;
			}
		}
	};

	const isActive = function () {
		return modals.length > 0;
	};

	$.ever_modal = function () {
		return this.init.apply( this, arguments );
	};

	$.ever_modal.prototype = {
		constructor: $.ever_modal,
		defaults: {
			close_existing: true,
			escape_close: true,
			click_close: true,
			modal_class: '',
			spinner_html: '<div class="ever_modal__spinner"></div>',
			show_spinner: true,
			fade_duration: null, // Number of milliseconds the fade animation takes.
			fade_delay: 1.0, // Point during the overlay's fade-in that the modal begins to fade in (.5 = 50%, 1.5 = 150%, etc.)
		},
		isActive,
		getCurrent,
		selectCurrent,
		block() {
			this.$anchor.trigger( EVENTS.BEFORE_BLOCK, [ this ] );
			this.$body.css( 'overflow', 'hidden' );
			this.$container = $(
				'<div class="' +
					this.options.container_class +
					' ever_modal current" tabindex="-1"></div>'
			).appendTo( this.$body );
			this.selectCurrent();
			if ( this.options.doFade ) {
				this.$container
					.css( 'opacity', 0 )
					.animate( { opacity: 1 }, this.options.fade_duration );
			}
			this.$anchor.trigger( EVENTS.BLOCK, [ this ] );
		},
		unblock( now ) {
			if ( ! now && this.options.doFade )
				this.$container.fadeOut(
					this.options.fade_duration,
					this.unblock.bind( this, true )
				);
			else {
				this.$container.children().appendTo( this.$body );
				this.$container.remove();
				this.$container = null;
				this.selectCurrent();
				if ( ! this.isActive() ) this.$body.css( 'overflow', '' );
			}
		},
		show() {
			this.$anchor.trigger( EVENTS.BEFORE_OPEN, [ this ] );
			this.$elm
				.addClass( this.options.modal_class )
				.appendTo( this.$container );
			if ( this.options.doFade ) {
				this.$elm
					.css( { opacity: 0, display: 'inline-block' } )
					.animate( { opacity: 1 }, this.options.fade_duration );
			} else {
				this.$elm.css( 'display', 'inline-block' );
			}
			this.$anchor.trigger( EVENTS.OPEN, [ this ] );
		},
		hide() {
			this.$anchor.trigger( EVENTS.BEFORE_CLOSE, [ this ] );
			if ( this.closeButton ) this.closeButton.remove();
			const _this = this;
			if ( this.options.doFade ) {
				this.$elm.fadeOut( this.options.fade_duration, function () {
					_this.$anchor.trigger( EVENTS.AFTER_CLOSE, [ _this ] );
				} );
			} else {
				this.$elm.hide( 0, function () {
					_this.$anchor.trigger( EVENTS.AFTER_CLOSE, [ _this ] );
				} );
			}
			this.$anchor.trigger( EVENTS.CLOSE, [ this ] );
		},
		showSpinner() {
			if ( ! this.options.show_spinner ) return;
			const spinner =
				this.spinner || '<div class="ever_modal__spinner"></div>';
			this.spinner = $( spinner );
			this.$elm.append( this.spinner );
			this.spinner.show();
		},
		hideSpinner() {
			if ( this.spinner ) this.spinner.remove();
		},
		open() {
			const _this = this;
			this.block();
			this.$anchor.blur();
			if ( this.options.doFade ) {
				setTimeout( function () {
					_this.show();
				}, this.options.fade_duration * this.options.fade_delay );
			} else {
				this.show();
			}
			$( document )
				.off( 'keydown.ever_modal' )
				.on( 'keydown.ever_modal', function ( event ) {
					const current = _this.getCurrent();
					console.log( event.which );
					if ( event.which === 27 && current.options.escape_close )
						current.close();
				} );
			if ( this.options.click_close ) {
				this.$container.click( function ( e ) {
					if ( e.target === this ) $.ever_modal.close();
				} );
			}

			$( 'form', _this.$elm ).on( 'submit', function ( e ) {
				e.preventDefault();
				const form = $( 'form', _this.$elm );
				_this.$anchor.trigger( EVENTS.FORM_SUBMITTED, [ _this, form ] );
			} );
		},
		close() {
			modals.pop();
			this.unblock();
			this.hide();
			if ( ! this.isActive() ) {
				$( document ).off( 'keydown.ever_modal' );
			}
		},
		init( el, options ) {
			let remove,
				target,
				_this = this;
			this.$body = $( 'body' );
			this.options = $.extend( {}, this.defaults, options );
			this.options.doFade = ! isNaN(
				parseInt( this.options.fade_duration, 10 )
			);
			this.$container = null;
			if ( this.options.close_existing ) {
				while ( this.isActive() ) {
					this.close(); // Close any open modals.
				}
			}

			modals.push( this );

			if ( el.is( 'a' ) ) {
				target = el.attr( 'href' );
				this.$anchor = el;
				//Select element by id from href
				if ( /^#/.test( target ) ) {
					this.$elm = $( target );
					if ( this.$elm.length !== 1 ) return null;
					this.$body.append( this.$elm );
					this.open();
					//AJAX
				} else {
					this.$elm = $( '<div>' );
					this.$body.append( this.$elm );
					remove = function ( event, modal ) {
						modal.$elm.remove();
					};
					this.showSpinner();
					el.trigger( EVENTS.AJAX_SEND );
					$.get( target )
						.done( function ( json ) {
							if ( ! _this.isActive() ) return;
							el.trigger( EVENTS.AJAX_SUCCESS );
							const current = _this.getCurrent();
							if (
								! json.success ||
								! json.data ||
								! json.data.html
							) {
								throw new Error( 'something happened wrong' );
							}
							current.$elm
								.empty()
								.append( json.data.html )
								.on( EVENTS.CLOSE, remove );
							current.open();
							el.trigger( EVENTS.AJAX_COMPLETE );
						} )
						.fail( function () {
							el.trigger( EVENTS.AJAX_FAIL );
							const current = _this.getCurrent();
							current.hideSpinner();
							modals.pop(); // remove expected modal from the list
							el.trigger( EVENTS.AJAX_COMPLETE );
						} );
				}
			} else {
				this.$elm = el;
				this.$anchor = el;
				this.$body.append( this.$elm );
				this.open();
			}
		},
	};

	$.ever_modal.close = function ( event ) {
		if ( ! isActive() ) return;
		if ( event ) event.preventDefault();
		const current = getCurrent();
		current.close();
		return current.$elm;
	};

	$.fn.ever_modal = function ( options ) {
		if ( this.length === 1 ) {
			new $.ever_modal( this, options );
		}
		return this;
	};

	// Automatically bind links with rel="ever_modal:close" to, well, close the modal.
	$( document ).on(
		'click',
		'[rel~="ever_modal:close"]',
		$.ever_modal.close
	);
	$( document ).on( 'click', '[rel~="ever_modal:open"]', function ( event ) {
		event.preventDefault();
		$( this ).ever_modal( $( this ).data() );
	} );
} );
