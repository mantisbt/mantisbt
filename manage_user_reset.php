<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_user_reset.php,v 1.30 2005-03-21 20:48:55 vwegert Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$f_user_id = gpc_get_int( 'user_id' );
	$t_result = user_reset_password( $f_user_id );
	$t_redirect_url = 'manage_user_page.php';

	html_page_top1();
	if ( $t_result ) {
		html_meta_redirect( $t_redirect_url );
	}
	html_page_top2();

	echo "<br />";
	echo "<div align=\"center\">";

	if ( false == $t_result ) {
		# PROTECTED
		echo lang_get( 'account_reset_protected_msg' ) . '<br />';
	} else {
		# SUCCESS
		if ( ( ON == config_get( 'send_reset_password' ) ) && ( ON == config_get( 'enable_email_notification' ) ) ) {
			# send the new random password via email
			echo lang_get( 'account_reset_msg' ) . '<br />';
		} else {
			# email notification disabled, then set the password to blank
			echo lang_get( 'account_reset_msg2' ) . '<br />';
		}
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
	echo "</div>";
	html_page_bottom1( __FILE__ );
?>
