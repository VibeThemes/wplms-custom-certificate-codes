jQuery(document).ready(function($){

	$('.update_code').on('click',function(){
		var $this= $(this);
		var r = confirm("Are you sure you want to update the field ?");
		if (r == true) {
		    var key = $(this).attr('data-key');
		    var code = $(this).attr('data-val');
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
});