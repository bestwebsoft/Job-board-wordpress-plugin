( function( $ ) {
	$( document ).ready( function() {
		/* Add notice about changing in the settings page */
		$( '#jbbrd_settings_form input, #jbbrd_settings_form select' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#jbbrd_settings_notice' ).css( 'display', 'block' );
			};
		});
		/* Add datapicker. */
		if ( $.isFunction( $.fn.datepicker ) ) {
			$('#jbbrd_expiry_date').datepicker( function() {
				dateFormat : 'dd-mm-yy'
			});
		}
	});
})(jQuery);