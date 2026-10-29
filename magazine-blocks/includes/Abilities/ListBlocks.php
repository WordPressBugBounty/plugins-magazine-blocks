<?php
/**
 * List blocks ability.
 *
 * @since x.x.x
 * @package Magazine Blocks
 */

namespace MagazineBlocks\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * Lists the Magazine Blocks blocks registered on this site.
 *
 * @since x.x.x
 */
class ListBlocks extends AbstractAbility {

	/**
	 * Longest accepted search term.
	 *
	 * @var int
	 */
	const SEARCH_MAX_LENGTH = 100;

	/**
	 * Gets the ability slug.
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'list-blocks';
	}

	/**
	 * Gets the ability label.
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'List Magazine Blocks', 'magazine-blocks' );
	}

	/**
	 * Gets the ability description.
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Lists every Magazine Blocks block registered on this site (including blocks added by add-ons) with its name, title, description and editor category. Optionally filter by category or search text.', 'magazine-blocks' );
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
				'category' => array(
					'type'        => 'string',
					'description' => __( 'Only return blocks in this editor category slug, e.g. magazine-blocks.', 'magazine-blocks' ),
				),
				'search'   => array(
					'type'        => 'string',
					'maxLength'   => self::SEARCH_MAX_LENGTH,
					'description' => __( 'Only return blocks whose name, title or description contains this text.', 'magazine-blocks' ),
				),
			),
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
				'total'  => array( 'type' => 'integer' ),
				'blocks' => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'name'        => array( 'type' => 'string' ),
							'title'       => array( 'type' => 'string' ),
							'description' => array( 'type' => 'string' ),
							'category'    => array( 'type' => 'string' ),
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
		$input    = $this->normalize_input( $input );
		$category = isset( $input['category'] ) ? $input['category'] : '';
		$search   = isset( $input['search'] ) ? $input['search'] : '';
		$blocks   = array();

		foreach ( \WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $block_type ) {
			if ( 0 !== strpos( $name, self::NAME_PREFIX ) ) {
				continue;
			}

			$block = array(
				'name'        => $name,
				'title'       => (string) $block_type->title,
				'description' => (string) $block_type->description,
				'category'    => (string) $block_type->category,
			);

			if ( '' !== $category && $block['category'] !== $category ) {
				continue;
			}

			if ( '' !== $search && false === stripos( $name . ' ' . $block['title'] . ' ' . $block['description'], $search ) ) {
				continue;
			}

			$blocks[] = $block;
		}

		usort(
			$blocks,
			function ( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			}
		);

		return array(
			'total'  => count( $blocks ),
			'blocks' => $blocks,
		);
	}
}
