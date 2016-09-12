//Theme Options
var themeElements = {
	submitButton: '.submit-button',
};

//Loaded
jQuery(document).ready(function($) {
	//Submit Button
	$(themeElements.submitButton).not('.disabled').click(function() {
		var form=$($(this).attr('href'));
		
		if(!form.length || !form.is('form')) {
			form=$(this).parent();
			while(!form.is('form')) {
				form=form.parent();
			}
		};
			
		form.submit();		
		return false;
	});
	
	$('a[data-toggle="modal"], #videopro_submit_form .close, #videopro_submit_form').on('click', function(){
		$('#videopro_submit_form').toggleClass('active');
		return false;
	});	
	$('.modal .modal-content').on('click', function(event){
		event.stopPropagation();
	});
	
	var $videoScreenShotsbtn = $('#video-screenshots-button');
	
	if($('#video-screenshots-button').length>0){
		if(typeof(json_listing_img)=='object'){
			var html = '';
			html+='<div id="screenshots-overlay"><div class="spinner"></div></div>';
			html+='<div id="screenshots-lightbox">';
			html+=		'<div id="screenshots-preview"></div>';
			html+=		'';
			html+=		'';	
			html+='</div>';
			
			$videoScreenShotsbtn.on('click touchstart touchend',function(){
				
				$('body').addClass('active-screen-overlay');
				
				if($('#screenshots-overlay').length>0){
					$('body').addClass('active-screen-lightbox');
					return;
				};
				
				$('body').append(html);
				
				var $html_item = '';
				var firstIMG ='';				
				
				for(var i = 0; i < json_listing_img.length; i++){
					
					var smallIMG = json_listing_img[i][0];
					var largeIMG = json_listing_img[i][1];
					
					var activeClass = ' active-item';
										
					if(i==0){						
						firstIMG = smallIMG;
					}else{
						activeClass='';
					};				
					
					$html_item+=('<div class="screen-item'+activeClass+'"><img src="'+smallIMG+'" data-large-img="'+largeIMG+'"></div>');
					
				};				
				
				if($html_item!='' && firstIMG!=''){
					$('#screenshots-preview').append(
						'<div class="slider-screen"><div class="close-preview"><i class="fa fa-times" aria-hidden="true"></i></div><div class="large-img-wrapper"></div><div class="ctr-wrapper"><div class="slider-wrapper">'+$html_item+'</div></div></div>'
					);
					
					$('#screenshots-preview, .close-preview').on('click', function(){
						$('body').removeClass('active-screen-overlay active-screen-lightbox');
					});
					
					$('#screenshots-preview .slider-screen').on('click', function(event){
						event.stopPropagation();
					});
					
					$('#screenshots-preview .slider-wrapper').on('init', function(){
												
						$('#screenshots-preview .screen-item').on('click', function(){
							$('#screenshots-preview .screen-item').removeClass('active-item');
							$(this).addClass('active-item');
							
							var findLargeImg = $(this).find('img').attr('data-large-img');
							var findSmallImg = $(this).find('img').attr('src');
							var imgIndex = $('#screenshots-preview .large-img-wrapper img[data-index="'+$(this).attr('data-slick-index')+'"]');
							
							if(imgIndex.length==0){
								$('<img src="'+findSmallImg+'" data-index="'+$(this).attr('data-slick-index')+'" class="lazy-img">').appendTo('#screenshots-preview .large-img-wrapper');
								imgIndex = $('#screenshots-preview .large-img-wrapper img[data-index="'+$(this).attr('data-slick-index')+'"]');
								$('<img src="'+findLargeImg+'">').load(function(){
									imgIndex.attr('src', findLargeImg).removeClass('lazy-img');
								});
							};
							
							$('#screenshots-preview .large-img-wrapper img').hide();
							imgIndex.show();
							
							var offsetWrap = $('#screenshots-preview .ctr-wrapper').offset().left+$('#screenshots-preview .ctr-wrapper').width();
							var elmOffsetWrap = $(this).offset().left+$(this).outerWidth();
							
							if(elmOffsetWrap>=(offsetWrap-($(this).outerWidth()/2))) {$('#screenshots-preview .slick-next').trigger('click');};							
							if($('#screenshots-preview .ctr-wrapper').offset().left >= $(this).offset().left){$('#screenshots-preview .slick-prev').trigger('click');};
						});
						
						$('#screenshots-preview .screen-item[data-slick-index="0"]').trigger('click');
						
						$('body').addClass('active-screen-lightbox');
					});
					
					$('#screenshots-preview .slider-wrapper').slick({
						dots: false,
						infinite: false,
						speed: 200,
						variableWidth:true,
						slidesToShow: 5,
						draggable:false,
						responsive: [
							{
								breakpoint: 480,
								settings: {
									slidesToShow: 3,
								}
							}
						],
					});
										
				}
			});
			
		}else{
			$videoScreenShotsbtn.on('click touchstart touchend',function(evt){
				$('#video-screenshots').toggle();
				evt.stopPropagation();
			});
		}
	};
    
    $('#video_thumbnail_image .link').on('click', function(evt){
        var video_id = $(this).attr('data-id');
        if(video_id != ''){
            $('#video_thumbnail_image .ct-icon-video').addClass('loading');
            $.post({
                data: {action: 'get_video_player', id: video_id},
                url: cactus.ajaxurl,
                success: function(html){
                    $('#video_thumbnail_image').html(html);
                },
                error: function(){
                    
                }
            });
            
            evt.stopPropagation();
            return false;
        }
    });
    
    $('.btn-watch-later').on('click', function(evt){
        
        thebtn = $(this);
        var video_id = thebtn.attr('data-id');
        if(video_id != ''){
            thebtn.children('i').addClass('fa-spin');
            action = thebtn.attr('data-action');
            $.post({
                data: {action: 'add_watch_later', id: video_id, url: location.href, do: action},
                url: cactus.ajaxurl,
                success: function(result){
                    res = JSON.parse(result);
                    if(res.status == 1){
                        thebtn.addClass('added');
                        thebtn.children('i').addClass('fa-check');
                        thebtn.children('i').removeClass('fa-clock-o');
                    } else if(res.status == 0){
                        // show message
                        div = $('<div class="mouse-message font-size-1">' + res.message + '</div>');
                        position = thebtn.offset();
                        div.css({
                                top:position.top + 34,
                                left:position.left
                                });
                        div.appendTo('body');
                        
                        $(document).mouseup(function (e)
                        {
                            if (!div.is(e.target)
                                && div.has(e.target).length === 0)
                            {
                                div.hide();
                            }
                        });
                    } else if(res.status == -1){
                        // remove from list
                        thebtn.closest('.cactus-post-item').remove();
                    }
                    thebtn.children('i').removeClass('fa-spin');
                },
                error: function(){
                    alert('fail');
                    thebtn.children('i').removeClass('fa-spin');
                }
            });
        }
        
        evt.stopPropagation();
        return false;
    })
    
    updatePlayerSideAdPosition = function(){
        // ads on Video Player background
        $('.player-side-ad').each(function(){
            $parent_width = $(this).parent().width();
            $player_width = $('.cactus-post-format-video-wrapper', $(this).parent()).width();
            $ad_width = $(this).width();
            
            if($parent_width >= $player_width + 2 * $ad_width){
                if($(this).hasClass('left')){
                    $(this).css({left: ($parent_width - $player_width) / 2 - $ad_width});
                } else if($(this).hasClass('right')){
                    $(this).css({right: ($parent_width - $player_width) / 2 - $ad_width});
                }
                $(this).show();
            } else {
                $(this).hide();
            } 
        });
    }
    
    updatePlayerSideAdPosition();
    $(window)
		.on('resize', function(){			
            updatePlayerSideAdPosition();
        });
});

