jQuery(document).ready(function($){


	$('.update_code').on('click',function(){
		var $this= $(this);
		var r = confirm("Are you sure you want to update the Code ?");
		if (r == true) {
		    var key = $(this).attr('data-key');
		    
	        $.ajax({
	            type: "POST",
	            url: ajaxurl,
	            async:true,
	            data: { action: 'update_certificate_code_meta', 
	                    security: $('#_wpnonce').val(),
	                    activity_id: $this.attr('data-key'),
	                    meta_value: $('#'+key).val(),
	                    meta_key: $('#'+key).attr('data-key')
	                  },
	            cache: false,
	            cache: false,
	            success: function (html) {
	              $this.html(html);
	            }
	        });

		} else {
		    return false;
		}
	});

//delete 
$('.delete_code').on('click',function(){

		var $this= $(this);
		var x = confirm("Are you sure you want to delete the Code ?");
		if (x == true) {
		    var key = $(this).attr('data-key');
		    
	        $.ajax({
	            type: "POST",
	            url: ajaxurl,
	            async:true,
	            data: { action: 'delete_certificate_code_meta', 
	                    security_nonce: $('#_wpnonce').val(),
	                    a_id: $this.attr('data-key'),
	                    meta_value: $('#'+key).val(),
	                    meta_key: $('#'+key).attr('data-key')
	                  },

	            cache: false,
	            cache: false,
	            success: function (html) {

	              $this.html(html);
	              $('#'+key).val("");

	            }

	        });

		} else {
		    return false;
		}
	});

});