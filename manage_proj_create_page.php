<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_create_page.php,v 1.10 2005-07-13 20:45:01 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'create_project_threshold' ) );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php print_manage_menu( 'manage_proj_create_page.php' ) ?>

<?php
	$f_parent_id = gpc_get_int( 'parent_id', null );
?>

<br />
<div align="center">
<form method="post" action="manage_proj_create.php">
<?php if ( null !== $f_parent_id ) { ?>
<input type="hidden" name="parent_id" value="<?php echo $f_parent_id ?>">
<?php } ?>
<table class="width75" cellspacing="1">
<tr>
<td class="form-title" colspan="2">
		<?php
			if ( null !== $f_parent_id ) {
				echo lang_get( 'add_subproject_title' );
			} else {
				echo lang_get( 'add_project_title' );
			}
		?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<span class="required">*</span><?php echo lang_get( 'project_name' )?>
	</td>
	<td width="75%">
		<input type="text" name="name" size="64" maxlength="128" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td>
		<select name="status">
		<?php print_enum_string_option_list( 'project_status' ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state' ) ?>
		</select>
	</td>
</tr>
<?php
	if ( config_get( 'allow_file_upload' ) ) {
	?>
		<tr class="row-2">
			<td class="category">
				<?php echo lang_get( 'upload_file_path' ) ?>
			</td>
			<td>
				<input type="text" name="file_path" size="70" maxlength="250" />
			</td>
		</tr>
		<?php
	}
?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="5" wrap="virtual"></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_project_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
