<?php
defined( 'ABSPATH' ) or die( 'You cannot access this script directly' );

class cactus_demo_importer{
	private $packages = array();
	private $base_dir = '';
	private $base_uri = '';

	function __construct($packages, $base_dir, $base_uri){
		
		$this->packages = $packages;
		$this->base_dir = $base_dir;
		$this->base_uri = $base_uri;

		require_once 'cactus_demo_content.php';
		
		add_action( 'wp_ajax_cactus_import', array($this, 'do_import' ));
		add_action( 'wp_ajax_nopriv_cactus_import', array($this, 'do_import' ));
		
		add_action( 'init', array($this,'init'), 1000);
		add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_styles') );
		
		add_action('admin_menu', array($this,'register_admin_menu'));
	}
	
	function register_admin_menu(){
		$import_page = add_submenu_page('tools.php', 'VideoPro - Export Taxonomy Metadata', 'VideoPro - Export Taxonomy Metadata', 'administrator', 'ct_video_settings_taxonomy', array($this, '_display_export_import_setting_page'));
	}
	
	function _display_export_import_setting_page() {
		ob_start();
?>
	<div class="wrap">
		<h1>VideoPro - Export Taxonomy Metadata</h1>
		<p>Copy content of settings into a text file to export. Paste content of settings into appropriate textbox and click "Import"</p>
		<?php
		
		$cats = get_categories(array('hide_empty' => 0));
		$cat_meta_list = array('cat_sidebar_', 'cat_layout_', 'cat_icon_', 'z_taxonomy_image');
		$cat_data = array();
		foreach($cats as $cat){
			$id = $cat->term_id;
			$slug = $cat->slug;
			$data = array();
			foreach($cat_meta_list as $key){
				$val = get_option($key . $id);
				if($val){
					$data[$key] = $val;
				}
			}
			
			$cat_data[$slug] = $data;
		}
		
		$cats = get_categories(array('taxonomy' => 'video-series', 'hide_empty' => 0));

		$cat_meta_list = array('video_series_release_', 'video_series_creator_', 'video_series_stars_', 'z_taxonomy_image');
		$series_data = array();
		foreach($cats as $cat){
			$id = $cat->term_id;
			$slug = $cat->slug;
			$data = array();
			foreach($cat_meta_list as $key){
				$val = get_option($key . $id);
				if($val){
					$data[$key] = $val;
				}
			}
			
			$series_data[$slug] = $data;
		}
		
		?>
		<table class="tbl-settings">
			<tr>
				<td width="160px" class="header">Category Metadata</td>
				<td><textarea id="category_metadata" cols="100" rows="8"><?php echo serialize($cat_data);?></textarea></td>
				<td>Import (not available yet)</td>
			</tr>
			<tr>
				<td class="header">Video Series Metadata</td>
				<td><textarea id="video_series_metadata" cols="100" rows="8"><?php echo serialize($series_data);?></textarea></td>
				<td>Import (not available yet)</td></tr>
		</table>
	</div>
<?php
		$html = ob_get_contents();
		ob_end_clean();
		echo $html;
	
	}
	
	private function get_base_directory(){
		return $this->base_dir; 
	}
	
	private function get_base_uri(){
		return $this->base_uri; 
	}
	
	function init(){
		// init action
		add_action('videopro_import_data_tab', array($this,'get_import_data_tab_content'));
	}
	
	function admin_enqueue_scripts_styles(){
		wp_enqueue_style('cactus-importer-css', $this->get_base_uri() . 'css/admin.css');
		wp_enqueue_script('cactus-importer-js', $this->get_base_uri() . 'js/admin.js', array('jquery'), '', true );
	}
	
	function do_import($package = '', $step = '', $index = ''){
		if(!$package)
			$package = $_POST['pack'];
		
		if(!$step)
			$step = $_POST['step'];
		
		if(!$index)
			$index = $_POST['index'];

		$option_only = $_POST['option_only'];
		
		$pack = $this->get_base_directory() . "packages/$package/cactus_demo_pack_$package.php";

		if(file_exists($pack)){
			require_once $pack;
			
			$pack = "cactus_demo_pack_$package";
			$package = new $pack($this->get_base_uri(), $this->get_base_directory());
			
			$step = $package->do_import($step, $index, $option_only);
			
			echo json_encode($step);
		} else {
			echo array('error' => 1, 'error_message' => esc_html__('cannot find data', 'cactus'));
		}

		die();
	}
	
	/**
	 * echo content for Import Data Tab
	 */
	public function get_import_data_tab_content(){
		if ( !current_user_can( 'manage_options' ) )
		{
			global $current_user;
			$msg = sprintf(esc_html__("I'm sorry, %s I'm afraid I can't do that.",'cactus'), $current_user->display_name);
			echo '<div class="wrap">' . $msg . '</div>';
			return false;
		}
		?>
			<div class="wrap">
				<h2><?php esc_html_e('Import Sample Data','cactus');?></h2>
			</div>
			<div class="admin-notice"><p><?php esc_html_e('Make sure you have installed all recommended plugins from WordPress repository (WordPress.org) and built-in plugins which come in the full package','cactus');?></p></div>
			<h4><?php esc_html_e('Choose a sample data package','cactus');?></h4>
				<input type="hidden" name="is_submit_import_data_form" value="Y">
				<input type="hidden" name="site_url" value="<?php echo esc_attr(site_url());?>">
				
				<div class="cactus-image-select">
					<?php foreach($this->packages as $item){
					
						$pack = $this->base_dir . "packages/$item/cactus_demo_pack_$item.php";
						require_once $pack;
				
						$pack = "cactus_demo_pack_$item";
						$package = new $pack('', '');
						
			
					?>
					<div class="item" >
						<img src="<?php echo $this->base_uri; ?>packages/<?php echo $item;?>/thumbnail.jpg" />
						<br>
						<p class="data-package-name">
                            <?php if(isset($package->url)){?>
                            <a href="<?php echo $package->url;?>" target="_blank" title="<?php esc_html_e('View Demo','cactus');?>">
                            <?php }?>
                        <b><?php echo $package->heading; ?></b>
                            <?php if(isset($package->url)){?>
                            </a>
                            <?php }?>
                        </p>
						<p><label><input type="checkbox" id="import-options-<?php echo $item;?>" name="option_only" value="1" /> <?php esc_html_e('Import Options Only','cactus'); ?></label></p>
						<p id="import-button-<?php echo $item;?>" class="import-button"><a href="javascript:void(0)" onclick="cactus_importer.do_import('<?php echo $item;?>');"><?php echo esc_html__('Import','cactus');?></a></p>
						<div class="progress-bar animate" id="import-progress-<?php echo $item;?>"><span class="inner" style="width:0%"><span><!-- --></span></span></div>
						<?php if(isset($package->description)){?>
						<div class="description"><?php echo $package->description;?></div>
						<?php }?>
					</div>
					
					<?php } ?>
				</div>
				<script>
				jQuery(document).ready(function(e) {
					jQuery('.cactus-image-select .item').click(function(){
						jQuery(this).addClass('selected').siblings().removeClass('selected');
					});
				});
				</script>
		<?php
	}
}