<?php
/**
 * List post terms ability.
 *
 * @since x.x.x
 * @package Magazine Blocks
 */

namespace MagazineBlocks\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * Lists categories, tags or other REST-visible terms with their IDs.
 *
 * Post blocks filter by term ID, and WordPress core registers no ability for looking those IDs up.
 *
 * @since x.x.x
 */
class ListPostTerms extends AbstractAbility {

	/**
	 * Default terms per page.
	 *
	 * @var int
	 */
	const DEFAULT_PER_PAGE = 50;

	/**
	 * Maximum terms per page.
	 *
	 * @var int
	 */
	const MAX_PER_PAGE = 100;

	/**
	 * Longest accepted search term.
	 *
	 * @var int
	 */
	const SEARCH_MAX_LENGTH = 100;

	/**
	 * Slug of the default taxonomy.
	 *
	 * @var string
	 */
	const DEFAULT_TAXONOMY = 'category';

	/**
	 * Gets the ability slug.
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'list-post-terms';
	}

	/**
	 * Gets the ability label.
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'List post terms', 'magazine-blocks' );
	}

	/**
	 * Gets the ability description.
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Lists categories (or tags / other public taxonomies) with their IDs and post counts. Use the IDs to point post blocks such as featured posts or grid module at a category or tag.', 'magazine-blocks' );
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
				'taxonomy'   => array(
					'type'        => 'string',
					'default'     => self::DEFAULT_TAXONOMY,
					'description' => __( 'Taxonomy slug, e.g. category or post_tag.', 'magazine-blocks' ),
				),
				'search'     => array(
					'type'      => 'string',
					'maxLength' => self::SEARCH_MAX_LENGTH,
				),
				'hide_empty' => array(
					'type'        => 'boolean',
					'default'     => false,
					'description' => __( 'Skip terms that have no published posts.', 'magazine-blocks' ),
				),
				'page'       => array(
					'type'    => 'integer',
					'minimum' => 1,
					'default' => 1,
				),
				'per_page'   => array(
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
				'terms'       => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'id'     => array( 'type' => 'integer' ),
							'name'   => array( 'type' => 'string' ),
							'slug'   => array( 'type' => 'string' ),
							'count'  => array( 'type' => 'integer' ),
							'parent' => array( 'type' => 'integer' ),
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
		$taxonomy = isset( $input['taxonomy'] ) ? $input['taxonomy'] : self::DEFAULT_TAXONOMY;
		$page     = $this->get_page( $input );
		$per_page = $this->get_per_page( $input, self::DEFAULT_PER_PAGE, self::MAX_PER_PAGE );

		$taxonomy_object = get_taxonomy( $taxonomy );

		if ( ! $taxonomy_object || ! $taxonomy_object->public || ! $taxonomy_object->show_in_rest ) {
			return new \WP_Error(
				'mzb_taxonomy_not_found',
				__( 'No public taxonomy exists with that slug.', 'magazine-blocks' ),
				array( 'status' => 404 )
			);
		}

		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => ! empty( $input['hide_empty'] ),
			'search'     => isset( $input['search'] ) ? $input['search'] : '',
		);

		$total = wp_count_terms( $args );
		$terms = get_terms(
			array_merge(
				$args,
				array(
					'number'  => $per_page,
					'offset'  => ( $page - 1 ) * $per_page,
					'orderby' => 'name',
				)
			)
		);

		if ( is_wp_error( $total ) || is_wp_error( $terms ) ) {
			return new \WP_Error(
				'mzb_terms_query_failed',
				__( 'The terms could not be retrieved.', 'magazine-blocks' ),
				array( 'status' => 500 )
			);
		}

		return array(
			'total'       => (int) $total,
			'total_pages' => (int) ceil( $total / $per_page ),
			'page'        => $page,
			'per_page'    => $per_page,
			'terms'       => array_map(
				function ( $term ) {
					return array(
						'id'     => (int) $term->term_id,
						'name'   => $term->name,
						'slug'   => $term->slug,
						'count'  => (int) $term->count,
						'parent' => (int) $term->parent,
					);
				},
				$terms
			),
		);
	}
}
