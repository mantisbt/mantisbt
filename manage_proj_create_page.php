<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: manage_proj_create_page.php,v 1.14.2.1 2007-10-13 22:33:34 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'create_project_threshold' ) );

	html_page_top1();
	html_page_top2();

	print_manage_menu( 'manage_proj_create_page.php' );

	$f_parent_id = gpc_get( 'parent_id', null );
?>

<br />
<div align="center">
<form method="post" action="manage_proj_create.php">
<?php 
	echo form_security_field( 'manage_proj_create' );
	if ( null !== $f_parent_id ) { 
		$f_parent_id = (int) $f_parent_id;
?>
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
		<textarea name="description" cols="60" rows="5"></textarea>
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
