<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );

	$f_id	= gpc_get_int( 'f_id' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
	<?php print_hr() ?>
	<?php echo lang_get( 'delete_account_sure_msg' ) ?>

	<form method="post" action="manage_user_delete.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>" />
		<input type="submit" value="<?php echo lang_get( 'delete_account_button' ) ?>" />
	</form>

	<?php print_hr() ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
