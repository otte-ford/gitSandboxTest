(function($) {
	$(document).ready( function() {
		var colorPickerOptions = {
			change: function( event, ui ) {
				if( typeof bws_show_settings_notice == "function" )
					bws_show_settings_notice();
			}
		}

		if ( $.isFunction( $.fn.wpColorPicker ) ) {
			$( '.wp-color-picker' ).each( function() {
				$( this ).wpColorPicker( colorPickerOptions );
			});
		}

		$( '#ftrdpsts_theme_style' ).change( function() {
			if ( $( this ).attr( 'checked' ) ) {
				$( '.ftrdpsts_theme_style' ).hide();
			} else {
				$( '.ftrdpsts_theme_style' ).show();
			}
		});
	});
})(jQuery);