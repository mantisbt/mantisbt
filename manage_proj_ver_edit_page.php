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

	$f_project_id	= gpc_get_int( 'f_project_id' );
	$f_version		= gpc_get_string( 'f_version' );
	$f_date_order	= gpc_get_string( 'f_date_order' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu( 'manage_proj_ver_edit_page.php' ) ?>

<p />
<div align="center">
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_edit_project_version_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<form method="post" action="manage_proj_ver_update.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="hidden" name="f_orig_version" value="<?php echo $f_version ?>" />
		<?php echo $s_version ?>
	</td>
	<td>
		<input type="text" name="f_version" size="32" maxlength="64" value="<?php echo urldecode( $f_version ) ?>" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_date_order ?>
	</td>
	<td>
		<input type="text" name="f_date_order" size="32" value="<?php echo urldecode( $f_date_order ) ?>" />
	</td>
</tr>
<tr>
	<td class="left" width="50%">
		<input type="submit" value="<?php echo $s_update_version_button ?>" />
		</form>
	</td>
	<td class="right" width="50%">
		<form method="post" action="manage_proj_ver_del_page.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="hidden" name="f_version" value="<?php echo $f_version ?>" />
		<input type="submit" value="<?php echo $s_delete_version_button ?>" />
		</form>
	</td>
</tr>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>
