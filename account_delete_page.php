<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.16 $
	# $Author: jfitzell $
	# $Date: 2002-09-16 00:39:19 $
	#
	# $Id: account_delete_page.php,v 1.16 2002-09-16 00:39:19 jfitzell Exp $
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
	if ( OFF == config_get( 'allow_account_delete' ) ) {
		print_header_redirect( 'account_page.php' );
	}

	# protected account check
	if ( current_user_is_protected() ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
	<?php print_hr() ?>
	<?php echo lang_get( 'confirm_delete_msg' ) ?>

	<form method="post" action="account_delete.php">
		<input type="submit" value="<?php echo lang_get( 'delete_account_button' ) ?>" />
	</form>

	<?php print_hr() ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
