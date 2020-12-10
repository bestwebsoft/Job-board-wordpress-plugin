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
			} ).on( 'keydown', function () {
				return false;
			} );
		}
	} );
} )( jQuery );