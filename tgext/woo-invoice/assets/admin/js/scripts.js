jQuery(document).ready(function($) {

    $(document).on( 'click', '.template-containers .template-single', function() {

        $(this).find('input[type=radio]').attr('checked', 'checked');

        $(this).parent().find('.template-single.active').removeClass( 'active' );
        if( ! $(this).hasClass( 'active' ) ) {
            $(this).addClass( 'active' );
        }
    })
	
	$(document).on( 'click', '.wooin_invoice_preview', function() {

        __HTML__ = $(this).html();
        $(this).html( '<i class="icofont icofont-spinner-alt-2 icofont-spin"></i>');
        
        order_id = $(this).attr( 'order_id' );

        $.ajax(
			{
		type: 'POST',
		context: this,
		url:wooin_ajax.wooin_ajaxurl,
		data: {
			"action" 		: "wooin_admin_ajax_load_invoice", 
			"order_id"      : order_id,
		},
		success: function( response ) {
			
			var data = JSON.parse( response );

            console.log( response );

			if( ! data.status ) return false;

			$('.wooin_modal_container').find('.wooin_modal_content').html( data.html );
            $('.wooin_modal_container').fadeIn();
            $(this).html( __HTML__ );
		}
            });

        return false;
	})

	$(document).on( 'click', '.wooin_invoice_download', function(e) {

        target_url = $(this).attr( 'target_url' );
        
        if( typeof target_url === undefined || target_url.length == 0 ) return false;
        window.open( target_url, '_blank' );
        return false;
    })
    
    
	$(document).on( 'click', '.wooin_modal_close', function() {
        $('.wooin_modal_container').fadeOut();
    })
    


});	