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
 * Bug action group attach tags include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses tag_api.php
 */

if( !defined( 'BUG_ACTIONGROUP_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'tag_api.php' );

/**
 * Prints the title for the custom action page.
 * @return void
 */
function action_attach_tags_print_title() {
	echo lang_get( 'tag_attach_long' );
}

/**
 * Prints the table and form for the Attach Tags group action page.
 * @return void
 */
function action_attach_tags_print_fields() {
	echo '<tr><th class="category">', lang_get( 'tag_attach_long' ), '</th><td>';
	print_tag_input();
	echo '<input type="submit" class="btn btn-primary btn-white btn-round btn-sm" value="' . lang_get( 'tag_attach' ) . ' " /></td></tr>';
}

/**
 * Validates the Attach Tags group action.
 * Checks if a user can attach the requested tags to a given bug.
 * @param integer $p_bug_id A bug identifier.
 * @return string|null On failure: the reason for tags failing validation for the given bug. On success: null.
 */
function action_attach_tags_validate( $p_bug_id ) {
	global $g_action_attach_tags_tags;
	global $g_action_attach_tags_attach;
	global $g_action_attach_tags_create;

	$t_can_attach = access_has_bug_level( config_get( 'tag_attach_threshold' ), $p_bug_id );
	if( !$t_can_attach ) {
		return lang_get( 'tag_attach_denied' );
	}

	if( !isset( $g_action_attach_tags_tags ) ) {
		if( !isset( $g_action_attach_tags_attach ) ) {
			$g_action_attach_tags_attach = array();
			$g_action_attach_tags_create = array();
		}
		$g_action_attach_tags_tags = tag_parse_string( gpc_get_string( 'tag_string' ) );
		foreach ( $g_action_attach_tags_tags as $t_tag_row ) {
			if( $t_tag_row['id'] == -1 ) {
				$g_action_attach_tags_create[$t_tag_row['name']] = $t_tag_row;
			} else if( $t_tag_row['id'] >= 0 ) {
				$g_action_attach_tags_attach[$t_tag_row['name']] = $t_tag_row;
			}
		}
	}

	$t_can_create = access_has_bug_level( config_get( 'tag_create_threshold' ), $p_bug_id );
	if( count( $g_action_attach_tags_create ) > 0 && !$t_can_create ) {
		return lang_get( 'tag_create_denied' );
	}

	if( count( $g_action_attach_tags_create ) == 0 &&
		count( $g_action_attach_tags_attach ) == 0 ) {
		return lang_get( 'tag_none_attached' );
	}

	return null;
}

/**
 * Attaches all the tags to each bug in the group action.
 * @param integer $p_bug_id A bug identifier.
 * @return null Previous validation ensures that this function doesn't fail. Therefore we can always return null to indicate no errors occurred.
 */
function action_attach_tags_process( $p_bug_id ) {
	global $g_action_attach_tags_attach, $g_action_attach_tags_create;

	foreach( $g_action_attach_tags_create as $t_tag_row ) {
		$g_action_attach_tags_attach[] = array( 'name' => $t_tag_row['name'] );
	}

	$g_action_attach_tags_create = array();

	$t_data = array(
		'query' => array( 'issue_id' => $p_bug_id ),
		'payload' => array(
			'tags' => $g_action_attach_tags_attach
		)
	);

	$t_command = new TagAttachCommand( $t_data );
	$t_command->execute();

	return null;
}
