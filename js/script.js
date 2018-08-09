( function( $ ) {
	$( document ).ready( function() {
		$( '#jbbrd_gdpr' ).on( 'change', function() {
			if( $( this ).is( ':checked' ) ) {
				$( '#jbbrd_gdpr_link_options' ).show();
			} else {
				$( '#jbbrd_gdpr_link_options' ).hide();
			}
		} ).trigger( 'change' );
		/* Change radio button of currency unit chosen*/
		$( 'select[name="jbbrd_money_choose"]' ).on( 'focus', function() {
			$( '#preset_money_unit_select' ).attr( 'checked', 'checked' );
		} );
		$( 'input[name="jbbrd_custom_money_unit"]' ).on( 'focus', function() {
			$( '#custom_money_unit_select' ).attr( 'checked', 'checked' );
		} );		
		/* Add datapicker. */
		if ( $.isFunction( $.fn.datepicker ) ) {
			var is_rtl = ( $( 'body' ).hasClass( 'rtl' ) );
			$( '#jbbrd_expiry_date' ).datepicker( {
				dateFormat : 'dd-mm-yy',
				isRTL : is_rtl
			} );
		}
		/* Change form settings */
		if ( ! $( 'input[name="jbbrd_frontend_form"]' ).is( ":checked" ) ) {
			$( 'input[name="jbbrd_frontend_form_non_registered"]' ).hide();
			$( 'input[name="jbbrd_frontend_form_non_registered"]' ).next( 'span' ).hide();
		} else {
			$( 'input[name="jbbrd_frontend_form_non_registered"]' ).show();
			$( 'input[name="jbbrd_frontend_form_non_registered"]' ).next( 'span' ).show();
		}
		$( $( 'input[name="jbbrd_frontend_form"]' ) ).on( 'change', function() {
			if ( ! $( this ).is( ":checked" ) ) {
				$( 'input[name="jbbrd_frontend_form_non_registered"]' ).hide();
				$( 'input[name="jbbrd_frontend_form_non_registered"]' ).next( 'span' ).hide();
			} else {
				$( 'input[name="jbbrd_frontend_form_non_registered"]' ).show();
				$( 'input[name="jbbrd_frontend_form_non_registered"]' ).next( 'span' ).show();
			}
		} );
	} );
} )( jQuery );