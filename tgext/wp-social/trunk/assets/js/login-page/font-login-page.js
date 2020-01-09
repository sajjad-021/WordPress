
function custom_login_form(){
		var mainDiv = document.getElementById('XS_social_login_div');
		var innerData = mainDiv.innerHTML;
		mainDiv.innerHTML = '';
		mainDiv.remove();
		
		var appendDiv = document.createElement('div');
		appendDiv.setAttribute('id', 'XS_social_login_div_login');
		appendDiv.setAttribute('class', mainDiv.classList);
		appendDiv.innerHTML = innerData;

	   // for login form page
	   	var formId = document.getElementById('loginform');
		if(formId){
			formId.appendChild(appendDiv);
		 } else { 
			// for login form page
			var formRegId = document.getElementById('registerform');
			if(formRegId){
				//formRegId.appendChild(appendDiv);
				formRegId.insertBefore(appendDiv, formRegId.childNodes[0]);
			}
		}
}

window.addEventListener('load', function () {
	custom_login_form();
});

