<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Bug delete confirmation page
	# Page contiues to bug_delete.php3
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( $g_handle_bug_threshold );
	check_access( $g_allow_bug_delete_access_level );
	check_bug_exists( $f_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
	<?php echo $s_delete_bug_sure_msg ?>

	<form method="post" action="bug_delete.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="hidden" name="f_bug_text_id" value="<?php echo $f_bug_text_id ?>">
		<input type="submit" value="<?php echo $s_delete_bug_button ?>">
	</form>

	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
