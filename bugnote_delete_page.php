<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Bugnote delete confirmation page
	# Page continues to bug_delete.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	bugnote_ensure_exists( $f_bugnote_id );
	$t_bug_id = bugnote_field( $f_bugnote_id, 'bug_id' );
	project_access_check( $t_bug_id );
	check_access( $g_delete_bugnote_threshold );
	check_bug_exists( $t_bug_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
	<?php echo $s_delete_bugnote_sure_msg ?>

	<form method="post" action="bugnote_delete.php">
		<input type="hidden" name="f_bugnote_id" value="<?php echo $f_bugnote_id ?>">
		<input type="submit" value="<?php echo $s_delete_bugnote_button ?>">
	</form>

	<?php print_hr( $g_hr_size, $g_hr_width ) ?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
