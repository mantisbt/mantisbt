<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_reminder.php,v 1.1 2002-12-21 10:07:15 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This page allows an authorized user to send a reminder by email to another user
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id		= gpc_get_int( 'f_bug_id' );
	$f_to			= gpc_get_int_array( 'f_to' );
	$f_body			= gpc_get_string( 'f_body' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'bug_reminder_threshold' ) );
	bug_ensure_exists( $f_bug_id );

	$result = email_bug_reminder( $f_to, $f_bug_id, $f_body );
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
