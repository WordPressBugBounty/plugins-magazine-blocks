<?php
/**
 * LatestPosts.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

defined( 'ABSPATH' ) || exit;

class LatestPosts extends AbstractBlock {

	protected $block_name = 'latest-posts';

	public function render( $attributes, $content, $block ) {

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
		$enable_author          = magazine_blocks_array_get( $attributes, 'enableAuthor', '' );
		$enable_date            = magazine_blocks_array_get( $attributes, 'enableDate', '' );
		$hover_animation        = magazine_blocks_array_get( $attributes, 'hoverAnimation', '' );
		$hide_on_desktop        = magazine_blocks_array_get( $attributes, 'hideOnDesktop', '' );
		$page                   = magazine_blocks_array_get( $attributes, 'page', '' );
		$layout1_advanced_style = magazine_blocks_array_get( $attributes, 'layout1AdvancedStyle', '' );
		$layout2_advanced_style = magazine_blocks_array_get( $attributes, 'layout2AdvancedStyle', '' );
		$excluded_category      = magazine_blocks_array_get( $attributes, 'excludedCategory', '' );

		$categories = get_categories();
		$posts      = $this->get_latest_posts_by_category( $categories, $excluded_category );
		$output     = $this->render_block( $attributes, $posts );

		return $output;
	}

	/**
	 * Get Latest Posts.
	 *
	 * @param mixed $categories
	 * @return array
	 */
	protected function get_latest_posts_by_category( $categories, $excluded_category ) {
		$latest_posts    = array();
		$displayed_posts = array();

		foreach ( $categories as $category ) {
			if ( ! in_array( $category->term_id, $excluded_category ) ) {
				$latest_post = $this->get_latest_post_in_category( $category->term_id, $excluded_category );

				if ( $latest_post && ! in_array( $latest_post->ID, $displayed_posts ) ) {
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
	protected function get_latest_post_in_category( $category_id, $excluded_category ) {
		$latest_posts = get_posts(
			array(
				'category'         => $category_id,
				'numberposts'      => 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'category__not_in' => $excluded_category,
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

		// Extract attributes
		extract( $attributes );

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
									<a href="<?php echo get_permalink( $post->ID ); ?>">
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
											<?php echo wp_trim_words( get_the_excerpt( $post->ID ), $excerpt_limit, '...' ); ?>
										</div>
									<?php endif; ?>
									<?php if ( $enable_read_more ) : ?>
										<div class="mzb-read-more">
											<a href="<?php echo get_permalink( $post->ID ); ?>">
												<?php echo esc_html( $readMoreText ); ?>
											</a>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
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
		extract( $attributes );
		?>
		<div class="mzb-post-entry-meta">
			<?php if ( $enable_author ) : ?>
				<span class="mzb-post-author">
					<?php echo get_avatar( $post->post_author, 32 ); ?>
					<a href="<?php echo get_author_posts_url( $post->post_author ); ?>">
						<?php echo get_the_author_meta( 'display_name', $post->post_author ); ?>
					</a>
				</span>
			<?php endif; ?>
			<?php if ( $enable_date ) : ?>
				<span class="mzb-post-date">
					<i class="icon-calendar"></i>
					<a href="<?php echo get_permalink( $post->ID ); ?>">
						<?php echo get_the_date( '', $post->ID ); ?>
					</a>
				</span>
			<?php endif; ?>
		</div>
		<?php
	}
}
