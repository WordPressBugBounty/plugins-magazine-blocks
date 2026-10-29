<?php
/**
 * Preview block posts ability.
 *
 * @since x.x.x
 * @package Magazine Blocks
 */

namespace MagazineBlocks\Abilities;

use MagazineBlocks\Services\PostQueryBuilder;
use MagazineBlocks\Services\TemplateContextHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Previews the posts a post block (featured posts, grid module, ...) would show for a given query.
 *
 * Post blocks render empty inside REST requests, so this runs the shared query builder directly.
 *
 * @since x.x.x
 */
class PreviewBlockPosts extends AbstractAbility {

	/**
	 * Default posts returned.
	 *
	 * @var int
	 */
	const DEFAULT_PER_PAGE = 5;

	/**
	 * Maximum posts returned.
	 *
	 * @var int
	 */
	const MAX_PER_PAGE = 20;

	/**
	 * Words kept in each excerpt.
	 *
	 * @var int
	 */
	const EXCERPT_WORDS = 30;

	/**
	 * Post type queried when none is given.
	 *
	 * @var string
	 */
	const DEFAULT_POST_TYPE = 'post';

	/**
	 * Ordering field used when none is given.
	 *
	 * @var string
	 */
	const DEFAULT_ORDER_BY = 'date';

	/**
	 * Ordering direction used when none is given.
	 *
	 * @var string
	 */
	const DEFAULT_ORDER = 'desc';

	/**
	 * Gets the ability slug.
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'preview-block-posts';
	}

	/**
	 * Gets the ability label.
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Preview posts for a Magazine Blocks post block', 'magazine-blocks' );
	}

	/**
	 * Gets the ability description.
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Returns the published posts that a Magazine Blocks post block would display for the given post type, category, tag and ordering, so a layout can be checked before it is built.', 'magazine-blocks' );
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
				'post_type' => array(
					'type'    => 'string',
					'default' => self::DEFAULT_POST_TYPE,
				),
				'category'  => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'Category term ID, from list-post-terms. 0 means any category.', 'magazine-blocks' ),
				),
				'tag'       => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'Tag term ID, from list-post-terms. 0 means any tag.', 'magazine-blocks' ),
				),
				'order_by'  => array(
					'type'    => 'string',
					'enum'    => array( 'date', 'title', 'rand', 'comment_count', 'modified', 'popular' ),
					'default' => self::DEFAULT_ORDER_BY,
				),
				'order'     => array(
					'type'    => 'string',
					'enum'    => array( 'asc', 'desc' ),
					'default' => self::DEFAULT_ORDER,
				),
				'per_page'  => array(
					'type'    => 'integer',
					'minimum' => 1,
					'maximum' => self::MAX_PER_PAGE,
					'default' => self::DEFAULT_PER_PAGE,
				),
				'offset'    => array(
					'type'    => 'integer',
					'minimum' => 0,
					'default' => 0,
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
				'total' => array( 'type' => 'integer' ),
				'posts' => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'id'      => array( 'type' => 'integer' ),
							'title'   => array( 'type' => 'string' ),
							'link'    => array( 'type' => 'string' ),
							'date'    => array( 'type' => 'string' ),
							'excerpt' => array( 'type' => 'string' ),
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
		$input     = $this->normalize_input( $input );
		$post_type = isset( $input['post_type'] ) ? $input['post_type'] : self::DEFAULT_POST_TYPE;

		if ( ! array_key_exists( $post_type, magazine_blocks_get_post_types() ) ) {
			return new \WP_Error(
				'mzb_post_type_not_supported',
				__( 'That post type is not available to post blocks.', 'magazine-blocks' ),
				array( 'status' => 400 )
			);
		}

		$query = ( new PostQueryBuilder( new TemplateContextHandler() ) )->build_query(
			array(
				'post_type'      => $post_type,
				'posts_per_page' => $this->get_per_page( $input, self::DEFAULT_PER_PAGE, self::MAX_PER_PAGE ),
				'cat'            => isset( $input['category'] ) ? (int) $input['category'] : 0,
				'tag_id'         => isset( $input['tag'] ) ? (int) $input['tag'] : 0,
				'orderby'        => isset( $input['order_by'] ) ? $input['order_by'] : self::DEFAULT_ORDER_BY,
				'order'          => isset( $input['order'] ) ? $input['order'] : self::DEFAULT_ORDER,
				'offset'         => isset( $input['offset'] ) ? (int) $input['offset'] : 0,
			),
			array( 'exclude_template' => true )
		);

		return array(
			'total' => (int) $query->found_posts,
			'posts' => array_map( array( $this, 'prepare_post' ), $query->posts ),
		);
	}

	/**
	 * Strips markup and decodes entities (e.g. the curly quotes WordPress texturizes titles into).
	 *
	 * @param string $text Text that may contain markup or entities.
	 * @return string
	 */
	private function to_plain_text( $text ) {
		return html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Reduces a post to the fields exposed to agents.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array
	 */
	private function prepare_post( $post ) {
		return array(
			'id'      => (int) $post->ID,
			'title'   => $this->to_plain_text( get_the_title( $post ) ),
			'link'    => (string) get_permalink( $post ),
			'date'    => (string) get_the_date( 'c', $post ),
			'excerpt' => wp_trim_words( $this->to_plain_text( get_the_excerpt( $post ) ), self::EXCERPT_WORDS ),
		);
	}
}
