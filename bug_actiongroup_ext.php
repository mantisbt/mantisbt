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
 * Bug action group additional actions
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bug_group_action_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bug_group_action_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

auth_ensure_user_authenticated();

helper_begin_long_process();

$f_action = gpc_get_string( 'action' );
$f_bug_arr	= gpc_get_int_array( 'bug_arr', array() );

$t_form_name = 'bug_actiongroup_' . $f_action;

form_security_validate( $t_form_name );

bug_group_action_init( $f_action );

# group bugs by project
$t_projects_bugs = array();
foreach( $f_bug_arr as $t_bug_id ) {
	bug_ensure_exists( $t_bug_id );
	$t_bug = bug_get( $t_bug_id, true );

	if( isset( $t_projects_bugs[$t_bug->project_id] ) ) {
	  $t_projects_bugs[$t_bug->project_id][] = $t_bug_id;
	} else {
	  $t_projects_bugs[$t_bug->project_id] = array( $t_bug_id );
	}
}

$t_failed_ids = array();

foreach( $t_projects_bugs as $t_project_id => $t_bugs ) {
	$g_project_override = $t_project_id;
	foreach( $t_bugs as $t_bug_id ) {
		$t_fail_reason = bug_group_action_validate( $f_action, $t_bug_id );
		if( $t_fail_reason !== null ) {
			$t_failed_ids[$t_bug_id] = $t_fail_reason;
		}
		if( !isset( $t_failed_ids[$t_bug_id] ) ) {
			$t_fail_reason = bug_group_action_process( $f_action, $t_bug_id );
			if( $t_fail_reason !== null ) {
				$t_failed_ids[$t_bug_id] = $t_fail_reason;
			}
		}
	}
}

$g_project_override = null;

form_security_purge( $t_form_name );

if( count( $t_failed_ids ) > 0 ) {
	html_page_top();

	echo '<div>';

	$t_word_separator = lang_get( 'word_separator' );
	foreach( $t_failed_ids as $t_id => $t_reason ) {
		$t_label = sprintf( lang_get( 'label' ), string_get_bug_view_link( $t_id ) ) . $t_word_separator;
		printf( "<p>%s%s</p>\n", $t_label, $t_reason );
	}

	print_bracket_link( 'view_all_bug_page.php', lang_get( 'proceed' ) );
	echo '</div>';

	html_page_bottom();
} else {
	print_header_redirect( 'view_all_bug_page.php' );
}
