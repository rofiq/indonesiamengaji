<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" lang="en-US">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" lang="en-US">
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html lang="en-US">
	<!--<![endif]-->
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo $title;?></title>		
		<?php wp_head();?>    
		<?php echo $header;?>
		<?php if($css != ''){?>
		<style type="text/css">
			<?php echo $css;?>
		</style>
		<?php }?>
	</head>
	<body>
		<div id="body-wrap">
			<div id="wrap">
				<div class="comming-soon-wrapper dark-div">
					<div class="comming-soon-content">
                                            
                        <?php echo apply_filters('the_content', $content);?>
						
					</div>
				</div>
			</div>
		</div>
        <script src="<?php echo plugins_url('js/countdown/jquery.plugin.min.js', __FILE__);?>"></script>
		<script src="<?php echo plugins_url('js/countdown/jquery.countdown.js', __FILE__);?>"></script>
		<script src="<?php echo plugins_url('js/main.js', __FILE__);?>"></script>
		<?php wp_footer();?>
	</body>
</html>