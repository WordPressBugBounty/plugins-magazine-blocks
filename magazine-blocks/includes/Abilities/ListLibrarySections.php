<?php
/**
 * List library sections ability.
 *
 * @since x.x.x
 * @package Magazine Blocks
 */

namespace MagazineBlocks\Abilities;

use MagazineBlocks\RestApi\Controllers\LibraryDataController;

defined( 'ABSPATH' ) || exit;

/**
 * Lists the ready-made sections from the Magazine Blocks design library.
 *
 * Reuses the library REST controller so the remote fetch and its week-long cache are shared with the editor.
 *
 * @since x.x.x
 */
class ListLibrarySections extends AbstractAbility {

	/**
	 * Default sections per page.
	 *
	 * @var int
	 */
	const DEFAULT_PER_PAGE = 20;

	/**
	 * Maximum sections per page.
	 *
	 * @var int
	 */
	const MAX_PER_PAGE = 50;

	/**
	 * Category slug the library controller assigns to items without one.
	 *
	 * @var string
	 */
	const UNCATEGORIZED = 'uncategorized';

	/**
	 * Gets the ability slug.
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'list-library-sections';
	}

	/**
	 * Gets the ability label.
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'List Magazine Blocks library sections', 'magazine-blocks' );
	}

	/**
	 * Gets the ability description.
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Lists the ready-made page sections (sliders, banners, post grids, post lists, ads, ...) from the Magazine Blocks design library, with the blocks each one uses. Filter by category slug or by an included block such as slider.', 'magazine-blocks' );
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
					'description' => __( 'Only return sections in this library category slug, e.g. slider.', 'magazine-blocks' ),
				),
				'block'    => array(
					'type'        => 'string',
					'description' => __( 'Only return sections that include this block slug, e.g. grid-module.', 'magazine-blocks' ),
				),
				'page'     => array(
					'type'    => 'integer',
					'minimum' => 1,
					'default' => 1,
				),
				'per_page' => array(
					'type'    => 'integer',
					'minimum' => 1,
					'maximum' => self::MAX_PER_PAGE,
					'default' => self::DEFAULT_PER_PAGE,
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
				'total'       => array( 'type' => 'integer' ),
				'total_pages' => array( 'type' => 'integer' ),
				'page'        => array( 'type' => 'integer' ),
				'per_page'    => array( 'type' => 'integer' ),
				'sections'    => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'id'              => array( 'type' => 'integer' ),
							'title'           => array( 'type' => 'string' ),
							'slug'            => array( 'type' => 'string' ),
							'categories'      => array(
								'type'  => 'array',
								'items' => array( 'type' => 'string' ),
							),
							'included_blocks' => array(
								'type'  => 'array',
								'items' => array( 'type' => 'string' ),
							),
							'thumbnail'       => array( 'type' => 'string' ),
							'preview_url'     => array( 'type' => 'string' ),
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
		$block    = isset( $input['block'] ) ? $input['block'] : '';
		$page     = $this->get_page( $input );
		$per_page = $this->get_per_page( $input, self::DEFAULT_PER_PAGE, self::MAX_PER_PAGE );

		$response = ( new LibraryDataController() )->get_items( new \WP_REST_Request( 'GET' ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data     = $response->get_data();
		$sections = array();

		foreach ( $data['categorized_sections'] as $group ) {
			foreach ( $group['items'] as $item ) {
				$section = $this->prepare_section( $item );

				if ( $section['id'] < 1 || isset( $sections[ $section['id'] ] ) ) {
					continue;
				}
				if ( '' !== $category && ! in_array( $category, $section['categories'], true ) ) {
					continue;
				}
				if ( '' !== $block && ! in_array( $block, $section['included_blocks'], true ) ) {
					continue;
				}

				$sections[ $section['id'] ] = $section;
			}
		}

		$total = count( $sections );

		return array(
			'total'       => $total,
			'total_pages' => (int) ceil( $total / $per_page ),
			'page'        => $page,
			'per_page'    => $per_page,
			'sections'    => array_slice( array_values( $sections ), ( $page - 1 ) * $per_page, $per_page ),
		);
	}

	/**
	 * Reduces a remote library item to the fields exposed to agents, sanitizing the untrusted values.
	 *
	 * @param array $item Library item.
	 * @return array
	 */
	private function prepare_section( array $item ) {
		$categories = array();
		foreach ( isset( $item['category'] ) ? (array) $item['category'] : array() as $category ) {
			if ( ! empty( $category['slug'] ) ) {
				$categories[] = sanitize_key( $category['slug'] );
			}
		}

		// Mirrors the library controller, which files category-less items under this slug.
		$categories = $categories ? $categories : array( self::UNCATEGORIZED );

		$blocks = array();
		foreach ( isset( $item['included_blocks'] ) ? (array) $item['included_blocks'] : array() as $block ) {
			if ( ! empty( $block['value'] ) ) {
				$blocks[] = sanitize_key( $block['value'] );
			}
		}

		return array(
			'id'              => isset( $item['ID'] ) ? (int) $item['ID'] : 0,
			'title'           => isset( $item['post_title'] ) ? sanitize_text_field( $item['post_title'] ) : '',
			'slug'            => isset( $item['post_name'] ) ? sanitize_title( $item['post_name'] ) : '',
			'categories'      => $categories,
			'included_blocks' => $blocks,
			'thumbnail'       => ! empty( $item['post_thumbnail'] ) ? esc_url_raw( $item['post_thumbnail'] ) : '',
			'preview_url'     => ! empty( $item['permalink'] ) ? esc_url_raw( $item['permalink'] ) : '',
		);
	}
}
