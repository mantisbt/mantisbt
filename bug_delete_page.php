<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_delete_page.php,v 1.17 2002-10-23 00:50:53 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Bug delete confirmation page
	# Page contiues to bug_delete.php
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'f_bug_id' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'allow_bug_delete_access_level' ) );
	bug_ensure_exists( $f_bug_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
	<?php print_hr() ?>
	<?php echo lang_get( 'delete_bug_sure_msg' ) ?>

	<form method="post" action="bug_delete.php">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="submit" value="<?php echo lang_get( 'delete_bug_button' ) ?>" />
	</form>

	<?php print_hr() ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
