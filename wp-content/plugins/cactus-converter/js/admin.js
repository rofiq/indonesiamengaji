var converter = {};

;(function($){
	converter.submit = function(index, btn){
		btn.addClass('disabled');
		
		var val = $('#converter-theme-select').val();
		
		params = {
				action: 'videopro_import',
				theme: val,
				index: index
				};

		$.ajax({
					type: 'post',
					url: ajaxurl,
					dataType: 'html',
					data: params,
					success: function(result){
						var obj = JSON.parse(result);
						
						if(obj.error){
							$('#converter-message').prepend('<p class="error">' + obj.message + '</p>');
						} else {
                            $('#converter-message').prepend('<p>' + obj.message + '</p>');
							progress = obj.progress;
							if(progress < 100){
								btn.next().children('.inner').attr('style','width:' + progress + '%');
								
								converter.submit(index + 1, btn);
							} else {
								btn.next().children('.inner').attr('style','width:' + 100 + '%');
                                
                                $('#converter-message').prepend('<p class="success">DONE! Congratulations! You can now remove this plugin as it just needs to be run once.</p>');
                                
                                
							}
						}
					},
                    error: function(result){
                        var obj = JSON.parse(result.responseText);
                        $('#converter-message').prepend('<p class="error">' + obj.message.message + ' in ' + obj.message.file + ' at ' + obj.message.line + '</p>');
                        
                        // continue
                        converter.submit(index + 1, btn);
                    }
				});
	}
	
	
}(jQuery));

jQuery(document).ready(function($) {
	$('#converter-button').on('click', function(evt){
		if(!$(this).hasClass('disabled')){
			converter.submit(0, $(this));	
		}		
	});
});