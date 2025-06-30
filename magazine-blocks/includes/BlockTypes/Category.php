<?php

/**
 * Category.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Category block.
 */
class Category extends AbstractBlock {


	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'category';

	public function render( $attributes, $content, $block ) {

		$client_id = magazine_blocks_array_get( $attributes, 'clientId', '' );

		$current_post_id = get_the_ID();

		$selected_category = isset( $attributes['category'] ) ? (int) $attributes['category'] : 0;

		$assigned_categories = [];
		if ( $current_post_id ) {
			$assigned_categories = wp_get_post_categories( $current_post_id, [ 'fields' => 'all' ] );
		}

		if ( ! $selected_category && ! empty( $assigned_categories ) ) {
			$selected_category = $assigned_categories[0]->term_id;
		}

		$category_name = '';
		if ( $selected_category ) {
			$term = get_term( $selected_category, 'category' );
			if ( $term && ! is_wp_error( $term ) ) {
				$category_name = $term->name;
			}
		}

		ob_start();
		?>
	<div class="mzb-category mzb-category-<?php echo esc_attr( $client_id ); ?>">
		<div className="mzb-category-list">
		<?php if ( $category_name ) : ?>
			<span class="mzb-category-item">
				<?php
				/* translators: %s: Category name. */
				echo esc_html( sprintf( __( '%s', 'magazine-blocks' ), $category_name ) );
				?>
			</span>
		<?php endif; ?>
		</div>
	</div>
		<?php

		return ob_get_clean();
	}
}
