<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_relationship_delete.php,v 1.3 2004-09-12 00:17:56 thraxisp Exp $
	# --------------------------------------------------------

	# --------------------------------------------------------
	# 2004 by Marcello Scata' (marcello@marcelloscata.com) - ITALY
	# --------------------------------------------------------
	# To delete a relationship we need to ensure that:
	# - User not anomymous
	# - Source bug exists and is not in read-only state (peer bug could not exist...)
	# - User that update the source bug and at least view the destination bug
	# - Relationship must exist
	# --------------------------------------------------------

	# MASC RELATIONSHIP

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path . 'relationship_api.php' );

	$f_rel_id = gpc_get_int( 'rel_id' );
	$f_bug_id = gpc_get_int( 'bug_id' );

	if ( current_user_is_anonymous()) {
		access_denied();
	}

	# user has access to update the bug...
	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

	# bug is not read-only...
	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
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

	# Add log lines to both the histories
	switch ( $t_rel_type ) {
		case BUG_BLOCKS:
			history_log_event_special( $f_bug_id, BUG_DEL_RELATIONSHIP, BUG_BLOCKS, $t_dest_bug_id );
			email_relationship_deleted( $f_bug_id );

	if ( bug_exists( $t_dest_bug_id )) {
				history_log_event_special( $t_dest_bug_id, BUG_DEL_RELATIONSHIP, BUG_DEPENDANT, $f_bug_id );
				email_relationship_deleted( $t_dest_bug_id );
			}
			break;

		case BUG_DEPENDANT:
			history_log_event_special( $f_bug_id, BUG_DEL_RELATIONSHIP, BUG_DEPENDANT, $t_dest_bug_id );
			email_relationship_deleted( $f_bug_id );

			if ( bug_exists( $t_dest_bug_id )) {
				history_log_event_special( $t_dest_bug_id, BUG_DEL_RELATIONSHIP, BUG_BLOCKS, $f_bug_id );
				email_relationship_deleted( $t_dest_bug_id );
			}
			break;

		case BUG_HAS_DUPLICATE:
			history_log_event_special( $f_bug_id, BUG_DEL_RELATIONSHIP, BUG_HAS_DUPLICATE, $t_dest_bug_id );
			email_relationship_deleted( $f_bug_id );

			if ( bug_exists( $t_dest_bug_id )) {
				history_log_event_special( $t_dest_bug_id, BUG_DEL_RELATIONSHIP, BUG_DUPLICATE, $f_bug_id );
				email_relationship_deleted( $t_dest_bug_id );
			}
			break;

		case BUG_DUPLICATE:
			history_log_event_special( $f_bug_id, BUG_DEL_RELATIONSHIP, BUG_DUPLICATE, $t_dest_bug_id );
			email_relationship_deleted( $f_bug_id );

			if ( bug_exists( $t_dest_bug_id )) {
				history_log_event_special( $t_dest_bug_id, BUG_DEL_RELATIONSHIP, BUG_HAS_DUPLICATE, $f_bug_id );
				email_relationship_deleted( $t_dest_bug_id );
			}
			break;

		case BUG_RELATED:
			history_log_event_special( $f_bug_id, BUG_DEL_RELATIONSHIP, BUG_RELATED, $t_dest_bug_id );
			email_relationship_deleted( $f_bug_id );

			if ( bug_exists( $t_dest_bug_id )) {
				history_log_event_special( $t_dest_bug_id, BUG_DEL_RELATIONSHIP, BUG_RELATED, $f_bug_id );
				email_relationship_deleted( $t_dest_bug_id );
			}
			break;

		default:
			trigger_error( ERROR_GENERIC, ERROR );
			break;
	}

	print_header_redirect_view( $f_bug_id );

	# MASC RELATIONSHIP
?>