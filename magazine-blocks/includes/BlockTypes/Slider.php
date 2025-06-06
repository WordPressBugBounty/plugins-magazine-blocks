<?php

/**
 * Slider block.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Button block class.
 */
class Slider extends AbstractBlock {


	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'slider';

	public function render( $attributes, $content, $block ) {

		$client_id = magazine_blocks_array_get( $attributes, 'clientId', '' );

		$category          = magazine_blocks_array_get( $attributes, 'category', '' );
		$excluded_category = magazine_blocks_array_get( $attributes, 'excludedCategory', '' );
		$class_name        = magazine_blocks_array_get( $attributes, 'className', '' );
		$post_count        = magazine_blocks_array_get( $attributes, 'postCount', '' );
		$slider_style      = magazine_blocks_array_get( $attributes, 'sliderStyle', 'style1' );
		$slides_per_view   = ( 'style3' === $slider_style || 'style4' === $slider_style ) ? intval( magazine_blocks_array_get( $attributes, 'slidesPerView', '1' ) ) : 1;
		$slider_args       = array(
			'speed'         => magazine_blocks_array_get( $attributes, 'sliderSpeed', '' ),
			'autoplay'      => magazine_blocks_array_get( $attributes, 'enableAutoPlay', 'true' ),
			'arrows'        => magazine_blocks_array_get( $attributes, 'enableArrow', '' ),
			'pagination'    => magazine_blocks_array_get( $attributes, 'enableDot', '' ),
			'pauseOnHover'  => magazine_blocks_array_get( $attributes, 'enablePauseOnHover', '' ),
			'slidesPerView' => $slides_per_view,
			'sliderStyle'   => $slider_style,
		);

		// Header Meta.
		$enable_category = magazine_blocks_array_get( $attributes, 'enableCategory', '' );

		// Meta.
		$meta_position = magazine_blocks_array_get( $attributes, 'metaPosition', '' );
		$enable_author = magazine_blocks_array_get( $attributes, 'enableAuthor', '' );
		$enable_date   = magazine_blocks_array_get( $attributes, 'enableDate', '' );
		$enable_icon   = magazine_blocks_array_get( $attributes, 'enableIcon', '' );

		// Excerpt.
		$enable_excerpt = magazine_blocks_array_get( $attributes, 'enableExcerpt', '' );
		$excerpt_limit  = magazine_blocks_array_get( $attributes, 'excerptLimit', '' );

		// ReadMore.
		$enable_readmore = magazine_blocks_array_get( $attributes, 'enableReadMore', '' );
		$read_more_text  = magazine_blocks_array_get( $attributes, 'readMoreText', '' );

		// Hide on Desktop.
		$hide_on_desktop = magazine_blocks_array_get( $attributes, 'hideOnDesktop', '' );

		// Arrow Position.
		$enable_arrow   = magazine_blocks_array_get( $attributes, 'enableArrow', '' );
		$arrow_position = magazine_blocks_array_get( $attributes, 'arrowPosition', 'center' );

		// Pagination dots
		$enable_pagination = magazine_blocks_array_get( $attributes, 'enableDot', '' );

		//slider style
		$slider_style = magazine_blocks_array_get( $attributes, 'sliderStyle', 'style1' );

		// Post Title.
		$post_title_markup = magazine_blocks_array_get( $attributes, 'postTitleMarkup', 'h3' );
		$post_title_markup = magazine_blocks_sanitize_html_tag( $post_title_markup, 'h3' );

		// Define the custom excerpt length function as an anonymous function
		$custom_excerpt_length = function ( $length ) use ( $excerpt_limit ) {
			return $excerpt_limit; // Change this number to your desired word limit
		};

		// Query.
		$category          = magazine_blocks_array_get( $attributes, 'category', '' );
		$tag               = magazine_blocks_array_get( $attributes, 'tag', '' );
		$excluded_category = magazine_blocks_array_get( $attributes, 'excludedCategory', '' );
		$order_by          = magazine_blocks_array_get( $attributes, 'orderBy', '' );
		$order_type        = magazine_blocks_array_get( $attributes, 'orderType', '' );
		$author            = magazine_blocks_array_get( $attributes, 'authorName', '' );
		$post_count        = magazine_blocks_array_get( $attributes, 'postCount', '' );

		// Add the filter to modify the excerpt length using the anonymous function
		add_filter( 'excerpt_length', $custom_excerpt_length );

		$args = array(
			'posts_per_page'      => $post_count,
			'status'              => 'publish',
			'cat'                 => $category,
			'tag_id'              => $tag,
			'orderby'             => $order_by,
			'order'               => $order_type,
			'author'              => $author,
			'category__not_in'    => $excluded_category,
			'ignore_sticky_posts' => 1,
		);

		$cat_name = get_cat_name( $category );

		$cat_name = empty( $cat_name ) ? 'Latest' : $cat_name;

		$query = new WP_Query( $args );

		# The Loop.
		$html = '';

		if ( $query->have_posts() ) {
			$html .= '<div class="mzb-slider mzb-slider-' . $client_id . ' ' . $class_name .
				( $hide_on_desktop ? 'magazine-blocks-hide-on-desktop' : '' ) . 'mzb-arrow-position-' . $arrow_position . '">';
			if ( 'top' === $arrow_position && $enable_arrow ) {
				$html .= '<div class="swiper-button-prev"></div>';
				$html .= '<div class="swiper-button-next"></div>';
			}
			$html .= '<div class="swiper" data-swiper=' . htmlspecialchars_decode( wp_json_encode( $slider_args ) ) . '>';
			$html .= '<div class="swiper-wrapper">';

			while ( $query->have_posts() ) {
				$html .= '<div class="swiper-slide ' . ( $slider_style ? $slider_style : 'style1' ) . '">';
				$query->the_post();
				$id       = get_post_thumbnail_id();
				$src      = wp_get_attachment_image_src( $id );
				$src      = has_post_thumbnail( get_the_ID() ) ? get_the_post_thumbnail_url( get_the_ID() ) : '';
				$image    = $src ? '<div class="mzb-featured-image"><a href="' . esc_url( get_the_permalink() ) . '"alt="' . get_the_title() . '"/><img src="' . esc_url( $src ) . '" alt="' . get_the_title() . '"/> </a></div>' : '';
				$title    = '<' . $post_title_markup . ' class="mzb-post-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></' . $post_title_markup . '>';
				$category = '<span class="mzb-post-categories">' . get_the_category_list( ' ' ) . '</span>';
				$author   = '<span class="mzb-post-author" >' . ( ( true === $enable_icon ) ? '<img class="post-author-image" src="' . get_avatar_url( get_the_author_meta( 'ID' ) ) . ' "/>' : '' ) . get_the_author_posts_link() . '</span>';
				$date     = '<span class ="mzb-post-date">' . ( ( true === $enable_icon ) ? '<svg class="mzb-icon mzb-icon--calender" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14">
								<path d="M1.892 12.929h10.214V5.5H1.892v7.429zm2.786-8.822v-2.09a.226.226 0 00-.066-.166.226.226 0 00-.166-.065H3.98a.226.226 0 00-.167.065.226.226 0 00-.065.167v2.09c0 .067.022.122.065.166.044.044.1.065.167.065h.465a.226.226 0 00.166-.065.226.226 0 00.066-.167zm5.571 0v-2.09a.226.226 0 00-.065-.166.226.226 0 00-.167-.065h-.464a.226.226 0 00-.167.065.226.226 0 00-.065.167v2.09c0 .067.021.122.065.166.043.044.099.065.167.065h.464a.226.226 0 00.167-.065.226.226 0 00.065-.167zm2.786-.464v9.286c0 .251-.092.469-.276.652a.892.892 0 01-.653.276H1.892a.892.892 0 01-.653-.275.892.892 0 01-.276-.653V3.643c0-.252.092-.47.276-.653a.892.892 0 01.653-.276h.929v-.696c0-.32.113-.593.34-.82.228-.227.501-.34.82-.34h.465c.319 0 .592.113.82.34.227.227.34.5.34.82v.696h2.786v-.696c0-.32.114-.593.34-.82.228-.227.501-.34.82-.34h.465c.32 0 .592.113.82.34.227.227.34.5.34.82v.696h.93c.25 0 .468.092.652.276a.892.892 0 01.276.653z" />
							</svg>' : '' ) .
					'<a href="' . esc_url( get_the_permalink() ) . '"> ' . get_the_date() . '</a></span>';
				$html    .= '';
				$html    .= $image;
				$html    .= ( 'style2' === $slider_style ) ? '<div class="mzb-overlay">' : '';
				$html    .= '<div class="mzb-post-content">';
				if ( $enable_category ) {
					$html .= '<div class="mzb-post-meta">';
					$html .= $category;
					$html .= '</div>';
				}
				if ( 'top' === $meta_position ) {
					if ( $enable_author || $enable_date ) {
						$html .= '<div class="mzb-post-entry-meta">';
						$html .= $enable_author ? $author : '';
						$html .= '';
						$html .= $enable_date ? $date : '';
						$html .= '</div>';
					}
				}
				$html .= $title;
				if ( 'bottom' === $meta_position ) {
					if ( $enable_author || $enable_date ) {
						$html .= '<div class="mzb-post-entry-meta">';
						$html .= $enable_author ? $author : '';
						$html .= '';
						$html .= $enable_date ? $date : '';
						$html .= '</div>';
					}
				}
				if ( $enable_excerpt || $enable_readmore ) {
					$html .= '<div class="mzb-entry-content">';
					$html .= $enable_excerpt ? '<div class="mzb-entry-summary"><p> ' . get_the_excerpt() . '</p></div>' : '';
					$html .= $enable_readmore ? '<div class="mzb-read-more"><a href="' . esc_url( get_the_permalink() ) . '">' . $read_more_text . ' </a></div>' : '';
					$html .= '</div>';
				}
				$html .= ( 'style2' === $slider_style ) ? '</div>' : '';
				$html .= '</div>';
				$html .= '</div>';
			}

			$html .= '</div>';

			$html .= '<div class="swiper-pagination"></div>';
			if ( 'center' === $arrow_position && $enable_arrow ) {
				$html .= '<div class="swiper-button-prev"></div>';
				$html .= '<div class="swiper-button-next"></div>';
			}
			$html .= '</div>';
			if ( 'bottom' === $arrow_position && $enable_arrow ) {
				$html .= '<div class="swiper-button-prev"></div>';
				$html .= '<div class="swiper-button-next"></div>';
			}
			$html .= '</div>';
			wp_reset_postdata();
		}
		return $html;
	}
}
