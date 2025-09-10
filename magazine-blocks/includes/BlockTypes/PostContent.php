<?php

/**
 * Post Content.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

use MagazineBlocks\Abstracts\Block;

defined( 'ABSPATH' ) || exit;

/**
 * Heading block.
 */
class PostContent extends Block {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'post-content';

	/**
	 * Render the block.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block content.
	 * @param object $block      Block object.
	 * @return string Rendered HTML output.
	 */
	public function render( $attributes = array(), $content = '', $block = null ) {
		// Get client ID for unique class.
		$client_id = magazine_blocks_array_get( $attributes, 'clientId', '' );

		// Start output div.
		$html = '<div class="mzb-post-content mzb-post-content-' . esc_attr( $client_id ) . '">';

		if ( is_singular( 'post' ) ) {
			// Output the post content.
			ob_start();
			the_content();
			$html .= ob_get_clean();
		} else {
			// Optionally, show a message or nothing.
			$html .= esc_html__( 'No post content available.', 'magazine-blocks' );
		}

		$html .= '</div>';

		return $html;
	}
}
