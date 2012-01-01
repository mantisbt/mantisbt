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
	 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'relationship_api.php' );

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
		if ( $f_src_bug_id == $f_dest_bug_id ) {
			trigger_error( ERROR_RELATIONSHIP_SAME_BUG, ERROR );
		}

		# the related bug exists...
		bug_ensure_exists( $f_dest_bug_id );

		# bug is not read-only...
		if ( bug_is_readonly( $f_src_bug_id ) ) {
			error_parameters( $f_src_bug_id );
			trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
		}

		# user can access to the related bug at least as viewer...
		if ( !access_has_bug_level( VIEWER, $f_dest_bug_id ) ) {
			error_parameters( $f_dest_bug_id );
			trigger_error( ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, ERROR );
		}

		$t_bug = bug_get( $f_src_bug_id, true );
		if( $t_bug->project_id != helper_get_current_project() ) {
			# in case the current project is not the same project of the bug we are viewing...
			# ... override the current project. This to avoid problems with categories and handlers lists etc.
			$g_project_override = $t_bug->project_id;
		}

		# check if there is other relationship between the bugs...
		$t_old_id_relationship = relationship_same_type_exists( $f_src_bug_id, $f_dest_bug_id, $f_rel_type );

		if ( $t_old_id_relationship == -1 ) {
			# the relationship type is exactly the same of the new one. No sense to proceed
			trigger_error( ERROR_RELATIONSHIP_ALREADY_EXISTS, ERROR );
		}
		else if ( $t_old_id_relationship > 0 ) {
			# there is already a relationship between them -> we have to update it and not to add a new one
			helper_ensure_confirmed( lang_get( 'replace_relationship_sure_msg' ), lang_get( 'replace_relationship_button' ) );

			# Update the relationship
			relationship_update( $t_old_id_relationship, $f_src_bug_id, $f_dest_bug_id, $f_rel_type );

			# Add log line to the history (both bugs)
			history_log_event_special( $f_src_bug_id, BUG_REPLACE_RELATIONSHIP, $f_rel_type, $f_dest_bug_id );
			history_log_event_special( $f_dest_bug_id, BUG_REPLACE_RELATIONSHIP, relationship_get_complementary_type( $f_rel_type ), $f_src_bug_id );
		} else {
			# Add the new relationship
			relationship_add( $f_src_bug_id, $f_dest_bug_id, $f_rel_type );

			# Add log line to the history (both bugs)
			history_log_event_special( $f_src_bug_id, BUG_ADD_RELATIONSHIP, $f_rel_type, $f_dest_bug_id );
			history_log_event_special( $f_dest_bug_id, BUG_ADD_RELATIONSHIP, relationship_get_complementary_type( $f_rel_type ), $f_src_bug_id );
		}

		# update bug last updated for both bugs
		bug_update_date( $f_src_bug_id );
		bug_update_date( $f_dest_bug_id );

		# send email notification to the users addressed by both the bugs
		email_relationship_added( $f_src_bug_id, $f_dest_bug_id, $f_rel_type );
		email_relationship_added( $f_dest_bug_id, $f_src_bug_id, relationship_get_complementary_type( $f_rel_type ) );
	}

	form_security_purge( 'bug_relationship_add' );

	print_header_redirect_view( $f_src_bug_id );
