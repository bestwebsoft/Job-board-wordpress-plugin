( function( $ ) {
	$( document ).ready( function() {
		/* Add notice about changing in the settings page */
		$( '#jbbrd_settings_form input, #jbbrd_settings_form select' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade, #jbbrd_settings_error' ).css( 'display', 'none' );
				$( '#jbbrd_settings_notice' ).css( 'display', 'block' );
			};
		});
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
	});
})(jQuery);