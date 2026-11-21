<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Generate CSS from that requires output from php for specific settings e.g. status values
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses http_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

@require_once( dirname( __DIR__ ) . '/core.php' );
require_api( 'config_api.php' );
require_api( 'http_api.php' );

/**
 * Send correct MIME Content-Type header for css content.
 */
header( 'Content-Type: text/css; charset=UTF-8' );

/**
 * Disable MIME type sniffing for security reasons.
 */
header( 'X-Content-Type-Options: nosniff' );

/**
 * WARNING: DO NOT EXPOSE SENSITIVE CONFIGURATION VALUES!
 *
 * All configuration values below are publicly available to visitors of the bug
 * tracker regardless of whether they're authenticated. Server paths should not
 * be exposed. It is OK to expose paths that the user sees directly (short
 * paths) but you do need to be careful in your selections. Consider servers
 * using URL rewriting engines to mask/convert user-visible paths to paths that
 * should only be known internally to the server.
 */

# rewrite headers to allow caching
if( gpc_isset( 'cache_key' ) ) {
	http_caching_headers( true );
}

$t_status_string = config_get( 'status_enum_string' );
$t_statuses = MantisEnum::getAssocArrayIndexedByValues( $t_status_string );
$t_colors = config_get( 'status_colors' );
$t_font_family = config_get( 'font_family', null, null, ALL_PROJECTS );
$t_max_width = config_get( 'preview_max_width' );
$t_max_height = config_get( 'preview_max_height' );

echo '*, h1, h2, h3, h4, h5, h6 { font-family: "' . @htmlspecialchars( $t_font_family, ENT_COMPAT, 'utf-8' ) . '"; }
.bug-attachment-preview-image img {	border: 0; ';
if( $t_max_width  > 0 ) {
	echo 'max-width: ' . $t_max_width . 'px; ';
}
if( $t_max_height > 0 ) {
	echo 'max-height: ' . $t_max_height . 'px; ';
}
echo '}
';

foreach( $t_statuses as $t_id => $t_label ) {
	# Status color class
	if( array_key_exists( $t_label, $t_colors ) ) {
		$t_color = $t_colors[$t_label];
		echo '.' . html_get_status_css_fg( $t_id ) . " { color: {$t_color}; }\n";
		echo '.' . html_get_status_css_bg( $t_id ) . " { background-color: {$t_color}; }\n";
	}
}
