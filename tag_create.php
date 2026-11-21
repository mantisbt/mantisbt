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
 * Tag Create
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses tag_api.php
 *
 * Unhandled exceptions will be caught by the default error handler
 * @noinspection PhpUnhandledExceptionInspection
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'tag_api.php' );

form_security_validate( 'tag_create' );

$f_tag_name = gpc_get_string( 'name' );
$f_tag_description = gpc_get_string( 'description', '' );
$f_project_id = gpc_get_int( 'project_id', ALL_PROJECTS );
$f_add_and_edit = gpc_get_bool( 'add_and_edit_tag' );

$t_tag_user = auth_get_current_user_id();

# The names are separated by the tag separator.  Validate them all before creating any.
$t_names = array();
foreach( explode( config_get( 'tag_separator' ), $f_tag_name ) as $t_name ) {
	$t_name = trim( $t_name );
	if( is_blank( $t_name ) ) {
		continue;
	}
	tag_ensure_name_is_valid( $t_name );
	$t_names[] = $t_name;
}

# Tags that already exist in the scope are left unchanged
$t_tag_ids = array();
foreach( $t_names as $t_name ) {
	if( tag_is_unique( $t_name, $f_project_id ) ) {
		$t_tag_ids[] = tag_create( $t_name, $t_tag_user, $f_tag_description, $f_project_id );
	}
}

form_security_purge( 'tag_create' );

if( $f_add_and_edit && !empty( $t_tag_ids ) ) {
	print_header_redirect( 'tag_update_page.php?tag_id=' . $t_tag_ids[0] );
} else if( $f_project_id == ALL_PROJECTS ) {
	print_header_redirect( 'manage_tags_page.php' );
} else {
	print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id . '#tags' );
}
