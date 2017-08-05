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
 * Manage filter edit update page
 *
 * @package MantisBT
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses filter_api.php
 */

require_once( 'core.php' );
require_api( 'filter_api.php' );

form_security_validate( 'manage_filter_edit_update' );

auth_ensure_user_authenticated();

$t_errors = array();

$f_filter_id = gpc_get_int( 'filter_id', null );
if( null === $f_filter_id ) {
	error_parameters( 'FILTER_ID' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$t_filter_string = filter_db_get_filter( $f_filter_id );
if( !$t_filter_string ) {
	access_denied();
} else {
	$t_filter = filter_deserialize( $t_filter_string );
	filter_cache_row( $f_filter_id );
}

$f_filter_name = gpc_get_string( 'filter_name', null );
# Check if filter name has been modified
# The filter name is now trimmed of white spaces before being saved, if the filter name
# had untrimmed spaces but the user did not change it, we keep it as is for compatibility
if( $f_filter_name != filter_get_field( $f_filter_id, 'name' ) ) {
	$f_filter_name = string_normalize( strip_tags( gpc_get_string( 'filter_name' ) ) );
	# Check for a blank name
	if( is_blank( $f_filter_name ) ) {
		$t_errors[] = lang_get( 'query_blank_name' );
	}
	# Check name length
	if( !filter_name_valid_length( $f_filter_name ) ) {
		$t_errors[] = lang_get( 'query_name_too_long' );
	}
	# Check and make sure they don't already have a query with the same name
	$t_query_arr = filter_db_get_available_queries();
	foreach( $t_query_arr as $t_name )	{
		if( $f_filter_name == string_normalize( $t_name ) ) {
			$t_errors[] = lang_get( 'query_dupe_name' );
			break;
		}
	}
} else {
	# filter name will not be updated
	$f_filter_name = null;
}

if( access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
	$f_is_public = gpc_get_bool( 'is_public' );
} else {
	# dont modify it
	$f_is_public = null;
}

$f_project_id = gpc_get_int( 'project_id', null );

$t_editable = filter_db_can_delete_filter( $f_filter_id );
if( !$t_editable ) {
	access_denied();
}

$t_filter = filter_gpc_get( $t_filter );

form_security_purge( 'manage_filter_edit_update' );

if( empty( $t_errors ) ) {
	filter_db_update_filter( $f_filter_id, filter_serialize( $t_filter ), $f_project_id, $f_is_public, $f_filter_name );
	print_header_redirect( 'manage_filter_page.php' );
}

# If there is any error message:
$t_error_html = '<li>' . implode( '</li><li>', $t_errors ) . '</li>';

layout_page_header();
layout_admin_page_begin();
echo '<div class="space-10"></div>';
html_operation_failure(
				helper_mantis_url( 'manage_filter_edit_page.php?filter_id=' . $f_filter_id ),
				$t_error_html
			);
layout_admin_page_end();
