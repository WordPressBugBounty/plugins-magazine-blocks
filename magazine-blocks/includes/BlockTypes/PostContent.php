<?php

/**
 * Post Content.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Heading block.
 */
class PostContent extends AbstractBlock {



	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'post-content';

	public function render( $attributes, $content, $block ) {
		// Get client ID for unique class
		$client_id = magazine_blocks_array_get( $attributes, 'clientId', '' );

		// Start output div
		$html = '<div class="mzb-post-content mzb-post-content-' . esc_attr( $client_id ) . '">';

		if ( is_singular( 'post' ) ) {
			// Output the post content
			ob_start();
			the_content();
			$html .= ob_get_clean();
		} else {
			// Optionally, show a message or nothing
			$html .= esc_html__( 'No post content available.', 'magazine-blocks' );
		}

		$html .= '</div>';

		return $html;
	}
}
