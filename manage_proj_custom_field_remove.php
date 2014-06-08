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
 * Remove Custom Fields
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses custom_field_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'manage_proj_custom_field_remove' );

auth_reauthenticate();

$f_field_id = gpc_get_int( 'field_id' );
$f_project_id = gpc_get_int( 'project_id' );
$f_return = gpc_get_string( 'return', '' );

# We should check both since we are in the project section and an
# admin might raise the first threshold and not realize they need
# to raise the second
access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
access_ensure_project_level( config_get( 'custom_field_link_threshold' ), $f_project_id );

$t_definition = custom_field_get_definition( $f_field_id );

# Confirm with the user
helper_ensure_confirmed( lang_get( 'confirm_custom_field_unlinking' ) .
	'<br/>' . lang_get( 'custom_field_label' ) . lang_get( 'word_separator' ) . string_attribute( $t_definition['name'] ),
	lang_get( 'field_remove_button' ) );

if( $f_return == 'custom_field' ) {
	$t_redirect_url = 'manage_custom_field_edit_page.php?field_id=' . $f_field_id;
} else {
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
}

custom_field_unlink( $f_field_id, $f_project_id );

form_security_purge( 'manage_proj_custom_field_remove' );

html_page_top( null, $t_redirect_url );

html_operation_successful( $t_redirect_url );

html_page_bottom();
