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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_hierarchy_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_hierarchy_api.php' );

form_security_validate( 'manage_proj_subproj_delete' );

auth_reauthenticate();

$f_project_id    = gpc_get_int( 'project_id' );
$f_subproject_id = gpc_get_int( 'subproject_id' );

access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

project_hierarchy_remove( $f_subproject_id, $f_project_id );

form_security_purge( 'manage_proj_subproj_delete' );

$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;

html_page_top( null, $t_redirect_url );
?>
<br />
<div>
<?php
echo lang_get( 'operation_successful' ).'<br />';
print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
html_page_bottom();
