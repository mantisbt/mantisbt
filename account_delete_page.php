<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.13 $
	# $Author: prescience $
	# $Date: 2002-09-01 22:00:35 $
	#
	# $Id: account_delete_page.php,v 1.13 2002-09-01 22:00:35 prescience Exp $
	# --------------------------------------------------------
?>
<?php
	# This is the delete confirmation page
	# The result is POSTed to account_delete.php3
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
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p />
<div align="center">
	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
	<?php echo $s_confirm_delete_msg ?>

	<form method="post" action="account_delete.php">
		<input type="submit" value="<?php echo $s_delete_account_button ?>" />
	</form>

	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
