<?php

declare(strict_types=1);

$root          = dirname( __DIR__ );
$module        = (string) file_get_contents( $root . '/categories-all-in-one.php' );
$classicEditor = (string) file_get_contents( $root . '/js/plugin-4.0.js' );

assertContains( "current_user_can('edit_posts')", $module, 'shortcode generator checks editor capability' );
assertContains( "check_ajax_referer('categories_all_in_one_shortcode_generator')", $module, 'shortcode generator checks its AJAX nonce' );
assertContains( "wp_create_nonce('categories_all_in_one_shortcode_generator')", $module, 'classic editor receives a matching nonce' );
assertContains( "encodeURIComponent(config.nonce || '')", $classicEditor, 'classic editor sends the nonce' );

echo "Categories All In One AJAX security smoke test passed.\n";

function assertContains( string $needle, string $haystack, string $label ): void {
	if ( str_starts_with( ltrim( $haystack ), '<?php' ) ) {
		$tokens   = static function ( string $code ): string {
			$parts = [];
			foreach ( token_get_all( str_starts_with( ltrim( $code ), '<?php' ) ? $code : '<?php ' . $code ) as $token ) {
				if ( is_array( $token ) ) {
					if ( in_array( $token[0], [ T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG, T_CLOSE_TAG ], true ) ) {
						continue;
					}
					$parts[] = $token[1];
				} else {
					$parts[] = $token;
				}
			}
			return implode( "\0", $parts );
		};
		$haystack = $tokens( $haystack );
		$needle   = $tokens( $needle );
	}
	if ( strpos( $haystack, $needle ) === false ) {
		fwrite( STDERR, "FAIL: {$label}\n" );
		exit( 1 );
	}
}
