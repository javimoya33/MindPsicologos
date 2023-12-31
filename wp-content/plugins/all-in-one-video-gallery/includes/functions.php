<?php

/**
 * Helper Functions.
 *
 * @link    https://plugins360.com
 * @since   1.0.0
 *
 * @package All_In_One_Video_Gallery
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Allow iframe & script tags in the "Iframe Embed Code" field.
 * 
 * @since  1.0.0
 * @param  array $allowed_tags Allowed HTML Tags.
 * @return array               Iframe & script tags included.
 */
function aiovg_allow_iframe_script_tags( $allowed_tags ) {
	// Only change for users who has "unfiltered_html" capability
	if ( ! current_user_can( 'unfiltered_html' ) ) return $allowed_tags;
	
	// Allow script and the following attributes
	$allowed_tags['script'] = array(
		'type'   => true,
		'src'    => true,
		'width'  => true,
		'height' => true
	);

	// Allow iframes and the following attributes
	$allowed_tags['iframe'] = array(
		'align'        => true,
		'width'        => true,
		'height'       => true,
		'frameborder'  => true,
		'name'         => true,
		'src'          => true,
		'id'           => true,
		'class'        => true,
		'style'        => true,
		'scrolling'    => true,
		'marginwidth'  => true,
		'marginheight' => true,
	);
	
	return $allowed_tags;	
}

/**
 * Check if Yoast SEO plugin is active and AIOVG can use that.
 *
 * @since  1.5.6
 * @return bool  $can_use_yoast True if can use Yoast, false if not.
 */
function aiovg_can_use_yoast() {
	$can_use_yoast = false;

	$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
	if ( in_array( 'wordpress-seo/wp-seo.php', $active_plugins ) || in_array( 'wordpress-seo-premium/wp-seo-premium.php', $active_plugins ) ) {
		$can_use_yoast = true;
	}

	return $can_use_yoast;
}

/**
 * Combine video attributes as a string.
 * 
 * @since 2.0.0
 * @param array  $atts Array of video attributes.
 * @param string       Combined attributes string.
 */
function aiovg_combine_video_attributes( $atts ) {
	$attributes = array();
	
	foreach ( $atts as $key => $value ) {
		if ( '' === $value ) {
			$attributes[] = $key;
		} else {
			$attributes[] = sprintf( '%s="%s"', $key, $value );
		}
	}
	
	return implode( ' ', $attributes );
}

/**
 * Whether the current user has a specific capability.
 *
 * @since  1.0.0
 * @param  string $capability Capability name.
 * @param  int    $post_id    Optional. ID of the specific object to check against if
 *							  `$capability` is a "meta" cap.
 * @return bool               True if the current user has the capability, false if not.
 */
function aiovg_current_user_can( $capability, $post_id = 0 ) {
	$user_id = get_current_user_id();
	
	// If editing, deleting, or reading a video, get the post and post type object
	if ( 'edit_aiovg_video' == $capability || 'delete_aiovg_video' == $capability || 'read_aiovg_video' == $capability ) {
		$post = get_post( $post_id );
		$post_type = get_post_type_object( $post->post_type );

		// If editing a video, assign the required capability
		if ( 'edit_aiovg_video' == $capability ) {
			if ( $user_id == $post->post_author ) {
				$capability = 'edit_aiovg_videos';
			} else {
				$capability = 'edit_others_aiovg_videos';
			}
		}
		
		// If deleting a video, assign the required capability
		elseif ( 'delete_aiovg_video' == $capability ) {
			if ( $user_id == $post->post_author ) {
				$capability = 'delete_aiovg_videos';
			} else {
				$capability = 'delete_others_aiovg_videos';
			}
		}
		
		// If reading a private video, assign the required capability
		elseif ( 'read_aiovg_video' == $capability ) {
			if ( 'private' != $post->post_status ) {
				$capability = 'read';
			} elseif ( $user_id == $post->post_author ) {
				$capability = 'read';
			} else {
				$capability = 'read_private_aiovg_videos';
			}
		}		
	}
		
	return current_user_can( $capability );	
}

/**
 * Delete category attachments.
 *
 * @since 1.0.0
 * @param int   $term_id Term ID.
 */
function aiovg_delete_category_attachments( $term_id ) {
	$general_settings = get_option( 'aiovg_general_settings' );
	
	if ( ! empty( $general_settings['delete_media_files'] ) ) {
		$image_id = get_term_meta( $term_id, 'image_id', true );
		if ( ! empty( $image_id ) ) wp_delete_attachment( $image_id, true );
	}
}

/**
 * Delete video attachments.
 *
 * @since 1.0.0
 * @param int   $post_id Post ID.
 */
function aiovg_delete_video_attachments( $post_id ) {	
	$general_settings = get_option( 'aiovg_general_settings' );
	
	if ( ! empty( $general_settings['delete_media_files'] ) ) {
		$mp4_id = get_post_meta( $post_id, 'mp4_id', true );
		if ( ! empty( $mp4_id ) ) wp_delete_attachment( $mp4_id, true );
		
		$webm_id = get_post_meta( $post_id, 'webm_id', true );
		if ( ! empty( $webm_id ) ) wp_delete_attachment( $webm_id, true );
		
		$ogv_id = get_post_meta( $post_id, 'ogv_id', true );
		if ( ! empty( $ogv_id ) ) wp_delete_attachment( $ogv_id, true );
		
		$image_id = get_post_meta( $post_id, 'image_id', true );
		if ( ! empty( $image_id ) ) wp_delete_attachment( $image_id, true );
		
		$tracks = get_post_meta( $post_id, 'track' );	
		if ( count( $tracks ) ) {
			foreach ( $tracks as $key => $track ) {
				if ( 'src_id' == $key ) wp_delete_attachment( (int) $track['src_id'], true );
			}
		}
	}
}

/**
 * Get attachment ID of the given URL.
 * 
 * @since  1.0.0
 * @param  string $url   Media file URL.
 * @param  string $media "image" or "video". Type of the media. 
 * @return int           Attachment ID on success, 0 on failure.
 */
function aiovg_get_attachment_id( $url, $media = 'image' ) {
	$attachment_id = 0;
	
	if ( empty( $url ) ) {
		return $attachment_id;
	}	
	
	if ( 'image' == $media ) {
		$dir = wp_upload_dir();
	
		if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?	
			$file = basename( $url );
	
			$query_args = array(
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'fields' => 'ids',
				'no_found_rows' => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'meta_query' => array(
					array(
						'key' => '_wp_attachment_metadata',
						'value' => $file,
						'compare' => 'LIKE'						
					),
				)
			);
	
			$query = new WP_Query( $query_args );
	
			if ( $query->have_posts() ) {	
				foreach ( $query->posts as $post_id ) {	
					$meta = wp_get_attachment_metadata( $post_id );
	
					$original_file = basename( $meta['file'] );
					$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
	
					if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
						$attachment_id = $post_id;
						break;
					}	
				}	
			}	
		}	
	} else {
		$url = wp_make_link_relative( $url );
		
		if ( ! empty( $url ) ) {
			global $wpdb;
			
			$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid RLIKE %s", $url );
			$attachment_id = $wpdb->get_var( $query );
		}		
	}

	return $attachment_id;	
}

/**
 * Get the category page URL.
 *
 * @since  1.0.0
 * @param  object $term The term object.
 * @return string       Category page URL.
 */
function aiovg_get_category_page_url( $term ) {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';
	
	if ( $page_settings['category'] > 0 ) {
		$url = get_permalink( $page_settings['category'] );
	
		if ( '' != get_option( 'permalink_structure' ) ) {
    		$url = user_trailingslashit( trailingslashit( $url ) . $term->slug );
  		} else {
    		$url = add_query_arg( 'aiovg_category', $term->slug, $url );
  		}
	}
  
	return apply_filters( 'aiovg_category_page_url', $url, $term );
}

/**
 * Get the list of custom plugin pages.
 * 
 * @since  1.6.5
 * @return array $pages Array of pages.
 */
