(function($) {
    'use strict';
    jQuery(document).ready(function() {
        jQuery('#selectall').click(function(event) {  //on click 
            if (this.checked) { // check select status
                jQuery('.woo_chk').each(function() { //loop through each checkbox
                    this.checked = true;  //select all checkboxes with class "checkbox1"               
                });
            } else {
                jQuery('.woo_chk').each(function() { //loop through each checkbox
                    this.checked = false; //deselect all checkboxes with class "checkbox1"                       
                });
            }
        });
    });
})(jQuery);