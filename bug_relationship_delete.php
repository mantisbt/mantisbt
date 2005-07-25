<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_relationship_delete.php,v 1.10 2005-07-25 16:34:10 thraxisp Exp $
	# --------------------------------------------------------

	# ======================================================================
	# Author: Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	# ======================================================================
	# To delete a relationship we need to ensure that:
	# - User not anomymous
	# - Source bug exists and is not in read-only state (peer bug could not exist...)
	# - User that update the source bug and at least view the destination bug
	# - Relationship must exist
	# ----------------------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path . 'relationship_api.php' );

	$f_rel_id = gpc_get_int( 'rel_id' );
	$f_bug_id = gpc_get_int( 'bug_id' );

	# user has access to update the bug...
	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

	# bug is not read-only...
	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	# retrieve the destination bug of the relationship
	$t_dest_bug_id = relationship_get_linked_bug_id( $f_rel_id, $f_bug_id );

	# user can access to the related bug at least as viewer, if it's exist...
	if ( bug_exists( $t_dest_bug_id )) {
		if ( !access_has_bug_level( VIEWER, $t_dest_bug_id ) ) {
			error_parameters( $t_dest_bug_id );
			trigger_error( ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW, ERROR );
		}
	}

	helper_ensure_confirmed( lang_get( 'delete_relationship_sure_msg' ), lang_get( 'delete_relationship_button' ) );

	$t_bug_relationship_data = relationship_get( $f_rel_id );
	$t_rel_type = $t_bug_relationship_data->type;

	# delete relationship from the DB
	relationship_delete( $f_rel_id );

	# update bug last updated (just for the src bug)
	bug_update_date( $f_bug_id );

	# set the rel_type for both bug and dest_bug based on $t_rel_type and on who is the dest bug
	if ($f_bug_id == $t_bug_relationship_data->src_bug_id) {
		$t_bug_rel_type = $t_rel_type;
		$t_dest_bug_rel_type = relationship_get_complementary_type( $t_rel_type );
	}
	else {
		$t_bug_rel_type = relationship_get_complementary_type( $t_rel_type );
		$t_dest_bug_rel_type = $t_rel_type;
	}

	# send email and update the history for the src issue
	history_log_event_special( $f_bug_id, BUG_DEL_RELATIONSHIP, $t_bug_rel_type, $t_dest_bug_id );
	email_relationship_deleted( $f_bug_id, $t_dest_bug_id, $t_bug_rel_type );

	if ( bug_exists( $t_dest_bug_id )) {
		# send email and update the history for the dest issue
		history_log_event_special( $t_dest_bug_id, BUG_DEL_RELATIONSHIP, $t_dest_bug_rel_type, $f_bug_id );
		email_relationship_deleted( $t_dest_bug_id, $f_bug_id, $t_dest_bug_rel_type );
	}

	print_header_redirect_view( $f_bug_id );
?>
