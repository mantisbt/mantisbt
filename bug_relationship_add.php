<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_relationship_add.php,v 1.2 2004-07-18 00:07:44 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# --------------------------------------------------------
	# 2004 by Marcello Scata' (marcello@marcelloscata.com) - ITALY
	# --------------------------------------------------------

	# MASC RELATIONSHIP

	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path . 'relationship_api.php' );

	$f_rel_type = gpc_get_int( 'rel_type' );
	$f_src_bug_id = gpc_get_int( 'src_bug_id' );
	$f_dest_bug_id = gpc_get_int( 'dest_bug_id' );

	if ( current_user_is_anonymous()) {
		access_denied();
	}

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

	# there is no other relationship between the same bugs...
	if ( relationship_exists($f_src_bug_id, $f_dest_bug_id) > 0 ) {
		trigger_error( ERROR_RELATIONSHIP_ALREADY_EXISTS, ERROR );
	}

	switch ( $f_rel_type ) {

		case BUG_BLOCKS:
			# BUG_BLOCKS -> swap src and dest with relationship BUG_DEPENDANT

			# Add relation to the DB
			relationship_add( $f_dest_bug_id, $f_src_bug_id, BUG_DEPENDANT );

			# Add log line to the history (both bugs)
			history_log_event_special( $f_src_bug_id, BUG_ADD_RELATIONSHIP, BUG_BLOCKS, $f_dest_bug_id );
			history_log_event_special( $f_dest_bug_id, BUG_ADD_RELATIONSHIP, BUG_DEPENDANT, $f_src_bug_id );

			break;

		case BUG_DEPENDANT:
			# Add relation to the DB
			relationship_add( $f_src_bug_id, $f_dest_bug_id, BUG_DEPENDANT );

			# Add log line to the history (both bugs)
			history_log_event_special( $f_src_bug_id, BUG_ADD_RELATIONSHIP, BUG_DEPENDANT, $f_dest_bug_id );
			history_log_event_special( $f_dest_bug_id, BUG_ADD_RELATIONSHIP, BUG_BLOCKS, $f_src_bug_id );

			break;

		case BUG_HAS_DUPLICATE:
			# BUG_HAS_DUPLICATE -> swap src and dest with relationship BUG_DUPLICATE

			# Add relation to the DB
			relationship_add( $f_dest_bug_id, $f_src_bug_id, BUG_DUPLICATE );

			# Add log line to the history (both bugs)
			history_log_event_special( $f_src_bug_id, BUG_ADD_RELATIONSHIP, BUG_HAS_DUPLICATE, $f_dest_bug_id );
			history_log_event_special( $f_dest_bug_id, BUG_ADD_RELATIONSHIP, BUG_DUPLICATE, $f_src_bug_id );

			break;

		case BUG_DUPLICATE:
			# Add relation to the DB
			relationship_add( $f_src_bug_id, $f_dest_bug_id, BUG_DUPLICATE );

			# Add log line to the history (both bugs)
			history_log_event_special( $f_src_bug_id, BUG_ADD_RELATIONSHIP, BUG_DUPLICATE, $f_dest_bug_id );
			history_log_event_special( $f_dest_bug_id, BUG_ADD_RELATIONSHIP, BUG_HAS_DUPLICATE, $f_src_bug_id );

			break;

		case BUG_RELATED:
			relationship_add( $f_src_bug_id, $f_dest_bug_id, BUG_RELATED );

			# Add log line to the history (both bugs)
			history_log_event_special( $f_src_bug_id, BUG_ADD_RELATIONSHIP, BUG_RELATED, $f_dest_bug_id );
			history_log_event_special( $f_dest_bug_id, BUG_ADD_RELATIONSHIP, BUG_RELATED, $f_src_bug_id );

			break;

		default:
			trigger_error( ERROR_GENERIC, ERROR );

			break;
	}

	# update bug last updated (just for the src bug)
	bug_update_date( $f_src_bug_id );

	# send email notification to the users addressed by both the bugs
	email_relationship_added( $f_src_bug_id );
	email_relationship_added( $f_dest_bug_id );

	print_header_redirect_view( $f_src_bug_id );

	# MASC RELATIONSHIP
?>
