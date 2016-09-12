<?php
/**
 * cactus theme sample theme options file. This file is generated from Export feature in Option Tree.
 *
 * @package videopro
 */

//hook and redirect
function videopro_activation($oldname, $oldtheme=false) {
	//header( 'Location: '.admin_url().'admin.php?page=cactus-welcome');
	wp_redirect(admin_url().'themes.php?page=videopro-welcome');
}
add_action('after_switch_theme', 'videopro_activation', 10 ,  2); 

//welcome menu
add_action('admin_menu', 'videopro_welcome_menu');
function videopro_welcome_menu() {
	add_theme_page(esc_html__('Welcome','videopro'), esc_html__('VideoPro Welcome','videopro'), 'edit_theme_options', 'videopro-welcome', 'videopro_welcome_function', 'dashicons-megaphone', '2.5');
}

//welcome page
function videopro_welcome_function(){
	wp_enqueue_style( 'font-awesome', get_template_directory_uri() . '/css/font-awesome/css/font-awesome.min.css', array(), '4.3.0');
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'welcome';
    ?>
    <div class="wrap">
        <?php
		videopro_welcome_tabs();
		videopro_welcome_tab_content( $tab );
		?>
    </div>
    <?php
}

function videopro_admin_enqueue_scripts() {
        wp_enqueue_style( 'videopro-adm-google-fonts', videopro_get_google_fonts_url(array('Poppins')), array(), '1.0.0' );
}
add_action( 'admin_enqueue_scripts', 'videopro_admin_enqueue_scripts' );

//tabs
function videopro_welcome_tabs() {
    $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'welcome';
	$cactus_welcome_tabs = array(
		'welcome' => '<span class="dashicons dashicons-smiley"></span> '.esc_html__('Welcome','videopro'),
		'document' => '<span class="dashicons dashicons-format-aside"></span> '.esc_html__('Document','videopro'),
		'sample' => '<span class="dashicons dashicons-download"></span> '.esc_html__('Sample Data','videopro'),
		'support' => '<span class="dashicons dashicons-businessman"></span> '.esc_html__('Support','videopro'),
	);
	
	echo '<h1></h1>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $cactus_welcome_tabs as $tab_key => $tab_caption ) {
        $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
        echo '<a class="nav-tab ' . $active . '" href="?page=videopro-welcome&tab=' . $tab_key . '">' . $tab_caption . '</a>';
    }
    echo '</h2>';
}
function videopro_welcome_tab_content( $tab ){
	if($tab == 'document'){ ?>
    	<p>You could view <a class="button button-primary button-large" href="http://videopro.cactusthemes.com/doc/" target="_blank">Full Document</a> in new window</p        
    ><?php 
	} elseif($tab == 'sample'){
    	if(!class_exists('cactus_demo_importer')){
			?>
			<p style="color:#FF0000"> <?php echo esc_html__('Please install VideoPro-SampleData plugin to use this feature','videopro');?> </p>
			<?php
		} else {
			do_action('videopro_import_data_tab'); 
		}
	} elseif($tab == 'support'){ ?> 
    	<p>You could open <a class="button button-primary button-large" href="http://ticket.cactusthemes.com/" target="_blank">Support Ticket</a> in new window</p>        
	<?php } else{ ?>
		<div class="cactus-welcome-message">
			<h2 class="cactus-welcome-title"><?php esc_html_e('Welcome to VideoPro - The Ultimate Video Solution for WordPress','videopro');?></h2>
            <div class="cactus-welcome-inner">
            	<a class="cactus-welcome-item" href="http://doc.cactusthemes.com/videopro/quickstart.html" target="_blank">
                	<i class="fa fa-file-text"></i>
                    <h3><?php echo esc_html__('Quick Start Guide','videopro'); ?></h3>
                    <p><?php echo esc_html__('Save your time with VideoPro quick start document','videopro'); ?></p>
                </a>
                <a class="cactus-welcome-item" href="?page=videopro-welcome&tab=document">
                	<i class="fa fa-book"></i>
                    <h3><?php echo esc_html__('Full Document','videopro'); ?></h3>
                    <p><?php echo esc_html__('See the full user guide for all VideoPro functions','videopro'); ?></p>
                </a>
                <br />
                <a class="cactus-welcome-item" href="?page=videopro-welcome&tab=sample">
                	<i class="fa fa-download"></i>
                    <h3><?php echo esc_html__('Sample Data','videopro'); ?></h3>
                    <p><?php echo esc_html__('Import sample data to have homepage like our live DEMO','videopro'); ?></p>
                </a>
                <a class="cactus-welcome-item" href="?page=videopro-welcome&tab=support">
                	<i class="fa fa-user"></i>
                    <h3><?php echo esc_html__('Support','videopro'); ?></h3>
                    <p><?php echo esc_html__('Need support using the theme? We are here for you.','videopro'); ?></p>
                </a>
                <div class="cactus-welcome-item cactus-welcome-item-wide cactus-welcome-changelog">
                	<ul>
						<li>#First release - 2016.06.18</li>
					</ul>
                </div>
            </div>
		</div>
	<?php }
}


//old import sample data
add_action( 'admin_notices', 'videopro_print_current_version_msg' );
function videopro_print_current_version_msg()
{
	$current_theme = wp_get_theme();
	$current_version =  $current_theme->get('Version');
	echo '<div style="display:none" id="current_version">' . $current_version . '</div>';
}

add_action( 'admin_footer', 'videopro_import_sample_data_comfirm' );
function videopro_import_sample_data_comfirm()
{
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#ct_support_forum').parent().attr('target','_blank');
        $('#ct_documentaion').parent().attr('target','_blank');
        $('#option-tree-sub-header').append('<span class="option-tree-ui-button left text">VideoPro</span><span class="option-tree-ui-button left vesion ">ver. ' + $('#current_version').text() + '</span>');
    });
    </script>
    <?php
}