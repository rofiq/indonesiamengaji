/**
 * DEBUG:
 *
 * step = 0  - Import categories
 * step = 10 - Import pages
 * step = 25 - Import posts
 * step = 55 - Import menu items
 * step = 85 - Import theme options, setup mega menu
 * step = 90 - Import widget settings, widget logics, setup homepage * blog pages, do other things
 *
 * item_index is the index of item to start import in each step
 *
 *
 *
 * 
 */
 var DEBUG_MODE = false;
 var DEBUG_STEP = 0;
 var DEBUG_INDEX = 0;
 
/* =================  do not edit anything below this line ====================================
 * ============================================================================================
 */
 var cactus_importer = {};
;(function($){
	cactus_importer.doing = false;
	cactus_importer.do_import = function(pack){
		if(!cactus_importer.doing){
			step = 0;
			item_index = 0;
			
			if(DEBUG_MODE){
				step = DEBUG_STEP;
				item_index = DEBUG_INDEX;
			}
			
			option_only = $('#import-options-' + pack).is(':checked');

			if(confirm('Install Demo Data:\n' +
						'-----------------------------------------\n' +
						'Are you sure? This will import our predefined settings for the demo (background, template layouts, fonts, colors etc...) and our sample content. \n\n' +
						'Please backup your settings to be sure that you don\'t lose them by accident.\n\n\n' +
						'-----------------------------------------')){
				$('#import-button-' + pack).html('installing...');
				$('#import-progress-' + pack).addClass('loading');
				$('.import-button a').addClass('disabled');
				
				cactus_importer.doing = true;
				$('#import-options-default').attr('disabled', true);
				cactus_importer.do_import_partial(pack, step, item_index, option_only);
			}
		}
	}
	
	cactus_importer.do_import_partial = function(pack, step, index, option_only){
		params = {
					action:'cactus_import',
					pack: pack,
					step: step,
					index: index,
					option_only: (option_only ? 1 : 0),
					debug: (DEBUG_MODE ? 1 : 0)
				};

		$.ajax({
					type: 'post',
					url: ajaxurl,
					dataType: 'html',
					data: params,
					success: function(progress){
						var obj = JSON.parse(progress);
						
						if(obj.error){
							alert(obj.error_message);
						} else {
							progress = obj.total_progress;
							index = obj.index; // index in each progress
							
							if(obj.step > step){
								index = -1; // reset index
							}
							
							step = obj.step;

							$('#import-progress-' + pack + ' .inner').attr('style', 'width:' + progress + '%');
							
							if(progress >= 100){
								$('#import-button-' + pack).html('Installed!');
								
								// hide progress bar
								$('#import-progress-' + pack + ' .inner').attr('style', 'width:' + 0 + '%');
								$('#import-progress-' + pack).css('background', 'transparent');
								
								cactus_importer.doing = false;
							} else {
								if(DEBUG_MODE && step > DEBUG_STEP){
									// if we are debugging, then we stop here
									$('#import-button-' + pack).html('([DEBUG MODE] Installed!');
								
									// hide progress bar
									$('#import-progress-' + pack + ' .inner').attr('style', 'width:' + 0 + '%');
									$('#import-progress-' + pack).css('background', 'transparent');
									
									cactus_importer.doing = false;
								} else {
									cactus_importer.do_import_partial(pack, step, parseInt(index) + 1, option_only);
								}
							}
						}
					}
			});
	}
}(jQuery));

jQuery(document).ready(function($){
	
});