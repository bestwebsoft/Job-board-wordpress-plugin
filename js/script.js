( function( $ ) {
	$( document ).ready( function() {
		/* Change radio button of currency unit chosen*/
		$( 'select[name="jbbrd_money_choose"]' ).focus( function() {
			$( '#preset_money_unit_select' ).attr( 'checked', 'checked' );
		});
		$( 'input[name="jbbrd_custom_money_unit"]' ).focus( function() {
			$( '#custom_money_unit_select' ).attr( 'checked', 'checked' );
		});		
		/* Add datapicker. */
		if ( $.isFunction( $.fn.datepicker ) ) {
			$('#jbbrd_expiry_date').datepicker( function() {
				dateFormat : 'dd-mm-yy'
			});
		}
		/* Change form settings */
		if ( ! $( 'input[name="jbbrd_frontend_form"]' ).is(":checked") ) {
			$( 'input[name="jbbrd_frontend_form_non_registered"]' ).hide();
			$( 'input[name="jbbrd_frontend_form_non_registered"]' ).next( 'span' ).hide();
		} else {
			$( 'input[name="jbbrd_frontend_form_non_registered"]' ).show();
			$( 'input[name="jbbrd_frontend_form_non_registered"]' ).next( 'span' ).show();
		}
		$( $( 'input[name="jbbrd_frontend_form"]' ) ).change( function() {
			if ( ! $( this ).is(":checked") ) {
				$( 'input[name="jbbrd_frontend_form_non_registered"]' ).hide();
				$( 'input[name="jbbrd_frontend_form_non_registered"]' ).next( 'span' ).hide();
			} else {
				$( 'input[name="jbbrd_frontend_form_non_registered"]' ).show();
				$( 'input[name="jbbrd_frontend_form_non_registered"]' ).next( 'span' ).show();
			}
		});

	});
})(jQuery);