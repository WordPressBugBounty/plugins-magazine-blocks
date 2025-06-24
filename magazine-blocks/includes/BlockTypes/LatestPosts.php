<?php

/**
 * LatestPosts.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

use WP_Query;

use function MagazineBlocks\mzb_numbered_pagination;

defined( 'ABSPATH' ) || exit;

class LatestPosts extends AbstractBlock {


	protected $block_name = 'latest-posts';

	public function render( $attributes, $content, $block ) {

		$excluded_category = magazine_blocks_array_get( $attributes, 'excludedCategory', '' );
		$offset            = magazine_blocks_array_get( $attributes, 'offset', 0 );

		$categories = get_categories();
		$posts      = $this->get_latest_posts_by_category( $categories, $excluded_category, $offset );
		$output     = $this->render_block( $attributes, $posts );

		return $output;
	}

	/**
	 * Get Latest Posts.
	 *
	 * @param mixed $categories
	 * @return array
	 */
	protected function get_latest_posts_by_category( $categories, $excluded_category, $offset = 0 ) {
		if ( ! is_array( $excluded_category ) ) {
			$excluded_category = empty( $excluded_category ) ? array() : array( $excluded_category );
		}

		$latest_posts    = array();
		$displayed_posts = array();

		foreach ( $categories as $category ) {
			if ( ! in_array( $category->term_id, $excluded_category, true ) ) {
				$latest_post = $this->get_latest_post_in_category( $category->term_id, $excluded_category, $offset );

				if ( $latest_post && ! in_array( $latest_post->ID, $displayed_posts, true ) ) {
					$displayed_posts[] = $latest_post->ID;
					$latest_posts[]    = $latest_post;
				}
			}
		}

		return $latest_posts;
	}

	/**
	 * Latest Posts in Category.
	 *
	 * @param mixed $category_id
	 * @return mixed
	 */
	protected function get_latest_post_in_category( $category_id, $excluded_category, $offset = 0 ) {
		$post_type = magazine_blocks_array_get( $this->attributes, 'postType', 'post' );

		$latest_posts = get_posts(
			array(
				'post_type'        => $post_type,
				'category'         => $category_id,
				'numberposts'      => 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'category__not_in' => $excluded_category,
				'offset'           => $offset,
			)
		);

		return ! empty( $latest_posts ) ? $latest_posts[0] : null;
	}

	/**
	 * Render Block.
	 *
	 * @param array $attributes
	 * @param array $posts
	 * @return string
	 */
	protected function render_block( $attributes, $posts ) {

		ob_start();

		$enable_heading         = magazine_blocks_array_get( $attributes, 'enableHeading', true );
		$label                  = magazine_blocks_array_get( $attributes, 'label', '' );
		$layout                 = magazine_blocks_array_get( $attributes, 'layout', 'layout-1' );
		$column                 = magazine_blocks_array_get( $attributes, 'column', 2 );
		$excerpt_limit          = magazine_blocks_array_get( $attributes, 'excerptLimit', '' );
		$enable_excerpt         = magazine_blocks_array_get( $attributes, 'enableExcerpt', '' );
		$enable_read_more       = magazine_blocks_array_get( $attributes, 'enableReadMore', '' );
		$read_more_text         = magazine_blocks_array_get( $attributes, 'readMoreText', '' );
		$enable_pagination      = magazine_blocks_array_get( $attributes, 'enablePagination', '' );
		$meta_position          = magazine_blocks_array_get( $attributes, 'metaPosition', '' );
		$enable_post_title      = magazine_blocks_array_get( $attributes, 'enablePostTitle', '' );
		$hover_animation        = magazine_blocks_array_get( $attributes, 'hoverAnimation', '' );
		$hide_on_desktop        = magazine_blocks_array_get( $attributes, 'hideOnDesktop', '' );
		$page                   = magazine_blocks_array_get( $attributes, 'page', '' );
		$layout1_advanced_style = magazine_blocks_array_get( $attributes, 'layout1AdvancedStyle', '' );
		$layout2_advanced_style = magazine_blocks_array_get( $attributes, 'layout2AdvancedStyle', '' );
		$client_id              = magazine_blocks_array_get( $attributes, 'clientId', '' );

		// Extract attributes
		// extract( $attributes );

		// Pagination.
		$paged         = isset( $_GET[ 'block_id_' . $client_id ] ) ? max( 1, intval( $_GET[ 'block_id_' . $client_id ] ) ) : 1;
		$args['paged'] = $paged;

		$type = get_query_var( 'mzb_template_type' );

		if ( in_array( $type, [ 'archive', 'search', 'single', 'front' ], true ) ) {
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

		$query = new WP_Query( $args );

		// Generate unique class names
		$client_id   = uniqid( 'mzb-latest-posts-' );
		$block_class = "mzb-latest-posts $client_id";
		if ( $hide_on_desktop ) {
			$block_class .= ' magazine-blocks-hide-on-desktop';
		}
		$posts_class = "mzb-posts mzb-$layout mzb-post-col--" . ( $column ? $column : 4 );
		if ( 'layout-1' === $layout ) {
			$posts_class .= " mzb-$layout1_advanced_style";
		} elseif ( 'layout-2' === $layout ) {
			$posts_class .= " mzb-$layout2_advanced_style";
		}

		?>
		<div class="<?php echo esc_attr( $block_class ); ?>">
			<?php if ( $enable_heading ) : ?>
				<div class="mzb-post-heading">
					<h2><?php echo esc_html( $label ); ?></h2>
				</div>
			<?php endif; ?>

			<div class="<?php echo esc_attr( $posts_class ); ?>">
				<?php foreach ( $posts as $post ) : ?>
					<div class="mzb-post">
						<?php if ( has_post_thumbnail( $post->ID ) ) : ?>
							<div class="mzb-featured-image <?php echo esc_attr( $hover_animation ); ?>">
								<?php echo get_the_post_thumbnail( $post->ID, 'full' ); ?>
							</div>
						<?php endif; ?>

						<div class="mzb-post-content">
							<?php if ( 'top' === $meta_position ) : ?>
								<?php $this->render_meta( $post, $attributes ); ?>
							<?php endif; ?>

							<?php if ( $enable_post_title ) : ?>
								<h3 class="mzb-post-title">
									<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
										<?php echo esc_html( get_the_title( $post->ID ) ); ?>
									</a>
								</h3>
							<?php endif; ?>

							<?php if ( 'bottom' === $meta_position ) : ?>
								<?php $this->render_meta( $post, $attributes ); ?>
							<?php endif; ?>

							<?php if ( $enable_excerpt || $enable_read_more ) : ?>
								<div class="mzb-entry-content">
									<?php if ( $enable_excerpt ) : ?>
										<div class="mzb-entry-summary">
											<?php echo esc_html( wp_trim_words( get_the_excerpt( $post->ID ), $excerpt_limit, '...' ) ); ?>
										</div>
									<?php endif; ?>
									<?php if ( $enable_read_more ) : ?>
										<div class="mzb-read-more">
											<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
												<?php echo esc_html( $read_more_text ); ?>
											</a>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if ( $enable_pagination ) : ?>
					<div class="mzb-pagination-numbers">
						<h2><?php echo esc_html( mzb_numbered_pagination( $query->max_num_pages, $paged, $client_id ) ); ?></h2>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render Meta.
	 *
	 * @param array $post
	 * @param array $attributes
	 * @return void
	 */
	protected function render_meta( $post, $attributes ) {
		$enable_author = magazine_blocks_array_get( $attributes, 'enableAuthor', '' );
		$enable_date   = magazine_blocks_array_get( $attributes, 'enableDate', '' );
		?>
		<div class="mzb-post-entry-meta">
			<?php if ( $enable_author ) : ?>
				<span class="mzb-post-author">
					<?php echo get_avatar( $post->post_author, 32 ); ?>
					<a href="<?php echo esc_url( get_author_posts_url( $post->post_author ) ); ?>">
						<?php echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) ); ?>
					</a>
				</span>
			<?php endif; ?>
			<?php if ( $enable_date ) : ?>
				<span class="mzb-post-date">
					<svg class="mzb-icon mzb-icon--calender" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 14">
						<path d="M1.892 12.929h10.214V5.5H1.892v7.429zm2.786-8.822v-2.09a.226.226 0 00-.066-.166.226.226 0 00-.166-.065H3.98a.226.226 0 00-.167.065.226.226 0 00-.065.167v2.09c0 .067.022.122.065.166.044.044.1.065.167.065h.465a.226.226 0 00.166-.065.226.226 0 00.066-.167zm5.571 0v-2.09a.226.226 0 00-.065-.166.226.226 0 00-.167-.065h-.464a.226.226 0 00-.167.065.226.226 0 00-.065.167v2.09c0 .067.021.122.065.166.043.044.099.065.167.065h.464a.226.226 0 00.167-.065.226.226 0 00.065-.167zm2.786-.464v9.286c0 .251-.092.469-.276.652a.892.892 0 01-.653.276H1.892a.892.892 0 01-.653-.275.892.892 0 01-.276-.653V3.643c0-.252.092-.47.276-.653a.892.892 0 01.653-.276h.929v-.696c0-.32.113-.593.34-.82.228-.227.501-.34.82-.34h.465c.319 0 .592.113.82.34.227.227.34.5.34.82v.696h2.786v-.696c0-.32.114-.593.34-.82.228-.227.501-.34.82-.34h.465c.32 0 .592.113.82.34.227.227.34.5.34.82v.696h.93c.25 0 .468.092.652.276a.892.892 0 01.276.653z" />
					</svg>
					<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
						<?php echo get_the_date( '', $post->ID ); ?>
					</a>
				</span>
			<?php endif; ?>
		</div>
		<?php
	}
}
