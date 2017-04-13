<?php
/*
Plugin Name: Featured Posts by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/wordpress/plugins/featured-posts/
Description: Add featured posts to WordPress posts or widgets. Highlight important information.
Author: BestWebSoft
Text Domain: bws-featured-posts
Domain Path: /languages
Version: 1.0.0
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*  @ Copyright 2016  BestWebSoft  ( http://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Add option page in admin menu
*/
if ( ! function_exists( 'ftrdpsts_admin_menu' ) ) {
	function ftrdpsts_admin_menu() {
		bws_general_menu();
		$settings = add_submenu_page( 'bws_panel', __( 'Featured Posts Settings', 'bws-featured-posts' ), 'Featured Posts', 'manage_options', 'featured-posts.php', 'ftrdpsts_settings_page' );
		add_action( 'load-' . $settings, 'ftrdpsts_add_tabs' );
	}
}

/**
 * Internationalization
 */
if ( ! function_exists( 'ftrdpsts_plugins_loaded' ) ) {
	function ftrdpsts_plugins_loaded() {
		load_plugin_textdomain( 'bws-featured-posts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
* Plugin initialization
*/
if ( ! function_exists( 'ftrdpsts_init' ) ) {
	function ftrdpsts_init() {
		global $ftrdpsts_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $ftrdpsts_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$ftrdpsts_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $ftrdpsts_plugin_info, '3.8' );
	}
}

if ( ! function_exists( 'ftrdpsts_admin_init' ) ) {
	function ftrdpsts_admin_init() {
		global $bws_plugin_info, $ftrdpsts_plugin_info, $bws_shortcode_list;
		/* Add variable for bws_menu */
		if ( empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '179', 'version' => $ftrdpsts_plugin_info["Version"] );

		/* Call register settings function */
		if ( isset( $_REQUEST['page'] ) && ( 'featured-posts.php' == $_REQUEST['page'] ) )
			ftrdpsts_settings();

		/* add Featured Posts to global $bws_shortcode_list  */
		$bws_shortcode_list['ftrdpsts'] = array( 'name' => 'Featured Posts' );
	}
}

/**
* Register settings for plugin
*/
if ( ! function_exists( 'ftrdpsts_settings' ) ) {
	function ftrdpsts_settings() {
		global $ftrdpsts_options, $ftrdpsts_plugin_info, $ftrdpsts_option_defaults;

		$ftrdpsts_option_defaults = array(
			'plugin_option_version' 	=> $ftrdpsts_plugin_info["Version"],
			'display_before_content'	=> 0,
			'display_after_content'		=> 1,
			'block_width'				=> '100%',
			'text_block_width'			=> '960px',
			'posts_count'				=> 1,
			'theme_style'				=> 1,
			'background_color_block'	=> '#f3f3f3',
			'background_color_text'		=> '#f3f3f3',
			'color_text'				=> '#777b7e',
			'color_header'				=> '#777b7e',
			'color_link'				=> '#777b7e',
			'display_settings_notice'	=>	1,
			'suggest_feature_banner'	=>  1
		);

		/* Install the option defaults */
		if ( ! get_option( 'ftrdpsts_options' ) )
			add_option( 'ftrdpsts_options', $ftrdpsts_option_defaults );

		$ftrdpsts_options = get_option( 'ftrdpsts_options' );

		if ( ! isset( $ftrdpsts_options['plugin_option_version'] ) || $ftrdpsts_options['plugin_option_version'] != $ftrdpsts_plugin_info["Version"] ) {
			$ftrdpsts_options = array_merge( $ftrdpsts_option_defaults, $ftrdpsts_options );
			$ftrdpsts_options['plugin_option_version'] = $ftrdpsts_plugin_info["Version"];
			update_option( 'ftrdpsts_options', $ftrdpsts_options );
		}
	}
}

/**
* Add settings page in admin area
*/
if ( ! function_exists( 'ftrdpsts_settings_page' ) ) {
	function ftrdpsts_settings_page(){
		global $title, $ftrdpsts_options, $ftrdpsts_plugin_info, $ftrdpsts_option_defaults;
		$message = $error = '';
		$plugin_basename = plugin_basename( __FILE__ );

		if ( isset( $_POST['ftrdpsts_form_submit'] ) && check_admin_referer( $plugin_basename, 'ftrdpsts_check_field' ) ) {
			$ftrdpsts_options['display_before_content'] = ( isset( $_POST['ftrdpsts_display_before_content'] ) ) ? 1 : 0;
			$ftrdpsts_options['display_after_content'] = ( isset( $_POST['ftrdpsts_display_after_content'] ) ) ? 1 : 0;
			$block_width = trim( stripslashes( esc_html( $_POST['ftrdpsts_block_width'] ) ) );			
			if ( preg_match( '/^([^0]\d{1,4})(px|%)$/', $block_width ) )
				$ftrdpsts_options['block_width'] = $block_width;
			else
				$error = __( "Invalid value for 'Block width'", 'bws-featured-posts' );

			$text_block_width = trim( stripslashes( esc_html( $_POST['ftrdpsts_text_block_width'] ) ) );
			if ( preg_match( '/^([^0]\d{1,4})(px|%)$/', $text_block_width ) )
				$ftrdpsts_options['text_block_width'] = $text_block_width;
			else
				$error = __( "Invalid value for 'Content block width'", 'bws-featured-posts' );

			$ftrdpsts_options['posts_count'] = intval( $_POST['ftrdpsts_posts_count'] );
			$ftrdpsts_options['theme_style'] = ( isset( $_POST['ftrdpsts_theme_style'] ) ) ? 1 : 0;
			$ftrdpsts_options['background_color_block'] = stripslashes( esc_html( $_POST['ftrdpsts_background_color_block'] ) );
			$ftrdpsts_options['background_color_text'] = stripslashes( esc_html( $_POST['ftrdpsts_background_color_text'] ) );
			$ftrdpsts_options['color_text'] = stripslashes( esc_html( $_POST['ftrdpsts_color_text'] ) );
			$ftrdpsts_options['color_header'] = stripslashes( esc_html( $_POST['ftrdpsts_color_header'] ) );
			$ftrdpsts_options['color_link'] = stripslashes( esc_html( $_POST['ftrdpsts_color_link'] ) );			

			update_option( 'ftrdpsts_options', $ftrdpsts_options );
			$message = __( 'Settings saved.', 'bws-featured-posts' );
		}

		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$ftrdpsts_options = $ftrdpsts_option_defaults;
			update_option( 'ftrdpsts_options', $ftrdpsts_options );
			$message = __( 'All plugin settings were restored.', 'bws-featured-posts' );
		}

		$theme_style_class = $ftrdpsts_options['theme_style'] == 1 ? 'hidden-field' : ''; ?>
		<div class="wrap">
			<h1><?php echo $title; ?></h1>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=featured-posts.php"><?php _e( 'Settings', 'bws-featured-posts' ); ?></a>
				<a class="nav-tab <?php if ( isset( $_GET['action'] ) && 'custom_code' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=featured-posts.php&amp;action=custom_code"><?php _e( 'Custom code', 'bws-featured-posts' ); ?></a>
			</h2>
			<?php bws_show_settings_notice(); ?>
			<div class="updated fade below-h2" <?php if ( empty( $message ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error below-h2" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
			<?php if ( ! isset( $_GET['action'] ) ) {
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else { ?>
					<p><strong><?php _e( "If you would like to add Featured Posts to your page or post, select posts to be displayed (on the page/post editing page, in Featured Post block, please mark 'Display this post in the Featured Post block?').", 'bws-featured-posts' ); ?></strong></p>
					<div><?php printf(
						__( "If you would like to add Featured Posts to your page or post, please use %s button", 'bws-featured-posts' ),
						'<span class="bws_code"><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt=""/></span>' ); ?>
						<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help">
							<div class="bws_hidden_help_text" style="min-width: 180px;">
								<?php printf(
									__( "You can add Featured Posts to your page or post by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode %s", 'bws-featured-posts' ),
									'<code><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt="" /></code>',
									'<span class="bws_code">[bws_featured_post]</span>'
								); ?>
							</div>
						</div>
					</div>
					<p>
						<?php _e( "Also, you can paste the following strings into the template source code", 'bws-featured-posts' ); ?>
						<code>
							&lt;?php if( has_action( 'ftrdpsts_featured_posts' ) ) {
								do_action( 'ftrdpsts_featured_posts' );
							} ?&gt;
						</code>
					</p>
					<form class="bws_form" method='post' action='admin.php?page=featured-posts.php'>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row"><?php _e( 'Display the block with Featured Posts', 'bws-featured-posts' ); ?></th>
									<td>
										<label>
											<input type="checkbox" value="1" name="ftrdpsts_display_before_content" <?php if ( $ftrdpsts_options['display_before_content'] == 1 ) echo 'checked="checked"'; ?> />
											<?php _e( 'Before the Post', 'bws-featured-posts' ); ?>
										</label><br />
										<label>
											<input type="checkbox" value="1" name="ftrdpsts_display_after_content" <?php if ( $ftrdpsts_options['display_after_content'] == 1 ) echo 'checked="checked"'; ?> />
											<?php _e( 'After the Post', 'bws-featured-posts' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Block width', 'bws-featured-posts' ); ?></th>
									<td>
										<input maxlength="6" type="text" class="regular-text" value="<?php echo $ftrdpsts_options['block_width']; ?>" name="ftrdpsts_block_width">
										<p class="description"><?php _e( 'Please, enter the value in &#37; or px, for instance, 100&#37; or 960px', 'bws-featured-posts' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Content block width', 'bws-featured-posts' ); ?></th>
									<td>
										<input maxlength="6" type="text" class="regular-text" value="<?php echo $ftrdpsts_options['text_block_width']; ?>" name="ftrdpsts_text_block_width" />
										<p class="description"><?php _e( 'Please, enter the value in &#37; or px, for instance, 100&#37; or 960px', 'bws-featured-posts' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Number of posts to display', 'bws-featured-posts' ); ?></th>
									<td>
										<label>
											<input type="number" min="1" max="999" value="<?php echo $ftrdpsts_options['posts_count']; ?>" name="ftrdpsts_posts_count"  />
										</label>
									</td>
								</tr>								
								<tr>
									<th scope="row"><?php _e( 'Style', 'bws-featured-posts' ); ?></th>
									<td>
										<label>
											<input id="ftrdpsts_theme_style" type="checkbox" value="1" name="ftrdpsts_theme_style" <?php if ( $ftrdpsts_options['theme_style'] == 1 ) echo 'checked="checked"'; ?> />
											<?php _e( 'Use theme styles for Featured Posts block', 'bws-featured-posts' ); ?>
										</label>
									</td>
								</tr>
								<tr class="ftrdpsts_theme_style <?php echo $theme_style_class; ?>">
									<th scope="row"><?php _e( 'Background Color for block', 'bws-featured-posts' ); ?></th>
									<td>
										<input type="text" value="<?php echo $ftrdpsts_options['background_color_block']; ?>" name="ftrdpsts_background_color_block" maxlength="7" class="wp-color-picker" />
									</td>
								</tr>
								<tr class="ftrdpsts_theme_style <?php echo $theme_style_class; ?>">
									<th scope="row"><?php _e( 'Background Color for text', 'bws-featured-posts' ); ?></th>
									<td>
										<input type="text" value="<?php echo $ftrdpsts_options['background_color_text']; ?>" name="ftrdpsts_background_color_text" maxlength="7" class="wp-color-picker" />
									</td>
								</tr>
								<tr class="ftrdpsts_theme_style <?php echo $theme_style_class; ?>">
									<th scope="row"><?php _e( 'Title Color', 'bws-featured-posts' ); ?></th>
									<td>
										<input type="text" value="<?php echo $ftrdpsts_options['color_header']; ?>" name="ftrdpsts_color_header" maxlength="7" class="wp-color-picker" />
									</td>
								</tr>
								<tr class="ftrdpsts_theme_style <?php echo $theme_style_class; ?>">
									<th scope="row"><?php _e( 'Text Color', 'bws-featured-posts' ); ?></th>
									<td>
										<input type="text" value="<?php echo $ftrdpsts_options['color_text']; ?>" name="ftrdpsts_color_text" maxlength="7" class="wp-color-picker" />
									</td>
								</tr>
								<tr class="ftrdpsts_theme_style <?php echo $theme_style_class; ?>">
									<th scope="row"><?php _e( '"Learn more" Link Color', 'bws-featured-posts' ); ?></th>
									<td>
										<input type="text" value="<?php echo $ftrdpsts_options['color_link']; ?>" name="ftrdpsts_color_link" maxlength="7" class="wp-color-picker" />
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<input id="bws-submit-button" type="submit" value="<?php _e( 'Save Changes', 'bws-featured-posts' ); ?>" class="button button-primary" name="ftrdpsts_submit" >
							<input type="hidden" name="ftrdpsts_form_submit" value="submit" />
							<?php wp_nonce_field( $plugin_basename, 'ftrdpsts_check_field' ); ?>
						</p>
					</form>
					<?php bws_form_restore_default_settings( $plugin_basename );
				}
			} else {
				bws_custom_code_tab();
			}
			bws_plugin_reviews_block( $ftrdpsts_plugin_info['Name'], 'bws-featured-posts' ); ?>
		</div>
	<?php }
}

if( ! function_exists( 'ftrdpsts_loop_start' ) ) {
	function ftrdpsts_loop_start( $query ) {
		global $wp_query, $ftrdpsts_is_main_query;
		if ( is_main_query() && $query === $wp_query ) {
			$ftrdpsts_is_main_query = true;
		}
	}
}

if( ! function_exists( 'ftrdpsts_loop_end' ) ) {
	function ftrdpsts_loop_end( $query ) {
		global $ftrdpsts_is_main_query;
		$ftrdpsts_is_main_query = false;
	}
}

/**
 * Display Block with Featured Post
 * @return echo Featured Post block
 */
if ( ! function_exists( 'ftrdpsts_display_block' ) ) {
	function ftrdpsts_display_block( $content ) {
		global $ftrdpsts_options, $ftrdpsts_is_main_query;

		if ( ( is_single() || is_page() ) && $ftrdpsts_is_main_query ) {
			if ( empty( $ftrdpsts_options ) )
				ftrdpsts_settings();

			$ftrdpsts_block = ftrdpsts_featured_posts( true );

			/* Indication where show Facebook Button depending on selected item in admin page. */
			if ( 1 == $ftrdpsts_options['display_before_content'] && 1 == $ftrdpsts_options['display_after_content'] )
				return $ftrdpsts_block . $content . $ftrdpsts_block;
			elseif ( 1 == $ftrdpsts_options['display_before_content'] )
				return $ftrdpsts_block . $content;
			else if ( 1 == $ftrdpsts_options['display_after_content'] )
				return $content . $ftrdpsts_block;
			else
				return $content;
		} else {
			return $content;
		}
	}
}

if ( ! function_exists( 'ftrdpsts_get_the_excerpt' ) ) {
	function ftrdpsts_get_the_excerpt( $content ) {
		$charlength = 100;
		$content = wp_strip_all_tags( $content );
		if ( strlen( $content ) > $charlength ) {
			$subex = substr( $content, 0, $charlength-5 );
			$exwords = explode( " ", $subex );
			$excut = - ( strlen( $exwords [ count( $exwords ) - 1 ] ) );
			$new_content = ( $excut < 0 ) ? substr( $subex, 0, $excut ) : $subex;
			$new_content .= "...";
			return $new_content;
		} else {
			return $content;
		}
	}
}

/**
 * Display Featured Post shortcode
 * @return Featured Post block
 */
if ( ! function_exists( 'ftrdpsts_featured_posts_shortcode' ) ) {
	function ftrdpsts_featured_posts_shortcode() {
		return ftrdpsts_featured_posts( true );
	}
}

/**
 * Display Featured Post
 * @return echo Featured Post block
 */
if ( ! function_exists( 'ftrdpsts_featured_posts' ) ) {
	function ftrdpsts_featured_posts( $return = false ) {
		global $ftrdpsts_options, $post;

		if ( empty( $ftrdpsts_options ) )
			ftrdpsts_settings();

		$result = '';
		$post__not_in = array();
		if ( isset( $post->ID ) )
			$post__not_in[] = $post->ID;

		$the_query = new WP_Query( array(
			'post_type'				=> array( 'post', 'page' ),
			'meta_key'				=> '_ftrdpsts_add_to_featured_post',
			'meta_value'			=> '1',
			'posts_per_page'		=> $ftrdpsts_options['posts_count'],
			'orderby'				=> 'rand',
			'ignore_sticky_posts' 	=> 1,
			'post__not_in'			=> $post__not_in
		) );
		/* The Loop */
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				global $post;
				$post->post_content = str_replace( '[bws_featured_post]', '', $post->post_content );
				$result .= '<div class="ftrdpsts_heading_featured_post">
					<div class="widget_content">
						<h2>
							<a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a>
						</h2>' .
						'<p>' . ftrdpsts_get_the_excerpt( $post->post_content ) . '</p>' .
						'<a href="' . get_permalink( $post->ID ) . '" class="more">' . __( 'Learn more', 'bws-featured-posts' ) . '</a>
					</div><!-- .widget_content -->
				</div><!-- .ftrdpsts_heading_featured_post -->';
			}
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		wp_reset_query();

		if ( true === $return )
			return $result;
		else
			echo $result;
	}
}

/*
 * Add a box to the main column on the Post and Page edit screens.
 */
if ( ! function_exists( 'ftrdpsts_featured_posts_add_custom_box' ) ) {
	function ftrdpsts_featured_posts_add_custom_box() {
		$screens = array( 'post', 'page' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				'showonfeaturedpost',
				__( 'Featured Post', 'bws-featured-posts' ),
				'ftrdpsts_featured_post_inner_custom_box',
				$screen
			);
		}
	}
}

/**
 * Prints the meta box content.
 *
 * @param WP_Post $post The object for the current post/page.
 */
if ( ! function_exists( 'ftrdpsts_featured_post_inner_custom_box' ) ) {
	function ftrdpsts_featured_post_inner_custom_box( $post ) {
		/* Add an nonce field so we can check for it later. */
		wp_nonce_field( 'ftrdpsts_featured_post_inner_custom_box', 'ftrdpsts_featured_post_inner_custom_box_nonce' );
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$is_check = get_post_meta( $post->ID, '_ftrdpsts_add_to_featured_post', true ); ?>
		<div class="check-to-display">
			<label>
				<input type="checkbox" name="ftrdpsts_featured_post_checkbox" <?php if ( $is_check == true ) echo 'checked="checked"'; ?> value="1" />
				<?php _e( "Display this post in the Featured Post block?", 'bws-featured-posts' ); ?>
			</label>
		</div>
	<?php }
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
if ( ! function_exists( 'ftrdpsts_featured_posts_save_postdata' ) ) {
	function ftrdpsts_featured_posts_save_postdata( $post_id ) {
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
		/* If this is an autosave, our form has not been submitted, so we don't want to do anything. */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		/* Check if our nonce is set. */
		if ( ! isset( $_POST[ 'ftrdpsts_featured_post_inner_custom_box_nonce' ] ) )
			return $post_id;
		else {
			$nonce = $_POST[ 'ftrdpsts_featured_post_inner_custom_box_nonce' ];
			/* Verify that the nonce is valid. */
			if ( ! wp_verify_nonce( $nonce, 'ftrdpsts_featured_post_inner_custom_box' ) )
				return $post_id;
		}
		if ( isset( $_POST[ 'ftrdpsts_featured_post_inner_custom_box_nonce' ] ) ) {
			$ftrdpsts_featured_post_checkbox = isset( $_POST[ 'ftrdpsts_featured_post_checkbox' ] ) ? 1 : 0;
			/* Update the meta field in the database. */
			update_post_meta( $post_id, '_ftrdpsts_add_to_featured_post', $ftrdpsts_featured_post_checkbox );
		}
	}
}

/**
	* Add style for featured posts block
	*/
if ( ! function_exists( 'ftrdpsts_wp_head' ) ) {
	function ftrdpsts_wp_head() {
		global $ftrdpsts_options;

		wp_enqueue_style( 'ftrdpsts_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );

		if ( empty( $ftrdpsts_options ) )
			ftrdpsts_settings(); ?>
		<style type="text/css">
			.ftrdpsts_heading_featured_post {
				width: <?php echo $ftrdpsts_options['block_width']; ?>;
			}
			.ftrdpsts_heading_featured_post .widget_content {
				width: <?php echo $ftrdpsts_options['text_block_width']; ?>;
			}
			<?php if ( $ftrdpsts_options['theme_style'] != 1 ) { ?>
				.ftrdpsts_heading_featured_post {
					background-color: <?php echo $ftrdpsts_options['background_color_block']; ?> !important;
				}
				.ftrdpsts_heading_featured_post .widget_content {
					background-color: <?php echo $ftrdpsts_options['background_color_text']; ?> !important;
				}
				.ftrdpsts_heading_featured_post .widget_content h2 a {
					color: <?php echo $ftrdpsts_options['color_header']; ?> !important;
				}
				.ftrdpsts_heading_featured_post .widget_content p {
					color: <?php echo $ftrdpsts_options['color_text']; ?> !important;
				}
				.ftrdpsts_heading_featured_post .widget_content > a {
					color: <?php echo $ftrdpsts_options['color_link']; ?> !important;
				}
			<?php } ?>
		</style>
	<?php }
}

/**
* Add style for admin page
*/
if ( ! function_exists( 'ftrdpsts_admin_head' ) ) {
	function ftrdpsts_admin_head() {
		if ( isset( $_REQUEST['page'] ) && 'featured-posts.php' == $_REQUEST['page'] ) {
			wp_enqueue_style( 'ftrdpsts_stylesheet', plugins_url( 'css/style.css', __FILE__ ), array( 'wp-color-picker' ) );
			wp_enqueue_script( 'ftrdpsts_script', plugins_url( '/js/script.js', __FILE__ ) ,array( 'jquery', 'wp-color-picker' ) );

			if ( isset( $_GET['action'] ) && 'custom_code' == $_GET['action'] )
				bws_plugins_include_codemirror();
		}
	}
}

/* add shortcode content  */
if ( ! function_exists( 'ftrdpsts_shortcode_button_content' ) ) {
	function ftrdpsts_shortcode_button_content( $content ) { ?>
		<div id="ftrdpsts" style="display:none;">
			<fieldset>
				<?php _e( 'Add Featured Posts to your website', 'bws-featured-posts' ); ?>
			</fieldset>
			<input class="bws_default_shortcode" type="hidden" name="default" value="[bws_featured_post]" />
			<div class="clear"></div>
		</div>
	<?php }
}

/**
 * Function to handle action links
 */
if ( ! function_exists( 'ftrdpsts_plugin_action_links' ) ) {
	function ftrdpsts_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename(__FILE__);

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=featured-posts.php">' . __( 'Settings', 'bws-featured-posts' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/**
* Additional links on the plugin page
*/
if ( ! function_exists( 'ftrdpsts_register_plugin_links' ) ) {
	function ftrdpsts_register_plugin_links( $links, $file ) {
		$base = plugin_basename(__FILE__);
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[] = '<a href="admin.php?page=featured-posts.php">' . __( 'Settings','bws-featured-posts' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/bws-featured-posts/faq/" target="_blank">' . __( 'FAQ','bws-featured-posts' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support','bws-featured-posts' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'ftrdpsts_admin_notices' ) ) {
	function ftrdpsts_admin_notices() {
		global $hook_suffix, $ftrdpsts_plugin_info;
		if ( 'plugins.php' == $hook_suffix ) {
			bws_plugin_banner_to_settings( $ftrdpsts_plugin_info, 'ftrdpsts_options', 'bws-featured-posts', 'admin.php?page=featured-posts.php' );
		}

		if ( isset( $_GET['page'] ) && 'featured-posts' == $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $ftrdpsts_plugin_info, 'ftrdpsts_options', 'bws-featured-posts' );
		}
	}
}

/* add help tab  */
if ( ! function_exists( 'ftrdpsts_add_tabs' ) ) {
	function ftrdpsts_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id' 			=> 'ftrdpsts',
			'section' 		=> '200892655'
		);
		bws_help_tab( $screen, $args );
	}
}

/**
 * Delete plugin options
 */
if ( ! function_exists( 'ftrdpsts_plugin_uninstall' ) ) {
	function ftrdpsts_plugin_uninstall() {
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			global $wpdb;
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );

				$allposts = get_posts( 'meta_key=_ftrdpsts_add_to_featured_post' );
				foreach( $allposts as $postinfo ) {
					delete_post_meta( $postinfo->ID, '_ftrdpsts_add_to_featured_post' );
				}

				delete_option( 'ftrdpsts_options' );
			}
			switch_to_blog( $old_blog );
		} else {
			$allposts = get_posts( 'meta_key=_ftrdpsts_add_to_featured_post' );
			foreach( $allposts as $postinfo ) {
				delete_post_meta( $postinfo->ID, '_ftrdpsts_add_to_featured_post' );
			}

			delete_option( 'ftrdpsts_options' );
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

/* Add option page in admin menu */
add_action( 'admin_menu', 'ftrdpsts_admin_menu' );
/* Plugin Internationalization */
add_action( 'plugins_loaded', 'ftrdpsts_plugins_loaded' );
/* Plugin initialization */
add_action( 'init', 'ftrdpsts_init' );

/* Plugin initialization for admin page */
add_action( 'admin_init', 'ftrdpsts_admin_init' );

/* Adds a box to the main column on the Post and Page edit screens. */
add_action( 'add_meta_boxes', 'ftrdpsts_featured_posts_add_custom_box' );

/* When the post is saved, saves our custom data. */
add_action( 'save_post', 'ftrdpsts_featured_posts_save_postdata' );

/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'ftrdpsts_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'ftrdpsts_register_plugin_links', 10, 2 );
add_action( 'admin_notices', 'ftrdpsts_admin_notices' );

/* Add style for Featured Posts block */
add_action( 'wp_enqueue_scripts', 'ftrdpsts_wp_head' );
/* Add style for admin page */
add_action( 'admin_enqueue_scripts', 'ftrdpsts_admin_head' );

/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'ftrdpsts_shortcode_button_content' );
/* Display Featured Post */
add_action( 'ftrdpsts_featured_posts', 'ftrdpsts_featured_posts' );

/* Add shortcode and plugin block */
add_shortcode( 'bws_featured_post', 'ftrdpsts_featured_posts_shortcode' );
add_action( 'loop_start', 'ftrdpsts_loop_start' );
add_filter( 'the_content', 'ftrdpsts_display_block' );
add_action( 'loop_end', 'ftrdpsts_loop_end' );

register_uninstall_hook( __FILE__, 'ftrdpsts_plugin_uninstall' );