<?php
# MantisBT - a php based bugtracking system

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
	 */

	/**
	 * Requires Tag API
	 */
	require_once( 'tag_api.php' );

	/**
	 * Prints the title for the custom action page.
	 */
	function action_attach_tags_print_title() {
        echo '<tr class="form-title">';
        echo '<td colspan="2">';
        echo lang_get( 'tag_attach_long' );
        echo '</td></tr>';
	}

	/**
	 * Prints the table and form for the Attach Tags group action page.
	 */
	function action_attach_tags_print_fields() {
		echo '<tr ',helper_alternate_class(),'><td class="category">',lang_get('tag_attach_long'),'</td><td>';
		print_tag_input();
		echo '<input type="submit" class="button" value="' . lang_get( 'tag_attach' ) . ' " /></td></tr>';
	}

	/**
	 * Validates the Attach Tags group action.
	 * Gets called for every bug, but performs the real tag validation only
	 * the first time.  Any invalid tags will be skipped, as there is no simple
	 * or clean method of presenting these errors to the user.
	 * @param integer Bug ID
	 * @return boolean True
	 */
	function action_attach_tags_validate( $p_bug_id ) {
		global $g_action_attach_tags_valid;
		if ( !isset( $g_action_attach_tags_valid ) ) {
			$f_tag_string = gpc_get_string( 'tag_string' );
			$f_tag_select = gpc_get_string( 'tag_select' );

			global $g_action_attach_tags_attach, $g_action_attach_tags_create, $g_action_attach_tags_failed;
			$g_action_attach_tags_attach = array();
			$g_action_attach_tags_create = array();
			$g_action_attach_tags_failed = array();

			$t_tags = tag_parse_string( $f_tag_string );
			$t_can_attach = access_has_bug_level( config_get( 'tag_attach_threshold' ), $p_bug_id );
			$t_can_create = access_has_global_level( config_get( 'tag_create_threshold' ) );

			foreach ( $t_tags as $t_tag_row ) {
				if ( -1 == $t_tag_row['id'] ) {
					if ( $t_can_create && $t_can_attach ) {
						$g_action_attach_tags_create[] = $t_tag_row;
					} else {
						$g_action_attach_tags_failed[] = $t_tag_row;
					}
				} else if ( -2 == $t_tag_row['id'] ) {
					$g_action_attach_tags_failed[] = $t_tag_row;
				} else if ( $t_can_attach ) {
					$g_action_attach_tags_attach[] = $t_tag_row;
				} else {
					$g_action_attach_tags_failed[] = $t_tag_row;
				}
			}

			if ( 0 < $f_tag_select && tag_exists( $f_tag_select ) ) {
				if ( $t_can_attach ) {
					$g_action_attach_tags_attach[] = tag_get( $f_tag_select );
				} else {
					$g_action_attach_tags_failed[] = tag_get( $f_tag_select );
				}
			}

		}

		global $g_action_attach_tags_attach, $g_action_attach_tags_create, $g_action_attach_tags_failed;

		return true;
	}

	/**
	 * Attaches all the tags to each bug in the group action.
	 * @param integer Bug ID
	 * @return boolean True if all tags attach properly
	 */
	function action_attach_tags_process( $p_bug_id ) {
		global $g_action_attach_tags_attach, $g_action_attach_tags_create;

		$t_user_id = auth_get_current_user_id();

		foreach( $g_action_attach_tags_create as $t_tag_row ) {
			$t_tag_row['id'] = tag_create( $t_tag_row['name'], $t_user_id );
			$g_action_attach_tags_attach[] = $t_tag_row;
		}
		$g_action_attach_tags_create = array();

		foreach( $g_action_attach_tags_attach as $t_tag_row ) {
			if ( !tag_bug_is_attached( $t_tag_row['id'], $p_bug_id ) ) {
				tag_bug_attach( $t_tag_row['id'], $p_bug_id, $t_user_id );
			}
		}

		return true;
	}
