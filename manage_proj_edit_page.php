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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'category_api.php' );
	require_once( 'version_api.php' );
	require_once( 'custom_field_api.php' );
	require_once( 'icon_api.php' );

	auth_reauthenticate();

	$f_project_id = gpc_get_int( 'project_id' );
	$f_show_global_users = gpc_get_bool( 'show_global_users' );

	project_ensure_exists( $f_project_id );
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	$row = project_get_row( $f_project_id );

	$t_can_manage_users = access_has_project_level( config_get( 'project_user_threshold' ), $f_project_id );

	html_page_top( project_get_field( $f_project_id, 'name' ) );

	print_manage_menu( 'manage_proj_edit_page.php' );
?>
<br />
<!-- PROJECT PROPERTIES -->
<div align="center">
<form method="post" action="manage_proj_update.php">
<?php echo form_security_field( 'manage_proj_update' ) ?>
<table class="width75" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
		<?php echo lang_get( 'edit_project_title' ) ?>
	</td>
</tr>

<!-- Name -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="25%">
		<?php echo lang_get( 'project_name' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="name" size="50" maxlength="128" value="<?php echo string_attribute( $row['name'] ) ?>" />
	</td>
</tr>

<!-- Status -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td>
		<select name="status">
		<?php print_enum_string_option_list( 'project_status', $row['status'] ) ?>
		</select>
	</td>
</tr>

<!-- Enabled -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'enabled' ) ?>
	</td>
	<td>
		<input type="checkbox" name="enabled" <?php check_checked( $row['enabled'], ON ); ?> />
	</td>
</tr>

<!-- Category Inheritance -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'inherit_global' ) ?>
	</td>
	<td>
		<input type="checkbox" name="inherit_global" <?php check_checked( $row['inherit_global'], ON ); ?> />
	</td>
</tr>

<!-- View Status (public/private) -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state', $row['view_state']) ?>
		</select>
	</td>
</tr>

<!-- File upload path (if uploading is enabled) -->
<?php if ( file_is_uploading_enabled() ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'upload_file_path' ) ?>
	</td>
<?php
	$t_file_path = $row['file_path'];
	# Don't reveal the absolute path to non-administrators for security reasons
	if ( is_blank( $t_file_path ) && current_user_is_administrator() ) {
		$t_file_path = config_get( 'absolute_path_default_upload_folder' );
	}
?>
	<td>
		<input type="text" name="file_path" size="50" maxlength="250" value="<?php echo string_attribute( $t_file_path ) ?>" />
	</td>
</tr>
<?php } ?>

<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="5"><?php echo string_textarea( $row['description'] ) ?></textarea>
	</td>
</tr>

<?php event_signal( 'EVENT_MANAGE_PROJECT_UPDATE_FORM', array( $f_project_id ) ); ?>

<!-- Submit Button -->
<tr>
	<td>&#160;</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_project_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<!-- PROJECT DELETE -->
<?php
# You must have global permissions to delete projects
if ( access_has_global_level ( config_get( 'delete_project_threshold' ) ) ) { ?>
<div class="border center">
	<form method="post" action="manage_proj_delete.php">
		<?php echo form_security_field( 'manage_proj_delete' ) ?>
		<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'delete_project_button' ) ?>" />
	</form>
</div>
<?php } ?>

<br />

<?php
	# reset the class counter
	helper_alternate_class( 0 );
?>

<!-- SUBPROJECTS -->
<div align="center">
<table class="width75" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="6">
		<?php
			echo lang_get( 'subprojects' );

			# Check the user's global access level before allowing project creation
			if ( access_has_global_level ( config_get( 'create_project_threshold' ) ) ) {
				print_button( 'manage_proj_create_page.php?parent_id=' . $f_project_id, lang_get( 'create_new_subproject_link' ) );
			}
		?>
	</td>
</tr>

