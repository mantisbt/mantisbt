<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_reminder.php,v 1.6 2003-01-25 19:10:40 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows an authorized user to send a reminder by email to another user
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path . 'bug_api.php' );
	require_once( $t_core_path . 'email_api.php' );
	require_once( $t_core_path . 'bugnote_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id		= gpc_get_int( 'bug_id' );
	$f_to			= gpc_get_int_array( 'to' );
	$f_body			= gpc_get_string( 'body' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'bug_reminder_threshold' ) );
	bug_ensure_exists( $f_bug_id );

	# Automically add recipients to monitor list if they are above the monitor
	# threshold, option is enabled, and not reporter or handler.
	foreach ( $f_to as $t_recipient )
	{
		if ( ON == config_get( 'reminder_recipents_monitor_bug' ) &&
			access_level_check_greater_or_equal( config_get( 'monitor_bug_threshold' ) ) &&
			!bug_is_user_handler( $f_bug_id, $t_recipient ) && 
			!bug_is_user_reporter( $f_bug_id, $t_recipient ) ) {
			bug_monitor( $f_bug_id, $t_recipient );
		}
	}

	$result = email_bug_reminder( $f_to, $f_bug_id, $f_body );

	# Add reminder as bugnote if store reminders option is ON.
	if ( ON == config_get( 'store_reminders' ) ) {
		$t_body = lang_get( 'reminder_sent_to' ) . ' ' .
					( implode( ', ', $result ) ) . 
					"\n\n" . $f_body;
		bugnote_add( $f_bug_id, $t_body );
	}

	print_page_top1();
	print_meta_redirect( string_get_bug_view_url( $f_bug_id ) );
	print_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( string_get_bug_view_url( $f_bug_id ), lang_get( 'proceed' ) );
?>
</div>
<?php print_page_bot1( __FILE__ ) ?>