function isNumber(n) {return !isNaN(parseFloat(n)) && isFinite(n);};

var cactus_video = {};
cactus_video.subscribe_channel = function(button_id, subscribe_url){
	var self = this;
	jQuery(button_id).addClass('cactus-disable-btn');			
	
	subscribe_url = (subscribe_url.split("amp;").join(""));
	var id = self.getParameterByName('id', subscribe_url);
	var id_user = self.getParameterByName('id_user', subscribe_url);
	var counterCheck = 0;
	var url_ajax  		= jQuery('input[name=url_ajax]').val();
	var param = {
		action: 'videopro_subscribe',
		id: id,
		id_user: id_user,
	};
	
	jQuery.ajax({
		type: "post",
		url: url_ajax,
		dataType: 'html',
		data: (param),
		success: function(data){
			if(data == 1){
				jQuery(button_id).addClass( "subscribed" ).removeClass('cactus-disable-btn');
				jQuery(button_id+' a.btn').addClass( "subscribed" ).removeClass('subscribe');
				counterCheck=jQuery(button_id).find('.subscribe-counter').text();
				if(isNumber(counterCheck)) {
					counterCheck=parseFloat(counterCheck);
					jQuery(button_id).find('.subscribe-counter').text(counterCheck+1);
				};
			}else{
				jQuery(button_id).removeClass( "subscribed" ).removeClass('cactus-disable-btn');
				jQuery(button_id+' a.btn').removeClass( "subscribed" ).addClass('subscribe');
				counterCheck=jQuery(button_id).find('.subscribe-counter').text();
				if(isNumber(counterCheck)) {
					counterCheck = parseFloat(counterCheck);
					jQuery(button_id).find('.subscribe-counter').text(counterCheck-1);
				};
			};
		}
	});
	return false;	
	
	/*jQuery.get(subscribe_url, function( data ) {
	  if(data == 1){
		  jQuery(button_id).addClass( "subscribed" ).removeClass('cactus-disable-btn');
		  jQuery(button_id+' a.btn').addClass( "subscribed" ).removeClass('subscribe');
		  counterCheck=jQuery(button_id).find('.subscribe-counter').text();
		  if(isNumber(counterCheck)) {
			  counterCheck=parseFloat(counterCheck);
			  jQuery(button_id).find('.subscribe-counter').text(counterCheck+1);
		  };
	  }else{
		  jQuery(button_id).removeClass( "subscribed" ).removeClass('cactus-disable-btn');
		  jQuery(button_id+' a.btn').removeClass( "subscribed" ).addClass('subscribe');
		  counterCheck=jQuery(button_id).find('.subscribe-counter').text();
		  if(isNumber(counterCheck)) {
			  counterCheck = parseFloat(counterCheck);
			  jQuery(button_id).find('.subscribe-counter').text(counterCheck-1);
		  };
	  };
	});*/
};
cactus_video.getParameterByName = function(name, url){
	var self = this;
	name = name.replace(/[\[\]]/g, "\\$&");
	var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, " "));
};	

cactus_video.subscribe_login_popup = function(popup_id){
	
	jQuery(popup_id).toggleClass('active');
	
	jQuery(popup_id+' .close, '+popup_id)
	.off('.popupDestroy')
	.on('click.popupDestroy', function(){
		jQuery(popup_id).toggleClass('active');
		return false;
	});	
		
	jQuery(popup_id+' .modal-content')
	.off('.popupDestroy')
	.on('click.popupDestroy', function(event){
		event.stopPropagation();
	});
};