<!-- Subprojects -->
<form name="update_children_form" action="manage_proj_update_children.php" method="post">
<?php echo form_security_field( 'manage_proj_update_children' ) ?>
<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
<?php
	$t_subproject_ids = current_user_get_accessible_subprojects( $f_project_id, /* show_disabled */ true );

	if ( Array() != $t_subproject_ids ) {
?>
<tr class="row-category">
	<td width="20%">
		<?php echo lang_get( 'name' ) ?>
	</td>
	<td width="10%">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td width="10%">
		<?php echo lang_get( 'enabled' ) ?>
	</td>
	<td width="10%">
		<?php echo lang_get( 'inherit' ) ?>
	</td>
	<td width="10%">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'actions' ) ?>
	</td>
</tr>

<?php
		foreach ( $t_subproject_ids as $t_subproject_id ) {
			$t_subproject = project_get_row( $t_subproject_id );
			$t_inherit_parent = project_hierarchy_inherit_parent( $t_subproject_id, $f_project_id, true );
?>
<tr <?php echo helper_alternate_class() ?>>
	<td>
		<a href="manage_proj_edit_page.php?project_id=<?php echo $t_subproject['id'] ?>"><?php echo string_display( $t_subproject['name'] ) ?></a>
	</td>
	<td class="center">
		<?php echo get_enum_element( 'project_status', $t_subproject['status'] ) ?>
	</td>
	<td class="center">
		<?php echo trans_bool( $t_subproject['enabled'] ) ?>
	</td>
	<td class="center">
		<input type="checkbox" name="inherit_child_<?php echo $t_subproject_id ?>" <?php echo ( $t_inherit_parent ? 'checked="checked"' : '' ) ?> />
	</td>
	<td class="center">
		<?php echo get_enum_element( 'project_view_state', $t_subproject['view_state'] ) ?>
	</td>
	<td>
		<?php echo string_display_links( $t_subproject['description'] ) ?>
	</td>
	<td class="center">
		<?php
				print_bracket_link( 'manage_proj_edit_page.php?project_id=' . $t_subproject['id'], lang_get( 'edit_link' ) );
				print_bracket_link( "manage_proj_subproj_delete.php?project_id=$f_project_id&subproject_id=" . $t_subproject['id'] . form_security_param( 'manage_proj_subproj_delete' ), lang_get( 'unlink_link' ) );
		?>
	</td>
</tr>
<?php
		} # End of foreach loop over subprojects
	} # End of hiding subproject listing if there are no subprojects
?>

<tr>
	<td colspan="6">
	<input type="submit" value="<?php echo lang_get( 'update_subproject_inheritance' ) ?>" />
		</form>
	</td>
</tr>

<!-- Add subproject -->
<tr>
	<td colspan="7">
		<form method="post" action="manage_proj_subproj_add.php">
			<?php echo form_security_field( 'manage_proj_subproj_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<select name="subproject_id">
<?php
	$t_all_subprojects = project_hierarchy_get_subprojects( $f_project_id, /* $p_show_disabled */ true );
	$t_all_subprojects[] = $f_project_id;
	$t_manage_access = config_get( 'manage_project_threshold' );

	$t_projects = project_get_all_rows();

	$t_projects = multi_sort( $t_projects, 'name', ASCENDING );

	foreach ( $t_projects as $t_project ) {
		if ( in_array( $t_project['id'], $t_all_subprojects ) ||
            in_array( $f_project_id, project_hierarchy_get_all_subprojects( $t_project['id'] ) ) ||
            !access_has_project_level( $t_manage_access, $t_project['id'] ) ) {
                continue;
		}
?>
				<option value="<?php echo $t_project['id'] ?>"><?php echo string_attribute( $t_project['name'] ) ?></option>
<?php
	} # End looping over projects
?>
			</select>
			<input type="submit" value="<?php echo lang_get('add_subproject'); ?>">
		</form>
	</td>
</tr>

</table>
</div>

<br />

<!-- PROJECT CATEGORIES -->
<a name="categories" />
<div align="center">
<table class="width75" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'categories' ) ?>
	</td>
