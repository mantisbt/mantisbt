<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php 
	print_manage_menu( 'manage_proj_cat_edit_page.php' );

	check_varset( $f_assigned_to, '0' );
?>

<p>
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_edit_project_category_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<form method="post" action="manage_proj_cat_update.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
		<input type="hidden" name="f_orig_category" value="<?php echo $f_category ?>">
		<?php echo $s_category ?>
	</td>
	<td>
		<input type="text" name="f_category" size="32" maxlength="64" value="<?php echo urldecode( stripslashes( $f_category ) ) ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_assigned_to ?>
	</td>
	<td>
		<select name="f_assigned_to">
			<option value="0"></option>
			<?php print_assign_to_option_list($f_assigned_to) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="left" width="50%">
		<input type="submit" value="<?php echo $s_update_category_button ?>">
		</form>
	</td>
	<td class="right" width="50%">
		<form method="post" action="manage_proj_cat_del_page.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
		<input type="hidden" name="f_category" value="<?php echo $f_category ?>">
		<input type="submit" value="<?php echo $s_delete_category_button ?>">
		</form>
	</td>
</tr>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>
