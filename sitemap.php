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
 * @copyright Copyright 2026 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses helper_api.php
 * @uses layout_api.php
 * @uses news_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

# Prevent output of HTML in the content if errors occur
if( !defined( 'DISABLE_INLINE_ERROR_REPORTING' ) ) {
	define( 'DISABLE_INLINE_ERROR_REPORTING', true );
}

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'helper_api.php' );
require_api( 'layout_api.php' );
require_api( 'news_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

# Maximum number of URLs
$t_max_urls = 50000;

if( !layout_sitemap_enabled() ) {
	# Disabled
	http_response_code( HTTP_STATUS_NOT_FOUND );
	return;
}
	
# Force anonymous user
$t_anonymous_user_id = user_get_id_by_name( auth_anonymous_account() );
current_user_set( $t_anonymous_user_id );

$g_project_override = ALL_PROJECTS;

$t_mtime = 0;

# Home link
$t_urls[] = config_get_global( 'default_home_page' );

# Sidebar links
foreach( layout_get_sidebar_items() as $t_menu_item ) {
	$t_urls[] = $t_menu_item['url'];
}

# Make absolute links
$t_path = config_get_global( 'path' );
foreach( $t_urls as &$t_url ) {
	if( is_null( parse_url( $t_url, PHP_URL_SCHEME ) ) ) {
		$t_url = $t_path . ltrim( $t_url, '/' );
	}
}

# Skip external or duplicate links
$t_urls = array_filter( array_unique( $t_urls ), function($p_url) {
	return !helper_is_link_external( $p_url );
} );

# News
if( news_is_enabled() ) {
	$t_news = news_get_rows( ALL_PROJECTS );
	foreach( $t_news as $t_item ) {
		if( !news_is_private( $t_item['id'] ) ) {
			$t_mtime = max( $t_mtime, $t_item['last_modified'] );
			$t_urls[] = [
				'loc' => $t_path . 'news_view_page.php?news_id=' . $t_item['id'],
				'lastmod' => $t_item['last_modified'],
			];
		}
	}
}

# Issues
if( access_has_global_level( config_get( 'view_bug_threshold' ), $t_anonymous_user_id )
	&& access_has_global_level( config_get( 'limit_view_unless_threshold' ), $t_anonymous_user_id ) ) {
	$t_filter = filter_ensure_valid_filter( [
		FILTER_PROPERTY_VIEW_STATE => VS_PUBLIC,
		FILTER_PROPERTY_STATUS => META_FILTER_ANY,
		FILTER_PROPERTY_HIDE_STATUS => META_FILTER_NONE,
		FILTER_PROPERTY_SORT_FIELD_NAME => 'id',
		FILTER_PROPERTY_SORT_DIRECTION => 'ASC',
	] );
	$t_filter_query = new BugFilterQuery( $t_filter );
	$t_filter_query->set_limit( $t_max_urls - count( $t_urls ) );
	$t_result = $t_filter_query->execute();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_mtime = max( $t_mtime, $t_row['last_updated'] );
		$t_urls[] = [
			'loc' => $t_path . string_get_bug_view_url( $t_row['id'] ),
			'lastmod' => $t_row['last_updated'],
		];
	}
}

if( !$t_mtime ) {
	$t_mtime = time();
}

header_remove( 'Cache-Control' );

header( 'Content-Type: application/xml; charset=UTF-8' );

header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $t_mtime ) );
if( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] )
	&& ( $t_mtime <= strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) ) {
	# Not modified
	http_response_code( HTTP_STATUS_NOT_MODIFIED );
	return;
}

http_response_code( HTTP_STATUS_SUCCESS );

# Print sitemap
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n",
	'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach( $t_urls as $t_item ) {
	echo '<url>', "\n",
		'<loc>', string_html_specialchars( $t_item['loc'] ?? $t_item ), '</loc>', "\n",
		'<lastmod>', gmdate( 'Y-m-d', $t_item['lastmod'] ?? $t_mtime ), '</lastmod>', "\n",
		'</url>', "\n";
}
echo '</urlset>', "\n";
