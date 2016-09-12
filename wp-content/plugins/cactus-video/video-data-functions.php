<?php
/*
 ********************
	 Multi links
 ********************
 */
add_action( 'add_meta_boxes', 'tm_mtl_add_custom_box' );

/* Do something with the data entered */
add_action( 'save_post', 'tm_mtl_save_postdata', 10, 3 );

/* Adds a box to the main column on the Post and Page edit screens */
function tm_mtl_add_custom_box() {
    add_meta_box(
        'tm_multilink_box',
        esc_html__( 'Multi Links', 'videopro' ),
        'tm_mtl_inner_custom_box',
        'post');
}

/* Prints the box content */
function tm_mtl_inner_custom_box() {
    global $post;
    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'tm_mtl_noncename' );
    ?>
    <div id="meta_inner">
    <table id="tm_here" cellpadding="4">
    <tr><td width="240"><strong><?php esc_html_e('Group Title','videopro');?></strong></td>
    <td><strong><?php esc_html_e('Links','videopro');?></strong></td>
    <td></td></tr>
    <?php

    //get the saved meta as an arry
    $links = get_post_meta($post->ID,'tm_multi_link',true);
    $c = 0;
    if ( $links && count( $links ) > 0 ) {
        foreach( $links as $track ) {
            if ( (isset( $track['title'] ) && $track['title'] != '') || (isset( $track['links'] ) && $track['links'] != '') ) {
                printf( '
				<tr><td valign="top"><input type="text" name="tm_multi_link[%1$s][title]" value="%2$s" placeholder="Group Title" size=30 /></td><td valign="top"><textarea type="text" name="tm_multi_link[%1$s][links]" cols=90 rows=4>%3$s</textarea></td><td valign="top"><button class="mtl-remove button"><i class="fa fa-times"></i> '.esc_html__('Remove', 'videopro').'</button></td></tr>
				', $c, $track['title'], $track['links'] );
                $c = $c +1;
            }
        }
    }else{ ?>
		<tr>
            <td><?php echo wp_kses(__( '<i>Click Add Group to start</i>','videopro'),array('i'=>array())); ?></td>
            <td></td>
        </tr>
	<?php }

    ?>
    </table>
    <table cellpadding="4">
    <tr>
        <td width="240" valign="top"><button class="add_tm_link button-primary button-large"><i class="fa fa-plus"></i> <?php esc_html_e('Add Group', 'videopro'); ?></button></td>
        <td><?php echo wp_kses(__( '<i>Paste your videos link (and title) here. Enter one per line.<br/> For Example:<br/> <code>Trailer 1</code><br/><code>http://www.youtube.com/watch?v=nTDNLUzjkpg</code><br/><code>Trailer 2</code><br/><code>http://www.youtube.com/watch?v=nTDNLUzjkpg</code><br> You could enter links without title</i>','videopro'),array('br'=>array()),array('strong'=>array()),array('code'=>array()));?></td>
    </tr>
    </table>
<script>
    var $ =jQuery.noConflict();
    $(document).ready(function() {
        var count = <?php echo $c; ?>;
        $(".add_tm_link").click(function() {
            count = count + 1;

            $('#tm_here').append('<tr><td valign="top"><input type="text" name="tm_multi_link['+count+'][title]" value="" placeholder="Group Title" size=30 /></td><td valign="top"><textarea type="text" name="tm_multi_link['+count+'][links]" cols=90 rows=4></textarea></td><td valign="top"><button class="mtl-remove button"><i class="fa fa-times"></i> <?php esc_html_e('Remove','videopro');?></button></td></tr>' );
            return false;
        });
        $(".mtl-remove").live('click', function() {
            $(this).parent().parent().remove();
        });
    });
    </script>
</div><?php

}

/* When the post is saved, saves our custom data */
function tm_mtl_save_postdata( $post_id, $post, $update ) {
    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( !isset( $_POST['tm_mtl_noncename'] ) )
        return;
	
	if('post' != get_post_type($post_id))
			return;

    if ( !wp_verify_nonce( $_POST['tm_mtl_noncename'], plugin_basename( __FILE__ ) ) )
        return;

    // OK, we're authenticated: we need to find and save the data

    $links = $_POST['tm_multi_link'];

    update_post_meta($post_id,'tm_multi_link',$links);
}
