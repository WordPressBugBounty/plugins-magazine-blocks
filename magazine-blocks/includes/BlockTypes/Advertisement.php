<?php
/**
 * Advertisement block.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * SocialIcon block.
 */
class Advertisement extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'advertisement';

	/**
	 * Render callback.
	 *
	 * @param array     $attributes Block attributes.
	 * @param string    $content Block content.
	 * @param \WP_Block $block Block object.
	 *
	 * @return string
	 */
	public function render( $attributes, $content, $block ) {
		$start_date = magazine_blocks_array_get( $attributes, 'startDate', '' );
		$end_date   = magazine_blocks_array_get( $attributes, 'endDate', '' );
		$today      = gmdate( 'Y-m-d' );
		if ( $start_date <= $today && $end_date >= $today ) {
			return $content;
		}
		return '';
	}
}
