<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Allows the user to select a project that is visible to him
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_ref  = gpc_get_string( 'ref', '' );
?>
<?php print_page_top1() ?>
<?php print_page_top2a() ?>

<?php # Project Select Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="set_project.php">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="ref" value="<?php echo $f_ref ?>" />
		<?php echo lang_get( 'login_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="40%">
		<?php echo $s_choose_project ?>:
	</td>
	<td width="60%">
		<select name="project_id">
		<?php print_project_option_list( 0 ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'make_default' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="make_default" />
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<?php echo lang_get( 'select_project_button') ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Project Select Form END ?>

<?php print_page_bot1( __FILE__ ) ?>
