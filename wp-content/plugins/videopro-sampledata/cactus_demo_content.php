<?php

require_once 'cactus_import_widget_settings.php';
require_once 'cactus_demo_media.php';
require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

class cactus_demo_content{
	public $name = '';
	public $home_page = '';
	public $heading = '';
	public $base_uri;
	public $base_dir;
	
	function __construct($base_uri, $base_dir){
		$this->base_uri = $base_uri;
		$this->base_dir = $base_dir;
	}
	
	public function import_pages($pages, $index = 0){
		$i = 0;
		foreach($pages as $key => $page){
			if($i < $index){
				$i++;
			} else {
				$id = $this->add_page($key, $page);
				$pages[$key]['id'] = $id;
				
				break; // only import one page a time
			}
		}
		
		return $pages;
	}
	
	public function import_posts($posts, $categories, $index = 0){
		$i = 0;
		foreach($posts as $key => $post){
			if($i < $index){
				$i++;
			} else {
				$id = $this->add_post($key, $post, $categories);
				$post[$key]['id'] = $id;
				
				break; // only import one post a time
			}
		}
		
		return $posts;
	}
	
	/**
	 * import woocommerce product
	 */
	public function import_products($posts, $index){
		$i = 0;
		foreach($posts as $key => $post){
			if($i < $index){
				$i++;
			} else {
				$id = $this->add_product($key, $post);
				$post[$key]['id'] = $id;
				
				break; // only import one post a time
			}
		}
		
		return $posts;
	}
	
