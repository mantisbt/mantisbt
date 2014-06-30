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
 * Handler to store a filter
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses filter_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'filter_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'query_store' );

auth_ensure_user_authenticated();
compress_enable();

$f_query_name = strip_tags( gpc_get_string( 'query_name' ) );
$f_is_public = gpc_get_bool( 'is_public' );
$f_all_projects = gpc_get_bool( 'all_projects' );

$t_query_redirect_url = 'query_store_page.php';

# We can't have a blank name
if( is_blank( $f_query_name ) ) {
	$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
		. urlencode( lang_get( 'query_blank_name' ) );
	print_header_redirect( $t_query_redirect_url );
}

# mantis_filters_table.name has a length of 64. Not allowing longer.
if( !filter_name_valid_length( $f_query_name ) ) {
	$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
		. urlencode( lang_get( 'query_name_too_long' ) );
	print_header_redirect( $t_query_redirect_url );
}

# Check and make sure they don't already have a
# query with the same name
$t_query_arr = filter_db_get_available_queries();
foreach( $t_query_arr as $t_id => $t_name )	{
	if( $f_query_name == $t_name ) {
		$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
			. urlencode( lang_get( 'query_dupe_name' ) );
		print_header_redirect( $t_query_redirect_url );
		exit;
	}
}

$t_project_id = helper_get_current_project();
if( $f_all_projects ) {
	$t_project_id = 0;
}

$t_filter_string = filter_db_get_filter( gpc_get_cookie( config_get( 'view_all_cookie' ), '' ) );

$t_new_row_id = filter_db_set_for_current_user( $t_project_id, $f_is_public,
												$f_query_name, $t_filter_string );

form_security_purge( 'query_store' );

if( $t_new_row_id == -1 ) {
	$t_query_redirect_url = $t_query_redirect_url . '?error_msg='
		. urlencode( lang_get( 'query_store_error' ) );
	print_header_redirect( $t_query_redirect_url );
} else {
	print_header_redirect( 'view_all_bug_page.php' );
}
