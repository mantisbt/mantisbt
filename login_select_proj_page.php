<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Allows the user to select a project that is visible to him
?>
<?php require_once( 'core.php' ) ?>
<?php login_user_check_only() ?>
<?php
	check_varset( $f_ref, '' );
?>
<?php print_page_top1() ?>
<?php print_page_top2a() ?>

<?php # Project Select Form BEGIN ?>
<p>
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<form method="post" action="set_project.php">
		<input type="hidden" name="f_ref" value="<?php echo $f_ref ?>" />
		<?php echo $s_login_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="40%">
		<?php echo $s_choose_project ?>:
	</td>
	<td width="60%">
		<select name="f_project_id">
		<option value="00000000"><?php echo $s_all_projects ?></option>
		<?php print_project_option_list() ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_make_default ?>:
	</td>
	<td>
		<input type="checkbox" name="f_make_default" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo $s_select_project_button ?>" />
		</form>
	</td>
</tr>
</table>
</div>
<?php # Project Select Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
