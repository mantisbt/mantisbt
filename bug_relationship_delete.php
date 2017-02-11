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
 * To delete a relationship we need to ensure that:
 * - User not anomymous
 * - Source bug exists and is not in read-only state (peer bug could not exist...)
 * - User that update the source bug and at least view the destination bug
 * - Relationship must exist
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'relationship_api.php' );

form_security_validate( 'bug_relationship_delete' );

$f_rel_id = gpc_get_int( 'rel_id' );
$f_bug_id = gpc_get_int( 'bug_id' );

$t_bug = bug_get( $f_bug_id, true );
if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

# user has access to update the bug...
access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

# bug is not read-only...
if( bug_is_readonly( $f_bug_id ) ) {
	error_parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

# retrieve the destination bug of the relationship
$t_dest_bug_id = relationship_get_linked_bug_id( $f_rel_id, $f_bug_id );

$t_dest_bug = bug_get( $t_dest_bug_id, true );

# user can access to the related bug at least as viewer, if it's exist...
if( bug_exists( $t_dest_bug_id ) ) {
	if( !access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_dest_bug->project_id ), $t_dest_bug_id ) ) {
		error_parameters( $t_dest_bug_id );
		trigger_error( ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, ERROR );
	}
}

helper_ensure_confirmed( lang_get( 'delete_relationship_sure_msg' ), lang_get( 'delete_relationship_button' ) );

relationship_delete( $f_rel_id );

form_security_purge( 'bug_relationship_delete' );

print_header_redirect_view( $f_bug_id );