</tr>
<?php
	$t_categories = category_get_all_rows( $f_project_id );

	if ( count( $t_categories ) > 0 ) {
?>
		<tr class="row-category">
			<td>
				<?php echo lang_get( 'category' ) ?>
			</td>
			<td>
				<?php echo lang_get( 'assign_to' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'actions' ) ?>
			</td>
		</tr>
<?php
	}

	foreach ( $t_categories as $t_category ) {
		$t_id = $t_category['id'];

		$t_inherited = ( $t_category['project_id'] != $f_project_id );
?>
<!-- Repeated Info Row -->
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<?php echo string_display( category_full_name( $t_id , /* showProject */ $t_inherited, $f_project_id ) ) ?>
			</td>
			<td>
				<?php echo prepare_user_name( $t_category['user_id'] ) ?>
			</td>
			<td class="center">
				<?php if ( !$t_inherited ) {
					$t_id = urlencode( $t_id );
					$t_project_id = urlencode( $f_project_id );

					print_button( 'manage_proj_cat_edit_page.php?id=' . $t_id . '&project_id=' . $t_project_id, lang_get( 'edit_link' ) );
					echo '&#160;';
					print_button( 'manage_proj_cat_delete.php?id=' . $t_id . '&project_id=' . $t_project_id, lang_get( 'delete_link' ) );
				} ?>
			</td>
		</tr>
<?php
	} # end for loop
?>

