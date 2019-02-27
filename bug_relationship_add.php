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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'bug_relationship_add' );

$f_rel_type = gpc_get_int( 'rel_type' );
$f_src_bug_id = gpc_get_int( 'src_bug_id' );
$f_dest_bug_id_string = gpc_get_string( 'dest_bug_id' );

$t_dest_bug_id_string = str_replace( ',', '|', $f_dest_bug_id_string );
$t_dest_bug_id_array = explode( '|', $t_dest_bug_id_string );

foreach( $t_dest_bug_id_array as $t_dest_bug_id ) {
	# Skip empty bug ids and ignore source bug when processing multiple targets
	if( count( $t_dest_bug_id_array ) > 1
	&& ( is_blank( $t_dest_bug_id ) || $f_src_bug_id == $t_dest_bug_id )
	) {
		continue;
	}

	$t_data = array(
		'query' => array( 'issue_id' => $f_src_bug_id ),
		'payload' => array(
			'type' => array( 'id' => $f_rel_type ),
			'issue' => array( 'id' => $t_dest_bug_id )
		)
	);

	$t_command = new IssueRelationshipAddCommand( $t_data );
	$t_command->execute();
}

form_security_purge( 'bug_relationship_add' );

print_header_redirect_view( $f_src_bug_id );
