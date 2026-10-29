<?php
/**
 * Abilities API integration.
 *
 * @since x.x.x
 * @package Magazine Blocks
 */

namespace MagazineBlocks\Abilities;

use MagazineBlocks\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Magazine Blocks ability category and abilities.
 *
 * Both hooks only fire on WordPress versions that ship the Abilities API, so nothing happens elsewhere.
 *
 * @since x.x.x
 */
final class Abilities {

	use Singleton;

	/**
	 * Ability category slug.
	 *
	 * @var string
	 */
	const CATEGORY = 'magazine-blocks';

	/**
	 * Constructor.
	 *
	 * @since x.x.x
	 */
	protected function __construct() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Registers the ability category.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function register_category() {
		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'Magazine Blocks', 'magazine-blocks' ),
				'description' => __( 'Discover and use the Magazine Blocks editor blocks, layouts and design settings.', 'magazine-blocks' ),
			)
		);
	}

	/**
	 * Registers every ability.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function register_abilities() {
		/**
		 * Filters the ability classes registered by Magazine Blocks.
		 *
		 * @since x.x.x
		 *
		 * @param string[] $abilities Class names extending AbstractAbility.
		 */
		$abilities = apply_filters( 'magazine_blocks_abilities', $this->get_abilities() );

		foreach ( $abilities as $ability ) {
			if ( is_string( $ability ) && is_subclass_of( $ability, AbstractAbility::class ) ) {
				( new $ability() )->register();
			}
		}
	}

	/**
	 * Built-in ability classes.
	 *
	 * @return string[]
	 */
	private function get_abilities() {
		return array(
			ListBlocks::class,
			GetBlockSchema::class,
			GetGlobalStyles::class,
			ListLibrarySections::class,
			ListPostTerms::class,
			PreviewBlockPosts::class,
		);
	}
}