<!-- Add Category Form -->
<tr>
	<td class="left" colspan="3">
		<form method="post" action="manage_proj_cat_add.php">
			<?php echo form_security_field( 'manage_proj_cat_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<input type="text" name="name" size="32" maxlength="128" />
			<input type="submit" class="button" value="<?php echo lang_get( 'add_category_button' ) ?>" />
		</form>
	</td>
</tr>

<!-- Copy Categories Form -->
<tr>
	<td class="left" colspan="3">
		<form method="post" action="manage_proj_cat_copy.php">
			<?php echo form_security_field( 'manage_proj_cat_copy' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<select name="other_project_id">
				<?php print_project_option_list( null, false, $f_project_id ); ?>
			</select>
			<input type="submit" name="copy_from" class="button" value="<?php echo lang_get( 'copy_categories_from' ) ?>" />
			<input type="submit" name="copy_to" class="button" value="<?php echo lang_get( 'copy_categories_to' ) ?>" />
		</form>
	</td>
</tr>
</table>

<br />

<?php
	# reset the class counter
	helper_alternate_class( 0 );
?>

<!-- PROJECT VERSIONS -->
<a name="versions" />
<table class="width75" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'versions' ) ?>
	</td>
</tr>
<?php
	$t_versions = version_get_all_rows( $f_project_id, /* released = */ null, /* obsolete = */ null );

	if ( count( $t_versions ) > 0 ) {
?>
		<tr class="row-category">
			<td>
				<?php echo lang_get( 'version' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'released' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'obsolete' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'timestamp' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'actions' ) ?>
			</td>
		</tr>
<?php
	}

	foreach ( $t_versions as $t_version ) {
		if ( $t_version['project_id'] != $f_project_id ) {
			$t_inherited = true;
		} else {
			$t_inherited = false;
		}

		$t_name = version_full_name( $t_version['id'], /* showProject */ $t_inherited, $f_project_id );

		$t_released = $t_version['released'];
		$t_obsolete = $t_version['obsolete'];
		if( !date_is_null( $t_version['date_order'] ) ) {
			$t_date_formatted = date( config_get( 'complete_date_format' ), $t_version['date_order'] );
		} else {
			$t_date_formatted = ' ';
		}
?>
<!-- Repeated Info Rows -->
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<?php echo string_display( $t_name ) ?>
			</td>
			<td class="center">
				<?php echo trans_bool( $t_released ) ?>
			</td>
			<td class="center">
				<?php echo trans_bool( $t_obsolete ) ?>
			</td>
			<td class="center">
				<?php echo $t_date_formatted ?>
			</td>
			<td class="center">
				<?php
					$t_version_id = version_get_id( $t_name, $f_project_id );

					if ( !$t_inherited ) {
						print_button( 'manage_proj_ver_edit_page.php?version_id=' . $t_version_id, lang_get( 'edit_link' ) );
						echo '&#160;';
						print_button( 'manage_proj_ver_delete.php?version_id=' . $t_version_id, lang_get( 'delete_link' ) );
					}
				?>
			</td>
		</tr>
<?php
	} # end for loop
?>

<!-- Version Add Form -->
<tr>
	<td class="left" colspan="3">
		<form method="post" action="manage_proj_ver_add.php">
			<?php echo form_security_field( 'manage_proj_ver_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<input type="text" name="version" size="32" maxlength="64" />
			<input type="submit" name="add_version" class="button" value="<?php echo lang_get( 'add_version_button' ) ?>" />
			<input type="submit" name="add_and_edit_version" class="button" value="<?php echo lang_get( 'add_and_edit_version_button' ) ?>" />
		</form>
	</td>
</tr>
<tr>
	<td class="left" colspan="3">
		<form method="post" action="manage_proj_ver_copy.php">
			<?php echo form_security_field( 'manage_proj_ver_copy' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<select name="other_project_id">
				<?php print_project_option_list( null, false, $f_project_id ); ?>
			</select>
			<input type="submit" name="copy_from" class="button" value="<?php echo lang_get( 'copy_versions_from' ) ?>" />
			<input type="submit" name="copy_to" class="button" value="<?php echo lang_get( 'copy_versions_to' ) ?>" />
		</form>
	</td>
</tr>
</table>
</div>


<?php
	# reset the class counter
	helper_alternate_class( 0 );
?>

<!-- PROJECT CUSTOM FIELD -->
<a name="customfields" />

<?php
# You need either global permissions or project-specific permissions to link
#  custom fields
if ( access_has_project_level( config_get( 'custom_field_link_threshold' ), $f_project_id ) &&
	( count( custom_field_get_ids() ) > 0 ) ) {
?>
	<br />
	<div align="center">
	<table class="width75" cellspacing="1">
	<tr>
		<td class="form-title" colspan="3">
			<?php echo lang_get( 'custom_fields_setup' ) ?>
		</td>
	</tr>
	<?php
		$t_custom_fields = custom_field_get_linked_ids( $f_project_id );

		if ( count( $t_custom_fields ) > 0 ) {
	?>
			<tr class="row-category">
				<td width="50%">
					<?php echo lang_get( 'custom_field' ) ?>
				</td>
				<td width="25%">
					<?php echo lang_get( 'custom_field_sequence' ) ?>
				</td>
				<td class="center" width="25%">
					<?php echo lang_get( 'actions' ); ?>
				</td>
			</tr>
	<?php
		$t_index = 0;

		foreach( $t_custom_fields as $t_field_id ) {
			$t_desc = custom_field_get_definition( $t_field_id );
	?>
			<tr <?php echo helper_alternate_class() ?>>
				<td>
					<?php echo string_display( $t_desc['name'] ) ?>
				</td>
				<td>
<form method="post" action="manage_proj_custom_field_update.php">
	<?php echo form_security_field( 'manage_proj_custom_field_update' ) ?>
	<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
	<input type="hidden" name="field_id" value="<?php echo $t_field_id ?>" />
	<input type="text" name="sequence" value="<?php echo custom_field_get_sequence( $t_field_id, $f_project_id ) ?>" size="2" />
	<input type="submit" class="button-small" value="<?php echo lang_get( 'update' ) ?>" />
</form>
	<?php
		$t_index++;
	?>
				</td>
				<td class="center">
				<?php
					# You need global permissions to edit custom field defs
					print_button( "manage_proj_custom_field_remove.php?field_id=$t_field_id&project_id=$f_project_id", lang_get( 'remove_link' ) );
				?>
				</td>
			</tr>
	<?php
		} # end for loop
		}
?>
	<tr>
		<td class="left" colspan="3">
			<form method="post" action="manage_proj_custom_field_add_existing.php">
			<?php echo form_security_field( 'manage_proj_custom_field_add_existing' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<select name="field_id">
				<?php
					$t_custom_fields = custom_field_get_ids();

					foreach( $t_custom_fields as $t_field_id )
					{
						if( !custom_field_is_linked( $t_field_id, $f_project_id ) ) {
							$t_desc = custom_field_get_definition( $t_field_id );
							echo "<option value=\"$t_field_id\">" . string_attribute( $t_desc['name'] ) . '</option>' ;
						}
					}
				?>
			</select>
			<input type="submit" class="button" value="<?php echo lang_get( 'add_existing_custom_field' ) ?>" />
			</form>
		</td>
	</tr>
	<tr>
		<td class="left" colspan="3">
			<form method="post" action="manage_proj_custom_field_copy.php">
				<?php echo form_security_field( 'manage_proj_custom_field_copy' ) ?>
				<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
				<select name="other_project_id">
					<?php print_project_option_list( null, false, $f_project_id ); ?>
				</select>
				<input type="submit" name="copy_from" class="button" value="<?php echo lang_get( 'copy_from' ) ?>" />
				<input type="submit" name="copy_to" class="button" value="<?php echo lang_get( 'copy_to' ) ?>" />
			</form>
		</td>
	</tr>
	</table>
	</div>
<?php
}

event_signal( 'EVENT_MANAGE_PROJECT_PAGE', array( $f_project_id ) );
?>

<!-- PROJECT VIEW STATUS -->
<br />
<div align="center">
	<table class="width75" cellspacing="1">
		<tr>
			<td class="center">
			<?php
				if ( VS_PUBLIC == project_get_field( $f_project_id, 'view_state' ) ) {
					echo lang_get( 'public_project_msg' );
				} else {
					echo lang_get( 'private_project_msg' );
				}
			?>
			</td>
		</tr>
	</table>
</div>


<!-- USER MANAGEMENT (ADD) -->
<?php
# We want to allow people with global permissions and people with high enough
#  permissions on the project we are editing
if ( $t_can_manage_users ) {
?>
<br />
<br />
<div align="center">
	<table class="width75" cellspacing="1">
		<form method="post" action="manage_proj_user_add.php">
			<?php echo form_security_field( 'manage_proj_user_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<tr>
				<td class="form-title" colspan="5">
					<?php echo lang_get( 'add_user_title' ) ?>
				</td>
			</tr>
			<tr class="row-1" valign="top">
				<td class="category">
					<?php echo lang_get( 'username' ) ?>
				</td>
				<td class="category">
					<?php echo lang_get( 'access_level' ) ?>
				</td>
				<td class="category">&#160;  </td>
			</tr>
			<tr class="row-1" valign="top">
				<td>
					<select name="user_id[]" multiple="multiple" size="10">
						<?php print_project_user_list_option_list( $f_project_id ) ?>
					</select>
				</td>
				<td>
					<select name="access_level">
						<?php
							# only access levels that are less than or equal current user access level for current project
							print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ), $f_project_id );
						?>
					</select>
				</td>
				<td>
					<input type="submit" class="button" value="<?php echo lang_get( 'add_user_button' ) ?>" />
				</td>
			</tr>
		</form>
		<!-- Copy Users Form -->
		<form method="post" action="manage_proj_user_copy.php">
			<?php echo form_security_field( 'manage_proj_user_copy' ) ?>
			<tr>
				<td class="left" colspan="3">
						<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
						<select name="other_project_id">
							<?php print_project_option_list( null, false, $f_project_id ); ?>
						</select>
						<input type="submit" name="copy_from" class="button" value="<?php echo lang_get( 'copy_users_from' ) ?>" />
						<input type="submit" name="copy_to" class="button" value="<?php echo lang_get( 'copy_users_to' ) ?>" />
				</td>
			</tr>
		</form>
	</table>
</div>
<?php
}
?>


<!-- LIST OF USERS -->
<br />
<div align="center">
	<table class="width75" cellspacing="1">
		<tr>
			<td class="form-title" colspan="4">
				<?php echo lang_get( 'manage_accounts_title' ) ?>
			</td>
		</tr>
		<tr class="row-category">
			<td>
				<?php echo lang_get( 'username' ) ?>
			</td>
			<td>
				<?php echo lang_get( 'email' ) ?>
			</td>
			<td>
				<?php echo lang_get( 'access_level' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'actions' ) ?>
			</td>
		</tr>
<?php
	$t_users = project_get_all_user_rows( $f_project_id, ANYBODY, $f_show_global_users );
	$t_display = array();
	$t_sort = array();
	foreach ( $t_users as $t_user ) {
		$t_user_name = string_attribute( $t_user['username'] );
		$t_sort_name = utf8_strtolower( $t_user_name );
		if ( ( isset( $t_user['realname'] ) ) && ( $t_user['realname'] > "" ) && ( ON == config_get( 'show_realname' ) ) ){
			$t_user_name = string_attribute( $t_user['realname'] ) . " (" . $t_user_name . ")";
			if ( ON == config_get( 'sort_by_last_name') ) {
				$t_sort_name_bits = explode( ' ', utf8_strtolower( $t_user_name ), 2 );
				$t_sort_name = $t_sort_name_bits[1] . ', ' . $t_sort_name_bits[1];
			} else {
				$t_sort_name = utf8_strtolower( $t_user_name );
			}
		}
		$t_display[] = $t_user_name;
		$t_sort[] = $t_sort_name;
	}

	array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );

	# reset the class counter
	helper_alternate_class( 0 );

	$t_users_count = count( $t_sort );
	$t_removable_users_exist = false;

	for ( $i = 0; $i < $t_users_count; $i++ ) {
		$t_user = $t_users[$i];
?>
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<?php echo $t_display[$i] ?>
			</td>
			<td>
			<?php
				$t_email = user_get_email( $t_user['id'] );
				print_email_link( $t_email, $t_email );
			?>
			</td>
			<td>
				<?php echo get_enum_element( 'access_levels', $t_user['access_level'] ) ?>
			</td>
			<td class="center">
			<?php
				# You need global or project-specific permissions to remove users
				#  from this project
				if ( $t_can_manage_users && access_has_project_level( $t_user['access_level'], $f_project_id ) ) {
					if ( project_includes_user( $f_project_id, $t_user['id'] )  ) {
						print_button( "manage_proj_user_remove.php?project_id=$f_project_id&user_id=" . $t_user['id'], lang_get( 'remove_link' ) );
						$t_removable_users_exist = true;
					}
				}
			?>
			</td>
		</tr>
<?php
	}  # end for
?>
	<tr>
	<td>&#160;  </td>
	<td>&#160;  </td>
	<td>&#160;  </td>
	<td class="center">
	<?php
		# You need global or project-specific permissions to remove users
		#  from this project
		if ( !$f_show_global_users ) {
			print_button( "manage_proj_edit_page.php?project_id=$f_project_id&show_global_users=true", lang_get( 'show_global_users' ) );
		} else {
			print_button( "manage_proj_edit_page.php?project_id=$f_project_id", lang_get( 'hide_global_users' ) );
		}

		if ( $t_removable_users_exist ) {
			echo '&#160;';
			print_button( "manage_proj_user_remove.php?project_id=$f_project_id", lang_get( 'remove_all_link' ) );
		}
	?>
	</td>
	</tr>
	</table>
</div>

<?php
	html_page_bottom();
