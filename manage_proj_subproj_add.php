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
 * Add subproject to Project
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
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );

form_security_validate( 'manage_proj_subproj_add' );

auth_reauthenticate();

$f_project_id    = gpc_get_int( 'project_id' );
$f_subproject_id = gpc_get_int( 'subproject_id' );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

if ( config_get( 'subprojects_enabled' ) == OFF ) {
	access_denied();
}

project_ensure_exists( $f_project_id );
project_ensure_exists( $f_subproject_id );

if( $f_project_id == $f_subproject_id ) {
	trigger_error( ERROR_GENERIC, ERROR );
}
project_hierarchy_add( $f_subproject_id, $f_project_id );

form_security_purge( 'manage_proj_subproj_add' );

$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;

layout_page_header( null, $t_redirect_url );

layout_page_begin( 'manage_overview_page.php' );

html_operation_successful( $t_redirect_url );

layout_page_end();
