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

	$f_field_id		= gpc_get_int( 'f_field_id' );
	$f_project_id	= gpc_get_int( 'f_project_id' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php
	print_manage_menu( 'manage_proj_custom_field_edit_page.php' );

	custom_field_ensure_exists( $f_field_id );

	$t_definition = custom_field_get_definition( $f_field_id )
?>

<br />
<div align="center">
<form method="post" action="manage_proj_custom_field_update.php">
<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
<input type="hidden" name="f_field_id" value="<?php echo $f_field_id ?>" />
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_custom_field_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_name' ) ?>
	</td>
	<td>
		<input type="text" name="f_name" size="32" maxlength="64" value="<?php echo $t_definition['name'] ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_type' ) ?>
	</td>
	<td>
		<select name="f_type">
			<?php print_enum_string_option_list( 'custom_field_type', $t_definition['type'] ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_possible_values' ) ?>
	</td>
	<td>
		<input type="text" name="f_possible_values" size="32" maxlength="255" value="<?php echo $t_definition['possible_values'] ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_default_value' ) ?>
	</td>
	<td>
		<input type="text" name="f_default_value" size="32" maxlength="255" value="<?php echo $t_definition['default_value'] ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_valid_regexp' ) ?>
	</td>
	<td>
		<input type="text" name="f_valid_regexp" size="32" maxlength="255" value="<?php echo $t_definition['valid_regexp'] ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_access_level_r' ) ?>
	</td>
	<td>
		<select name="f_access_level_r">
			<?php print_enum_string_option_list( 'access_levels', $t_definition['access_level_r'] ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_access_level_rw' ) ?>
	</td>
	<td>
		<select name="f_access_level_rw">
			<?php print_enum_string_option_list( 'access_levels', $t_definition['access_level_rw'] ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_length_min' ) ?>
	</td>
	<td>
		<input type="text" name="f_length_min" size="32" maxlength="64" value="<?php echo $t_definition['length_min'] ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_length_max' ) ?>
	</td>
	<td>
		<input type="text" name="f_length_max" size="32" maxlength="64" value="<?php echo $t_definition['length_max'] ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'custom_field_advanced' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced" value="1" <?php if($t_definition['advanced']) echo 'checked'; ?>>
	</td>
</tr>
<tr>
	<td>
		&nbsp;
	</td>
	<td>
		<input type="submit" value="<?php echo lang_get( 'update_custom_field_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border-center">
	<form method="post" action="manage_proj_custom_field_delete.php">
	<input type="hidden" name="f_field_id" value="<?php echo $f_field_id ?>" />
	<input type="submit" value="<?php echo lang_get( 'delete_custom_field_button' ) ?>" />
	</form>
</div>

<?php print_page_bot1( __FILE__ ) ?>
