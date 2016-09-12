<?php

class cactus_import_widget_settings{
	// Import Widget Settings
	// Thanks to http://wordpress.org/plugins/widget-settings-importexport/
	/* @return 
			array - widget index mapping array. Imported Index of widget will be updated after importing, so we save the mapping to use later
	*/
	public static function import($file){

		$widgets_settings = wp_remote_get( $file );
		if(is_array($widgets_settings)){
			$widget_data = $widgets_settings['body'];
		}		

		$json_data = json_decode( $widget_data, true );

		$sidebar_data = $json_data[0];
		$widget_data = $json_data[1];
		
		foreach ( $widget_data as $widget_data_title => $widget_data_value ) {
			$widgets[ $widget_data_title ] = '';
			foreach( $widget_data_value as $widget_data_key => $widget_data_array ) {
				if( is_int( $widget_data_key ) ) {
					$widgets[$widget_data_title][$widget_data_key] = 'on';
				}
			}
		}
		unset($widgets[""]);

		foreach ( $sidebar_data as $title => $sidebar ) {
			$count = count( $sidebar );
			for ( $i = 0; $i < $count; $i++ ) {
				$widget = array( );
				$widget['type'] = trim( substr( $sidebar[$i], 0, strrpos( $sidebar[$i], '-' ) ) );
				$widget['type-index'] = trim( substr( $sidebar[$i], strrpos( $sidebar[$i], '-' ) + 1 ) );
				if ( !isset( $widgets[$widget['type']][$widget['type-index']] ) ) {
					unset( $sidebar_data[$title][$i] );
				}
			}
			$sidebar_data[$title] = array_values( $sidebar_data[$title] );
		}

		foreach ( $widgets as $widget_title => $widget_value ) {
			foreach ( $widget_value as $widget_key => $widget_value ) {
				$widgets[$widget_title][$widget_key] = $widget_data[$widget_title][$widget_key];
			}
		}

		$sidebar_data = array( array_filter( $sidebar_data ), $widgets );

		$widget_index_mapping = self::parse_import_data( $sidebar_data );
		
		return $widget_index_mapping;
	}

	private static function parse_import_data( $import_array ) {
		$sidebars_data = $import_array[0];
		$widget_data = $import_array[1];
		$current_sidebars = get_option( 'sidebars_widgets' );
		$new_widgets = array( );
		
		$widget_index_mapping = array();

		foreach ( $sidebars_data as $import_sidebar => $import_widgets ) :
			$current_sidebars[$import_sidebar] = array(); // clear current widgets in sidebar
			
			foreach ( $import_widgets as $import_widget ) :
				//if the sidebar exists
				if ( isset( $current_sidebars[$import_sidebar] ) ) :
					
					$title = trim( substr( $import_widget, 0, strrpos( $import_widget, '-' ) ) );
					$index = trim( substr( $import_widget, strrpos( $import_widget, '-' ) + 1 ) );
					$current_widget_data = get_option( 'widget_' . $title );
					$new_widget_name = self::get_new_widget_name( $title, $index );
					$new_index = trim( substr( $new_widget_name, strrpos( $new_widget_name, '-' ) + 1 ) );

					if ( !empty( $new_widgets[ $title ] ) && is_array( $new_widgets[$title] ) ) {
						while ( array_key_exists( $new_index, $new_widgets[$title] ) ) {
							$new_index++;
						}
					}
					$current_sidebars[$import_sidebar][] = $title . '-' . $new_index;
					if ( array_key_exists( $title, $new_widgets ) ) {
						$new_widgets[$title][$new_index] = $widget_data[$title][$index];
						$multiwidget = $new_widgets[$title]['_multiwidget'];
						unset( $new_widgets[$title]['_multiwidget'] );
						$new_widgets[$title]['_multiwidget'] = $multiwidget;
					} else {
						$current_widget_data[$new_index] = $widget_data[$title][$index];
						$current_multiwidget = isset($current_widget_data['_multiwidget']) ? $current_widget_data['_multiwidget'] : false;
						$new_multiwidget = isset($widget_data[$title]['_multiwidget']) ? $widget_data[$title]['_multiwidget'] : false;
						$multiwidget = ($current_multiwidget != $new_multiwidget) ? $current_multiwidget : 1;
						unset( $current_widget_data['_multiwidget'] );
						$current_widget_data['_multiwidget'] = $multiwidget;
						$new_widgets[$title] = $current_widget_data;
					}
					
					
					$widget_index_mapping = array_merge($widget_index_mapping, array($title . '-' . $index => $title . '-' . $new_index));
					
				endif;
			endforeach;
		endforeach;

		if ( isset( $new_widgets ) && isset( $current_sidebars ) ) {
			update_option( 'sidebars_widgets', $current_sidebars );

			foreach ( $new_widgets as $title => $content )
				update_option( 'widget_' . $title, $content );

			return $widget_index_mapping;
		}

		return false;
	}

	private static function get_new_widget_name( $widget_name, $widget_index ) {
		$current_sidebars = get_option( 'sidebars_widgets' );
		$all_widget_array = array( );
		foreach ( $current_sidebars as $sidebar => $widgets ) {
			if ( !empty( $widgets ) && is_array( $widgets ) && $sidebar != 'wp_inactive_widgets' ) {
				foreach ( $widgets as $widget ) {
					$all_widget_array[] = $widget;
				}
			}
		}
		while ( in_array( $widget_name . '-' . $widget_index, $all_widget_array ) ) {
			$widget_index++;
		}
		$new_widget_name = $widget_name . '-' . $widget_index;
		return $new_widget_name;
	}
	
	/**
	 * Set widget option. This option is added by cactus-based themes
	 *
	 * $sidebar - string - name of sidebar
	 * $index - int - Index of widget in the sidebar, starting from 0
	 * $option - string - name of option
	 * $value - string - value of option
	 */
	public static function set_widget_options( $sidebar, $index, $option, $value){
		$current_sidebars = get_option( 'sidebars_widgets' );
		
		$i = 0;
		$widget_id = '';
		foreach($current_sidebars[$sidebar] as $sidebar){
			if($i == $index){
				// found the widget
				$widget_id = $sidebar;
				
				break;
			}
			
			$i++;
		}
		
		$s = get_option( $option );
        
		if(!$s) $s = array();
		$s[$widget_id] = $value;
        
		
		//update_option( $option, $s );
		
		return true;
	}
}