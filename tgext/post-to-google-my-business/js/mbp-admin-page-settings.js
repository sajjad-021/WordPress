jQuery(document).ready(function($) {
	var userSelector = $('select[name="mbp_google_settings[google_user]"]');
	var businessSelector = $('input[name="mbp_google_settings[google_location]"]:checked');
	
	if(businessSelector.val() == '0' && userSelector.val() != '0'){
		refreshBusinesses();
	}
	
	userSelector.change(function(){
		refreshBusinesses();
	});
	
	$('#refresh-api-cache').click(function(){
		refreshBusinesses(true);
		$(this).html("Please wait...").attr('disabled', true);
	});
	
	function refreshBusinesses(refresh){
		refresh = refresh || false;
		var data = {
			'action': 'mbp_get_businesses',
			'user_id': userSelector.val(),
			'refresh': refresh,
			'selected': businessSelector.val(),
		};
		$('.mbp-business-selector').empty();
		jQuery.post(ajaxurl, data, function(response) {
			
			$('.mbp-business-selector').replaceWith(response);
			$('#refresh-api-cache').html("Refresh locations").attr('disabled', false);
			checkForDisabledLocations();
		});
	}
	
	
	function checkForDisabledLocations(){
		if($('.mbp-business-selector input:disabled').length){
			$('.mbp-location-blocked-info').show();
			return;
		}
		$('.mbp-location-blocked-info').hide();
	}
	checkForDisabledLocations();
});