function aiovg_get_custom_pages_list() {
	$pages = array(
		'category' => array( 
			'title'   => __( 'Video Category', 'all-in-one-video-gallery' ), 
			'content' => '[aiovg_category]' 
		),
		'tag' => array( 
			'title'   => __( 'Video Tag', 'all-in-one-video-gallery' ), 
			'content' => '[aiovg_tag]' 
		),
		'search' => array( 
			'title'   => __( 'Search Videos', 'all-in-one-video-gallery' ), 
			'content' => '[aiovg_search]' 
		),
		'user_videos' => array( 
			'title'   => __( 'User Videos', 'all-in-one-video-gallery' ), 
			'content' => '[aiovg_user_videos]' 
		),
		'player' => array( 
			'title'   => __( 'Player Embed', 'all-in-one-video-gallery' ), 
			'content' => '' 
		)
	);

	return apply_filters( 'aiovg_custom_pages_list', $pages );
}

/**
 * Get Dailymotion ID from URL.
 *
 * @since  1.5.0
 * @param  string $url Dailymotion video URL.
 * @return string $id  Dailymotion video ID.
 */
function aiovg_get_dailymotion_id_from_url( $url ) {	
	$id = '';
	
	if ( preg_match( '!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!', $url, $m ) ) {
        if ( isset( $m[6] ) ) {
            $id = $m[6];
        }
		
        if ( isset( $m[4] ) ) {
            $id = $m[4];
        }
		
        $id = $m[2];
    }

	return $id;	
}

/**
 * Get Dailymotion image from URL.
 *
 * @since  1.5.0
 * @param  string $url Dailymotion video URL.
 * @return string $url Dailymotion image URL.
 */
function aiovg_get_dailymotion_image_url( $url ) {	
	$id  = aiovg_get_dailymotion_id_from_url( $url );		
	$url = '';
	
	if ( ! empty( $id ) ) {
		$dailymotion_response = wp_remote_get( 'https://api.dailymotion.com/video/' . $id . '?fields=thumbnail_large_url,thumbnail_medium_url' );

		if ( ! is_wp_error( $dailymotion_response ) ) {
			$dailymotion_response = json_decode( $dailymotion_response['body'] );

			if ( isset( $dailymotion_response->thumbnail_large_url ) ) {
				$url = $dailymotion_response->thumbnail_large_url;
			} else {
				$url = $dailymotion_response->thumbnail_medium_url;
			}
		}
	}
    	
	return $url;	
}

/**
 * Get default plugin settings.
 *
 * @since  1.5.3
 * @return array $defaults Array of plugin settings.
 */
function aiovg_get_default_settings() {
	$defaults = array(		
		'aiovg_player_settings' => array(
			'player'   => 'iframe',
			'width'    => '',
			'ratio'    => 56.25,
			'autoplay' => 0,
			'loop'     => 0,
			'muted'    => 0,
			'preload'  => 'metadata',
			'controls' => array(
				'playpause'  => 'playpause',
				'current'    => 'current',
				'progress'   => 'progress', 
				'duration'   => 'duration',
				'tracks'     => 'tracks',
				'quality'    => 'quality',
				'speed'      => 'speed',
				'volume'     => 'volume', 
				'fullscreen' => 'fullscreen'					
			),
			'quality_levels' => implode( "\n", array( '360p', '480p', '720p', '1080p' ) ),
			'use_native_controls' => array(
				'facebook' => 'facebook'
			)
		),
		'aiovg_videos_settings' => array(
			'template'        => 'classic',			
			'columns'         => 3,
			'limit'           => 10,
			'orderby'         => 'date',
			'order'           => 'desc',
			'thumbnail_style' => 'standard',
			'display'         => array(
				'count'    => 'count',
				'title'    => 'title',
				'category' => 'category',
				'tag'      => 'tag',
				'views'    => 'views',
				'duration' => 'duration'
			),
			'excerpt_length'  => 75
		),		
		'aiovg_categories_settings' => array(
			'template'         => 'grid',
			'columns'          => 3,
			'limit'            => 0,
			'orderby'          => 'name',
			'order'            => 'asc',
			'hierarchical'     => 1,
			'show_description' => 0,
			'show_count'       => 1,				
			'hide_empty'       => 0
		),		
		'aiovg_video_settings' => array(
			'display'      => array( 
				'category' => 'category',
				'tag'      => 'tag', 
				'views'    => 'views', 
				'related'  => 'related'
			),
			'has_comments' => 1
		),
		'aiovg_related_videos_settings' => array(
			'columns' => 3,
			'limit'   => 10,
			'orderby' => 'date',
			'order'   => 'desc',
			'display' => array(
				'pagination' => 'pagination'
			)
		),
		'aiovg_image_settings' => array(
			'ratio' => 56.25
		),
		'aiovg_pagination_settings' => array(
			'ajax' => 0
		),
		'aiovg_socialshare_settings' => array(				
			'services' => array( 
				'facebook'  => 'facebook',
				'twitter'   => 'twitter',				
				'linkedin'  => 'linkedin',
				'pinterest' => 'pinterest',
				'tumblr'    => 'tumblr',
				'whatsapp'  => 'whatsapp'
			),
			'open_graph_tags'  => 1,
			'twitter_username' => ''
		),		
		'aiovg_permalink_settings' => array(
			'video' => 'aiovg_videos'
		),
		'aiovg_general_settings' => array(
			'delete_plugin_data' => 1,
			'delete_media_files' => 1,
			'vimeo_access_token' => '',
		),
		'aiovg_page_settings' => aiovg_insert_custom_pages(),
		'aiovg_privacy_settings' => array(
			'show_consent'         => 0,
			'consent_message'      => __( '<strong>Please accept cookies to play this video</strong>. By accepting you will be accessing content from a service provided by an external third party.', 'all-in-one-video-gallery' ),
			'consent_button_label' => __( 'Accept', 'all-in-one-video-gallery' ),
			'disable_cookies'      => array()
		)					
	);
		
	return $defaults;		
}

/**
 * Get image from the Iframe Embed Code.
 *
 * @since  1.0.0
 * @param  string $embedcode Iframe Embed Code.
 * @return string $url       Image URL.
 */
function aiovg_get_embedcode_image_url( $embedcode ) {
	$url = '';

	$document = new DOMDocument();
  	@$document->loadHTML( $embedcode );	

	$iframes = $document->getElementsByTagName( 'iframe' ); 
	if ( $iframes->length > 0 ) {
		if ( $iframes->item(0)->hasAttribute( 'src' ) ) {
			$src = $iframes->item(0)->getAttribute( 'src' );

			// YouTube
			if ( false !== strpos( $src, 'youtube.com' ) || false !== strpos( $src, 'youtu.be' ) ) {
				$url = aiovg_get_youtube_image_url( $src );
			}
			
			// Vimeo
			elseif ( false !== strpos( $src, 'vimeo.com' ) ) {
				$oembed = aiovg_get_vimeo_oembed_data( $src );
				$url = $oembed['thumbnail_url'];
			}
			
			// Dailymotion
			elseif ( false !== strpos( $src, 'dailymotion.com' ) ) {
				$url = aiovg_get_dailymotion_image_url( $src );
			}
		}
	}
    	
	// Return image url
	return $url;	
}

/**
 * Get the video excerpt.
 *
 * @since  1.0.0
 * @param  int    $post_id     Post ID.
 * @param  int    $char_length Excerpt length.
 * @param  string $append      String to append to the end of the excerpt.
 * @return string $content     Excerpt content.
 */
