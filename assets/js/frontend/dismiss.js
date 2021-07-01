import jQuery from 'jquery';
import { getItem, setItem } from './cookie';
import {displayModal} from './modal';

let $ = jQuery;

export default function dismiss() {

	let $body = $('body'),
		$notices;

	init();

	/**
	 * Initialize our dismiss
	 * Add our events
	 */
	function init () {
		$body.on( 'click', '.courier-close, .trigger-close', closeClick );
	}

	/**
	 * Utility wrapper to block clicks.
	 *
	 * Grab the data-courier-notice-id from current notice.
	 *
	 * @param event
	 */
	function closeClick( event ) {

		let $this = $( this ),
			href  = $this.attr( 'href' );

		if ( true !== $this.data( 'dismiss' ) ) {
			event.preventDefault();
			event.stopPropagation();

			// Store that our notice should be dismissed
			// This will stop an infinite loop.
			$this.data( 'dismiss', true );

			let $notice   = $this.parents( '.courier-notice' );
			let notice_id = parseInt( $notice.data( 'courier-notice-id' ), 10 );

			if ( 0 === $notice.length || isNaN( notice_id ) ) {
				return;
			}

			// Only pass an ajax call if the user has an ID
			if ( courier_notices_data.user_id && courier_notices_data.user_id > 0 ) {
				$.post( {
						url: courier_notices_data.notice_endpoint + ( notice_id ) + '/dismiss',
						data: {
							'dismiss_nonce' : courier_notices_data.dismiss_nonce,
							'user_id' : courier_notices_data.user_id,
						},
						beforeSend: function( request ) {
							request.setRequestHeader( 'X-WP-Nonce', courier_notices_data.wp_rest_nonce );
						},
					} ).done( function () {
						$notice.find('.courier-close').trigger('click');
						hideNotice( notice_id );
					} );

				if ( href && href !== '#' ) {
					$(document).ajaxComplete( function ( event, request, settings ) {
						window.location = href;
					} );
				}

			} else {
				$notice.find('.courier-close').trigger('click');
				hideNotice( notice_id );

				if ( href && href !== '#' ) {
					window.location = href;
				}
			}
		}
	}

	/**
	 * Hide the notice so it is not longer visible.
	 *
	 * @param notice_id
	 */
	function hideNotice( notice_id ) {
		$( ".courier_notice[data-courier-notice-id='" + notice_id + "']" ).fadeOut( 500, function() {
			if ( 0 === window.courier_notices_modal_notices.length ) {
				$( '.courier-modal-overlay' ).addClass('hide').hide();
			} else {
				displayModal(0);
			}
		});

		setCookie( notice_id );
	}

	/**
	 * Send all the notices in a comma delimited list.
	 *
	 * @param event
	 */
	function close_all_click( event ) {

		var $this = $(this);

		if (true !== $this.data('dismiss')) {
			event.preventDefault();
			event.stopPropagation();

			// Store that our notice should be dismissed
			// This will stop an infinite loop.
			$this.data('dismiss', true);

			$notices = $('.courier-notices').find('.courier-notice');

			var notice_ids = $notices.data('courier-notice-id');

			ajax( notice_ids.join(',') );
		}
	}

	/**
	 * Call out to our ajax endpoint pass either a single id or a comma
	 * delimited list of id
	 *
	 * @param notice_ids example 1 or 1,2,3
	 */
	function ajax( notice_ids ) {
		$.get( courier_notices_data.endpoint + notice_ids + '/').done( function () {
			$notices.find('.courier-close').trigger('click');

			notice_ids = String(notice_ids).split(',');

			$.each( notice_ids, function ( index, value ) {
				$(".courier_notice[data-courier-notice-id='" + value + "']").fadeOut();
				$('.courier-modal-overlay').hide();
				setCookie( value );
			});
		} );
	}

	function setCookie( notice_id ) {
		let notice_ids = getItem( 'dismissed_notices' );
			notice_ids = JSON.parse( notice_ids );
			notice_ids = notice_ids || [];

		// Add to array if not already in there
		if ( notice_ids.indexOf( notice_id ) === -1 ) {
			notice_ids.push( notice_id );
		}

		setItem( 'dismissed_notices', JSON.stringify( notice_ids ) );
	}
}
