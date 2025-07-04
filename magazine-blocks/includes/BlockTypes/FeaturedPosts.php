<?php

/**
 * Featured Posts block.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

use WP_Query;

use function MagazineBlocks\mzb_numbered_pagination;


defined( 'ABSPATH' ) || exit;

/**
 * Button block class.
 */
class FeaturedPosts extends AbstractBlock {


	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'featured-posts';

	public function render( $attributes, $content, $block ) {
		if ( magazine_blocks_is_rest_request() ) {
			return $content;
		}

		$client_id  = magazine_blocks_array_get( $attributes, 'clientId', '' );
		$class_name = magazine_blocks_array_get( $attributes, 'className', '' );

		// General.
		$layout                  = magazine_blocks_array_get( $attributes, 'layout', '' );
		$layout_1_advanced_style = magazine_blocks_array_get( $attributes, 'layout1AdvancedStyle', '' );
		$layout_2_advanced_style = magazine_blocks_array_get( $attributes, 'layout2AdvancedStyle', '' );
		$layout_3_advanced_style = magazine_blocks_array_get( $attributes, 'layout3AdvancedStyle', '' );
		$layout_4_advanced_style = magazine_blocks_array_get( $attributes, 'layout4AdvancedStyle', '' );
		$layout_5_advanced_style = magazine_blocks_array_get( $attributes, 'layout5AdvancedStyle', '' );
		$layout_6_advanced_style = magazine_blocks_array_get( $attributes, 'layout6AdvancedStyle', '' );
		$column                  = magazine_blocks_array_get( $attributes, 'column', '' );

		// Query.
		$category          = magazine_blocks_array_get( $attributes, 'category', '' );
		$tag               = magazine_blocks_array_get( $attributes, 'tag', '' );
		$excluded_category = magazine_blocks_array_get( $attributes, 'excludedCategory', '' );
		$order_by          = magazine_blocks_array_get( $attributes, 'orderBy', '' );
		$order_type        = magazine_blocks_array_get( $attributes, 'orderType', '' );
		$author            = magazine_blocks_array_get( $attributes, 'authorName', '' );
		$post_count        = magazine_blocks_array_get( $attributes, 'postCount', '' );
		$post_type         = magazine_blocks_array_get( $attributes, 'postType', 'post' );

		// Heading
		$enable_heading                  = magazine_blocks_array_get( $attributes, 'enableHeading', '' );
		$heading_layout                  = magazine_blocks_array_get( $attributes, 'headingLayout', '' );
		$heading_layout_1_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout1AdvancedStyle', '' );
		$heading_layout_2_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout2AdvancedStyle', '' );
		$heading_layout_3_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout3AdvancedStyle', '' );
		$heading_layout_4_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout4AdvancedStyle', '' );
		$heading_layout_5_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout5AdvancedStyle', '' );
		$heading_layout_6_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout6AdvancedStyle', '' );
		$heading_layout_7_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout7AdvancedStyle', '' );
		$heading_layout_8_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout8AdvancedStyle', '' );
		$heading_layout_9_advanced_style = magazine_blocks_array_get( $attributes, 'headingLayout9AdvancedStyle', '' );
		$label                           = magazine_blocks_array_get( $attributes, 'label', 'Latest' );

		// Post Box.
		$post_box_style = magazine_blocks_array_get( $attributes, 'postBoxStyle', 'boxed' );

		// Post Title.
		$post_title_markup = magazine_blocks_array_get( $attributes, 'postTitleMarkup', 'h3' );
		$post_title_markup = magazine_blocks_sanitize_html_tag( $post_title_markup, 'h3' );

		// Featured Image.
		$enable_featured_image = magazine_blocks_array_get( $attributes, 'enableFeaturedImage', '' );
		$hover_animation       = magazine_blocks_array_get( $attributes, 'hoverAnimation', '' );

		// Header Meta.
		$enable_category               = magazine_blocks_array_get( $attributes, 'enableCategory', '' );
		$enable_comment                = magazine_blocks_array_get( $attributes, 'enableComment', '' );
		$enable_highlighted_category   = magazine_blocks_array_get( $attributes, 'enableHighlightedCategory', '' );
		$enable_highlighted_comment    = magazine_blocks_array_get( $attributes, 'enableHighlightedComment', '' );
		$category_position             = magazine_blocks_array_get( $attributes, 'categoryPosition', '' );
		$highlighted_category_position = magazine_blocks_array_get( $attributes, 'highlightedCategoryPosition', '' );

		// Meta.
		$meta_position         = magazine_blocks_array_get( $attributes, 'metaPosition', '' );
		$enable_author         = magazine_blocks_array_get( $attributes, 'enableAuthor', '' );
		$enable_date           = magazine_blocks_array_get( $attributes, 'enableDate', '' );
		$enable_readtime       = magazine_blocks_array_get( $attributes, 'enableReadTime', '' );
		$enable_viewcount      = magazine_blocks_array_get( $attributes, 'enableViewCount', '' );
		$enable_icon           = magazine_blocks_array_get( $attributes, 'enableIcon', '' );
		$enable_meta_separator = magazine_blocks_array_get( $attributes, 'enableMetaSeparator', '' );
		$meta_separator        = magazine_blocks_array_get( $attributes, 'separatorType', 'none' );

		// Excerpt.
		$enable_excerpt = magazine_blocks_array_get( $attributes, 'enableExcerpt', '' );
		$excerpt_limit  = magazine_blocks_array_get( $attributes, 'excerptLimit', '' );

		// ReadMore.
		$enable_readmore         = magazine_blocks_array_get( $attributes, 'enableReadMore', '' );
		$read_more_text          = magazine_blocks_array_get( $attributes, 'readMoreText', '' );
		$enable_read_more_icon   = magazine_blocks_array_get( $attributes, 'enableReadMoreIcon', '' );
		$read_more_icon          = magazine_blocks_array_get( $attributes, 'readMoreIcon', '' );
		$get_read_more_icon      = magazine_blocks_get_icon( $read_more_icon, false );
		$enable_read_more_border = magazine_blocks_array_get( $attributes, 'enableReadMoreBorder', '' );

		// Pagination
		$enable_pagination = magazine_blocks_array_get( $attributes, 'enablePagination', '' );

		//View All
		$enable_view_more         = magazine_blocks_array_get( $attributes, 'enableViewMore', '' );
		$view_more_text           = magazine_blocks_array_get( $attributes, 'viewMoreText', '' );
		$view_button_position     = magazine_blocks_array_get( $attributes, 'viewButtonPosition', '' );
		$enable_view_more_icon    = magazine_blocks_array_get( $attributes, 'enableViewMoreIcon', '' );
		$view_more_icon           = magazine_blocks_array_get( $attributes, 'viewMoreIcon', '' );
		$get_icon                 = magazine_blocks_get_icon( $view_more_icon, false );
		$view_more_url            = magazine_blocks_array_get( $attributes, 'viewMoreLink', array() );
		$layout4_top_row_count    = magazine_blocks_array_get( $attributes, 'layout4TopRowCount', array() );
		$layout4_bottom_row_count = magazine_blocks_array_get( $attributes, 'layout4BottomRowCount', array() );
		$enable_view_more_border  = magazine_blocks_array_get( $attributes, 'enableViewMoreBorder', '' );

		$enable_highlighted_category_border = magazine_blocks_array_get( $attributes, 'enableHighlightedCategoryBorder', '' );
		$enable_category_border             = magazine_blocks_array_get( $attributes, 'enableCategoryBorder', '' );

		$href   = isset( $view_more_url['url'] ) ? esc_url( $view_more_url['url'] ) : '';
		$target = ! empty( $view_more_url['newTab'] ) ? ' target="_blank"' : '';
		$rel    = ! empty( $view_more_url['noFollow'] ) ? ' rel="nofollow"' : '';

		//offset
		$offset = magazine_blocks_array_get( $attributes, 'offset', 0 );

		// Define the custom excerpt length function as an anonymous function
		$custom_excerpt_length = function ( $length ) use ( $excerpt_limit ) {
			return $excerpt_limit; // Change this number to your desired word limit
		};

		// Add the filter to modify the excerpt length using the anonymous function
		add_filter( 'excerpt_length', $custom_excerpt_length );

		if ( 'heading-layout-1' === $heading_layout ) {
			$heading_style = $heading_layout_1_advanced_style;
		} elseif ( 'heading-layout-2' === $heading_layout ) {
			$heading_style = $heading_layout_2_advanced_style;
		} elseif ( 'heading-layout-3' === $heading_layout ) {
			$heading_style = $heading_layout_3_advanced_style;
		} elseif ( 'heading-layout-4' === $heading_layout ) {
			$heading_style = $heading_layout_4_advanced_style;
		} elseif ( 'heading-layout-5' === $heading_layout ) {
			$heading_style = $heading_layout_5_advanced_style;
		} elseif ( 'heading-layout-6' === $heading_layout ) {
			$heading_style = $heading_layout_6_advanced_style;
		} elseif ( 'heading-layout-7' === $heading_layout ) {
			$heading_style = $heading_layout_7_advanced_style;
		} elseif ( 'heading-layout-8' === $heading_layout ) {
			$heading_style = $heading_layout_8_advanced_style;
		} elseif ( 'heading-layout-9' === $heading_layout ) {
			$heading_style = $heading_layout_9_advanced_style;
		}

		if ( 'layout-1' === $layout ) {
			$advanced_style = $layout_1_advanced_style;
		} elseif ( 'layout-2' === $layout ) {
			$advanced_style = $layout_2_advanced_style;
		} elseif ( 'layout-3' === $layout ) {
			$advanced_style = $layout_3_advanced_style;
		} elseif ( 'layout-4' === $layout ) {
			$advanced_style = $layout_4_advanced_style;
		} elseif ( 'layout-5' === $layout ) {
			$advanced_style = $layout_5_advanced_style;
		} elseif ( 'layout-6' === $layout ) {
			$advanced_style = $layout_6_advanced_style;
		}
		// Pagination.
		$paged         = isset( $_GET[ 'block_id_' . $client_id ] ) ? max( 1, intval( $_GET[ 'block_id_' . $client_id ] ) ) : 1;
		$args['paged'] = $paged;

		$post_card_border = magazine_blocks_array_get( $attributes, 'postCardBorder', '' );
		if ( isset( $post_card_border['border'] ) ) {
			$post_card_border = $post_card_border['border'];
		} else {
			$post_card_border = '';
		}

		$args = array(
			'post_type'           => $post_type,
			'posts_per_page'      => $post_count,
			'status'              => 'publish',
			'cat'                 => $category,
			'tag_id'              => $tag,
			'orderby'             => $order_by,
			'order'               => $order_type,
			'author'              => $author,
			'category__not_in'    => $excluded_category,
			'ignore_sticky_posts' => 1,
			'paged'               => $paged, // Use the paged parameter.
			'offset'              => $offset,
		);

		$cat_name = get_cat_name( $category );

		$cat_name = empty( $cat_name ) ? 'Latest' : $cat_name;

		$type = get_query_var( 'mzb_template_type' );

		if ( in_array( $type, array( 'archive', 'search', 'single', 'front' ), true ) ) {
			unset( $args['cat'], $args['tag_id'], $args['orderby'], $args['order'], $args['author'], $args['category__not_in'], $args['ignore_sticky_posts'], $args['paged'], $args['offset'] );
			$paged = get_query_var( 'paged' );
			switch ( get_query_var( 'mzb_template_type' ) ) {
				case 'archive':
					if ( is_archive() ) {
						if ( is_category() ) {
							$args['category_name'] = get_query_var( 'category_name' );
						} elseif ( is_tag() ) {
							$args['tag'] = get_query_var( 'tag' );
						} elseif ( is_author() ) {
							$args['author'] = get_query_var( 'author' );
						}
					}
					break;
				case 'search':
					$args['s'] = get_search_query();
					break;
			}
		}

		$query = new WP_Query(
			$args
		);

		# The Loop.
		$html = '';

		if ( $query->have_posts() ) {

			$html .= '<div class="mzb-featured-posts mzb-featured-posts-' . $client_id . ' ' . $class_name . ( $enable_view_more_border ? ' mzb-view-more-border' : '' ) . '">';
			$html .= '<div class="mzb-post-heading mzb-' . $heading_layout . ' mzb-' . $heading_style . '">';
			if ( $enable_heading ) {
				$html .= '<h2 class="mzb-heading-text">' . esc_html( $label ) . '</h2>';
			}
			if ( $enable_view_more && 'top' === $view_button_position ) {
				$html .= '<div class="mzb-view-more"><a href="' . $href . '"' . $target . $rel . '>';
				$html .= '<p>' . $view_more_text . '</p>';
				if ( $enable_view_more_icon ) {
					$html .= $get_icon;
				}
				$html .= '</a></div>';
			}
			$html .= '</div>';

			$html .= '<div class="mzb-posts mzb-post-col--' . $column . ' mzb-' . $layout . ' mzb-' . $advanced_style . ' mzb-' . $post_box_style . ( 'layout-4' === $layout ? ' mzb-layout-4-style-1-top-row-' . $layout4_top_row_count . ' mzb-layout-4-style-1-bottom-row-' . $layout4_bottom_row_count : '' ) . ( $post_card_border ? ' mzb-post-card-border' : '' ) . ( 1 === $post_count ? ' mzb-post-col--full' : '' ) . ( $enable_read_more_border ? ' mzb-read-more-border' : '' ) . '">';

			$index = 1;
			while ( $query->have_posts() ) {
				$query->the_post();
				$id       = get_post_thumbnail_id();
				$src      = wp_get_attachment_image_src( $id );
				$src      = has_post_thumbnail( get_the_ID() ) ? get_the_post_thumbnail_url( get_the_ID() ) : '';
				$category = '<span class="mzb-post-categories">' . get_the_category_list( ' ' ) . '</span>';
				$comment  = '<a href="' . get_comments_link() . '">' . get_comments_number() . '</a>';
				if ( $src ) {
					$image = '<div class="mzb-featured-image ' . esc_attr( $hover_animation ) . ' ';
					if ( 'layout-4' !== $layout && ( ( 1 === $index ) || ( 'layout-1' === $layout && ( ( 2 === (int) $column && ( 1 === $index || 2 === $index ) ) || ( 3 === (int) $column && ( 1 === $index || 2 === $index || 3 === $index ) ) ) ) ) ) {
						if ( 'in-image' === $highlighted_category_position ) {
							$image .= 'mzb-category--inside-image';
						}
					} elseif ( 'in-image' === $category_position ) {
						$image .= 'mzb-category--inside-image';
					}
					$image .= '">';
					$image .= '<a href="' . esc_url( get_the_permalink() ) . '"title="' . esc_attr( get_the_title() ) . '"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( get_the_title() ) . '"/></a>';
					if ( 'layout-4' !== $layout && ( ( 1 === $index ) || ( 'layout-1' === $layout && ( ( 2 === (int) $column && ( 1 === $index || 2 === $index ) ) || ( 3 === (int) $column && ( 1 === $index || 2 === $index || 3 === $index ) ) ) ) ) ) {
						if ( ( $enable_highlighted_category || $enable_highlighted_comment ) && ( 'in-image' === $highlighted_category_position ) ) {
							$image .= '<div class="mzb-post-meta">';
							$image .= $category;
							if ( true === $enable_comment ) {
								$image .= '<span class="comments-link">';
								$image .= '<svg class="mzb-icon mzb-icon--comment" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path fill-rule="evenodd" d="M12 4c-5.19 0-9 3.33-9 7 0 1.756.84 3.401 2.308 4.671l.412.358-.46 3.223 3.456-1.728.367.098c.913.245 1.893.378 2.917.378 5.19 0 9-3.33 9-7s-3.81-7-9-7zM1 11c0-5.167 5.145-9 11-9s11 3.833 11 9-5.145 9-11 9c-1.06 0-2.087-.122-3.06-.352l-6.2 3.1.849-5.94C1.999 15.266 1 13.246 1 11z"></path>
							</svg>';
								$image .= $comment;
								$image .= '</span>';
							}
								$image .= '</div>';
						}
					} elseif ( ( $enable_category || $enable_comment ) && ( 'in-image' === $category_position ) ) {
						$image .= '<div class="mzb-post-meta">';
						$image .= $category;
						if ( true === $enable_comment ) {
							$image .= '<span class="comments-link">';
							$image .= '<svg class="mzb-icon mzb-icon--comment" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
							<path fill-rule="evenodd" d="M12 4c-5.19 0-9 3.33-9 7 0 1.756.84 3.401 2.308 4.671l.412.358-.46 3.223 3.456-1.728.367.098c.913.245 1.893.378 2.917.378 5.19 0 9-3.33 9-7s-3.81-7-9-7zM1 11c0-5.167 5.145-9 11-9s11 3.833 11 9-5.145 9-11 9c-1.06 0-2.087-.122-3.06-.352l-6.2 3.1.849-5.94C1.999 15.266 1 13.246 1 11z"></path>
						</svg>';
							$image .= $comment;
							$image .= '</span>';
						}
						$image .= '</div>';
					}

					$image .= '</div>';
				} else {
					$image = '';
				}
					$title      = '<' . $post_title_markup . ' class="mzb-post-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></' . $post_title_markup . '>';
					$author     = '<span class="mzb-post-author" >' . ( ( true === $enable_icon ) ? '<img class="post-author-image" src="' . get_avatar_url( get_the_author_meta( 'ID' ) ) . ' "/> ' : '' ) . get_the_author_posts_link() . '</span>';
					$date       = '<span class ="mzb-post-date">' . ( ( true === $enable_icon ) ? '<svg class="mzb-icon mzb-icon--calender" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14">
								<path d="M1.892 12.929h10.214V5.5H1.892v7.429zm2.786-8.822v-2.09a.226.226 0 00-.066-.166.226.226 0 00-.166-.065H3.98a.226.226 0 00-.167.065.226.226 0 00-.065.167v2.09c0 .067.022.122.065.166.044.044.1.065.167.065h.465a.226.226 0 00.166-.065.226.226 0 00.066-.167zm5.571 0v-2.09a.226.226 0 00-.065-.166.226.226 0 00-.167-.065h-.464a.226.226 0 00-.167.065.226.226 0 00-.065.167v2.09c0 .067.021.122.065.166.043.044.099.065.167.065h.464a.226.226 0 00.167-.065.226.226 0 00.065-.167zm2.786-.464v9.286c0 .251-.092.469-.276.652a.892.892 0 01-.653.276H1.892a.892.892 0 01-.653-.275.892.892 0 01-.276-.653V3.643c0-.252.092-.47.276-.653a.892.892 0 01.653-.276h.929v-.696c0-.32.113-.593.34-.82.228-.227.501-.34.82-.34h.465c.319 0 .592.113.82.34.227.227.34.5.34.82v.696h2.786v-.696c0-.32.114-.593.34-.82.228-.227.501-.34.82-.34h.465c.32 0 .592.113.82.34.227.227.34.5.34.82v.696h.93c.25 0 .468.092.652.276a.892.892 0 01.276.653z" />
							</svg>' : '' ) .
							'<a href="' . esc_url( get_the_permalink() ) . '"> ' . get_the_date() . '</a></span>';
					$view       = get_post_meta( get_the_ID(), '_mzb_post_view_count', true );
					$read_time  = $enable_readtime ? '<span class="mzb-post-read-time">' .
					( ( true === $enable_icon ) ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
				<path fill-rule="evenodd" d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18ZM1 12C1 5.925 5.925 1 12 1s11 4.925 11 11-4.925 11-11 11S1 18.075 1 12Z" clip-rule="evenodd"/>
				<path fill-rule="evenodd" d="M12 5a1 1 0 0 1 1 1v5.382l3.447 1.724a1 1 0 1 1-.894 1.788l-4-2A1 1 0 0 1 11 12V6a1 1 0 0 1 1-1Z" clip-rule="evenodd"/>
				</svg>' : '' ) .
					'<span>' .
					self::calculate_read_time( $id ) . '
				min
				read
				</span>
				</span>' : '';
					$view_count = $enable_viewcount ? '<span class="mzb-post-view-count">' .
					( ( true === $enable_icon ) ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
				<path d="M12 17.9c-4.2 0-7.9-2.1-9.9-5.5-.2-.3-.2-.6 0-.9C4.1 8.2 7.8 6 12 6s7.9 2.1 9.9 5.5c.2.3.2.6 0 .9-2 3.4-5.7 5.5-9.9 5.5zM3.9 12c1.6 2.6 4.8 4.2 8.1 4.2s6.4-1.6 8.1-4.2c-1.6-2.6-4.7-4.2-8.1-4.2S5.6 9.4 3.9 12zm8.1 3.3c-1.8 0-3.3-1.5-3.3-3.3s1.5-3.3 3.3-3.3 3.3 1.5 3.3 3.3-1.5 3.3-3.3 3.3zm0-4.9c-.9 0-1.6.8-1.6 1.6 0 .9.8 1.6 1.6 1.6s1.6-.8 1.6-1.6c0-.9-.7-1.6-1.6-1.6z" />
				</svg>' : '' ) .
																				'<span>' . ( empty( $view ) ? 0 : $view ) . '
																					views
																				</span>
																			</span>' : '';
					$html      .= '<div class="mzb-post' . ( 'layout-4' !== $layout && ( ( 1 === $index ) || ( 'layout-1' === $layout && ( ( 2 === (int) $column && ( 1 === $index || 2 === $index ) ) || ( 3 === (int) $column && ( 1 === $index || 2 === $index || 3 === $index ) ) ) ) ) ? ' mzb-first-post--highlight ' . ( $enable_highlighted_category_border ? 'mzb-highlighted-post-category-border' : '' ) : ( $enable_category_border ? ' mzb-post-category-border' : '' ) ) . '">';
					$html      .= '';
					$html      .= ( true == $enable_featured_image ) ? $image : '';
					$html      .= '<div class="mzb-post-content ' . ( $meta_position ? 'mzb-meta-position--' . $meta_position : '' ) . '">';
				if ( 'layout-4' !== $layout && ( ( 1 === $index ) || ( 'layout-1' === $layout && ( ( 2 === (int) $column && ( 1 === $index || 2 === $index ) ) || ( 3 === (int) $column && ( 1 === $index || 2 === $index || 3 === $index ) ) ) ) ) ) {
					if ( ( $enable_highlighted_category || $enable_highlighted_comment ) && ( 'out-image' === $highlighted_category_position ) ) {
						$html .= '<div class="mzb-post-meta">';
						$html .= $category;
						if ( true === $enable_comment ) {
							$html .= '<span class="comments-link">';
							$html .= '<svg class="mzb-icon mzb-icon--comment" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path fill-rule="evenodd" d="M12 4c-5.19 0-9 3.33-9 7 0 1.756.84 3.401 2.308 4.671l.412.358-.46 3.223 3.456-1.728.367.098c.913.245 1.893.378 2.917.378 5.19 0 9-3.33 9-7s-3.81-7-9-7zM1 11c0-5.167 5.145-9 11-9s11 3.833 11 9-5.145 9-11 9c-1.06 0-2.087-.122-3.06-.352l-6.2 3.1.849-5.94C1.999 15.266 1 13.246 1 11z"></path>
							</svg>';
							$html .= $comment;
							$html .= '</span>';
						}
						$html .= '</div>';
					}
				} elseif ( ( $enable_category || $enable_comment ) && ( 'out-image' === $category_position ) ) {
					$html .= '<div class="mzb-post-meta">';
					$html .= $category;
					if ( true === $enable_comment ) {
						$html .= '<span class="comments-link">';
						$html .= '<svg class="mzb-icon mzb-icon--comment" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
							<path fill-rule="evenodd" d="M12 4c-5.19 0-9 3.33-9 7 0 1.756.84 3.401 2.308 4.671l.412.358-.46 3.223 3.456-1.728.367.098c.913.245 1.893.378 2.917.378 5.19 0 9-3.33 9-7s-3.81-7-9-7zM1 11c0-5.167 5.145-9 11-9s11 3.833 11 9-5.145 9-11 9c-1.06 0-2.087-.122-3.06-.352l-6.2 3.1.849-5.94C1.999 15.266 1 13.246 1 11z"></path>
						</svg>';
						$html .= $comment;
						$html .= '</span>';
					}
					$html .= '</div>';
				}

				if ( 'top' === $meta_position ) {
					if ( $enable_author || $enable_date || $enable_readtime || $enable_viewcount ) {
						$html .= '<div class="mzb-post-entry-meta' . ( $enable_meta_separator && $meta_separator ? ' mzb-meta-separator--' . $meta_separator : '' ) . '">';
						$html .= $enable_author ? $author : '';
						$html .= '';
						$html .= $enable_date ? $date : '';
						$html .= $enable_readtime ? $read_time : '';
						$html .= $enable_viewcount ? $view_count : '';
						$html .= '</div>';
					}
				}
					$html .= $title;
				if ( 'bottom' === $meta_position ) {
					if ( $enable_author || $enable_date || $enable_readtime || $enable_viewcount ) {
						$html .= '<div class="mzb-post-entry-meta' . ( $enable_meta_separator && $meta_separator ? ' mzb-meta-separator--' . $meta_separator : '' ) . '">';
						$html .= $enable_author ? $author : '';
						$html .= '';
						$html .= $enable_date ? $date : '';
						$html .= $enable_readtime ? $read_time : '';
						$html .= $enable_viewcount ? $view_count : '';
						$html .= '</div>';
					}
				}
				if ( ( $enable_excerpt || $enable_readmore ) && ( 'layout-6' !== $layout || 1 === $index ) && ( 'layout-5' !== $layout || 1 === $index ) ) {
					$html .= '<div class="mzb-entry-content">';
					$html .= $enable_excerpt ? '<div class="mzb-entry-summary"><p> ' . get_the_excerpt() . '</p></div>' : '';
					$html .= $enable_readmore ? '<div class="mzb-read-more"><a href="' . esc_url( get_the_permalink() ) . '">' . $read_more_text . ( $enable_read_more_icon ? $get_read_more_icon : '' ) . ' </a></div>' : '';
					$html .= '</div>';
				}
					$html .= '</div>';
					$html .= '</div>';
					++$index;
			}
			$html .= '</div>';
			if ( $enable_view_more && 'bottom' === $view_button_position ) {
				$html .= '<div class="mzb-view-more"><a href="' . $href . '"' . $target . $rel . '>';
				$html .= '<p>' . $view_more_text . '</p>';
				if ( $enable_view_more_icon ) {
					$html .= $get_icon;
				}
				$html .= '</a></div>';
			}
				// Custom pagination function.
			if ( $enable_pagination ) {
				$html .= mzb_numbered_pagination( $query->max_num_pages, $paged, $client_id );
			}

				$html .= '</div>';
				wp_reset_postdata();
		}
			return $html;
	}

	public function calculate_read_time( $post_id ) {
		$words_per_minute = 200;
		$content          = get_post_field( 'post_content', $post_id );
		$word_count       = str_word_count( wp_strip_all_tags( $content ) );
		$read_time        = ceil( $word_count / $words_per_minute );
		return $read_time;
	}
}