function aiovg_get_excerpt( $post_id = 0 , $char_length = 55, $append = '[...]' ) {
	$content = '';

	if ( $post_id > 0 ) {
		$post = get_post( $post_id );
	} else {
		global $post;
	}	

	if ( ! empty( $post->post_excerpt ) ) {
		$content = $post->post_excerpt;
	} elseif ( ! empty( $post->post_content ) ) {
		if ( 0 == $char_length ) {
			$content = $post->post_content;
		} else {
			$excerpt = wp_strip_all_tags( $post->post_content, true );
			$char_length++;		

			if ( mb_strlen( $excerpt ) > $char_length ) {
				$subex = mb_substr( $excerpt, 0, $char_length - 5 );
				$exwords = explode( ' ', $subex );
				$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
				if ( $excut < 0 ) {
					$content = mb_substr( $subex, 0, $excut );
				} else {
					$content = $subex;
				}
				$content .= $append;
			} else {
				$content = $excerpt;
			}
		}
	}
	
	return apply_filters( 'aiovg_get_excerpt', trim( $content ), $post_id, $char_length, $append );	
}

/**
 * Get the file extension.
 *
 * @since  2.4.4
 * @param  string $url     File URL.
 * @param  string $default Default file extension.
 * @return string $ext     File extension.
 */
function aiovg_get_file_ext( $url, $default = 'mp4' ) {
	if ( $ext = pathinfo( $url, PATHINFO_EXTENSION ) ) {
		return $ext;
	}

	return $default;
}

/**
 * Get image URL using the attachment ID.
 *
 * @since  1.0.0
 * @param  int    $id      Attachment ID.
 * @param  string $size    Size of the image.
 * @param  string $default Default image URL.
 * @param  string $type    "gallery" or "player".
 * @return string $url     Image URL.
 */
function aiovg_get_image_url( $id, $size = "large", $default = '', $type = 'gallery' ) {
	$url = '';
	
	// Get image from attachment
	if ( $id ) {
		$attributes = wp_get_attachment_image_src( (int) $id, $size );
		if ( ! empty( $attributes ) ) {
			$url = $attributes[0];
		}
	}
	
	// Set default image
	if ( ! empty( $default ) ) {
		$default = aiovg_resolve_url( $default );
	} else {
		if ( 'gallery' == $type ) {
			$default = AIOVG_PLUGIN_URL . 'public/assets/images/placeholder-image.png';
		}
	}	
	
	if ( empty( $url ) ) {
		$url = $default;
	}
	
	// Return image url
	return $url;
}

/**
 * Get the client IP Address.
 *
 * @since  2.0.0
 * @return string $ip_address The client IP Address.
 */
function aiovg_get_ip_address() {
	// Whether ip is from share internet
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	}
	
	// Whether ip is from proxy
	elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	
	// Whether ip is from remote address
	else {
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}
	
	return $ip_address;		
}

/**
 * Get message to display based on the $type input.
 *
 * @since  1.0.0
 * @param  string $msg_id  Message Identifier.
 * @return string $message Message to display.
 */
function aiovg_get_message( $msg_id ) {
	$message = '';
	
	switch ( $msg_id ) {
		case 'videos_empty':
			$message = __( 'No videos found.', 'all-in-one-video-gallery' );
			break;
		case 'categories_empty':
			$message = __( 'No categories found.', 'all-in-one-video-gallery' );
			break;
		case 'tags_empty':
			$message = __( 'No tags found.', 'all-in-one-video-gallery' );
			break;
	}
	
	return $message;	
}

/**
 * Get MySQL's RAND function seed value.
 * 
 * @since  1.6.5
 * @param  int    $paged Current Page Number.
 * @return string $seed Seed value.
 */
function aiovg_get_orderby_rand_seed( $paged = 1 ) {
	global $aiovg;

	$seed = '';
	
	if ( isset( $aiovg['rand_seed'] ) ) {
		$aiovg['rand_seed'] = sanitize_text_field( $aiovg['rand_seed'] );
		$seed = $aiovg['rand_seed'];

		if ( 1 == $paged ) {
			$transient_seed = get_transient( 'aiovg_rand_seed_' . $aiovg['rand_seed'] );

			if ( empty( $transient_seed ) ) {
				$seed = wp_rand();
				set_transient( 'aiovg_rand_seed_' . $aiovg['rand_seed'], $seed, 12 * 60 * 60 );				
			} else {
				$seed = sanitize_text_field( $transient_seed );
			}
		}		
	}
	  
	return $seed;
}

/**
 * Get current page number.
 *
 * @since  1.0.0
 * @return int   $paged The current page number.
 */
function aiovg_get_page_number() {
	global $paged;
	
	if ( get_query_var( 'paged' ) ) {
    	$paged = get_query_var( 'paged' );
	} elseif ( get_query_var( 'page' ) ) {
    	$paged = get_query_var( 'page' );
	} else {
		$paged = 1;
	}
    	
	return absint( $paged );		
}

/**
 * Get player HTML.
 * 
 * @since  1.0.0
 * @param  int    $post_id Post ID.
 * @param  array  $atts    Player configuration data.
 * @return string $html    Player HTML.
 */
function aiovg_get_player_html( $post_id = 0, $atts = array() ) {
	$player = AIOVG_Player::get_instance();
	return $player->create( $post_id, $atts );	
}

/**
 * Get player page URL.
 * 
 * @since  1.0.0
 * @param  int    $post_id Post ID.
 * @param  array  $atts    Player configuration data.
 * @return string $url     Player page URL.
 */
function aiovg_get_player_page_url( $post_id = 0, $atts = array() ) {
	$page_settings = get_option( 'aiovg_page_settings' );

	$url = '';
	
	if ( $page_settings['player'] > 0 ) {
		$url = get_permalink( $page_settings['player'] );
	
		$id = $post_id;
		
		if ( empty( $id ) ) {
			global $post;
						
			if ( isset( $post ) ) {
				$id = $post->ID;
			}
		}
		
		if ( ! empty( $id ) ) {
			if ( '' != get_option( 'permalink_structure' ) ) {
				$url = user_trailingslashit( trailingslashit( $url ) . 'id/' . $id );
			} else {
				$url = add_query_arg( array( 'aiovg_type' => 'id', 'aiovg_video' => $id ), $url );
			}
		}
	}
	
	$query_args = array();
	
	foreach ( $atts as $key => $value ) {
		if ( '' !== $value ) {
			switch ( $key ) {
				case 'mp4':
				case 'webm':
				case 'ogv':
				case 'youtube':
				case 'vimeo':
				case 'dailymotion':
				case 'facebook':
				case 'poster':
					$query_args[ $key ] = urlencode( $atts[ $key ] );
					break;
				case 'autoplay':
				case 'loop':
				case 'muted':
				case 'playpause':
				case 'current':
				case 'progress':
				case 'duration':
				case 'tracks':
				case 'quality':
				case 'speed':
				case 'volume':
				case 'fullscreen':
					$query_args[ $key ] = (int) $atts[ $key ];
					break;
			}
		}
	}
	
	if ( ! empty( $query_args ) ) {
		$url = add_query_arg( $query_args, $url );
	}
	
	// Return
	return apply_filters( 'aiovg_player_page_url', $url, $post_id, $atts );
}

/**
 * Generate the search results page URL.
 *
 * @since  1.0.0
 * @return string Search results page URL.
 */
function aiovg_get_search_page_url() {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';
	
	if ( $page_settings['search'] > 0 ) {
		$url = get_permalink( $page_settings['search'] );
	}
	
	return apply_filters( 'aiovg_search_page_url', $url );	
}

/**
 * Get shortcode builder form fields.
 *
 * @since 1.5.7
 */
