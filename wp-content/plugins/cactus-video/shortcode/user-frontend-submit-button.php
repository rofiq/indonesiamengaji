<?php

function parse_v_submit_button($atts, $content){
	ob_start();
	$bg_bt_submit = isset($atts['bg']) ? $atts['bg'] : false;
	$color_bt_submit = isset($atts['color']) ? $atts['color'] : false;
	$bg_hover_bt_submit = isset($atts['bg_hover']) ? $atts['bg_hover'] : false;
	$color_hover_bt_submit = isset($atts['color_hover']) ? $atts['color_hover'] : false;
	$limit_tags = isset($atts['tags']) ? $atts['tags'] : '';	
	?>
	<div class="cactus-submit-video">
    	<?php if($bg_hover_bt_submit != '' || $color_hover_bt_submit != '' ){ ?>
			<!--<style>
                .cactus-submit-video a {background-color:<?php echo "#".$bg_bt_submit;?> !important; color:<?php echo "#".$color_bt_submit;?> !important;}
                .cactus-submit-video a:hover{background-color:<?php echo '#'.$bg_hover_bt_submit;?> !important; color:<?php echo '#'.$color_hover_bt_submit;?> !important;}
            </style>-->
        <?php }	?>    	
		<a href="#" data-toggle="modal" data-target="#videopro_submit_form" class="btn btn-default bt-style-1 padding-small">        	
        	<span><?php echo esc_html($content); ?></span>
        </a>
	</div>
	<?php
	
	if($limit_tags){ ?>
		<script language="text/javascript">
		jQuery(document).ready(function(e) {
			jQuery("form.wpcf7-form").submit(function (e) {
				var submit_tags = jQuery('input[name=tag].wpcf7-form-control').val().split(",");
				if(submit_tags.length > <?php echo $limit_tags ?>){
					if(jQuery('.limit-tag-alert').length==0){
						jQuery('.wpcf7-form-control-wrap.tag').append('<span role="alert" class="wpcf7-not-valid-tip limit-tag-alert"><?php echo sprintf(esc_html__('Please enter less than or equal to %s tags', 'videopro'), $limit_tags); ?>.</span>');
					}
					return false;
				}else{
					return true;
				}
			});
		});
		</script>
	<?php
	}
	
	$html = ob_get_contents();
	ob_end_clean();
	
	return $html;
}
add_shortcode( 'v_submit_button', 'parse_v_submit_button' );