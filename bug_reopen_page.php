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
	project_access_check( $f_id );
	check_access( $g_reopen_bug_threshold  );
	bug_ensure_exists( $f_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Resolve Form BEGIN ?>
<br />
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<form method="post" action="bug_reopen.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>" />
		<?php echo $s_reopen_add_bugnote_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="center" colspan="2">
		<textarea name="f_bugnote_text" cols="80" rows="10" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_reopen_bug_button ?>" />
		</form>
	</td>
</tr>
</table>
</div>
<?php # Resolve Form END ?>

<?php include( $g_view_bug_inc ) ?>

<?php print_page_bot1( __FILE__ ) ?>
