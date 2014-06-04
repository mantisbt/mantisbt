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
 * Update Project Versions
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
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

form_security_validate( 'manage_proj_ver_update' );

auth_reauthenticate();

$f_version_id = gpc_get_int( 'version_id' );

$t_version = version_get( $f_version_id );

$f_date_order	= gpc_get_string( 'date_order' );
$f_new_version	= gpc_get_string( 'new_version' );
$f_description  = gpc_get_string( 'description' );
$f_released     = gpc_get_bool( 'released' );
$f_obsolete	= gpc_get_bool( 'obsolete' );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_version->project_id );

if( is_blank( $f_new_version ) ) {
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$f_new_version	= trim( $f_new_version );

$t_version->version = $f_new_version;
$t_version->description = $f_description;
$t_version->released = $f_released ? VERSION_RELEASED : VERSION_FUTURE;
$t_version->obsolete = $f_obsolete;
$t_version->date_order = $f_date_order;

version_update( $t_version );
event_signal( 'EVENT_MANAGE_VERSION_UPDATE', array( $t_version->id ) );

form_security_purge( 'manage_proj_ver_update' );

$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $t_version->project_id;

html_page_top( null, $t_redirect_url );

html_operation_successful( $t_redirect_url );

html_page_bottom();
