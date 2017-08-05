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
 * Project Delete
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
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );

form_security_validate( 'manage_proj_delete' );

auth_reauthenticate();

$f_project_id = gpc_get_int( 'project_id' );

access_ensure_project_level( config_get( 'delete_project_threshold' ), $f_project_id );

$t_project_name = project_get_name( $f_project_id );

helper_ensure_confirmed( lang_get( 'project_delete_msg' ) .
		'<br />' . lang_get( 'project_name_label' ) . lang_get( 'word_separator' ) . $t_project_name,
		lang_get( 'project_delete_button' ) );

project_delete( $f_project_id );

form_security_purge( 'manage_proj_delete' );

# Don't leave the current project set to a deleted project -
#  set it to All Projects
if( helper_get_current_project() == $f_project_id ) {
	helper_set_current_project( ALL_PROJECTS );
}

print_header_redirect( 'manage_proj_page.php' );
