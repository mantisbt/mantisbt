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
 * Insert the bugnote into the database then redirect to the bug page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'bugnote_add' );

$f_bug_id = gpc_get_int( 'bug_id' );
$f_text = gpc_get_string( 'bugnote_text', '' );
$f_duration = gpc_get_string( 'time_tracking', '0:00' );
$f_files = gpc_get_file( 'ufile', array() );

$t_query = array( 'issue_id' => $f_bug_id );

$t_payload = array(
	'text' => $f_text,
	'view_state' => array(
		'id' => gpc_get_bool( 'private' ) ? VS_PRIVATE : VS_PUBLIC
	),
	'time_tracking' => array(
		'duration' => $f_duration
	),
	'files' => helper_array_transpose( $f_files )
);

$t_data = array(
	'query' => $t_query,
	'payload' => $t_payload,
);

$t_command = new IssueNoteAddCommand( $t_data );
$t_command->execute();

form_security_purge( 'bugnote_add' );

print_successful_redirect_to_bug( $f_bug_id );
