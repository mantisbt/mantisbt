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
	check_access( MANAGER );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php
	print_manage_menu( 'manage_proj_cat_edit_page.php' );

	check_varset( $f_assigned_to, '0' );
?>

<br />
<div align="center">
<form method="post" action="manage_proj_cat_update.php">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_project_category_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="hidden" name="f_orig_category" value="<?php echo $f_category ?>" />
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td>
		<input type="text" name="f_category" size="32" maxlength="64" value="<?php echo urldecode( stripslashes( $f_category ) ) ?>" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td>
		<select name="f_assigned_to">
			<option value="0"></option>
			<?php print_assign_to_option_list($f_assigned_to) ?>
		</select>
	</td>
</tr>
<tr>
	<td>
		&nbsp;
	</td>
	<td>
		<input type="submit" value="<?php echo lang_get( 'update_category_button' ) ?>" />
	</td>
</tr>
</table>
</form>

<br />

<div class="border-center">
	<form method="post" action="manage_proj_cat_del_page.php">
	<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
	<input type="hidden" name="f_category" value="<?php echo $f_category ?>" />
	<input type="submit" value="<?php echo lang_get( 'delete_category_button' ) ?>" />
	</form>
</div>

</div>

<?php print_page_bot1( __FILE__ ) ?>
