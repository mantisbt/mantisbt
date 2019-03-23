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
 * Remove the bugnote and bugnote text and redirect back to
 * the viewing page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'bugnote_delete' );

$f_bugnote_id = gpc_get_int( 'bugnote_id' );

helper_ensure_confirmed( lang_get( 'delete_bugnote_sure_msg' ),
						 lang_get( 'delete_bugnote_button' ) );

$t_data = array( 'query' => array( 'id' => $f_bugnote_id  ) );

$t_command = new IssueNoteDeleteCommand( $t_data );
$t_result = $t_command->execute();

form_security_purge( 'bugnote_delete' );

print_successful_redirect( string_get_bug_view_url( $t_result['issue_id'] ) . '#bugnotes' );
