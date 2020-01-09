jQuery(document).ready(function($) {	
	var editing = false;
	var media_uploader = null;
	
	$('#meta-image-button').click(function() {
		media_uploader = wp.media({
			frame:    "post",
			state:    "insert",
			multiple: false
		});

		media_uploader.on("insert", function(){
			var json = media_uploader.state().get("selection").first().toJSON();
			console.log(json);
			var image_url = json.url;
			var image_caption = json.caption;
			var image_title = json.title;
			$('#meta-image').val(image_url);
			$('#meta-image-preview').attr('src',image_url);
			$('input[name="mbp_attachment_type"]').val('PHOTO');
		});

		media_uploader.open();
		return false;
	});

	$('#meta-video-button').click(function() {
		tb_show("Post to Google My Business", "#TB_inline?width=600&height=300&inlineId=video-thickbox");
		return false;
	});	
	
	
/* Premium Code Stripped by Freemius */


	
	function show_error(error){
		const error_notice = $('.mbp-error-notice');
        error_notice.html(error);
        error_notice.show();
	}
	
	function hide_error(){
		const error_notice = $('.mbp-error-notice');
		error_notice.hide();
	}
	
	function clear_fields(){	
		const tab = $('.mbp-tab-default');
		switch_tab(tab);
		$('#meta-image-preview').attr('src','');
		$('#mbp-publish-post').html(mbp_localize_script.publish_button);
		$('input[name="mbp_existing_post"]').val('');
		$(':input','fieldset#mbp-post-data').not(':button, :submit, :reset, .mbp-hidden, :radio').removeAttr('checked').removeAttr('selected').not(':checkbox, :radio, select').val('').change();
	}
	
	function load_post(post_id, edit){
		hide_error();
		clear_fields(); 
		if(edit){
			editing = post_id;
		}else{
			editing = false;
		}

        const post_form_container = $(".mbp-post-form-container");
        post_form_container.slideUp("slow");

		const data = {
			'action': 'mbp_load_post',
			'mbp_post_id': post_id,
			'mbp_post_nonce': mbp_localize_script.post_nonce
		};
		
		$.post(ajaxurl, data, function(response) {
			if(response.error){
				show_error(response.error);
				return;
			}	
			
			if(response.success){
				
				$.each(response.post.form_fields, function(name, value){
					var field = $('[name="' + name + '"], [name="' + name + '[]"]');
					
					if(field.is(':checkbox') || field.is(':radio')){
						
						if($.isArray(value)){
							$.each(value, function(key, checkboxVal){
								$('[name="' + name + '[]"][value="' + checkboxVal + '"]').attr('checked', true);
							});
						}else{
							$('[name="' + name + '"][value="' + value + '"]').attr('checked', true);
						}
						
					}else{
						field.val(value);
					}
				});
				if(editing && response.post.post_status === 'publish'){
                    $('#mbp-publish-post').html(mbp_localize_script.update_button);
				}
				if(response.has_error){
					show_error(response.has_error);
				}
				var tab = $('a[data-topic="'+ response.post.form_fields.mbp_topic_type +'"]');
				switch_tab(tab);
                post_form_container.slideDown("slow");
                $('#post_text').trigger("keyup");
			}
		});
	}
	
	function delete_post(post_id){
		hide_error();
		const data = {
			'action': 'mbp_delete_post',
			'mbp_post_id': post_id,
			'mbp_post_nonce': mbp_localize_script.post_nonce
		};
		$.post(ajaxurl, data, function(response) {
			if(response.success){
				return true;
			}else{
				show_error(response.data.error);
				return false;
			}			
		});

	}
	

	
	function switch_tab(clicked){
		$('.mbp-post-form-container .nav-tab').removeClass("nav-tab-active"); 
		$(clicked).addClass("nav-tab-active"); 
		$('.mbp-fields tr').not('.mbp-button-settings').hide(); //Spaghetti
		$('.mbp-fields tr.' + $(clicked).data('fields')).not('.mbp-button-settings').show();
		$('input[name="mbp_topic_type"]').val($(clicked).data("topic"));			
	}

	//Persist the state of the advanced settings menu
	if(localStorage.openAdvanced && JSON.parse(localStorage.openAdvanced) === true){
        var advanced_settings = $(".mbp-advanced-post-settings");
        advanced_settings.show();
	}
	
	$('#mbp-new-post').click(function(event) {
        event.preventDefault();
		editing = false;
		clear_fields();
		$(".mbp-post-form-container").slideToggle("slow");
		$('#mbp-draft-post').show();
        
/* Premium Code Stripped by Freemius */


	});		
	
	$('.mbp-toggle-advanced').click(function(event) {
		var advanced_settings = $(".mbp-advanced-post-settings");
		if(advanced_settings.is(":hidden")){
			localStorage.openAdvanced = JSON.stringify(true);
		}else{
            localStorage.openAdvanced = JSON.stringify(false);
		}
		advanced_settings.slideToggle("slow");
		event.preventDefault();
	});
	
	$('.mbp-post-form-container .nav-tab').click(function(event) {
		switch_tab(this);
		event.preventDefault();
	});
	
	$('#mbp-cancel-post').click(function(event){
		$(".mbp-post-form-container").slideUp("slow");
		clear_fields(); 
		event.preventDefault();
	});


    $('#publish, #original_publish').click(function(event) {
        if($(".mbp-post-form-container").is(":visible")){
        	const publish = confirm(mbp_localize_script.publish_confirmation);
        	if(publish){
                $("#mbp-publish-post" ).trigger("click");
			}else{
                $("#mbp-draft-post").trigger("click");
			}
		}
    });

    /*
    $('#mbp-draft-post').click(function(event){
        event.preventDefault();
    	const draftButton = this;
        $(draftButton).html("Please Wait...").attr('disabled', true);

	});
	*/

	$('#mbp-publish-post, #mbp-draft-post').click(function(event){
		hide_error();
		event.preventDefault();
		const publishButton = this;
		$(publishButton).html(mbp_localize_script.please_wait).attr('disabled', true);


		let draft = false;
		if(this.id === 'mbp-draft-post'){
           	draft = true;
		}


		var mbp_fields_data = {
			'action': 'mbp_new_post',
			'mbp_form_fields': $('fieldset#mbp-post-data').serializeArray(),
			'mbp_post_id': mbp_localize_script.post_id,
			'mbp_post_nonce': mbp_localize_script.post_nonce,
			'mbp_editing': editing,
			'mbp_draft': draft
		}; 						
		
		
		$.post(ajaxurl, mbp_fields_data, function(response) {
			if(response.success === false){
				show_error(response.data.error);
			}else if(response.success && !draft){
				$(".mbp-post-form-container").slideUp("slow");
			}
			
			if(!editing){
				$(".mbp-existing-posts tbody").prepend(response.data.row).show("slow");
			}else{
				$(".mbp-existing-posts tbody tr[data-postid='" + editing + "']").replaceWith(response.data.row);
			}
			$(".mbp-existing-posts .no-items").hide();
			
			if(!draft){
				$(publishButton).html(mbp_localize_script.publish_button).attr('disabled', false);				
			}else{				
                $(publishButton).html(mbp_localize_script.draft_button).attr('disabled', false);
			}
			editing = response.data.id;
		});	

		
		
		return true;
	});
	
	$('.mbp-existing-posts').on('click', 'a.mbp-action', function(event){
		var post_id = $(this).closest('tr').data('postid');
		var action = $(this).data('action');
		switch(action){
			case 'edit':
				load_post(post_id, true);
				break;
			
			case 'duplicate':
				load_post(post_id, false);
				break;
				
			case 'trash':
				delete_post(post_id);

				if(editing === post_id){
                    $(".mbp-post-form-container").slideUp("slow");
                    clear_fields();
				}
				const post_tr = $(this).closest('tr');
                post_tr.hide('slow');
                post_tr.remove();
				if($(".mbp-post").length <= 0){
                    $(".mbp-existing-posts .no-items").show();
				}
				break;
		}
		event.preventDefault();
	});
	
	$('#mbp_button').change(function() {	
		if(this.checked) {
			$(".mbp-button-settings").fadeIn("slow");
		}else{
			$(".mbp-button-settings").fadeOut("slow");
		}
	});
	
	$('input[type=radio][name=mbp_button_type]').change(function() {
		if(this.value == 'CALL'){
			$(".mbp-button-url").fadeOut("slow");
		}else{
			$(".mbp-button-url").fadeIn("slow");
		}
	});

	$('#post_text').change(function () {
        $(this).trigger("keyup");
    });

    $('#post_text').keyup(function () {
    	let counter = $('.mbp-character-count');
        let count = $(this).val().length;
        let words = $(this).val().split(' ').length - 1;
        counter.text(count);
        if(count > 1500){
            counter.css('color', 'red');
		}else{
            counter.css('color', 'inherit');
		}
        $('.mbp-word-count').text(words);
    });
	
	
/* Premium Code Stripped by Freemius */

});