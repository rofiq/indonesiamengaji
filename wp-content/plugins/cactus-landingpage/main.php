<?php

/*
Plugin Name: CactusTheme - Landing Page
Description: Setup maintenance mode, coming soon mode
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('cactus_landing')){
	class cactus_landing{
		function __construct(){
			add_action( 'wp_loaded', array($this,'wp_loaded'), 0);
			add_action( 'admin_init', array($this,'admin_init'), 0);
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array($this, 'admin_video_scripts_styles') );
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts') );
			
			if(!shortcode_exists('ct_timer')){
				add_shortcode('ct_timer', array($this, 'parse_ct_counter'));
			}
		}
		
		/**
		 * validate user role to ignore
		 */
		private function is_valid_roles(){
			if(current_user_can('editor') || current_user_can('administrator')){
				return true;
			}
			
			return false;
		}
		
		/**
		 * ignore some pages like wp-login.php
		 */
		private function is_valid_pages(){
			if(strstr($_SERVER['PHP_SELF'], 'wp-cron.php') ||
                    strstr($_SERVER['PHP_SELF'], 'wp-login.php') ||
                    strstr($_SERVER['PHP_SELF'], 'wp-admin/') ||
                    strstr($_SERVER['PHP_SELF'], 'async-upload.php') ||
                    (strstr($_SERVER['PHP_SELF'], 'upgrade.php') || $this->is_valid_roles()) ||
                    strstr($_SERVER['PHP_SELF'], '/plugins/') ||
                    strstr($_SERVER['PHP_SELF'], '/xmlrpc.php') || strstr($_SERVER['PHP_SELF'], 'admin-ajax.php')){
						return true;
			}
			
			return false;
		}
		
		/**
		 * ignore ajax calls
		 */
		private function is_ajax_calls(){
			if(isset($_POST['_wpcf7_is_ajax_call']) || isset($_POST['gform_ajax'])){
				return true;
			}
			
			return false;
		}
		
		public function wp_loaded(){
			if(get_option('cactus-landing-page')){
				if(!$this->is_ajax_calls() && !$this->is_valid_roles() && !$this->is_valid_pages()) {
					// redirect to landing page
					$title = get_option('cactus-landing-page-title');
					$header = get_option('cactus-landing-page-header'); // header meta tags
					$css = get_option('cactus-landing-page-css'); // custom css
					$content = get_option('cactus-landing-page-content');
					
					include 'html.php';
					
					exit;
				}
			}
		}
		
		public function parse_ct_counter($atts, $content){
			$id 						= rand(1, 9999);
			$years_text					= (isset($atts['years_text']) && trim($atts['years_text'])!='') ? trim($atts['years_text']) : esc_html__('YRS', 'cactus');
			$months_text				= (isset($atts['months_text']) && trim($atts['months_text'])!='') ? trim($atts['months_text']) : esc_html__('MTHS', 'cactus');		
			$days_text					= (isset($atts['days_text']) && trim($atts['days_text'])!='') ? trim($atts['days_text']) : esc_html__('DAYS', 'cactus');
			$hours_text					= (isset($atts['hours_text']) && trim($atts['hours_text'])!='') ? trim($atts['hours_text']) : esc_html__('HRS', 'cactus');
			$minutes_text				= (isset($atts['minutes_text']) && trim($atts['minutes_text'])!='') ? trim($atts['minutes_text']) : esc_html__('MINS', 'cactus');
			$seconds_text				= (isset($atts['seconds_text']) && trim($atts['seconds_text'])!='') ? trim($atts['seconds_text']) : esc_html__('SECS', 'cactus');
			
			$years						= (isset($atts['years']) && is_numeric(trim($atts['years']))) ? trim($atts['years']) : date("Y");
			$months						= (isset($atts['months']) && is_numeric(trim($atts['months']))) ? trim($atts['months']) : date("m");
			$days						= (isset($atts['days']) && is_numeric(trim($atts['days']))) ? trim($atts['days']) : date("d");
			$hours						= (isset($atts['hours']) && is_numeric(trim($atts['hours']))) ? trim($atts['hours']) : '00';
			$minutes					= (isset($atts['minutes']) && is_numeric(trim($atts['minutes']))) ? trim($atts['minutes']) : '00';
			$seconds					= (isset($atts['seconds']) && is_numeric(trim($atts['seconds']))) ? trim($atts['seconds']) : '00';

			ob_start();?>
				
			<span 
				<?php echo ($id != '' ? ('id="ct-c-timer-' . $id  . '"') : '');?> 
				class="countdown-time shortcode"
				data-years-text		="<?php echo $years_text;?>"
				data-months-text	="<?php echo $months_text;?>"
				data-days-text		="<?php echo $days_text;?>"
				data-hours-text		="<?php echo $hours_text;?>"
				data-minutes-text	="<?php echo $minutes_text;?>"
				data-seconds-text	="<?php echo $seconds_text;?>"
				
				data-countdown		="<?php echo $years.'/'.$months.'/'.$days.' '.$hours.':'.$minutes.':'.$seconds;?>"
			>
				<?php echo esc_html__('Loading...', 'cactus');?>       
			</span>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
		
		public function admin_init(){
			
			// register settings
			register_setting( 'cactus-landing-group', 'cactus-landing-page-title' );
			register_setting( 'cactus-landing-group', 'cactus-landing-page-css' );
			register_setting( 'cactus-landing-group', 'cactus-landing-page-header' );
			register_setting( 'cactus-landing-group', 'cactus-landing-page-content' );
			register_setting( 'cactus-landing-group', 'cactus-landing-page' );
		}
		
		public function admin_menu(){
			add_options_page(
				esc_html__('CactusThemes - Landing Page','cactus'),
				esc_html__('CactusThemes - Landing Page','cactus'),
				'manage_options',
				'cactus_landing_page',
				array(
					$this,
					'settings_page'
				)
			);
		}
		
		function enqueue_scripts(){
			wp_enqueue_script('jquery.plugin', plugins_url('/js/countdown/jquery.plugin.min.js', __FILE__) , array('jquery'), '20160701', true );
			wp_enqueue_script('jquery.countdown', plugins_url('/js/countdown/jquery.countdown.js', __FILE__) , array('jquery.plugin'), '20160701', true );
			wp_enqueue_script('cactusthemes-landingpage-js', plugins_url('/js/main.js', __FILE__) , array('jquery.plugin','jquery.countdown'), '20160701', true );
		}
		
		function admin_video_scripts_styles() {
			wp_enqueue_style('cactusthemes-landing-css',plugins_url('/css/admin.css', __FILE__));
		}
		
		public function settings_page(){
			?>
			<div class="cactus-landing-page wrap">
				<h2>CactusThemes - Landing Pages</h2>
				<p class="intro">Use shortcode <br/><b><i>[ct_timer years="0" years_text="YRS" months="0" months_text="MTHS" days="0" days_text"DAYS" hours="0" hours_text="HRS" minutes="0" minutes_text="MINS" seconds="0" seconds_text="SECS"]</i></b><br/> to add a time counter</p>
				<form method="post" action="options.php"> 
				<?php 
				
				settings_fields( 'cactus-landing-group' );
	
				?>
				<p><label><span class="label">Enable Maintenance Mode</span><input type="checkbox" name="cactus-landing-page" <?php echo get_option('cactus-landing-page') ? 'checked="checked"' : '';?>/>
				</label></p>
				<p>
					<label>
						<span class="label">Landing Page Title</span><br/>
						<input type="text" name="cactus-landing-page-title" size="100" value="<?php echo get_option('cactus-landing-page-title','');?>"/>
					</label>
				</p>
				<p>
					<label>
						<span class="label">Landing Page Header Tags</span><br/>
						<textarea name="cactus-landing-page-header" cols="100" rows="5"><?php echo get_option('cactus-landing-page-header','');?></textarea>
					</label>
					<span class="desc">Custom Meta Tags in <i>&lt;header&gt;</i>. Make sure you use valid HTML tags (such as <i>&lt;title&gt;</i></span>
				</p>
				<p><label><span class="label">Landing Page Custom CSS</span><br/>
					<textarea name="cactus-landing-page-css" cols="100" rows="10"><?php echo get_option('cactus-landing-page-css','');?></textarea>
				</label>
					<span class="desc">Custom CSS for landing page</span>
				</p>
				<p><label><span class="label">Landing Page Content</span><br/>
					<?php wp_editor(get_option('cactus-landing-page-content','') , 'cactus-landing-page-content', array('drag_drop_upload' => true,'wpautop' => false));?>
				</label></p>
				<?php submit_button();?>
				</form>
			</div>
			<?php
		}
	}
}

$cactus_landing = new cactus_landing();