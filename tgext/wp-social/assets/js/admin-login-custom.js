
// show hide js global function
function xs_show_hide(getID){
	var idData = document.getElementById('xs_data_tr__'+getID);
	idData.classList.toggle('active_tr');
}


function xs_show_hide_role(getIdData){
	var idData = document.getElementById('xs_data_tr__role');
	
	if(getIdData == 1){
		idData.classList.add('active_tr');
	}else{
		idData.classList.remove('active_tr');
	}
}
//copy function
function copy_callback(fordata){
	var copyText = document.getElementById(fordata+'_callback');
	copyText.select();
	document.execCommand("copy");
}

function xs_counter_open(evt){
	let targetId = evt.getAttribute("xs-target-id");
	if(!targetId){
		return false;
	}
	
	let targetData = document.getElementById(targetId);
	
	let back = document.querySelector('.xs-backdrop');
	if(targetData){
		targetData.classList.toggle('is-open');
		back.classList.toggle('is-open');
	}
}

jQuery(document).ready(function () {
	function nx_hash_access_tab_setActive() {
		setTimeout(function () {
			var hash = window.location.hash.substr(1);
			if (hash) {
				//jQuery('.xs-donate-metabox-tabs li').removeClass('active');
				alert(hash);
			}
		}, 15);
	}
	//nx_hash_access_tab_setActive(); // On Page Load

});



