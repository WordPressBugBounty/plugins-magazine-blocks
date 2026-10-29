<?php
/**
 * Get global styles ability.
 *
 * @since x.x.x
 * @package Magazine Blocks
 */

namespace MagazineBlocks\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the site's Magazine Blocks design tokens (colors and typography presets).
 *
 * @since x.x.x
 */
class GetGlobalStyles extends AbstractAbility {

	/**
	 * Gets the ability slug.
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'get-global-styles';
	}

	/**
	 * Gets the ability label.
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Get Magazine Blocks global styles', 'magazine-blocks' );
	}

	/**
	 * Gets the ability description.
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Returns the global color and typography presets and the per-category colors configured for Magazine Blocks, so new content can match the site design.', 'magazine-blocks' );
	}

	/**
	 * Gets the ability annotations.
	 *
	 * @return array
	 */
	protected function get_annotations() {
		return $this->read_only_annotations();
	}

	/**
	 * Gets the input schema.
	 *
	 * @return array
	 */
	protected function get_input_schema() {
		return array(
			'type'                 => 'object',
			'additionalProperties' => false,
			'default'              => array(),
		);
	}

	/**
	 * Gets the output schema.
	 *
	 * @return array
	 */
	protected function get_output_schema() {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'colors'           => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'id'    => array( 'type' => 'string' ),
							'name'  => array( 'type' => 'string' ),
							'value' => array( 'type' => 'string' ),
						),
						'additionalProperties' => false,
					),
				),
				'typographies'     => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'id'    => array( 'type' => 'string' ),
							'name'  => array( 'type' => 'string' ),
							'value' => array( 'type' => 'object' ),
						),
						'additionalProperties' => false,
					),
				),
				'categories_color' => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'id'    => array( 'type' => 'integer' ),
							'value' => array( 'type' => 'string' ),
						),
						'additionalProperties' => false,
					),
				),
			),
			'additionalProperties' => false,
		);
	}

	/**
	 * Runs the ability.
	 *
	 * @param mixed $input Validated input.
	 * @return array|\WP_Error
	 */
	public function execute( $input = null ) {
		$styles = magazine_blocks_get_global_styles();
		$styles = is_array( $styles ) ? $styles : array();

		return array(
			'colors'           => $this->map_list(
				$styles,
				'colors',
				function ( $item ) {
					return array(
						'id'    => $this->text( $item, 'id' ),
						'name'  => $this->text( $item, 'name' ),
						'value' => $this->text( $item, 'value' ),
					);
				}
			),
			'typographies'     => $this->map_list(
				$styles,
				'typographies',
				function ( $item ) {
					$value = isset( $item['value'] ) ? $item['value'] : array();

					return array(
						'id'    => $this->text( $item, 'id' ),
						'name'  => $this->text( $item, 'name' ),
						'value' => (object) ( is_array( $value ) ? $value : array() ),
					);
				}
			),
			'categories_color' => $this->map_list(
				$styles,
				'categories_color',
				function ( $item ) {
					return array(
						'id'    => (int) $this->text( $item, 'id' ),
						'value' => $this->text( $item, 'value' ),
					);
				}
			),
		);
	}

	/**
	 * Maps one stored list through a normalizer, so output only holds the fields the schema declares.
	 *
	 * Stored styles are user data; a malformed list or entry becomes an empty list or an empty field instead of an error.
	 *
	 * @param array    $styles     Stored global styles.
	 * @param string   $key        List key.
	 * @param callable $normalizer Receives one stored entry and returns the normalized entry.
	 * @return array
	 */
	private function map_list( array $styles, $key, callable $normalizer ) {
		if ( ! isset( $styles[ $key ] ) || ! is_array( $styles[ $key ] ) ) {
			return array();
		}

		return array_values( array_map( $normalizer, array_filter( $styles[ $key ], 'is_array' ) ) );
	}

	/**
	 * Reads a stored field as text, or an empty string when it is missing or not a scalar.
	 *
	 * @param array  $item  Stored entry.
	 * @param string $field Field name.
	 * @return string
	 */
	private function text( array $item, $field ) {
		return isset( $item[ $field ] ) && is_scalar( $item[ $field ] ) ? (string) $item[ $field ] : '';
	}
}
