<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_relationship_add.php,v 1.5 2005-06-28 11:04:04 vboctor Exp $
	# --------------------------------------------------------

	# ======================================================================
	# Author: Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	# ======================================================================

	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path . 'relationship_api.php' );

	$f_rel_type = gpc_get_int( 'rel_type' );
	$f_src_bug_id = gpc_get_int( 'src_bug_id' );
	$f_dest_bug_id = gpc_get_int( 'dest_bug_id' );

	# user has access to update the bug...
	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_src_bug_id );

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
	}
	else {
		# Add the new relationship
		relationship_add( $f_src_bug_id, $f_dest_bug_id, $f_rel_type );

		# Add log line to the history (both bugs)
		history_log_event_special( $f_src_bug_id, BUG_ADD_RELATIONSHIP, $f_rel_type, $f_dest_bug_id );
		history_log_event_special( $f_dest_bug_id, BUG_ADD_RELATIONSHIP, relationship_get_complementary_type( $f_rel_type ), $f_src_bug_id );
	}

	# update bug last updated (just for the src bug)
	bug_update_date( $f_src_bug_id );

	# send email notification to the users addressed by both the bugs
	email_relationship_added( $f_src_bug_id, $f_dest_bug_id, $f_rel_type );
	email_relationship_added( $f_dest_bug_id, $f_src_bug_id, relationship_get_complementary_type( $f_rel_type ) );

	print_header_redirect_view( $f_src_bug_id );
?>
