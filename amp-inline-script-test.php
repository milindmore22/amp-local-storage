<?php
/**
 * AMP Inline Script Test
 *
 * @package   AMP_Local_Storage_Test
 * @author    Milind, rtCamp
 * @license   GPL-2.0-or-later
 * @copyright 2020 rtCamp pvt. ltd.
 *
 * @wordpress-plugin
 * Plugin Name: AMP Local Storage Demo
 * Description: Demonstrage Local Storage, with and without AMP.
 * Version: 0.0.1
 * Author: Milind, rtCamp
 * Author URI: https://milindmore.wordpress.com/
 * License: GNU General Public License v2 (or later)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace AMP_Local_Storage_Test;


if ( ! function_exists( 'amp_generate_script_hash' ) ) {
	/**
	 * Generate hash for inline amp-script.
	 *
	 * The sha384 hash used by amp-script is represented not as hexadecimal but as base64url, which is defined in RFC 4648
	 * under section 5, "Base 64 Encoding with URL and Filename Safe Alphabet". It is sometimes referred to as "web safe".
	 *
	 * @since 1.4.0
	 * @link https://amp.dev/documentation/components/amp-script/#security-features
	 * @link https://github.com/ampproject/amphtml/blob/e8707858895c2af25903af25d396e144e64690ba/extensions/amp-script/0.1/amp-script.js#L401-L425
	 * @link https://github.com/ampproject/amphtml/blob/27b46b9c8c0fb3711a00376668d808f413d798ed/src/service/crypto-impl.js#L67-L124
	 * @link https://github.com/ampproject/amphtml/blob/c4a663d0ba13d0488c6fe73c55dc8c971ac6ec0d/src/utils/base64.js#L52-L61
	 * @link https://tools.ietf.org/html/rfc4648#section-5
	 *
	 * @param string $script Script.
	 * @return string|null Script hash or null if the sha384 algorithm is not supported.
	 */
	function amp_generate_script_hash( $script ) {
		$sha384 = hash( 'sha384', $script, true );
		if ( false === $sha384 ) {
			return null;
		}
		$hash = str_replace(
			[ '+', '/', '=' ],
			[ '-', '_', '.' ],
			base64_encode( $sha384 ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		);
		return 'sha384-' . $hash;
	}
}

/**
 * Get current time script contents.
 *
 * @return string Script contents.
 */
function get_current_time_script() {
	return escape_script_text( file_get_contents( __DIR__ . '/amp-local-storage.js' ) ); // phpcs:ignore
}

/**
 * Prevent script contents from breaking out of the script.
 *
 * This doesn't seem entirely right. It would seem better to use htmlspecialchars().
 *
 * @param string $script S
 * cript.
 * @return string Escaped script.
 */
function escape_script_text( $script ) {
	return str_replace( '</script>', '<\/script>', $script );
}

/**
 * Print inline amp-script.
 *
 * @param string $script      Inline script.
 * @param string $placeholder Placeholder.
 */
function print_inline_script( $script, $placeholder = 'Loading...' ) {
	printf(
		'<amp-script script="%1$s" layout="fill" height="1" width="1">%2$s</amp-script><script type="text/plain" target="amp-script" id="%1$s">%3$s</script>',
		esc_attr( wp_unique_id( 'amp-script-' ) ),
		$placeholder,
		escape_script_text( $script ) // phpcs:ignore -- Note that escape_script_text() is all that is required, not esc_html() or htmlspecialchars().
	);
}

add_action(
	'init',
	function () {
		add_hooks();
	}
);

/**
 * Adds AMP scripts if AMP plugin is not avilable.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! wp_script_is( 'amp-runtime' ) ) {
			wp_enqueue_script( 'amp-runtime', 'https://cdn.ampproject.org/v0.js', array(), '0.0', false );
			wp_enqueue_script( 'amp-script', 'https://cdn.ampproject.org/v0/amp-script-0.1.js', array( 'amp-runtime' ), '0.1', false );
		}
	}
);

/**
 * Add hooks.
 */
function add_hooks() {

	// Output two separate amp-script-src meta tags to confirm that the AMP plugin merges them during post-processing.
	add_action(
		'wp_head',
		function () {
			// This one will get merged with the first.
			printf( '<meta name="amp-script-src" content="%s">', esc_attr( amp_generate_script_hash( escape_script_text( get_current_time_script() ) ) ) );
		}
	);

	// Print the two inline scripts.
	add_action(
		'wp_head',
		function () {
			print_inline_script( get_current_time_script(), '<div id="page-url">' . esc_html( get_permalink() ) . '</div>' );
		}
	);
}
