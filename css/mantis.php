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
 * Generate CSS based on MantisBT settings
 *
 * @package MantisBT
 * @copyright Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses http_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( dirname( __DIR__ ) . '/core.php' );
require_api( 'config_api.php' );
require_api( 'http_api.php' );

# Send correct MIME Content-Type header for css content.
header( 'Content-Type: text/css; charset=UTF-8' );

# Don't let Internet Explorer second-guess our content-type, as per
# http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
header( 'X-Content-Type-Options: nosniff' );

# rewrite headers to allow caching
if( gpc_isset( 'cache_key' ) ) {
	http_caching_headers( true );
}

$t_font_family = config_get( 'font_family', null, null, ALL_PROJECTS );
$t_max_width = config_get( 'preview_max_width' );
$t_max_height = config_get( 'preview_max_height' );

echo '
*, h1, h2, h3, h4, h5, h6 {
	font-family: "' . @htmlspecialchars( $t_font_family, ENT_COMPAT, 'utf-8' ) . '";
}

.bug-attachment-preview-image img {
	border: 0;';
if( $t_max_width  > 0 ) {
	echo '
	max-width: ' . $t_max_width . 'px;';
}
if( $t_max_height > 0 ) {
	echo '
	max-height: ' . $t_max_height . 'px;';
}
echo '
}
';
