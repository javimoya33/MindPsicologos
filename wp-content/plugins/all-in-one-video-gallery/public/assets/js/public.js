(function( $ ) {
	'use strict';

	/**
	 * Called when the page has loaded.
	 *
	 * @since 2.4.3
	 */
	$(function() {

		// Tags
		$( '.aiovg-field-tag select' ).on( 'change', function() {			
			var value = parseInt( $( this ).val() );
			var $selected = $( this ).find( 'option:selected' );			

			if ( value != 0 ) {	
				var $tags = $( this ).closest( 'form' ).find( '.aiovg-tags-list' );				

				var html = '<span class="aiovg-tag-item" data-id="' + value + '">';
				html += '<span class="aiovg-tag-item-name">' + $selected.text() + '</span>';
				html += '<span class="aiovg-tag-item-close">&times;</span>';
				html += '<input type="hidden" name="ta[]" value="' + value + '" />';
				html += '</span>';
				
				$tags.append( html );
			}

			$selected.prop( 'disabled', true );
			$( this ).val( '' );
		});

		$( document ).on( 'click', '.aiovg-tag-item-close', function() {
			var $elem = $( this ).parent();
			var value = parseInt( $elem.data( 'id' ) );

			$( this ).closest( 'form' ).find( '.aiovg-field-tag select' ).children( 'option[value=' + value + ']' ).prop( 'disabled', false );
			$elem.remove();
		});

		// Pagination
		$( document ).on( 'click', '.aiovg-pagination-ajax a.page-numbers', function( e ) {
			e.preventDefault();

			var $this = $( this );			
			var $pagination = $this.closest( '.aiovg-pagination-ajax' );
			var uid = $pagination.data( 'uid' );
			var source = $pagination.data( 'source' );
			var current = parseInt( $pagination.data( 'current' ) );
			var paged = parseInt( $this.html() );
			var $gallery = $( '#aiovg-' + uid );			

			var params = window[ 'aiovg_pagination_' + uid ];
			params.action = 'aiovg_load_more_' + source;
			params.security = aiovg_public.ajax_nonce;
			
			params.paged = paged++;
			if ( $this.hasClass( 'prev' ) ) {
				params.paged = current - 1;
			}
			if ( $this.hasClass( 'next' ) ) {
				params.paged = current + 1;
			}

			$pagination.addClass( 'aiovg-spinner' );

			$.post( aiovg_public.ajax_url, params, function( response ) {
				console.log( response );
				if ( response.success ) {
					$gallery.html( $( response.data.html ).html() ).trigger( 'AIOVG.onGalleryUpdated' );

					$( 'html, body' ).animate({
						scrollTop: $gallery.offset().top - aiovg_public.scroll_to_top_offset
					}, 500);
				} else {
					$pagination.removeClass( 'aiovg-spinner' );
				}
			});
		});

		// Load More
		$( document ).on( 'click', '.aiovg-more-ajax button', function( e ) {
			e.preventDefault();

			var $this = $( this );
			var $pagination = $this.closest( '.aiovg-more-ajax' );
			var uid = $this.data( 'uid' );
			var source = $this.data( 'source' );
			var numpages = parseInt( $this.data( 'numpages' ) );
			var paged = parseInt( $this.data( 'paged' ) );

			var params = window[ 'aiovg_pagination_' + uid ];
			params.action = 'aiovg_load_more_' + source;
			params.security = aiovg_public.ajax_nonce;			
			params.paged = ++paged;
			
			$pagination.addClass( 'aiovg-spinner' );

			$.post( aiovg_public.ajax_url, params, function( response ) {
				$pagination.removeClass( 'aiovg-spinner' );

				if ( paged < numpages ) {
					$this.data( 'paged', params.paged );	
				} else {
					$this.hide();
				}			
				
				if ( response.success ) {					
					$( '.aiovg-grid', '#aiovg-' + uid ).append( $( '.aiovg-grid', response.data.html ).html() );					
				}
			});
		});
		
	});

})( jQuery );
