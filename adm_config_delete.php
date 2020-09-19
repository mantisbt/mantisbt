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
 * Handles deleting configuration settings from the configuration management page
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
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
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );

form_security_validate( 'adm_config_delete' );

$f_user_id = gpc_get_int( 'user_id' );
$f_project_id = gpc_get_int( 'project_id' );
$f_config_option = gpc_get_string( 'config_option' );

access_ensure_global_level( config_get( 'set_configuration_threshold' ) );

if( $f_project_id != ALL_PROJECTS ) {
	project_ensure_exists( $f_project_id );
}

helper_ensure_confirmed( lang_get( 'delete_config_sure_msg' ), lang_get( 'delete' ) );

config_delete( $f_config_option, $f_user_id, $f_project_id );

form_security_purge( 'adm_config_delete' );

print_successful_redirect( 'adm_config_report.php' );

