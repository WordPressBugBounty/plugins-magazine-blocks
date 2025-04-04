<?php
/**
 * Date & Weather block.
 *
 * @package Magazine Blocks
 */

namespace MagazineBlocks\BlockTypes;

use MagazineBlocks\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Button block class.
 */
class DateWeather extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'date-weather';

	public function render( $attributes, $content, $block ) {
		$client_id = magazine_blocks_array_get( $attributes, 'clientId', '' );
		$icon      = magazine_blocks_array_get( $attributes, 'icon', '' );
		$get_icon  = magazine_blocks_get_icon( $icon, false );

		# The Loop.
		$html = '';

		$html .= '<div class="mzb-date-weather mzb-date-weather-' . $client_id . '">';
		$html .= '<span class="mzb-weather-icon">' . $get_icon . '</span>';
		$html .= '<span class="mzb-temperature">';
		$html .= Helper::show_temp();
		$html .= '° ';
		$html .= '</span>';
		$html .= '<div class="mzb-weather-date">';
		$html .= Helper::show_weather();
		$html .= ', ';
		$html .= gmdate( 'F j, Y' );
		$html .= ' in ';
		$html .= Helper::show_location();
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}
}
