<?php
/**
 * Plugin Name: Magazine Blocks
 * Description: Craft your beautifully unique and dynamic Magazine, Newspaper website with various beautiful and advanced posts related blocks like Featured Posts, Banner Posts, Grid Module, Tab Posts, and more.
 * Author: WPBlockArt
 * Author URI: https://wpblockart.com/
 * Version: 1.3.24
 * Requires at least: 5.4
 * Requires PHP: 7.0
 * Text Domain: magazine-blocks
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Magazine Blocks
 */

use MagazineBlocks\Blocks;
use MagazineBlocks\Helper;
use MagazineBlocks\MagazineBlocks;

defined( 'ABSPATH' ) || exit;

! defined( 'MAGAZINE_BLOCKS_VERSION' ) && define( 'MAGAZINE_BLOCKS_VERSION', '1.3.24' );
! defined( 'MAGAZINE_BLOCKS_PLUGIN_FILE' ) && define( 'MAGAZINE_BLOCKS_PLUGIN_FILE', __FILE__ );
! defined( 'MAGAZINE_BLOCKS_PLUGIN_DIR' ) && define( 'MAGAZINE_BLOCKS_PLUGIN_DIR', __DIR__ );
! defined( 'MAGAZINE_BLOCKS_PLUGIN_DIR_URL' ) && define( 'MAGAZINE_BLOCKS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
! defined( 'MAGAZINE_BLOCKS_ASSETS' ) && define( 'MAGAZINE_BLOCKS_ASSETS', __DIR__ . '/assets' );
! defined( 'MAGAZINE_BLOCKS_ASSETS_DIR_URL' ) && define( 'MAGAZINE_BLOCKS_ASSETS_DIR_URL', MAGAZINE_BLOCKS_PLUGIN_DIR_URL . 'assets' );
! defined( 'MAGAZINE_BLOCKS_DIST_DIR_URL' ) && define( 'MAGAZINE_BLOCKS_DIST_DIR_URL', MAGAZINE_BLOCKS_PLUGIN_DIR_URL . 'dist' );
! defined( 'MAGAZINE_BLOCKS_LANGUAGES' ) && define( 'MAGAZINE_BLOCKS_LANGUAGES', __DIR__ . '/languages' );
! defined( 'MAGAZINE_BLOCKS_UPLOAD_DIR' ) && define( 'MAGAZINE_BLOCKS_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/magazine-blocks' );
! defined( 'MAGAZINE_BLOCKS_UPLOAD_DIR_URL' ) && define( 'MAGAZINE_BLOCKS_UPLOAD_DIR_URL', wp_upload_dir()['baseurl'] . '/magazine-blocks' );

// Load the autoloader.
require_once __DIR__ . '/vendor/autoload.php';

if ( ! function_exists( 'magazine_blocks' ) ) {
	/**
	 * Returns the main instance of Magazine Blocks to prevent the need to use globals.
	 *
	 * @return MagazineBlocks
	 */
	function magazine_blocks() {
		return MagazineBlocks::init();
	}
}

magazine_blocks();

/**
 * Create API fields for additional info
 *
 * @since 1.0.9
 */
function magazine_blocks_register_rest_fields() {
	$post_type = Helper::get_post_types();

	foreach ( $post_type as $key => $value ) {
		// Featured image.
		register_rest_field(
			$value['value'],
			'magazine_blocks_featured_image_url',
			array(
				'get_callback'    => 'magazine_blocks_get_featured_image_url',
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Author info.
		register_rest_field(
			$value['value'],
			'magazine_blocks_author',
			array(
				'get_callback'    => 'magazine_blocks_get_author_info',
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Add comment info.
		register_rest_field(
			$value['value'],
			'magazine_blocks_comment',
			array(
				'get_callback'    => 'magazine_blocks_get_comment_info',
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Add comment info.
		register_rest_field(
			$value['value'],
			'magazine_blocks_author_image',
			array(
				'get_callback'    => 'magazine_blocks_get_author_image',
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Category links.
		register_rest_field(
			$value['value'],
			'magazine_blocks_category',
			array(
				'get_callback'    => 'magazine_blocks_get_category_list',
				'update_callback' => null,
				'schema'          => array(
					'description' => esc_html__( 'Category list links' ),
					'type'        => 'string',
				),
			)
		);
	}
}

// Feature image.
function magazine_blocks_get_featured_image_url( $object ) {

	$featured_images = array();
	if ( ! isset( $object['featured_media'] ) ) {
		return $featured_images;
	} else {

		$full                         = wp_get_attachment_image_src( $object['featured_media'], 'full', false );
		$medium                       = wp_get_attachment_image_src( $object['featured_media'], 'medium', false );
		$thumbnail                    = wp_get_attachment_image_src( $object['featured_media'], 'thumbnail', false );
		$featured_images['full']      = $full;
		$featured_images['medium']    = $medium;
		$featured_images['thumbnail'] = $thumbnail;
		return $featured_images;
	}
}

// Author.
function magazine_blocks_get_author_info( $object ) {
	$author = ( isset( $object['author'] ) ) ? $object['author'] : '';

	$author_data['display_name'] = get_the_author_meta( 'display_name', $author );
	$author_data['author_link']  = get_author_posts_url( $author );

	return $author_data;
}

// Comment.
function magazine_blocks_get_comment_info( $object ) {
	$comments_count = wp_count_comments( $object['id'] );
	return $comments_count->total_comments;
}

// Author Image.
function magazine_blocks_get_author_image( $object ) {
	$author = ( isset( $object['author'] ) ) ? $object['author'] : '';

	$author_image = get_avatar_url( $author );

	return $author_image;
}

// Category list.
if ( ! function_exists( 'magazine_blocks_get_category_list' ) ) {
	function magazine_blocks_get_category_list( $object ) {
		$taxonomies = get_post_taxonomies( $object['id'] );
		if ( 'post' === get_post_type() ) {
			return get_the_category_list( esc_html__( ' ' ), '', $object['id'] );
		} elseif ( ! empty( $taxonomies ) ) {
				return get_the_term_list( $object['id'], $taxonomies[0], ' ' );
		}
	}
}
add_action( 'rest_api_init', 'magazine_blocks_register_rest_fields' );

add_action( 'wp_ajax_magazine_blocks_pagination_load', 'magazine_blocks_pagination_load' );
add_action( 'wp_ajax_nopriv_magazine_blocks_pagination_load', 'magazine_blocks_pagination_load' );

function magazine_blocks_pagination_load() {
	$page = intval( $_GET['page'] );

	$att = $_POST['att']; // Pass the attributes needed for rendering

	// Modify the attributes with the new page number
	$att['paged'] = $page;

	// Render the posts using your existing function
	$html = Blocks::render_block_magazine_blocks_featured_posts( $att );

	// Return the HTML as the response
	wp_send_json_success( array( 'html' => $html ) );

	wp_die();
}
