<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.14 $
	# $Author: prescience $
	# $Date: 2002-09-01 22:00:35 $
	#
	# $Id: account_delete.php,v 1.14 2002-09-01 22:00:35 prescience Exp $
	# --------------------------------------------------------
?>
<?php
	# Delete account, remove cookies, and redirect user to logout redirect page
	# If the account is protected this fails.
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# check if users can't delete their own accounts
	if ( OFF == $g_allow_account_delete ) {
		print_header_redirect( 'account_page.php' );
	}

	# get protected state
	$t_protected = current_user_get_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	# If an account is protected then no one can change the information
	# This is useful for shared accounts or for demo purposes
	$t_user_id = current_user_get_field( 'id' );

	if (user_delete( $t_user_id )) {
		# delete cookies
		setcookie( $g_string_cookie );
		setcookie( $g_project_cookie );
		setcookie( $g_view_all_cookie );
	}
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( 'login_page.php' );
	}
?>
<?php print_page_top2() ?>

<p />
<div align="center">
<?php
	if ( ON == $t_protected ) {				# PROTECTED
		PRINT $s_account_protected_msg.'<p />';
		print_bracket_link( 'account_page.php', $s_go_back );
	} else if ( $result ) {					# SUCCESS
		PRINT $s_operation_successful.'<p />';
		print_bracket_link( 'login_page.php', $s_proceed );
	} else {								# FAILURE
		print_sql_error( $query );
	}
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
