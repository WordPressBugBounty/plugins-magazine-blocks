<?php
/**
 * Base class for Abilities API abilities.
 *
 * @since x.x.x
 * @package Magazine Blocks
 */

namespace MagazineBlocks\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * Describes one ability and registers it with the Abilities API.
 *
 * @since x.x.x
 */
abstract class AbstractAbility {

	/**
	 * Namespace every Magazine Blocks ability name starts with.
	 *
	 * @var string
	 */
	const NAME_PREFIX = 'magazine-blocks/';

	/**
	 * Capability required by default; matches the plugin's editor-facing REST controllers.
	 *
	 * @var string
	 */
	const CAPABILITY = 'edit_posts';

	/**
	 * Ability slug without the namespace, e.g. `list-blocks`.
	 *
	 * @return string
	 */
	abstract protected function get_slug();

	/**
	 * Human-readable label.
	 *
	 * @return string
	 */
	abstract protected function get_label();

	/**
	 * What the ability does, written for an agent choosing between tools.
	 *
	 * @return string
	 */
	abstract protected function get_description();

	/**
	 * JSON Schema describing the accepted input.
	 *
	 * @return array
	 */
	abstract protected function get_input_schema();

	/**
	 * JSON Schema describing the returned output.
	 *
	 * @return array
	 */
	abstract protected function get_output_schema();

	/**
	 * Behavioural hints (`readonly`, `destructive`, `idempotent`); each ability states them explicitly.
	 *
	 * @return array
	 */
	abstract protected function get_annotations();

	/**
	 * Runs the ability.
	 *
	 * @param mixed $input Validated input.
	 * @return array|\WP_Error
	 */
	abstract public function execute( $input = null );

	/**
	 * Whether the current user may run the ability; override to require a different capability or to inspect the input.
	 *
	 * @return bool
	 */
	public function has_permission() {
		return current_user_can( static::CAPABILITY );
	}

	/**
	 * Registers the ability with the Abilities API.
	 *
	 * @return void
	 */
	public function register() {
		wp_register_ability(
			self::NAME_PREFIX . $this->get_slug(),
			array(
				'label'               => $this->get_label(),
				'description'         => $this->get_description(),
				'category'            => Abilities::CATEGORY,
				'input_schema'        => $this->get_input_schema(),
				'output_schema'       => $this->get_output_schema(),
				'execute_callback'    => array( $this, 'execute' ),
				'permission_callback' => array( $this, 'has_permission' ),
				'meta'                => array(
					'annotations'  => $this->get_annotations(),
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			)
		);
	}

	/**
	 * Annotations for an ability that only reads data.
	 *
	 * @return array
	 */
	protected function read_only_annotations() {
		return array(
			'readonly'    => true,
			'destructive' => false,
			'idempotent'  => true,
		);
	}

	/**
	 * Normalizes input to an array; abilities without input receive null.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	protected function normalize_input( $input ) {
		return is_array( $input ) ? $input : array();
	}

	/**
	 * Reads the requested page number, never below 1.
	 *
	 * @param array $input Normalized input.
	 * @return int
	 */
	protected function get_page( array $input ) {
		return isset( $input['page'] ) ? max( 1, (int) $input['page'] ) : 1;
	}

	/**
	 * Reads the requested page size, clamped to 1..$max.
	 *
	 * @param array $input   Normalized input.
	 * @param int   $fallback Page size when none was requested.
	 * @param int   $max     Largest allowed page size.
	 * @return int
	 */
	protected function get_per_page( array $input, $fallback, $max ) {
		return isset( $input['per_page'] ) ? min( $max, max( 1, (int) $input['per_page'] ) ) : $fallback;
	}
}