function aiovg_get_shortcode_fields() {
	$defaults            = aiovg_get_default_settings();
	$image_settings      = array_merge( $defaults['aiovg_image_settings'], get_option( 'aiovg_image_settings', array() ) );
	$categories_settings = array_merge( $defaults['aiovg_categories_settings'], get_option( 'aiovg_categories_settings', array() ) );
	$videos_settings     = array_merge( $defaults['aiovg_videos_settings'], get_option( 'aiovg_videos_settings', array() ) );
	$player_settings     = array_merge( $defaults['aiovg_player_settings'], get_option( 'aiovg_player_settings', array() ) );
	$video_templates     = aiovg_get_video_templates();
	
	// Fields	
	$fields = array(
		'video' => array(
			'title'    => __( 'Single Video', 'all-in-one-video-gallery' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'all-in-one-video-gallery' ),
					'fields' => array(
						array(
							'name'        => 'id',
							'label'       => __( 'Select Video', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'video',
							'value'       => 0
						),
						array(
							'name'        => 'type',
							'label'       => __( 'Source Type', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'select',
							'options'     => aiovg_get_video_source_types(),
							'value'       => 'default'
						),
						array(
							'name'        => 'mp4',
							'label'       => __( 'Video', 'all-in-one-video-gallery' ),
							'description' => __( 'Enter your direct file URL in the textbox above (OR) upload your file using the "Upload File" link.', 'all-in-one-video-gallery' ),
							'type'        => 'media',
							'value'       => ''
						),
						array(
							'name'        => 'youtube',
							'label'       => __( 'YouTube', 'all-in-one-video-gallery' ),
							'description' => sprintf( '%s: https://www.youtube.com/watch?v=twYp6W6vt2U', __( 'Example', 'all-in-one-video-gallery' ) ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'vimeo',
							'label'       => __( 'Vimeo', 'all-in-one-video-gallery' ),
							'description' => sprintf( '%s: https://vimeo.com/108018156', __( 'Example', 'all-in-one-video-gallery' ) ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'dailymotion',
							'label'       => __( 'Dailymotion', 'all-in-one-video-gallery' ),
							'description' => sprintf( '%s: https://www.dailymotion.com/video/x11prnt', __( 'Example', 'all-in-one-video-gallery' ) ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'facebook',
							'label'       => __( 'Facebook', 'all-in-one-video-gallery' ),
							'description' => sprintf( '%s: https://www.facebook.com/facebook/videos/10155278547321729', __( 'Example', 'all-in-one-video-gallery' ) ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'poster',
							'label'       => __( 'Image', 'all-in-one-video-gallery' ),
							'description' => __( 'Enter your direct file URL in the textbox above (OR) upload your file using the "Upload File" link.', 'all-in-one-video-gallery' ),
							'type'        => 'media',
							'value'       => ''
						),
						array(
							'name'        => 'width',
							'label'       => __( 'Width', 'all-in-one-video-gallery' ),
							'description' => __( 'In pixels. Maximum width of the player. Leave this field empty to scale 100% of its enclosing container/html element.', 'all-in-one-video-gallery' ),
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'ratio',
							'label'       => __( 'Ratio', 'all-in-one-video-gallery' ),
							'description' => __( "In percentage. 1 to 100. Calculate player's height using the ratio value entered.", 'all-in-one-video-gallery' ),
							'type'        => 'text',
							'value'       => $player_settings['ratio']
						),
						array(
							'name'        => 'autoplay',
							'label'       => __( 'Autoplay', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $player_settings['autoplay']
						),
						array(
							'name'        => 'loop',
							'label'       => __( 'Loop', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $player_settings['loop']
						),
						array(
							'name'        => 'muted',
							'label'       => __( 'Muted', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $player_settings['muted']
						)					
					)
				),
				'controls' => array(
					'title'  => __( 'Player Controls', 'all-in-one-video-gallery' ),
					'fields' => array(
						array(
							'name'        => 'playpause',
							'label'       => __( 'Play / Pause', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['playpause'] )
						),
						array(
							'name'        => 'current',
							'label'       => __( 'Current Time', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['current'] )
						),
						array(
							'name'        => 'progress',
							'label'       => __( 'Progressbar', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['progress'] )
						),
						array(
							'name'        => 'duration',
							'label'       => __( 'Duration', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['duration'] )
						),
						array(
							'name'        => 'tracks',
							'label'       => __( 'Subtitles', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['tracks'] )
						),
						array(
							'name'        => 'quality',
							'label'       => __( 'Quality Selector', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['quality'] )
						),
						array(
							'name'        => 'speed',
							'label'       => __( 'Speed Control', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['speed'] )
						),
						array(
							'name'        => 'volume',
							'label'       => __( 'Volume', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['volume'] )
						),
						array(
							'name'        => 'fullscreen',
							'label'       => __( 'Fullscreen', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $player_settings['controls']['fullscreen'] )
						)
					)
				)
			)
		),		
		'videos' => array(
			'title'    => __( 'Video Gallery', 'all-in-one-video-gallery' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'all-in-one-video-gallery' ),
					'fields' => array(
						array(
							'name'        => 'title',
							'label'       => __( 'Title', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'template',
							'label'       => __( 'Select Template', 'all-in-one-video-gallery' ),
							'description' => ( aiovg_fs()->is_not_paying() ? sprintf( __( '<a href="%s" target="_blank">Upgrade Pro</a> for more templates (Popup, Slider, etc.)', 'all-in-one-video-gallery' ), esc_url( aiovg_fs()->get_upgrade_url() ) ) : '' ),
							'type'        => 'select',
							'options'     => $video_templates,
							'value'       => $videos_settings['template']
						),
						array(
							'name'        => 'category',
							'label'       => __( 'Select Categories', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'categories',
							'value'       => array()
						),
						array(
							'name'        => 'tag',
							'label'       => __( 'Select Tags', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'tags',
							'value'       => array()
						),
						array(
							'name'        => 'include',
							'label'       => __( 'Include Video ID(s)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),	
						array(
							'name'        => 'exclude',
							'label'       => __( 'Exclude Video ID(s)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),				
						array(
							'name'        => 'limit',
							'label'       => __( 'Limit (per page)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 0,
							'max'         => 500,
							'step'        => 1,
							'value'       => $videos_settings['limit']
						),
						array(
							'name'        => 'orderby',
							'label'       => __( 'Order By', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'title' => __( 'Title', 'all-in-one-video-gallery' ),
								'date'  => __( 'Date Posted', 'all-in-one-video-gallery' ),
								'views' => __( 'Views Count', 'all-in-one-video-gallery' ),
								'rand'  => __( 'Random', 'all-in-one-video-gallery' )
							),
							'value'       => $videos_settings['orderby']
						),
						array(
							'name'        => 'order',
							'label'       => __( 'Order', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'asc'  => __( 'ASC', 'all-in-one-video-gallery' ),
								'desc' => __( 'DESC', 'all-in-one-video-gallery' )
							),
							'value'       => $videos_settings['order']
						),
						array(
							'name'        => 'featured',
							'label'       => __( 'Featured Only', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),
						array(
							'name'        => 'related',
							'label'       => __( 'Follow URL', 'all-in-one-video-gallery' ) . ' (' . __( 'Related Videos', 'all-in-one-video-gallery' ) . ')',
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),					
					)
				),
				'gallery' => array(
					'title'  => __( 'Gallery', 'all-in-one-video-gallery' ),
					'fields' => array(										
						array(
							'name'        => 'ratio',
							'label'       => __( 'Ratio', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => $image_settings['ratio']
						),	
						array(
							'name'        => 'columns',
							'label'       => __( 'Columns', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 1,
							'max'         => 12,
							'step'        => 1,
							'value'       => $videos_settings['columns']
						),
						array(
							'name'        => 'thumbnail_style',
							'label'       => __( 'Image Position (Thumbnails)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'standard'   => __( 'Top', 'all-in-one-video-gallery' ),
								'image-left' => __( 'Left', 'all-in-one-video-gallery' )
							),
							'value'       => $videos_settings['thumbnail_style']
						),
						array(
							'name'        => 'display',
							'label'       => __( 'Show / Hide (Thumbnails)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'header',
							'value'       => 0
						),
						array(
							'name'        => 'show_count',
							'label'       => __( 'Videos Count', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),
						array(
							'name'        => 'show_title',
							'label'       => __( 'Video Title', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['title'] )
						),
						array(
							'name'        => 'show_category',
							'label'       => __( 'Category Name(s)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['category'] )
						),
						array(
							'name'        => 'show_tag',
							'label'       => __( 'Tag Name(s)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['tag'] )
						),				
						array(
							'name'        => 'show_date',
							'label'       => __( 'Date Added', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['date'] )
						),
						array(
							'name'        => 'show_user',
							'label'       => __( 'Author Name', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['user'] )
						),
						array(
							'name'        => 'show_views',
							'label'       => __( 'Views Count', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['views'] )
						),			
						array(
							'name'        => 'show_duration',
							'label'       => __( 'Video Duration', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['duration'] )
						),
						array(
							'name'        => 'show_excerpt',
							'label'       => __( 'Video Excerpt (Short Description)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => isset( $videos_settings['display']['excerpt'] )
						),
						array(
							'name'        => 'excerpt_length',
							'label'       => __( 'Excerpt Length', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'number',
							'value'       => $videos_settings['excerpt_length']
						),
						array(
							'name'        => 'show_pagination',
							'label'       => __( 'Pagination', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'show_more',
							'label'       => __( 'More Button', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),
						array(
							'name'        => 'more_label',
							'label'       => __( 'More Button Label', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => __( 'Show More', 'all-in-one-video-gallery' )
						),
						array(
							'name'        => 'more_link',
							'label'       => __( 'More Button Link', 'all-in-one-video-gallery' ),
							'description' => __( 'Leave this field blank to use Ajax', 'all-in-one-video-gallery' ),
							'type'        => 'url',
							'value'       => ''
						),
					)
				)
			)
		),
		'categories' => array(
			'title'    => __( 'Categories', 'all-in-one-video-gallery' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'all-in-one-video-gallery' ),
					'fields' => array(
						array(
							'name'        => 'title',
							'label'       => __( 'Title', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'template',
							'label'       => __( 'Select Template', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'grid' => __( 'Grid', 'all-in-one-video-gallery' ),
								'list' => __( 'List', 'all-in-one-video-gallery' )
							),
							'value'       => $categories_settings['template']
						),
						array(
							'name'        => 'id',
							'label'       => __( 'Select Parent', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'parent',
							'options'     => array(),
							'value'       => 0
						),
						array(
							'name'        => 'include',
							'label'       => __( 'Include Category ID(s)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),
						array(
							'name'        => 'exclude',
							'label'       => __( 'Exclude Category ID(s)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => ''
						),					
						array(
							'name'        => 'ratio',
							'label'       => __( 'Ratio', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'text',
							'value'       => $image_settings['ratio']
						),
						array(
							'name'        => 'columns',
							'label'       => __( 'Columns', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 1,
							'max'         => 12,
							'step'        => 1,
							'value'       => $categories_settings['columns']
						),
						array(
							'name'        => 'limit',
							'label'       => __( 'Limit (per page)', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'number',
							'min'         => 0,
							'max'         => 500,
							'step'        => 1,
							'value'       => $categories_settings['limit']
						),
						array(
							'name'        => 'orderby',
							'label'       => __( 'Order By', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'id'    => __( 'ID', 'all-in-one-video-gallery' ),
								'count' => __( 'Count', 'all-in-one-video-gallery' ),
								'name'  => __( 'Name', 'all-in-one-video-gallery' ),
								'slug'  => __( 'Slug', 'all-in-one-video-gallery' )	
							),
							'value'       => $categories_settings['orderby']
						),
						array(
							'name'        => 'order',
							'label'       => __( 'Order', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'asc'  => __( 'ASC', 'all-in-one-video-gallery' ),
								'desc' => __( 'DESC', 'all-in-one-video-gallery' )
							),
							'value'       => $categories_settings['order']
						),
						array(
							'name'        => 'hierarchical',
							'label'       => __( 'Show Hierarchy', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $categories_settings['hierarchical']
						),
						array(
							'name'        => 'show_description',
							'label'       => __( 'Show Description', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $categories_settings['show_description']
						),
						array(
							'name'        => 'show_count',
							'label'       => __( 'Show Videos Count', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $categories_settings['show_count']
						),
						array(
							'name'        => 'hide_empty',
							'label'       => __( 'Hide Empty Categories', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => $categories_settings['hide_empty']
						),
						array(
							'name'        => 'show_pagination',
							'label'       => __( 'Show Pagination', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						)
					)
				)
			)
		),		
		'search_form' => array(
			'title'    => __( 'Search Form', 'all-in-one-video-gallery' ),
			'sections' => array(
				'general' => array(
					'title'  => __( 'General', 'all-in-one-video-gallery' ),
					'fields' => array(
						array(
							'name'        => 'template',
							'label'       => __( 'Select Template', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'select',
							'options'     => array(
								'vertical'   => __( 'Vertical', 'all-in-one-video-gallery' ),
								'horizontal' => __( 'Horizontal', 'all-in-one-video-gallery' )
							),
							'value'       => 'horizontal'
						),
						array(
							'name'        => 'keyword',
							'label'       => __( 'Search By Video Title, Description', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 1
						),
						array(
							'name'        => 'category',
							'label'       => __( 'Search By Categories', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						),
						array(
							'name'        => 'tag',
							'label'       => __( 'Search By Tags', 'all-in-one-video-gallery' ),
							'description' => '',
							'type'        => 'checkbox',
							'value'       => 0
						)
					)
				)
			)
		)
	);

	return apply_filters( 'aiovg_shortcode_fields', $fields );
}

/**
 * Get the tag page URL.
 *
 * @since  2.4.3
 * @param  object $term The term object.
 * @return string       Tag page URL.
 */
function aiovg_get_tag_page_url( $term ) {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';
	
	if ( $page_settings['tag'] > 0 ) {
		$url = get_permalink( $page_settings['tag'] );
	
		if ( '' != get_option( 'permalink_structure' ) ) {
    		$url = user_trailingslashit( trailingslashit( $url ) . $term->slug );
  		} else {
    		$url = add_query_arg( 'aiovg_tag', $term->slug, $url );
  		}
	}
  
	return apply_filters( 'aiovg_tag_page_url', $url, $term );
}

/**
 * Get unique ID.
 *
 * @since  1.5.7
 * @return string Unique ID.
 */
function aiovg_get_uniqid() {
	global $aiovg;

	if ( ! isset( $aiovg['uniqid'] ) ) {
		$aiovg['uniqid'] = 0;
	}

	return uniqid() . ++$aiovg['uniqid'];
}

/**
 * Get the user videos page URL.
 *
 * @since  1.0.0
 * @param  int    $user_id User ID.
 * @return string          User videos page URL.
 */
function aiovg_get_user_videos_page_url( $user_id ) {
	$page_settings = get_option( 'aiovg_page_settings' );
	
	$url = '/';

	if ( $page_settings['user_videos'] > 0 ) {	
		$url = get_permalink( $page_settings['user_videos'] );	
		$user_slug = get_the_author_meta( 'user_nicename', $user_id );
		
		if ( '' != get_option( 'permalink_structure' ) ) {
    		$url = user_trailingslashit( trailingslashit( $url ) . $user_slug );
  		} else {
    		$url = add_query_arg( 'aiovg_user', $user_slug, $url );
  		}		
	}
  
	return apply_filters( 'aiovg_author_page_url', $url, $user_id );
}

/**
 * Get video source types.
 * 
 * @since  1.0.0
 * @param  bool  $is_admin True if admin, false if not
 * @return array Array of source types.
 */
function aiovg_get_video_source_types( $is_admin = false ) {
	$types = array(
		'default'     => __( 'Self Hosted', 'all-in-one-video-gallery' ) . ' / ' . __( 'External URL', 'all-in-one-video-gallery' ),
		'youtube'     => __( 'YouTube', 'all-in-one-video-gallery' ),
		'vimeo'       => __( 'Vimeo', 'all-in-one-video-gallery' ),
		'dailymotion' => __( 'Dailymotion', 'all-in-one-video-gallery' ),
		'facebook'    => __( 'Facebook', 'all-in-one-video-gallery' )
	);

	if ( $is_admin && current_user_can( 'unfiltered_html' ) ) {
		$types['embedcode'] = __( 'Iframe Embed Code', 'all-in-one-video-gallery' );
	}
	
	return apply_filters( 'aiovg_video_source_types', $types );
}

/**
 * Get video templates.
 *
 * @since 1.5.7
 * @return array Array of video templates.
 */
function aiovg_get_video_templates() {
	$templates = array(
		'classic' => __( 'Classic', 'all-in-one-video-gallery' )
	);
	
	return apply_filters( 'aiovg_video_templates', $templates );
}

/**
 * Get Vimeo data using oEmbed.
 *
 * @since  1.6.6
 * @param  string $url  Vimeo URL.
 * @return string $data Vimeo oEmbed response data.
 */
function aiovg_get_vimeo_oembed_data( $url ) {
	$data = array(		
		'video_id'      => '',
		'thumbnail_url' => '',
		'html'          => ''
	);

	if ( ! empty( $url ) ) {		
		$vimeo_response = wp_remote_get( 'https://vimeo.com/api/oembed.json?url=' . urlencode( $url ) );

		if ( is_array( $vimeo_response ) && ! is_wp_error( $vimeo_response ) ) {
			$vimeo_response = json_decode( $vimeo_response['body'] );

			if ( isset( $vimeo_response->video_id ) ) {
				$data['video_id'] = $vimeo_response->video_id;
			}	
			
			if ( isset( $vimeo_response->thumbnail_url ) ) {
				$data['thumbnail_url'] = $vimeo_response->thumbnail_url;
			}

			if ( isset( $vimeo_response->html ) ) {
				$data['html'] = $vimeo_response->html;
			}
		}

		// Fallback to our old method to get the Vimeo ID
		if ( empty( $data['video_id'] ) ) {			
			$is_vimeo = preg_match( '/vimeo\.com/i', $url );  

			if ( $is_vimeo ) {
				$data['video_id'] = preg_replace( '/[^\/]+[^0-9]|(\/)/', '', rtrim( $url, '/' ) );
			}
		}

		// Find large thumbnail using the Vimeo API v2
		if ( ! empty( $data['video_id'] ) ) {			
			$vimeo_response = wp_remote_get( 'https://vimeo.com/api/v2/video/' . $data['video_id'] . '.php' );
			
			if ( ! is_wp_error( $vimeo_response ) ) {
				$vimeo_response = maybe_unserialize( $vimeo_response['body'] );

				if ( is_array( $vimeo_response ) && isset( $vimeo_response[0]['thumbnail_large'] ) ) {
					$data['thumbnail_url'] = $vimeo_response[0]['thumbnail_large'];
				}
			}
		}

		// Get images from private videos
		if ( ! empty( $data['video_id'] ) && empty( $data['thumbnail_url'] ) ) {	
			$general_settings = get_option( 'aiovg_general_settings' );

			if ( isset( $general_settings['vimeo_access_token'] ) && ! empty( $general_settings['vimeo_access_token'] ) ) {
				$args = array(
					'headers' => array(
						'Authorization' => 'Bearer ' . sanitize_text_field( $general_settings['vimeo_access_token'] )
					)
				);

				$vimeo_response = wp_remote_get( 'https://api.vimeo.com/videos/' . $data['video_id'] . '/pictures', $args );
				
				if ( is_array( $vimeo_response ) && ! is_wp_error( $vimeo_response ) ) {
					$vimeo_response = json_decode( $vimeo_response['body'] );					

					if ( isset( $vimeo_response->data ) ) {
						$bypass = false;
			
						foreach ( $vimeo_response->data as $item ) {
							foreach ( $item->sizes as $picture ) {
								$data['thumbnail_url'] = $picture->link;
		
								if ( $picture->width >= 400 ) {
									$bypass = true;
									break;
								}
							}
		
							if ( $bypass ) break;
						}
					}
				}
			}
		}
	}
	
	if ( ! empty( $data['thumbnail_url'] ) ) {
		$data['thumbnail_url'] = add_query_arg( 'isnew', 1, $data['thumbnail_url'] );
	}
	
	return $data;
}

/**
 * Get YouTube ID from URL.
 *
 * @since  1.0.0
 * @param  string $url YouTube video URL.
 * @return string $id  YouTube video ID.
 */
function aiovg_get_youtube_id_from_url( $url ) {	
	$id  = '';
    $url = parse_url( $url );
		
    if ( 0 === strcasecmp( $url['host'], 'youtu.be' ) ) {
       	$id = substr( $url['path'], 1 );
    } elseif ( 0 === strcasecmp( $url['host'], 'www.youtube.com' ) ) {
       	if ( isset( $url['query'] ) ) {
       		parse_str( $url['query'], $url['query'] );
           	if ( isset( $url['query']['v'] ) ) {
           		$id = $url['query']['v'];
           	}
       	}
			
       	if ( empty( $id ) ) {
           	$url['path'] = explode( '/', substr( $url['path'], 1 ) );
           	if ( in_array( $url['path'][0], array( 'e', 'embed', 'v' ) ) ) {
               	$id = $url['path'][1];
           	}
       	}
    }
    	
	return $id;	
}

/**
 * Get YouTube image from URL.
 *
 * @since  1.0.0
 * @param  string $url YouTube video URL.
 * @return string $url YouTube image URL.
 */
function aiovg_get_youtube_image_url( $url ) {	
	$id  = aiovg_get_youtube_id_from_url( $url );
	$url = '';

	if ( ! empty( $id ) ) {
		$url = "https://img.youtube.com/vi/$id/mqdefault.jpg"; 
	}
	   	
	return $url;	
}

/**
 * Inserts a new key/value after the key in the array.
 *
 * @since  1.0.0
 * @param  string $key       The key to insert after.
 * @param  array  $array     An array to insert in to.
 * @param  array  $new_array An array to insert.
 * @return                   The new array if the key exists, FALSE otherwise.
 */
function aiovg_insert_array_after( $key, $array, $new_array ) {
	if ( array_key_exists( $key, $array ) ) {
    	$new = array();
    	foreach ( $array as $k => $value ) {
      		$new[ $k ] = $value;
      		if ( $k === $key ) {
				foreach ( $new_array as $new_key => $new_value ) {
        			$new[ $new_key ] = $new_value;
				}
      		}
    	}
    	return $new;
  	}
		
  	return $array;  
}

/**
 * Insert required custom pages and return their IDs as array.
 * 
 * @since  1.0.0
 * @return array Array of created page IDs.
 */
function aiovg_insert_custom_pages() {
	// Vars
	if ( false == get_option( 'aiovg_page_settings' ) ) {		
		$pages = array();
		$page_definitions = aiovg_get_custom_pages_list();
		
		foreach ( $page_definitions as $slug => $page ) {
			$page_check = get_page_by_title( $page['title'] );

			if ( ! isset( $page_check->ID ) ) {
				$id = wp_insert_post(
					array(
						'post_title'     => $page['title'],
						'post_content'   => $page['content'],
						'post_status'    => 'publish',
						'post_author'    => 1,
						'post_type'      => 'page',
						'comment_status' => 'closed'
					)
				);
					
				$pages[ $slug ] = $id;	
			} else {
				$pages[ $slug ] = $page_check->ID;	
			}		
		}
	} else {
		$pages = get_option( 'aiovg_page_settings' );
	}

	return $pages;
}

/**
 * Insert missing custom pages.
 * 
 * @since 2.4.3
 */
function aiovg_insert_missing_pages() {
	$pages = get_option( 'aiovg_page_settings' );
	$page_definitions = aiovg_get_custom_pages_list();		

	foreach ( $page_definitions as $slug => $page ) {
		if ( ! array_key_exists( $slug, $pages ) ) {
			$page_check = get_page_by_title( $page['title'] );

			if ( ! isset( $page_check->ID ) ) {
				$id = wp_insert_post(
					array(
						'post_title'     => $page['title'],
						'post_content'   => $page['content'],
						'post_status'    => 'publish',
						'post_author'    => 1,
						'post_type'      => 'page',
						'comment_status' => 'closed'
					)
				);
					
				$pages[ $slug ] = $id;	
			} else {
				$pages[ $slug ] = $page_check->ID;	
			}	
		}	
	}

	update_option( 'aiovg_page_settings', $pages );
}

/**
 * Check whether the current post/page uses Gutenberg editor.
 *
 * @since  1.6.2
 * @return bool  True if the post/page uses Gutenberg, false if not.
 */
function aiovg_is_gutenberg_page() {
    if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
        // The Gutenberg plugin is on
        return true;
    }
	
    $current_screen = get_current_screen();
    if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
        // Gutenberg page on 5+
        return true;
    }
	
    return false;
}

/**
  * Removes an item or list from the query string.
  *
  * @since  1.0.0
  * @param  string|array $key   Query key or keys to remove.
  * @param  bool|string  $query When false uses the $_SERVER value. Default false.
  * @return string              New URL query string.
  */
function aiovg_remove_query_arg( $key, $query = false ) {
	if ( is_array( $key ) ) { // removing multiple keys
		foreach ( $key as $k ) {
			$query = str_replace( '#038;', '&', $query );
			$query = add_query_arg( $k, false, $query );
		}
		
		return $query;
	}
		
	return add_query_arg( $key, false, $query );	
}

/**
 * Resolve relative file paths as absolute URLs.
 * 
 * @since  2.4.0
 * @param  string $url Input file URL.
 * @return string $url Absolute file URL.
 */
function aiovg_resolve_url( $url ) {
	$host = parse_url( $url, PHP_URL_HOST );

	// Is relative path?
	if ( empty( $host ) ) {
		$url = get_site_url( null, $url );
	}

	return $url;
}

/**
 * Sanitize the array inputs.
 *
 * @since  1.0.0
 * @param  array $value Input array.
 * @return array        Sanitized array.
 */
function aiovg_sanitize_array( $value ) {
	return ! empty( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
}

/**
 * Sanitize the integer inputs, accepts empty values.
 *
 * @since  1.0.0
 * @param  string|int $value Input value.
 * @return string|int        Sanitized value.
 */
function aiovg_sanitize_int( $value ) {
	$value = intval( $value );
	return ( 0 == $value ) ? '' : $value;	
}

/**
 * Sanitize the URLs. Accepts relative file paths, spaces.
 *
 * @since  2.4.0
 * @param  string $value Input value.
 * @return string        Sanitized value.
 */
function aiovg_sanitize_url( $value ) {
	$value = sanitize_text_field( urldecode( $value ) );
	return $value;	
}

/**
 * Update video views count.
 *
 * @since 1.0.0
 * @param int   $post_id Post ID
 */
function aiovg_update_views_count( $post_id ) {	
	$privacy_settings = get_option( 'aiovg_privacy_settings' );
	
	if ( isset( $privacy_settings['disable_cookies'] ) && isset( $privacy_settings['disable_cookies']['aiovg_videos_views'] ) ) {
		$count = (int) get_post_meta( $post_id, 'views', true );
		update_post_meta( $post_id, 'views', ++$count );
	} else {			
		$visited = array();

		if ( isset( $_COOKIE['aiovg_videos_views'] ) ) {
			$visited = explode( '|', $_COOKIE['aiovg_videos_views'] );
			$visited = array_map( 'intval', $visited );
		}

		if ( ! in_array( $post_id, $visited ) ) {
			$count = (int) get_post_meta( $post_id, 'views', true );
			update_post_meta( $post_id, 'views', ++$count );

			// SetCookie
			$visited[] = $post_id;
			setcookie( 'aiovg_videos_views', implode( '|', $visited ), time() + ( 12 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );
		}
	}
}

/**
 * Category thumbnail HTML output.
 *
 * @since 1.5.7
 * @param WP_Term $term WP term object.
 * @param array   $atts Array of attributes.
 */
function the_aiovg_category_thumbnail( $term, $attributes ) {
	include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . 'public/templates/category-thumbnail.php', $attributes );
}

/**
 * Display the video excerpt.
 *
 * @since 1.0.0
 * @param int   $char_length Excerpt length.
 */
function the_aiovg_excerpt( $char_length ) {
	echo wp_kses_post( aiovg_get_excerpt( 0, $char_length ) );
}

/**
 * Display more button on gallery pages.
 *
 * @since 2.5.1
 * @param int   $numpages The total amount of pages.
 * @param array $atts     Array of attributes.
 */
function the_aiovg_more_button( $numpages = '', $atts = array() ) {
	if ( empty( $numpages ) ) {
    	$numpages = 1;
  	}

	$paged = 1;			
	if ( isset( $atts['paged'] ) ) {
		$paged = (int) $atts['paged'];
	}

	$uid = 0;
	if ( isset( $atts['uid'] ) && ! empty( $atts['uid'] ) ) {
		$uid = $atts['uid'];
	}

	$source = 'videos';
	if ( isset( $atts['source'] ) && ! empty( $atts['source'] ) ) {
		$source = $atts['source'];
	}

	if ( empty( $atts['more_link'] ) ) { // Ajax	
		if ( $paged < $numpages ) {			
			wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-public' );

			wp_add_inline_script(
				AIOVG_PLUGIN_SLUG . '-public',
				'var aiovg_pagination_' . esc_html( $uid ) . ' = ' . json_encode( $atts ) . ';',
				'before'
			);

			printf( 
				'<p class="aiovg-more aiovg-more-ajax aiovg-text-center"><button type="button" class="aiovg-link-more" data-uid="%s" data-source="%s" data-numpages="%d" data-paged="%d">%s</button></p>', 
				esc_attr( $uid ),
				esc_attr( $source ),
				$numpages,
				$paged,
				esc_html( $atts['more_label'] ) 
			);
		}
	} else {
		printf( 
			'<p class="aiovg-more aiovg-text-center"><button type="button" onclick="location.href=\'%s\'" class="aiovg-link-more">%s</button></p>', 
			esc_url( $atts['more_link'] ), 
			esc_html( $atts['more_label'] ) 
		);
	}
}

/**
 * Display paginated links on gallery pages.
 *
 * @since 1.0.0
 * @param int   $numpages  The total amount of pages.
 * @param int   $pagerange How many numbers to either side of current page.
 * @param int   $paged     The current page number.
 * @param array $atts      Array of attributes.
 */
function the_aiovg_pagination( $numpages = '', $pagerange = '', $paged = '', $atts = array() ) {
	if ( empty( $numpages ) ) {
    	$numpages = 1;
  	}
	
	if ( empty( $pagerange ) ) {
    	$pagerange = 2;
  	}

  	if ( empty( $paged ) ) {
    	$paged = aiovg_get_page_number();
  	}

	$uid = 0;
	if ( isset( $atts['uid'] ) && ! empty( $atts['uid'] ) ) {
		$uid = $atts['uid'];
	}

	$source = 'videos';
	if ( isset( $atts['source'] ) && ! empty( $atts['source'] ) ) {
		$source = $atts['source'];
	}

	$is_ajax = isset( $atts['pagination_ajax'] ) && ! empty( $atts['pagination_ajax'] );
	if ( $is_ajax ) {
		wp_enqueue_script( AIOVG_PLUGIN_SLUG . '-public' );

		wp_add_inline_script(
			AIOVG_PLUGIN_SLUG . '-public',
			'var aiovg_pagination_' . esc_html( $uid ) . ' = ' . json_encode( $atts ) . ';',
			'before'
		);
	}

  	// Construct the pagination arguments to enter into our paginate_links function
	$arr_params = array();

	parse_str( $_SERVER['QUERY_STRING'], $queries );
	if ( ! empty( $queries ) ) {
		$arr_params = array_keys( $queries );
	}
	 
	$base = aiovg_remove_query_arg( $arr_params, get_pagenum_link( 1 ) );	
	
	if ( ! get_option( 'permalink_structure' ) || isset( $_GET['aiovg'] ) ) {
		$prefix = strpos( $base, '?' ) ? '&' : '?';
    	$format = $prefix . 'paged=%#%';
    } else {
		$prefix = ( '/' == substr( $base, -1 ) ) ? '' : '/';
    	$format = $prefix . 'page/%#%';
	} 
	
  	$pagination_args = array(
    	'base'         => $base . '%_%',
    	'format'       => $format,
    	'total'        => $numpages,
    	'current'      => $paged,
    	'show_all'     => false,
    	'end_size'     => 1,
    	'mid_size'     => $pagerange,
    	'prev_next'    => true,
    	'prev_text'    => __( '&laquo;' ),
    	'next_text'    => __( '&raquo;' ),
    	'type'         => 'array',
    	'add_args'     => false,
    	'add_fragment' => ''
  	);

  	$paginate_links = paginate_links( $pagination_args );

  	if ( is_array( $paginate_links ) ) {
		printf(
			'<div class="aiovg-pagination aiovg-text-center%s" data-uid="%s" data-source="%s" data-current="%d">',			
			( $is_ajax ? ' aiovg-pagination-ajax' : '' ),
			esc_attr( $uid ),
			esc_attr( $source ),
			$paged
		);

		echo '<div class="aiovg-pagination-links">'; 		   	
		foreach ( $paginate_links as $key => $page_link ) {		
			echo $page_link;
		}
		echo '</div>';	

		echo '<p class="aiovg-text-muted">' . sprintf( __( 'Page %d of %d', 'all-in-one-video-gallery' ), $paged, $numpages ) . '</p>';		
		
		echo '</div>';
  	}
}

/**
 * Display a video player.
 * 
 * @since 1.0.0
 * @param int   $post_id Post ID.
 * @param array $atts    Player configuration data.
 */
function the_aiovg_player( $post_id = 0, $atts = array() ) {
	echo aiovg_get_player_html( $post_id, $atts );	
}

/**
 * Display social sharing buttons.
 *
 * @since 1.0.0
 */
function the_aiovg_socialshare_buttons() {
	global $post;
	
	$socialshare_settings = get_option( 'aiovg_socialshare_settings' );
	
	if ( is_singular( 'aiovg_videos' ) ) { 
 		// Get current page url
		$url = get_permalink();
		
		// Get current page title
		$title = get_the_title();
		$title = str_replace( ' ', '%20', $title );
		$title = str_replace( '|', '%7C', $title );
	
		// Get image
		$image_url = get_post_meta( $post->ID, 'image', true );
		$image_id  = get_post_meta( $post->ID, 'image_id', true );
		
		$image = aiovg_get_image_url( $image_id, 'large', $image_url, 'player' );	
 
		// Build sharing buttons
		$buttons = array();
	
		if ( isset( $socialshare_settings['services']['facebook'] ) ) {
			$buttons['facebook'] = array(
				'text' => __( 'Facebook', 'all-in-one-video-gallery' ),
				'url'  => "https://www.facebook.com/sharer/sharer.php?u={$url}"				
			);
		}

		if ( isset( $socialshare_settings['services']['twitter'] ) ) {
			$buttons['twitter'] = array(
				'text' => __( 'Twitter', 'all-in-one-video-gallery' ),
				'url'  => "https://twitter.com/intent/tweet?text={$title}&amp;url={$url}"				
			);
		}		
	
		if ( isset( $socialshare_settings['services']['linkedin'] ) ) {
			$buttons['linkedin'] = array(				
				'text' => __( 'Linkedin', 'all-in-one-video-gallery' ),
				'url'  => "https://www.linkedin.com/shareArticle?url={$url}&amp;title={$title}"
			);
		}

		if ( isset( $socialshare_settings['services']['pinterest'] ) ) {
			$buttons['pinterest'] = array(				
				'text' => __( 'Pin It', 'all-in-one-video-gallery' ),
				'url'  => "https://pinterest.com/pin/create/button/?url={$url}&amp;media={$image}&amp;description={$title}"
			);
		}

		if ( isset( $socialshare_settings['services']['tumblr'] ) ) {
			$tumblr_url = "https://www.tumblr.com/share/link?url={$url}&amp;name={$title}";

			$description = sanitize_text_field( aiovg_get_excerpt( $post->ID, 160, '' ) ); 
			if ( ! empty( $description ) ) {
				$description = str_replace( ' ', '%20', $description );
				$description = str_replace( '|', '%7C', $description );	

				$tumblr_url .= "&amp;description={$description}";
			}

			$buttons['tumblr'] = array(				
				'text' => __( 'Tumblr', 'all-in-one-video-gallery' ),
				'url'  => $tumblr_url
			);
		}

		if ( isset( $socialshare_settings['services']['whatsapp'] ) ) {
			if ( wp_is_mobile() ) {
				$whatsapp_url = "whatsapp://send?text={$title} " . rawurlencode( $url );
			} else {
				$whatsapp_url = "https://api.whatsapp.com/send?text={$title}&nbsp;{$url}";
			}

			$buttons['whatsapp'] = array(				
				'text' => __( 'WhatsApp', 'all-in-one-video-gallery' ),
				'url'  => $whatsapp_url
			);
		}
	
		$buttons = apply_filters( 'aiovg_socialshare_buttons', $buttons );

		if ( count( $buttons ) ) {
			echo '<div class="aiovg-social">';

			foreach ( $buttons as $label => $button ) {
				printf( 
					'<a class="aiovg-social-%s aiovg-link-social" href="%s" target="_blank">%s</a>', 
					esc_attr( $label ),
					esc_attr( $button['url'] ), 
					wp_kses_post( $button['text'] ) 
				);
			}

			echo '</div>';
		}	
	}
}

/**
 * Build & display attributes using the $atts array.
 * 
 * @since 1.0.0
 * @param array $atts Array of attributes.
 */
function the_aiovg_video_attributes( $atts ) {
	echo aiovg_combine_video_attributes( $atts );
}

/**
 * Video thumbnail HTML output.
 *
 * @since 1.5.7
 * @param WP_Post $post WP post object.
 * @param array   $atts Array of attributes.
 */
function the_aiovg_video_thumbnail( $post, $attributes ) {
	// Update Vimeo images to the latest
	$type   = get_post_meta( $post->ID, 'type', true );
	$src    = '';
	$update = 0;	

	if ( 'vimeo' == $type ) {
		$image = get_post_meta( $post->ID, 'image', true );		

		if ( empty( $image ) ) {
			$update = 1;
		} else {
			if ( false !== strpos( $image, 'vimeocdn.com' ) ) {
				$query = parse_url( $image, PHP_URL_QUERY );
				parse_str( $query, $parsed_url );
				
				if ( ! isset( $parsed_url['isnew'] ) ) {					
					$update = 1;
				}
			}
		}		

		if ( $update ) {
			$src = get_post_meta( $post->ID, 'vimeo', true );
		}
	}

	if ( 'embedcode' == $type ) {
		$embedcode = get_post_meta( $post->ID, 'embedcode', true );

		$document = new DOMDocument();
  		@$document->loadHTML( $embedcode );	

		$iframes = $document->getElementsByTagName( 'iframe' ); 
		if ( $iframes->length > 0 ) {
			if ( $iframes->item(0)->hasAttribute( 'src' ) ) {
				$src = $iframes->item(0)->getAttribute( 'src' );

				if ( false !== strpos( $src, 'vimeo.com' ) ) {
					$image = get_post_meta( $post->ID, 'image', true );		

					if ( empty( $image ) ) {
						$update = 1;
					} else {
						if ( false !== strpos( $image, 'vimeocdn.com' ) ) {
							$query = parse_url( $image, PHP_URL_QUERY );
							parse_str( $query, $parsed_url );
							
							if ( ! isset( $parsed_url['isnew'] ) ) {					
								$update = 1;
							}
						}
					}		
				}
			}
		}		
	}

	if ( $update && $src ) {
		$oembed = aiovg_get_vimeo_oembed_data( $src );
		$thumbnail_url = $oembed['thumbnail_url'];

		update_post_meta( $post->ID, 'image', $thumbnail_url );
	}

	// ...
	$template = 'video-thumbnail.php';
	
	if ( isset( $attributes['thumbnail_style'] ) && 'image-left' == $attributes['thumbnail_style'] ) {
		$template = 'video-thumbnail-image-left.php';
	}

	include apply_filters( 'aiovg_load_template', AIOVG_PLUGIN_DIR . "public/templates/{$template}", $attributes );
}