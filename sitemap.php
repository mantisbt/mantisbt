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
 * Sitemap - http://sitemaps.org
 *
 * @package MantisBT
 * @copyright Copyright 2002 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );

# Access level to get a sitemap
$t_threshold = config_get( 'view_bug_threshold' );

# Maximum number of URLs
$t_max_urls = 50000;

$t_path = config_get_global( 'path' );

/**
 * Print a sitemap URL.
 *
 * @param string $p_url The URL: has to be relative to the installation path {@see $g_path}.
 */
function print_sitemap_url( string $p_url ) {
	global $t_path;
	echo '<url>', "\n",
		'<loc>', htmlspecialchars( $t_path . $p_url, ENT_XML1 | ENT_QUOTES ), '</loc>', "\n",
		'</url>', "\n";
}

header( 'Content-Type: application/xml; charset=UTF-8' );

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n",
	'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";

if( auth_anonymous_enabled() ) {
	$t_anonymous_user_id = user_get_id_by_name( auth_anonymous_account() );
	if( $t_anonymous_user_id && user_is_enabled( $t_anonymous_user_id ) ) {
		if( access_has_global_level( $t_threshold, $t_anonymous_user_id ) ) {

			# Force anonymous user
			current_user_set( $t_anonymous_user_id );

			# Print home
			print_sitemap_url( config_get_global( 'default_home_page' ) );
			$t_max_urls --;

			# Print issues
			$t_filter = filter_ensure_valid_filter( [
				FILTER_PROPERTY_VIEW_STATE => VS_PUBLIC,
				FILTER_PROPERTY_STATUS => META_FILTER_ANY,
				FILTER_PROPERTY_HIDE_STATUS => META_FILTER_NONE,
			] );
			$t_filter_query = new BugFilterQuery( $t_filter );
			$t_filter_query->set_limit( $t_max_urls );
			$t_result = $t_filter_query->execute();
			while( $t_row = db_fetch_array( $t_result ) ) {
				print_sitemap_url( string_get_bug_view_url( $t_row['id'] ) );
			}
		} else {
			echo '<!-- Anonymous account has no required access level -->', "\n";
		}
	} else {
		echo '<!-- Anonymous account disabled -->', "\n";
	}
} else {
	echo '<!-- Anonymous login is not allowed or account is not set -->', "\n";
}

echo '</urlset>', "\n";
