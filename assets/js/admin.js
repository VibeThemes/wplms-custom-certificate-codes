jQuery(document).ready(function($){


	$('.update_code').on('click',function(){
		var $this= $(this);
		var defaulttext = $this.html();
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
	              setTimeout(function(){
	              	$this.html(defaulttext);
	              },2000);
	            }
	        });

		} else {
		    return false;
		}
	});

//delete 
$('.delete_code').on('click',function(){

		var $this= $(this);
		var defaulttext = $this.html();
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
	              setTimeout(function(){
	              	$this.html(defaulttext);
	              },2000);
	            }

	        });

		} else {
		    return false;
		}
	});
	$('#search_course').on('click',function(){
		var pathname = window.location.pathname; // Returns path only
		var url      = window.location.href; 
		var fullpath = url+'&course='+$('#course_id').val();
		window.location.href = fullpath;
	});

	$('#execute_pattern').on('click',function(){
		var $this= $(this);
		var defaulttext = $this.html();
		var x = confirm("Make sure you've saved the settings first. Are you sure you want to continue ?");
		if (x == true) {
		    $this.html('Processing...');
	        $.ajax({
	            type: "POST",
	            url: ajaxurl,
	            async:true,
	            data: { action: 'process_code_all', 
	                    security_nonce: $('#_wpnonce').val(),
	                  },
	            cache: false,
	            cache: false,
	            success: function (html) {
	              $this.html(html);
	              setTimeout(function(){
	              	$this.html(defaulttext);
	              },2000);
	            }
	        });

		} else {
		    return false;
		}
	});
});