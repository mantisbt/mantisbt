<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_reminder.php,v 1.19.10.1 2006-03-16 19:41:05 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows an authorized user to send a reminder by email to another user
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'email_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_to			= gpc_get_int_array( 'to' );
	$f_body			= gpc_get_string( 'body' );

	if ( bug_is_readonly( $f_bug_id ) ) {
		error_parameters( $f_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	access_ensure_bug_level( config_get( 'bug_reminder_threshold' ), $f_bug_id );

	$t_bug = bug_get( $f_bug_id, true );
	if( $t_bug->project_id != helper_get_current_project() ) {
		# in case the current project is not the same project of the bug we are viewing...
		# ... override the current project. This to avoid problems with categories and handlers lists etc.
		$g_project_override = $t_bug->project_id;
	}

	# Automically add recipients to monitor list if they are above the monitor
	# threshold, option is enabled, and not reporter or handler.
	foreach ( $f_to as $t_recipient )
	{
		if ( ON == config_get( 'reminder_recipents_monitor_bug' ) &&
			access_has_bug_level( config_get( 'monitor_bug_threshold' ), $f_bug_id ) &&
			!bug_is_user_handler( $f_bug_id, $t_recipient ) &&
			!bug_is_user_reporter( $f_bug_id, $t_recipient ) ) {
			bug_monitor( $f_bug_id, $t_recipient );
		}
	}

	$result = email_bug_reminder( $f_to, $f_bug_id, $f_body );

	# Add reminder as bugnote if store reminders option is ON.
	if ( ON == config_get( 'store_reminders' ) ) {
		if ( count( $f_to ) > 50 ) {		# too many recipients to log, truncate the list
			$t_to = array();
			for ( $i=0; $i<50; $i++ ) {
				$t_to[] = $f_to[$i];
			}
			$f_to = $t_to;
		}
		$t_attr = '|' . implode( '|', $f_to ) . '|';
		bugnote_add( $f_bug_id, $f_body, config_get( 'default_reminder_view_status' ) == VS_PRIVATE, REMINDER, $t_attr );
	}

	html_page_top1();
	html_meta_redirect( string_get_bug_view_url( $f_bug_id ) );
	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( string_get_bug_view_url( $f_bug_id ), lang_get( 'proceed' ) );
?>
</div>
<?php html_page_bottom1( __FILE__ ) ?>
