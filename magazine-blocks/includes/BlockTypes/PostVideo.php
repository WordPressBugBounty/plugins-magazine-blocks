<?php
/**
 * Post Video block.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Button block class.
 */
class PostVideo extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'post-video';

	public function render( $attributes, $content, $block ) {

		$client_id = magazine_blocks_array_get( $attributes, 'clientId', '' );

		$category          = magazine_blocks_array_get( $attributes, 'category', '' );
		$no_of_posts       = magazine_blocks_array_get( $attributes, 'postCount', '' );
		$column            = magazine_blocks_array_get( $attributes, 'column', '' );
		$presets           = magazine_blocks_array_get( $attributes, 'presets', 'style-1' );
		$enable_title      = magazine_blocks_array_get( $attributes, 'enableTitle', false );
		$post_title_markup = magazine_blocks_array_get( $attributes, 'postTitleMarkup', 'h3' );
		$enable_date       = magazine_blocks_array_get( $attributes, 'enableDate', false );
		$enable_excerpt    = magazine_blocks_array_get( $attributes, 'enableExcerpt', false );
		$excerpt_limit     = magazine_blocks_array_get( $attributes, 'excerptLimit', 20 );
		$enable_readmore   = magazine_blocks_array_get( $attributes, 'enableReadMore', false );
		$read_more_text    = magazine_blocks_array_get( $attributes, 'readMoreText', 'Read More' );
		$enable_category   = magazine_blocks_array_get( $attributes, 'enableCategory', false );
		$enable_author     = magazine_blocks_array_get( $attributes, 'enableAuthor', false );

		$args = array(
			'posts_per_page'      => $no_of_posts,
			'status'              => 'publish',
			'cat'                 => $category,
			'ignore_sticky_posts' => 1,
			'tax_query'           => array(
				array(
					'taxonomy' => 'post_format',
					'field'    => 'slug',
					'terms'    => array( 'post-format-video' ),
				),
			),
		);

		$query = new WP_Query( $args );

			# The Loop.
			$html = '';

			$index = 1;
		if ( $query->have_posts() ) {
			$html .= '<div class="mzb-post-video mzb-post-video-' . $client_id . ' ' . ( $presets ? 'mzb-' . $presets : '' ) . '">';
			$html .= '<div class="mzb-posts mzb-post-col--' . $column . '">';

			while ( $query->have_posts() ) {
				$query->the_post();

				$video_url = get_post_meta( get_the_ID(), 'video_url', true );

				$src      = get_the_post_thumbnail_url( get_the_ID() ) ? get_the_post_thumbnail_url( get_the_ID() ) : MAGAZINE_BLOCKS_ASSETS_DIR_URL . '/images/default_thumbnail.png';
				$image    = $src ? '<img class="mzb-featured-image" src="' . esc_url( $src ) . '" alt="' . get_the_title() . '"/>' : '';
				$author   = '<span class="magazine-post-author" >' . get_the_author_posts_link() . '</span>';
				$title    = '<' . $post_title_markup . ' class="mzb-post-title"><a href="' . esc_url( get_the_permalink() ) . '">' . get_the_title() . '</a></' . $post_title_markup . '>';
				$excerpt  = wp_trim_words( get_the_excerpt(), $excerpt_limit, '...' );
				$category = ( true === $enable_category ) ? '<span class="mzb-post-categories">' . get_the_category_list( ' ' ) . '</span>' : '';
				$author   = ( true === $enable_author ) ? '<span class="mzb-post-author" ><img class="post-author-image" src="' . get_avatar_url( get_the_author_meta( 'ID' ) ) . ' "/>' . get_the_author_posts_link() . '</span>' : '';
				$date     = ( true === $enable_date ) ? '<span class ="mzb-post-date"><svg class="mzb-icon mzb-icon--calender" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14">
								<path d="M1.892 12.929h10.214V5.5H1.892v7.429zm2.786-8.822v-2.09a.226.226 0 00-.066-.166.226.226 0 00-.166-.065H3.98a.226.226 0 00-.167.065.226.226 0 00-.065.167v2.09c0 .067.022.122.065.166.044.044.1.065.167.065h.465a.226.226 0 00.166-.065.226.226 0 00.066-.167zm5.571 0v-2.09a.226.226 0 00-.065-.166.226.226 0 00-.167-.065h-.464a.226.226 0 00-.167.065.226.226 0 00-.065.167v2.09c0 .067.021.122.065.166.043.044.099.065.167.065h.464a.226.226 0 00.167-.065.226.226 0 00.065-.167zm2.786-.464v9.286c0 .251-.092.469-.276.652a.892.892 0 01-.653.276H1.892a.892.892 0 01-.653-.275.892.892 0 01-.276-.653V3.643c0-.252.092-.47.276-.653a.892.892 0 01.653-.276h.929v-.696c0-.32.113-.593.34-.82.228-.227.501-.34.82-.34h.465c.319 0 .592.113.82.34.227.227.34.5.34.82v.696h2.786v-.696c0-.32.114-.593.34-.82.228-.227.501-.34.82-.34h.465c.32 0 .592.113.82.34.227.227.34.5.34.82v.696h.93c.25 0 .468.092.652.276a.892.892 0 01.276.653z" />
							</svg>
							<a href="' . esc_url( get_the_permalink() ) . '"> ' . get_the_date() . '</a></span>' : '';

				$html .= '<div class="mzb-post' . ( $column && 1 === $index ? ' mzb-first-video--highlight' : '' ) . ' ">';
				$html .= '<a class="mzb-video" href="' . esc_url( get_the_permalink() ) . '">';
				$html .= '<div class="mzb-image-overlay">';

				if ( $video_url ) {
					$html .= '<video class="mzb-video-player" src="' . esc_url( $video_url ) . '" controls poster="' . esc_url( $src ) . '"></video>';
				} else {
					$html .= $image;
				}

				$html .= '</div>';
				$html .= '<div class="mzb-custom-embed-play" role="button">
								<svg viewBox="0 0 18 21" xmlns="http://www.w3.org/2000/svg"><path d="M17.6602 10.9341L0.339646 20.9341L0.339647 0.934081L17.6602 10.9341Z" /></svg>
							</div>';
				$html .= '</a>';

				if ( $enable_category ) {
					$html .= '<div class="mzb-post-meta">';
					$html .= $category;
					$html .= '</div>';
				}
				if ( $enable_author || $enable_date ) {
					$html .= '<div class="mzb-post-entry-meta">';
					$html .= $enable_author ? $author : '';
					$html .= '';
					$html .= $enable_date ? $date : '';
					$html .= '</div>';
				}
				if ( $enable_title ) {
					$html .= $title;
				}
				if ( $enable_excerpt ) {
					$html .= '<div class="mzb-entry-content">';
					$html .= $enable_excerpt ? '<div class="mzb-entry-summary"><p> ' . $excerpt . '</p></div>' : '';
					$html .= $enable_readmore ? '<div class="mzb-read-more"><a href="' . esc_url( get_the_permalink() ) . '">' . $read_more_text . ' </a></div>' : '';
					$html .= '</div>';
				}
				$html .= '</div>';
				++$index;
			}

			$html .= '</div>';
			$html .= '</div>';
			wp_reset_postdata();
		}
			return $html;
	}
}
