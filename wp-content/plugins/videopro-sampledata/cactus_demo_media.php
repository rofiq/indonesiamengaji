<?php

class cactus_demo_media{
	/**
     * Download an image from the specified URL and attach it to a post.
     *
     * @param - 
			$file - string - The URL of the image to download
     * 		$post_id - int - The post ID the media is to be associated with
     * 		$desc - string Optional. Description of the image
	 *
     * @return 
			int|WP_Error ID of attachment or WP_Error
     */
    static function import_image($file, $post_id = '', $desc = null ) {
		require_once(ABSPATH . 'wp-includes/pluggable.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		
        // Set variables for storage, fix file filename for query strings.
        preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
        $file_array = array();
        $file_array['name'] = basename( $matches[0] );

        // Download file to temp location.
        $file_array['tmp_name'] = download_url( $file );

        // If error storing temporarily, return the error.
        if ( is_wp_error( $file_array['tmp_name'] ) ) {
            @unlink($file_array['tmp_name']);
			/*
            echo 'is_wp_error $file_array: ' . $file;
            print_r($file_array['tmp_name']);
            return $file_array['tmp_name'];*/
			return false;
        }

        // Do the validation and storage stuff.
        $id = media_handle_sideload( $file_array, $post_id, $desc ); //$id of attachement or wp_error

        // If error storing permanently, unlink.
        if ( is_wp_error( $id ) ) {
            @unlink( $file_array['tmp_name'] );
			/*
            echo 'is_wp_error $id: ' . $id->get_error_messages() . ' ' . $file;
            return $id;
			*/
			
			return false;
        }

        return $id;
    }
	
	static function set_feature_image($post_id, $attachment_id){
		set_post_thumbnail($post_id, $attachment_id);
	}
}