<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_cat_edit_page.php,v 1.28 2003-04-09 10:06:36 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'category_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_category		= gpc_get_string( 'category' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	$t_row = category_get_row( $f_project_id, $f_category );
	$t_assigned_to = $t_row['user_id'];
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php
	print_manage_menu( 'manage_proj_cat_edit_page.php' );
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
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<input type="hidden" name="project_id" value="<?php echo string_attribute( $f_project_id ) ?>" />
		<input type="hidden" name="category" value="<?php echo string_attribute( $f_category ) ?>" />
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td>
		<input type="text" name="new_category" size="32" maxlength="64" value="<?php echo string_attribute( $f_category ) ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td>
		<select name="assigned_to">
			<option value="0"></option>
			<?php print_assign_to_option_list( $t_assigned_to, $f_project_id ) ?>
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
</div>

<br />

<div class="border-center">
	<form method="post" action="manage_proj_cat_delete.php">
		<input type="hidden" name="project_id" value="<?php echo string_attribute( $f_project_id ) ?>" />
		<input type="hidden" name="category" value="<?php echo string_attribute( $f_category ) ?>" />
		<input type="submit" value="<?php echo lang_get( 'delete_category_button' ) ?>" />
	</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
