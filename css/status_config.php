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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

$t_allow_caching = isset( $_GET['cache_key'] );
if( $t_allow_caching ) {
	# Suppress default headers. This allows caching as defined in server configuration
	$g_bypass_headers = true;
}

@require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_api( 'config_api.php' );

if( $t_allow_caching ) {
	# if standard headers were bypassed, add security headers, at least
	http_security_headers();
}

/**
 * Send correct MIME Content-Type header for css content.
 */
header( 'Content-Type: text/css; charset=UTF-8' );

/**
 * Don't let Internet Explorer second-guess our content-type, as per
 * http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
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

/**
 *	@todo Modify to run sections only on certain pages.
 *	eg. status colors are only necessary on a few pages.(my view, view all bugs, bug view, etc. )
 *	other pages may need to include dynamic css styles as well
 */
$t_referer_page = array_key_exists( 'HTTP_REFERER', $_SERVER )
	? basename( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_PATH ) )
	: basename( __FILE__ );

if( $t_referer_page == auth_login_page() ) {
	# custom status colors not needed.
	exit;
}

switch( $t_referer_page ) {
	case AUTH_PAGE_USERNAME:
	case AUTH_PAGE_CREDENTIAL:
	case 'signup_page.php':
	case 'lost_pwd_page.php':
	case 'account_update.php':
		# We don't need custom status colors on login page, and this is
		# actually causing an error since we're not authenticated yet.
		exit;
}

$t_status_string = config_get( 'status_enum_string' );
$t_statuses = MantisEnum::getAssocArrayIndexedByValues( $t_status_string );
$t_colors = config_get( 'status_colors' );

foreach( $t_statuses as $t_id => $t_label ) {
	# Status color class
	if( array_key_exists( $t_label, $t_colors ) ) {
		$t_color = $t_colors[$t_label];
		echo '.' . html_get_status_css_fg( $t_id ) . " { color: {$t_color}; }\n";
		echo '.' . html_get_status_css_bg( $t_id ) . " { background-color: {$t_color}; }\n";
	}
}
