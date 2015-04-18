<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'create_project_threshold' ) );

	html_page_top();

	print_manage_menu( 'manage_proj_create_page.php' );

	$f_parent_id = gpc_get( 'parent_id', null );
?>

<br />
<!-- PROJECT PROPERTIES -->
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

<!-- Title -->
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

<!-- Name -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="25%">
		<span class="required">*</span><?php echo lang_get( 'project_name' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="name" size="60" maxlength="128" />
	</td>
</tr>

<!-- Status -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td>
		<select name="status">
		<?php print_enum_string_option_list( 'project_status' ) ?>
		</select>
	</td>
</tr>

<!-- Category Inheritance -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'inherit_global' ) ?>
	</td>
	<td>
		<input type="checkbox" name="inherit_global" checked="checked" />
	</td>
</tr>

<?php if ( !is_null( $f_parent_id ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'inherit_parent' ) ?>
	</td>
	<td>
		<input type="checkbox" name="inherit_parent" checked="checked" />
	</td>
</tr>
<?php } ?>

<!-- View Status (public/private) -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state' ) ?>
		</select>
	</td>
</tr>

<!-- File upload path (if uploading is enabled and uploading to disk) -->
<?php
	$g_project_override = ALL_PROJECTS;
	if ( file_is_uploading_enabled() && DATABASE !== config_get( 'file_upload_method' ) ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'upload_file_path' ) ?>
	</td>
<?php
		$t_file_path = '';
		# Don't reveal the absolute path to non-administrators for security reasons
		if ( current_user_is_administrator() ) {
			$t_file_path = config_get( 'absolute_path_default_upload_folder' );
		}
?>
	<td>
		<input type="text" name="file_path" size="60" maxlength="250" value="<?php echo $t_file_path ?>" />
	</td>
</tr>
<?php } ?>

<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="70" rows="5"></textarea>
	</td>
</tr>

<?php event_signal( 'EVENT_MANAGE_PROJECT_CREATE_FORM' ) ?>

<!-- Submit Button -->
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'add_project_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	html_page_bottom();
