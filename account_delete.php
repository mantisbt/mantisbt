<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.17 $
	# $Author: jfitzell $
	# $Date: 2002-09-16 00:05:44 $
	#
	# $Id: account_delete.php,v 1.17 2002-09-16 00:05:44 jfitzell Exp $
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
	if ( OFF == config_get( 'allow_account_delete' ) ) {
		print_header_redirect( 'account_page.php' );
	}

	# protected account check
	if ( ON == current_user_get_field( 'protected' ) ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	# If an account is protected then no one can change the information
	# This is useful for shared accounts or for demo purposes
	$t_user_id = auth_get_current_user_id();

	if ( user_delete( $t_user_id ) ) {
		# delete cookies
		#@@@ move these to a function... maybe in the gpc_api ??
		setcookie( config_get( 'string_cookie' ) );
		setcookie( config_get( 'project_cookie' ) );
		setcookie( config_get( 'view_all_cookie' ) );

		print_meta_redirect( 'login_page.php' );
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( 'login_page.php', lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