	private function add_product($slug, $demo){
		// make sure only one post is created

		$args = array(
		  'name'        => $slug,
		  'post_type'   => 'product',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$my_posts = get_posts($args);
		
		$new_ID = 0;

		if( $my_posts ) {
			$new_ID = $my_posts[0]->ID;
		} else {
			$content = $this->get_content($demo['content']);

			$data = array(
			  'post_content'   => $content,
			  'post_name' 	   => $slug, //slug
			  'post_title'     => $demo['title'],
			  'post_status'    => 'publish',
			  'post_type'      => 'product'
			);
			
			if(isset($demo['tags'])){
				$data['tags_input'] = $demo['tags'];
			}
			
			$new_ID = wp_insert_post( $data, false );
		}
		
		if($new_ID){
			if(isset($demo['fields']) && is_array($demo['fields'])){
				foreach($demo['fields'] as $key => $value){
					update_post_meta( $new_ID, $key, $value );
				}
			}

			if(isset($demo['categories'])){

				$cats = explode(',',$demo['categories']);
				$terms = array();
				foreach($cats as $slug){
					$term = get_term_by('slug', substr($slug, 4), 'product_cat');

					if(!is_wp_error($term)){
						
						array_push($terms, $term->term_id); // remove 'woo-' prefix
					}
					
				}
			
				
				wp_set_post_terms($new_ID, $terms, 'product_cat');
			}
			
			if(isset($demo['type'])){
				wp_set_post_terms($new_ID, $demo['type'], 'product_type');
			}
			
			if(isset($demo['feature_image']) && $demo['feature_image'] != ''){
				// check if this image has been imported
				$array = get_option('_cactus_demo_images', array());
				
				$attachment_name = $this->name . '-woo-image-' . $demo['feature_image'];

				$media_id = 0;
				foreach($array as $obj){
					// do some check
					if($obj['id'] == $attachment_name){
						$media_id = $obj['attachment_id'];
						
						// check if this attachment still exists
						$attachment = wp_get_attachment_image($media_id);
						if(!$attachment){
							$media_id = 0;
							// remove from saved array
							unset($obj);
						}
						break;
					}
				}
				
				if($media_id == 0){
					// now it is sure that attachment is not imported yet
					// or attachment does not exist anymore, so download again
					$feature_image_url = $this->base_uri . 'packages/' . $this->name . '/data/products/' . $demo['feature_image'];
					
					$media_id = cactus_demo_media::import_image($feature_image_url, $new_ID);

					if($media_id != 0){
						array_push($array, array(
												'attachment_id' => $media_id,
												'id' => $attachment_name
											));

						update_option('_cactus_demo_images', $array);
					}
				}
				
				if($media_id != 0){
					cactus_demo_media::set_feature_image($new_ID, $media_id);
				}
			}
			
			return $new_ID;
		}
		
		return false;
	}
	
	private function get_content($filename){
		$file = $this->base_dir . 'packages/' . $this->name . '/data/' . $filename;

		if(file_exists($file)){
			return file_get_contents($file);
		} else 
			return '(Cannot read from ' . $filename . ')';
	}
	
	/**
	 *
	 */
	public function import_menus($menus, $index = 0){
		$current_index = 0;
		foreach($menus as $location => $menu){
			$locations = get_nav_menu_locations();

			if (isset($locations[$location])) {
				$menu_id = $locations[$location];
			}
			
			$new_index = $this->import_menu($menu['children'], $menu_id, 0, $index, $current_index);
			
			// we break if an item has been found
			if($new_index < $current_index + $this->count_total_menu_chilren($menu['children'])){
				// item has been added in children, so break
				break;
			} else {
				$current_index = $new_index;
			}
		}
	}
	
	private function import_menu($items, $menu_id, $parent_id = 0, $index = 0, $current_index = 0){
		// order $items by 'order'
		usort($items, array($this, 'compare_menu_order'));
		
		foreach($items as $id => $menu_item){
			// ignore items which has been imported
			if($current_index < $index){
				$current_index++;
				if(isset($menu_item['children'])){
					// find ID of parent menu item
					$id = $this->find_menu_item_id($menu_item['title'], $menu_id);

					$new_index = $this->import_menu($menu_item['children'], $menu_id, $id, $index, $current_index);
					
					if($new_index < $current_index + $this->count_total_menu_chilren($menu_item['children'])){
						// item has been added in children, so break
						break;
					} else {
						$current_index = $new_index;
					}
				}
				
				continue;
			}
			
			$params = array();
			if($parent_id){
				$params['parent_id'] = $parent_id;
			}

			switch($menu_item['type']){
				case 'custom':
					$params['title'] = $menu_item['title'];
					$params['url'] = $menu_item['url'];
					
					$id = $this->add_menu_item_url(
									$params, 
									$menu_id
								);
					break;
				case 'page':
					$params['page_id'] = $this->get_page_id_by_slug($menu_item['page']);

					if($params['page_id'] != 0){
						if(isset($menu_item['title'])){
							$params['title'] = $menu_item['title'];
						}
						if($parent_id){
							$params['parent_id'] = $parent_id;
						}
								  
						$id = $this->add_menu_item_page(
										$params, 
										$menu_id
									);
									

					}
					
					break;
				case 'post':
					$params['post_id'] = $this->get_post_id_by_slug($menu_item['post']);
					
					if($params['post_id'] != 0){
						if(isset($menu_item['title'])){
							$params['title'] = $menu_item['title'];
						}
						if($parent_id){
							$params['parent_id'] = $parent_id;
						}
								  
						$id = $this->add_menu_item_post(
										$params, 
										$menu_id
									);
					}
					
					break;
				case 'category':
					$cat_slug = substr($menu_item['category'], 4); // $menu_item['category'] starts with 'cat-'
					$params['term_id'] = $this->get_cat_id_by_slug($cat_slug);
					
					if($params['term_id'] != 0){
						if(isset($menu_item['title'])){
							$params['title'] = $menu_item['title'];
						}
						if($parent_id){
							$params['parent_id'] = $parent_id;
						}
								  
						$id = $this->add_menu_item_term(
										$params, 
										$menu_id
									);
					}
					
					break;
			}
			
			// break as we only import 1 item a time
			break;
		}
		
		return $current_index;
	}
	
	private function find_menu_item_id($menu_item_name, $menu_slug){
		$items = wp_get_nav_menu_items($menu_slug);
		
		foreach($items as $key => $menu_item){
			if($menu_item->title == $menu_item_name){
				return $menu_item->ID;
			}
		}
		
		return 0;
	}
	
	private function update_mega_menu($menu_slug, $settings){
		$items = wp_get_nav_menu_items($menu_slug); // menu slug

		foreach ( (array) $items as $key => $menu_item ) {
			$title = $menu_item->title;
			$not_default = false;
			if(isset($settings['mega_item'])){
				if($title == $settings['mega_item']){
					$options =  array ( 'menu-item-isMega' => 'on',
												'menu-item-menu_style' => 'preview',
												'menu-item-addSidebar' => 0,
												'menu-item-displayLogic' => 'both' 
											);
					
					delete_post_meta($menu_item->ID, '_mashmenu_options');
					update_post_meta( $menu_item->ID, '_mashmenu_options', $options);
					$not_default = true;
				}
			}
			
			if(isset($settings['columns_item'])){
				if($title == $settings['columns_item']){
					$options =  array ( 'menu-item-isMega' => 'off',
												'menu-item-menu_style' => 'columns',
												'menu-item-addSidebar' => 0,
												'menu-item-displayLogic' => 'both' 
											);
					
					delete_post_meta($menu_item->ID, '_mashmenu_options');
					update_post_meta( $menu_item->ID, '_mashmenu_options', $options);
					
					$not_default = true;
				}
			}
			
			if(!$not_default){
				$options =  array ( 'menu-item-isMega' => 'off',
											'menu-item-menu_style' => 'list',
											'menu-item-addSidebar' => 0,
											'menu-item-displayLogic' => 'both' 
										);
				
				delete_post_meta($menu_item->ID, '_mashmenu_options');
				update_post_meta( $menu_item->ID, '_mashmenu_options', $options);
			}
		}
	}
	
	function compare_menu_order($a, $b){
		if($a['order'] > $b['order'])
			return 1;
		else
			return -1;
	}
	
	
	 /**
     * creates a menu and adds it to a location of the theme
     * @param $menu_name
     * @param $location
     * @return bool
     */
    public function create_menu($menu_name, $location) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id)) {
            return false;
        }

        $menu_spots_array = get_theme_mod('nav_menu_locations');
        // activate the menu only if it's not already active
        if (!isset($menu_spots_array[$location]) or $menu_spots_array[$location] != $menu_id) {
            $menu_spots_array[$location] = $menu_id;
            set_theme_mod('nav_menu_locations', $menu_spots_array);
        }
        return $menu_id;
    }
	
	/**
	 * add menu item
	 * @params
			$item - mixed
						array(	'title' => '',
								'url' => '',
								'parent_id' => '' // optional
								)
			
			$menu_id - int - ID of menu
	 */
    public function add_menu_item_url($item, $menu_id) {
		$url = $item['url'];
		if(sanitize_title($item['title']) == 'home')
			$url = get_site_url();

        $itemData =  array(
            'menu-item-object' => '',
            'menu-item-type'      => 'custom',
            'menu-item-title'    => $item['title'],
            'menu-item-url' => $url,
            'menu-item-status'    => 'publish'
        );

        if (!empty($item['parent_id'])) {
            $itemData['menu-item-parent-id'] = $item['parent_id'];
        }

        $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $itemData);
        return $menu_item_id;
    }

	/** 
	 * add a page to menu
	 * @params
			$item - mixed
						array(	'page_id' => '',
								'title' => '', // optional
								'parent_id' => '' // optional
								)
			
			$menu_id - int - ID of menu
	 */
	public function add_menu_item_page($item, $menu_id) {
        $itemData =  array(
            'menu-item-object-id' => $item['page_id'],
            'menu-item-parent-id' => 0,
            'menu-item-object' => 'page',
            'menu-item-type'      => 'post_type',
            'menu-item-status'    => 'publish'
        );

        if (!empty($item['parent_id'])) {
            $itemData['menu-item-parent-id'] = $item['parent_id'];
        }

        if (!empty($item['title'])) {
            $itemData['menu-item-title'] = $item['title'];
        }

        $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $itemData);
		
        return $menu_item_id;
    }
	
	/** 
	 * add a page to menu
	 * @params
			$item - mixed
						array(	'page_id' => '',
								'title' => '', // optional
								'parent_id' => '' // optional
								)
			
			$menu_id - int - ID of menu
	 */
	public function add_menu_item_post($item, $menu_id) {
        $itemData =  array(
            'menu-item-object-id' => $item['post_id'],
            'menu-item-parent-id' => 0,
            'menu-item-object' => 'post',
            'menu-item-type'      => 'post_type',
            'menu-item-status'    => 'publish'
        );

        if (!empty($item['parent_id'])) {
            $itemData['menu-item-parent-id'] = $item['parent_id'];
        }

        if (!empty($item['title'])) {
            $itemData['menu-item-title'] = $item['title'];
        }

        $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $itemData);
        return $menu_item_id;
    }
	
	/** 
	 * add a category to menu
	 * @params
			$item - mixed
						array(	'term_id' => '',
								'title' => '', // optional
								'parent_id' => '' // optional
								)
			
			$menu_id - int - ID of menu
	 */
	public function add_menu_item_term($item, $menu_id) {
        $itemData =  array(
            'menu-item-title' => $item['title'],
            'menu-item-object-id' => $item['term_id'],
            'menu-item-db-id' => 0,
            'menu-item-url' => get_category_link($item['term_id']),
            'menu-item-type' => 'taxonomy', //taxonomy
            'menu-item-status' => 'publish',
            'menu-item-object' => 'category',
        );

        if (!empty($item['parent_id'])) {
            $itemData['menu-item-parent-id'] = $item['parent_id'];
        }

        wp_update_nav_menu_item($menu_id, 0, $itemData);
    }

    /**
     * removes all the menus
     */
    public function remove_menus($menus) {
        foreach ($menus as $slug => $menu) {
			$name = sanitize_title($menu['name']);
            wp_delete_nav_menu($name);
        }
    }
	
	/**
	 * $demo data object 
			array('title' => '',
				   'content' => '', // relative path to .txt file
				   'fields' => array() // custom metadata
				   'template' => 'path/to/page-template.php' // '_wp_page_template'
				   )
	 */
	public function add_page($slug, $demo){
		// make sure only one post is created

		$args = array(
		  'name'        => $slug,
		  'post_type'   => 'page',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$my_posts = get_posts($args);
		
		$new_ID = 0;
		
		if( $my_posts ) {
			$new_ID = $my_posts[0]->ID;
		} else {
			$content = $this->get_content($demo['content']);

			$data = array(
			  'post_content'   => $content,
			  'post_name' 	   => $slug, //slug
			  'post_title'     => $demo['title'],
			  'post_status'    => 'publish',
			  'post_type'      => 'page'
			);

			$new_ID = wp_insert_post( $data, false );
		}

		if($new_ID){
			if(isset($demo['template'])){
				update_post_meta( $new_ID, '_wp_page_template', $demo['template'] );
			}
			
			if(isset($demo['fields']) && is_array($demo['fields'])){
				foreach($demo['fields'] as $key => $value){
					update_post_meta( $new_ID, $key, $value );
				}
			}
			
			return $new_ID;
		}
		
		return false;
	}
	
	/** 
	 * send $categories to determine category ID
	 */
	public function add_post($slug, $demo, $categories){
		// make sure only one post is created

		$args = array(
		  'name'        => $slug,
		  'post_type'   => $demo['post_type'],
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$my_posts = get_posts($args);
		
		$new_ID = 0;
		
		if( $my_posts ) {
			$new_ID = $my_posts[0]->ID;
		} else {
			$content = isset($demo['content']) ? $this->get_content($demo['content']) : '';

			$data = array(
			  'post_content'   => $content,
			  'post_name' 	   => $slug, //slug
			  'post_title'     => $demo['title'],
			  'post_status'    => 'publish',
			  'post_type'      => $demo['post_type']
			);
			
			if(isset($demo['categories'])){
				// find IDs of categories
				$cats = explode(',',$demo['categories']);
				$ids = array();
				foreach($cats as $slug){
					$id = isset($categories[trim($slug)]['id']) ? $categories[trim($slug)]['id'] : 0;
					if($id)
						array_push($ids, $id);
				}
				$data['post_category'] = $ids;
			}
			
			if(isset($demo['tags'])){
				$data['tags_input'] = $demo['tags'];
			}
			
			$new_ID = wp_insert_post( $data, false );
		}
		
		if($new_ID){
			if(isset($demo['fields']) && is_array($demo['fields'])){
				foreach($demo['fields'] as $key => $value){
					if(!$this->import_post_meta($new_ID, $key, $value, $demo['post_type'])){
						// do normal import post meta
						update_post_meta( $new_ID, $key, $value );
					}
				}
			}
			
			if(isset($demo['feature_image']) && $demo['feature_image'] != ''){
				
				// check if this image has been imported
				$array = get_option('_cactus_demo_images', array());
				
				$attachment_name = $this->name . '-image-' . $demo['feature_image'];

				$media_id = 0;
				foreach($array as $obj){
					// do some check
					if($obj['id'] == $attachment_name){
						$media_id = $obj['attachment_id'];
						
						// check if this attachment still exists
						$attachment = wp_get_attachment_image($media_id);
						if(!$attachment){
							$media_id = 0;
							// remove from saved array
							unset($obj);
						}
						break;
					}
				}
				
				if($media_id == 0){
					// now it is sure that attachment is not imported yet
					// or attachment does not exist anymore, so download again
					$media_id = cactus_demo_media::import_image($this->base_uri . 'packages/' . $this->name . '/data/images/' . $demo['feature_image'], $new_ID);
					
					if($media_id != 0){
						array_push($array, array(
												'attachment_id' => $media_id,
												'id' => $attachment_name
											));

						update_option('_cactus_demo_images', $array);
					}
				}
				
				if($media_id != 0){
					cactus_demo_media::set_feature_image($new_ID, $media_id);
				}
			}
			
			if(isset($demo['post_format'])){
				set_post_format($new_ID, $demo['post_format']);
			}
			
			// add posts to video-series term
			if(isset($demo['series']) && taxonomy_exists('video-series')){
				$series = explode(',',$demo['series']);
				foreach($series as $slug){
					$term = get_term_by('slug', $slug, 'video-series');
					if($term){
						wp_set_post_terms($new_ID, intval($term->term_id) , 'video-series');
					}
				}
			}
			
			return $new_ID;
		}
		
		return false;
	}
	
	/**
	 * Be overriden by sub class, to do extra work when importing post metadata
	 */
	public function import_post_meta($post_id, $key, $value, $post_type = ''){
		return false;
	}
	
	/**
	 * insert terms and return terms with newly added IDs
	 */
	public function import_terms($terms, $parent_id = 0){
		
		$i = 0;
		foreach($terms as $slug => $term){
			$term_slug = substr($slug, 4); // $slug starts with 'cat-'
			
			$term = $this->add_term($term, $term_slug, $parent_id);
			
			$terms[$slug] = $term;
		}
		
		return $terms;
	}
	
	/**
	 * add term
	 */
	private function add_term($term, $slug, $parent_id = 0){
		$term_id = 0;
		
		$data = array('description' => isset($term['description']) ? $term['description'] : '', 
																			'parent' => $parent_id,
																			'slug' => $slug);
																		
		
		$term_id = wp_insert_term($term['name'], $term['taxonomy'], $data);

		if(is_wp_error($term_id)){
			if(isset($term_id->errors['term_exists'])){
				$id = $term_id->error_data['term_exists'];
				$term_id = (array)get_term($id, $term['taxonomy']);
			} elseif(isset($term_id->errors['invalid_taxonomy'])){
				// plugin is not installed yet
				return;
			}
		}
		
		$term['id'] = $term_id['term_id'];

		if(isset($term['children'])){
			$this->import_terms($term['children'], $term_id['term_id']);
		}
		
		if(isset($term['fields'])){
			foreach($term['fields'] as $key => $value){
				update_option($key . '_' . $term_id['term_id'], $value);
			}
		}
		
		return $term;
	}
	
	/**
	 * import theme options settings
	 */
	public function import_themeoptions(){
		$theme_options_file = $this->base_uri . 'packages/'. $this->name .'/data/theme-options.txt';

		$theme_options_txt = wp_remote_get( $theme_options_file );

		if(is_array($theme_options_txt)){
			
			$data = unserialize( base64_decode( $theme_options_txt['body'])  );
					
			/* get settings array */
			$settings = get_option( 'option_tree_settings' );
				
			/* validate options */
			if ( is_array( $settings ) ) {
			
			  foreach( $settings['settings'] as $setting ) {
			  
				if ( isset( $data[$setting['id']] ) ) {
				  
				  $content = $data[$setting['id']];
				  
				  $data[$setting['id']] = $content;
				}
			  }
			}
			
			/* execute the action hook and pass the theme options to it */
			do_action( 'ot_before_theme_options_save', $data );
		  
			/* update the option tree array */
			update_option( 'option_tree', $data );
		}
	}
	
	/**
	 * import widget logic
	 * @params	
			$file - string - URI to the setting file
			$index_mapping - array - an array to map old index to the new index after widgets are imported
	 */
	public function import_widget_logic($file, $index_mapping = false){
		global $wl_options;

		$data = wp_remote_get( $file );

		if(is_array($data)){
			$widget_logic_settings = $data['body'];

			$settings = explode("\n",$widget_logic_settings);
			
			if (array_shift($settings) && array_pop($settings)){	

				foreach ($settings as $import_option){	
					list($key, $value)= explode("\t",$import_option);

					if($index_mapping && isset($index_mapping[$key])){
						$wl_options[$index_mapping[$key]] = json_decode($value);
					} else {
						$wl_options[$key]=json_decode($value);
					}
				}
			}

			update_option('widget_logic', $wl_options);
		}
	}
	
	public function do_import($step = 0, $index = 0, $option_only = 0){
		$progress = array();

		if($step < 10){
			$progress['step'] = 10;
			$progress['index'] = $index;
			$progress['total_progress'] = 10;

			if(!$option_only){
				$cats = $this->get_terms();

				$cats = $this->import_terms($cats, 0);
				update_option('_cactus_temp_import_cats', $cats);
				
				$index = $this->do_others($step, 0);
			}
		} elseif($step < 25){
			// this step worths 15% progress

			$progress['step'] = 10;
			$progress['index'] = $index;
			$progress['total_progress'] = 25;
			
			if(!$option_only){
				$pages = $this->get_pages();
				
				$total_items = count($pages) + $this->count_other_steps($step);

				
				if($index > count($pages) - 1){
					$index = $this->do_others($step, $index - count($pages));
					if($index == -1){
						$progress['step'] = 25;
						$progress['index'] = -1; // will be increased to 0 by client
					} else {
						$progress['total_progress'] = 25 + (($index + count($pages) + 1) * 30 / $total_items);
					}
				} else {
					$pages = $this->import_pages($pages, $index);
					
					$progress['total_progress'] = 10 + (($index + 1) * 15 / $total_items);
				}
			} else {
				$progress['step'] = 25;
				$progress['index'] = -1; // will be increased to 0 by client
			}
		} elseif($step < 55){
			// this step worths 30% progress
			
			$progress['step'] = 25;
			$progress['index'] = $index;
			$progress['total_progress'] = 55;
			
			if(!$option_only){
				$cats = get_option('_cactus_temp_import_cats');
				
				$posts = $this->get_posts();
				
				$total_items = count($posts) + $this->count_other_steps($step);
				
				if($index > count($posts) - 1){
					$index = $this->do_others($step, $index - count($posts));

					if($index == -1){
						$progress['step'] = 55;
						$progress['index'] = -1;
					} else {
						$progress['total_progress'] = 25 + (($index + count($posts) + 1) * 30 / $total_items);
					}
				} else {
					$posts = $this->import_posts($posts, $cats, $index);
					
					$progress['total_progress'] = 25 + (($index + 1) * 30 / $total_items);
				}
			} else {
				$progress['step'] = 55;
				$progress['index'] = -1;
			}
		} elseif($step < 85){
			$progress['step'] = 55;
			$progress['index'] = $index;
			$progress['total_progress'] = 85;
			
			// 30%
			// import menus
			if(!$option_only){
				
				$menus = $this->get_menus();
				if($index == 0){
					// remove existing menus
					$this->remove_menus($menus);

					foreach($menus as $location => $menu){
						$menu_id = $this->create_menu($menu['name'], $location);
					}
				}
				
				$total_menu_items = $this->count_total_menu_items($menus);

				if($index > ($total_menu_items - 1)){
					$progress['step'] = 85;
					$progress['index'] = -1;
				} else {
					$this->import_menus($menus, $index);
					$progress['total_progress'] = 55 + (($index + 1) * 30 / $total_menu_items);
				}
			} else {
				$progress['step'] = 85;
				$progress['index'] = -1;
			}
		} elseif($step < 90){
			$progress['step'] = 90;
			$progress['index'] = $index;
			$progress['total_progress'] = 90;
			
			// 5%
			// import theme options
			if(class_exists('OT_Loader')){
				// make sure Option Tree is installed and activated
				$this->import_themeoptions();
			}
			
			// set up megamenu
			$menus = $this->get_menus();
			foreach($menus as $location => $menu){
				
				if(isset($menu['is_mega']) && $menu['is_mega'] == 1){
					$this->update_mega_menu(sanitize_title($menu['name']), $menu);
				}
			}
			
			$this->do_others($step, $index);	
		} else{
			$progress['step'] = 100;
			$progress['index'] = $index;
			$progress['total_progress'] = 100;
			
			$widget_settings = $this->base_uri . 'packages/'. $this->name .'/data/widget_data.json';
			$widget_index_mapping = cactus_import_widget_settings::import( $widget_settings );
			
			$this->configure_widget_options($widget_settings, $widget_index_mapping);
			
			// import widget logic
			if(function_exists('widget_logic_expand_control')){
				// make sure Widget Logic is installed and activated
				$widget_logic_settings_file = $this->base_uri . 'packages/'. $this->name .'/data/widget_logic_options.txt';
				$this->import_widget_logic($widget_logic_settings_file, $widget_index_mapping);
			}
			
			// everything is done, so clear temp data
			delete_option('_cactus_temp_import_cats');
			
			wp_delete_post(1); // delete hello world post
					
			update_option('show_on_front', 'page');
			
			$home_id = $this->get_page_id_by_slug($this->home_page);
			
			if(!$home_id){
				// if $home page is not imported before, try to import it again
				$pages = $this->get_pages();
				$home_id = $this->add_page($this->home_page, $pages[$this->home_page]);
			}
			
			update_option('page_on_front', $home_id);
			
			$this->do_others($step, $index);
		}
		
		return $progress;
	}
	
	/**
	 * To be implemented by child class
	 *
	 * $widget_settings - string - Path to widget settings data file
	 * $widget_index_mapping - mixed - Mapping of widget indexes
	 * 
	 */
	public function configure_widget_options($widget_settings, $widget_index_mapping){
		return false;
	}
	
	/**
	 * Do other stuff in each step
	 *
	 * $step - int - Name of step
	 * $index - int - Current index of item in step
	 */
	public function do_others($step, $index){
		// to be implemented in child class

		return -1; // -1 means nothing to do here
	}
	
	/**
	 * count number of items in other steps, overriden in child class
	 */
	public function count_other_steps($step){
		return 0;
	}
	
	private function count_total_menu_items($menus){
		$total = 0;
		foreach($menus as $key => $menu){
			$total += $this->count_total_menu_chilren($menu['children']);
		}
		
		return $total;
	}
	
	private function count_total_menu_chilren($items){
		$total = 0;
		foreach($items as $item){
			$total++;
			if(isset($item['children'])){
				$total += $this->count_total_menu_chilren($item['children']);
			}
		}
		
		return $total;
	}
	
	private function get_page_id_by_slug($slug){
		$args = array(
		  'name'        => $slug,
		  'post_type'   => 'page',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$posts = get_posts($args);
		if( $posts ) :
		  return $posts[0]->ID;
		endif;
		
		return 0;
	}
	
	private function get_post_id_by_slug($slug){
		$args = array(
		  'name'        => $slug,
		  'post_type'   => 'post',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$posts = get_posts($args);
		if( $posts ) :
		  return $posts[0]->ID;
		endif;
		
		return 0;
	}
	
	private function get_cat_id_by_slug($slug){
		$obj = get_category_by_slug($slug);
		
		if($obj) return $obj->term_id;
		
		return 0;
	}
	
	private function get_menus(){
		
		include $this->base_dir . 'packages/' . $this->name . '/data/menu-data.php';
		return $menus;
	}
	
	private function get_terms(){
		include $this->base_dir . 'packages/' . $this->name . '/data/category-data.php';
					
		return $cats;
	}
	
	private function get_pages(){
		include $this->base_dir . 'packages/' . $this->name . '/data/page-data.php';
		
		return $pages;
	}
	
	private function get_posts(){
		include $this->base_dir . 'packages/' . $this->name . '/data/post-data.php';

		return $posts;
	}
}