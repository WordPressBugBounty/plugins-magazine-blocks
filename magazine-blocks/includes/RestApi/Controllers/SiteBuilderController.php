<?php
/**
 * Magazine Blocks Site Builder Controller.
 *
 * @package Magazine Blocks
 */
namespace MagazineBlocks\RestApi\Controllers;

defined( 'ABSPATH' ) || exit;
/**
 * SiteBuilder controller.
 */
class SiteBuilderController extends \WP_REST_Controller {
	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'magazine-blocks/v1';
	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base = 'site-builder';

	/**
	 * Template types supported by the API
	 *
	 * @var array<int, string>
	 */
	protected $template_types = [
		'all',
		'header',
		'footer',
		'front',
		'single',
		'archive',
		'404',
		'search',
	];

	/**
	 * {@inheritDoc}
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(
						'refresh' => array(
							'default'           => false,
							'sanitize_callback' => 'rest_sanitize_boolean',
							'required'          => false,
						),
						'type'    => [
							'default'  => 'all',
							'required' => false,
							'enum'     => $this->template_types,
						],
					),
				),
			)
		);

		// Register route for importing a template
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/import/(?P<type>[\w-]+)/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'import_template' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to get items.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return true|\WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You are not allowed to access this resource.', 'magazine-blocks' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}
		return true;
	}

	/**
	 * Get all Site Builder templates.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {

		$type    = $request['type'] ?? 'all';
		$type    = in_array( $type, $this->template_types, true ) ? $type : 'all';
		$refresh = (bool) $request->get_param( 'refresh' );

		// TODO
		$templates = $this->get_templates( $type, $refresh );

		if ( is_wp_error( $templates ) ) {
			return $templates;
		}

		return rest_ensure_response( $templates );
	}

	/**
 * Get templates from remote API or cache.
 *
 * @param string $type    The template type to retrieve.
 * @param bool   $refresh Whether to refresh the cached data.
 * @return array|\WP_Error Array of templates or WP_Error on failure.
 */
	protected function get_templates( $type = 'all', $refresh = false ) {
		// Transient name based on template type
		$transient_key = 'magazine_blocks_templates_' . $type;

		// Get cached templates if refresh is not requested
		if ( ! $refresh ) {
			$cached_templates = get_transient( $transient_key );
			if ( false !== $cached_templates ) {
				return $cached_templates;
			}
		}

		// If no cache or refresh requested, fetch from remote API
		$api_url = sprintf(
			'https://wpblockart.com/wp-json/magazine-blocks-site-builder/v1/templates/%s',
			$type
		);

		$response = wp_remote_get(
			$api_url,
			array(
				'timeout' => 60,
			)
		);

		// Check for errors
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'api_error',
				sprintf(
				/* translators: %d: HTTP response code */
					esc_html__( 'Error fetching templates. Response code: %d', 'magazine-blocks' ),
					$response_code
				),
				array( 'status' => $response_code )
			);
		}

		$body      = wp_remote_retrieve_body( $response );
		$templates = json_decode( $body, true );

		if ( null === $templates && JSON_ERROR_NONE !== json_last_error() ) {
			return new \WP_Error(
				'json_parse_error',
				esc_html__( 'Error parsing template data.', 'magazine-blocks' ),
				array( 'status' => 500 )
			);
		}

		// Cache the templates for future use (cache for 12 hours)
		set_transient( $transient_key, $templates, 12 * HOUR_IN_SECONDS );

		return $templates;
	}

	/**
	 * Import a specific template.
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function import_template( $request ) {
		$template_type = $request->get_param( 'type' );
		$template_id   = $request->get_param( 'id' );

		if ( ! in_array( $template_type, $this->template_types, true ) ) {
			return new \WP_Error(
				'invalid_template_type',
				esc_html__( 'Invalid template type specified.', 'magazine-blocks' ),
				array( 'status' => 400 )
			);
		}

		if ( empty( $template_id ) || ! is_numeric( $template_id ) ) {
			return new \WP_Error(
				'invalid_template_id',
				esc_html__( 'Invalid template ID specified.', 'magazine-blocks' ),
				array( 'status' => 400 )
			);
		}

		// Make a request to the remote API to import the template
		$import_url = sprintf(
			'https://wpblockart.com/wp-json/magazine-blocks-site-builder/v1/import/%s/%d',
			$template_type,
			$template_id
		);

		$response = wp_remote_get(
			$import_url,
			array(
				'timeout' => 120,
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return new \WP_Error(
				'import_failed',
				esc_html__( 'Failed to import the template.', 'magazine-blocks' ),
				array( 'status' => 500 )
			);
		}

		$import_data = json_decode( wp_remote_retrieve_body( $response ), true );

		// Here you would typically process the imported template data
		// This might involve creating or updating posts, storing template data, etc.
		// For now, we'll just return the data from the remote API

		return new \WP_REST_Response(
			array(
				'success' => true,
				'message' => sprintf(
					/* Translators: 1: Template type 2: Template id */
					esc_html__( '%1$s template with ID %2$d has been imported successfully.', 'magazine-blocks' ),
					ucfirst( $template_type ),
					$template_id
				),
				'data'    => $import_data,
			),
			200
		);
	}
}
