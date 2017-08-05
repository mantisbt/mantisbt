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
 * Add bug relationships
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

form_security_validate( 'bug_relationship_add' );

$f_rel_type = gpc_get_int( 'rel_type' );
$f_src_bug_id = gpc_get_int( 'src_bug_id' );
$f_dest_bug_id_string = gpc_get_string( 'dest_bug_id' );

# user has access to update the bug...
access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_src_bug_id );

$f_dest_bug_id_string = str_replace( ',', '|', $f_dest_bug_id_string );

$f_dest_bug_id_array = explode( '|', $f_dest_bug_id_string );

foreach( $f_dest_bug_id_array as $f_dest_bug_id ) {
	$f_dest_bug_id = (int)$f_dest_bug_id;

	# source and destination bugs are the same bug...
	if( $f_src_bug_id == $f_dest_bug_id ) {
		trigger_error( ERROR_RELATIONSHIP_SAME_BUG, ERROR );
	}

	# the related bug exists...
	bug_ensure_exists( $f_dest_bug_id );
	$t_dest_bug = bug_get( $f_dest_bug_id, true );

	# bug is not read-only...
	if( bug_is_readonly( $f_src_bug_id ) ) {
		error_parameters( $f_src_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	# user can access to the related bug at least as viewer...
	if( !access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_dest_bug->project_id ), $f_dest_bug_id ) ) {
		error_parameters( $f_dest_bug_id );
		trigger_error( ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, ERROR );
	}

	$t_bug = bug_get( $f_src_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	relationship_upsert( $f_src_bug_id, $f_dest_bug_id, $f_rel_type );
}

form_security_purge( 'bug_relationship_add' );

print_header_redirect_view( $f_src_bug_id );
