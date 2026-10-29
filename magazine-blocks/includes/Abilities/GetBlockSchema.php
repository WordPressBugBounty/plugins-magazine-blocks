<?php
/**
 * Get block schema ability.
 *
 * @since x.x.x
 * @package Magazine Blocks
 */

namespace MagazineBlocks\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * Returns the attribute schema of one Magazine Blocks block.
 *
 * Styling attributes carry CSS selector templates that make up most of a block's definition and are of no use
 * to an agent, so they are left out unless asked for, and their selectors are never returned.
 *
 * @since x.x.x
 */
class GetBlockSchema extends AbstractAbility {

	/**
	 * Attributes that are generated or editor-internal rather than authored.
	 *
	 * @var string[]
	 */
	const INTERNAL_ATTRIBUTES = array( 'clientId', 'blockHTML', 'blockCSS' );

	/**
	 * Matches attributes that select a layout's advanced style variant, e.g. headingLayout1AdvancedStyle.
	 *
	 * @var string
	 */
	const STYLE_VARIANT_PATTERN = '/AdvancedStyle$/';

	/**
	 * Gets the ability slug.
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'get-block-schema';
	}

	/**
	 * Gets the ability label.
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Get Magazine Blocks block schema', 'magazine-blocks' );
	}

	/**
	 * Gets the ability description.
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Returns the attributes (type and default) of one Magazine Blocks block, plus the parent blocks it must be placed in. By default only content and structure attributes are returned; set include_style to also get styling attributes, including the responsive hide and reverse toggles.', 'magazine-blocks' );
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
			'properties'           => array(
				'name'          => array(
					'type'        => 'string',
					'pattern'     => '^magazine-blocks/[a-z0-9-]+$',
					'description' => __( 'Block name as returned by list-blocks, e.g. magazine-blocks/heading.', 'magazine-blocks' ),
				),
				'include_style' => array(
					'type'        => 'boolean',
					'default'     => false,
					'description' => __( 'Also return styling attributes (colors, spacing, typography, ...).', 'magazine-blocks' ),
				),
			),
			'required'             => array( 'name' ),
			'additionalProperties' => false,
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
				'name'        => array( 'type' => 'string' ),
				'title'       => array( 'type' => 'string' ),
				'description' => array( 'type' => 'string' ),
				'category'    => array( 'type' => 'string' ),
				'parent'      => array(
					'type'  => 'array',
					'items' => array( 'type' => 'string' ),
				),
				'attributes'  => array(
					'type'                 => 'object',
					'additionalProperties' => array(
						'type'                 => 'object',
						'properties'           => array(
							'type'    => array( 'type' => array( 'string', 'array' ) ),
							// A default is whatever the attribute holds; REST schema validation requires a type, so list every JSON type.
							'default' => array( 'type' => array( 'string', 'number', 'integer', 'boolean', 'array', 'object', 'null' ) ),
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
		$input       = $this->normalize_input( $input );
		$name        = isset( $input['name'] ) ? $input['name'] : '';
		$block_type  = 0 === strpos( $name, self::NAME_PREFIX ) ? \WP_Block_Type_Registry::get_instance()->get_registered( $name ) : null;
		$with_styles = ! empty( $input['include_style'] );

		if ( ! $block_type ) {
			return new \WP_Error(
				'mzb_block_not_found',
				__( 'No Magazine Blocks block is registered with that name.', 'magazine-blocks' ),
				array( 'status' => 404 )
			);
		}

		return array(
			'name'        => $block_type->name,
			'title'       => (string) $block_type->title,
			'description' => (string) $block_type->description,
			'category'    => (string) $block_type->category,
			'parent'      => array_values( (array) $block_type->parent ),
			'attributes'  => (object) $this->prepare_attributes( (array) $block_type->attributes, $with_styles ),
		);
	}

	/**
	 * Reduces attribute definitions to type and default.
	 *
	 * @param array $attributes  Registered attribute definitions.
	 * @param bool  $with_styles Whether to keep styling attributes.
	 * @return array
	 */
	private function prepare_attributes( array $attributes, $with_styles ) {
		$prepared = array();

		foreach ( $attributes as $key => $definition ) {
			if ( in_array( $key, self::INTERNAL_ATTRIBUTES, true ) ) {
				continue;
			}

			if ( ! $with_styles && $this->is_style_attribute( $key, $definition ) ) {
				continue;
			}

			$prepared[ $key ] = array_intersect_key( $definition, array_flip( array( 'type', 'default' ) ) );
		}

		return $prepared;
	}

	/**
	 * Whether an attribute only affects appearance: it emits CSS, or it picks a layout's advanced style variant.
	 *
	 * @param string $key        Attribute name.
	 * @param array  $definition Registered attribute definition.
	 * @return bool
	 */
	private function is_style_attribute( $key, $definition ) {
		return isset( $definition['style'] ) || (bool) preg_match( self::STYLE_VARIANT_PATTERN, $key );
	}
}
