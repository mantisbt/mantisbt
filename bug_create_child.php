<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_create_child.php,v 1.3 2004-08-01 08:56:37 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );
	
	access_denied();

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'bug_api.php' );

	$t_bug_table = config_get( 'mantis_bug_table' );

	$f_bug_id = gpc_get_int( 'bug_id' );

	if ( current_user_is_anonymous()) {
		access_denied();
	}

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

	# bug is not read-only...
	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	# copy the bug...
	$t_new_bug_id = bug_copy( $f_bug_id, /* project_id = */ null, /* custom_fields = */ true, /* relationships = */ false, 
				/* history = */ false, /* attachments = */ false, /* bugnotes = */ false, /* monitoring users = */ false );

	# Add log line to record the cloning action
	history_log_event_special( $t_new_bug_id, NEW_BUG );
	history_log_event_special( $t_new_bug_id, BUG_CREATED_FROM, '', $f_bug_id );
	history_log_event_special( $f_bug_id, BUG_CLONED_TO, '', $t_new_bug_id );

	# Add relation
	relationship_add( $f_bug_id, $t_new_bug_id, BUG_DEPENDANT );

	# Add log line to the history (both bugs)
	history_log_event_special( $f_bug_id, BUG_ADD_RELATIONSHIP, BUG_DEPENDANT, $t_new_bug_id );
	history_log_event_special( $t_new_bug_id, BUG_ADD_RELATIONSHIP, BUG_BLOCKS, $f_bug_id );

	# update bug last updated
	bug_update_date( $f_bug_id );

	print_header_redirect_update( $t_new_bug_id );
?>