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
	# $Id: manage_proj_edit_page.php,v 1.104.2.2 2007-10-19 07:25:59 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'category_api.php' );
	require_once( $t_core_path . 'version_api.php' );
	require_once( $t_core_path . 'custom_field_api.php' );
	require_once( $t_core_path . 'icon_api.php' );

	auth_reauthenticate();

	$f_project_id = gpc_get_int( 'project_id' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	$row = project_get_row( $f_project_id );
	
	html_page_top1( project_get_field( $f_project_id, 'name' ) );
	html_page_top2();

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
	<td>
		<input type="text" name="file_path" size="50" maxlength="250" value="<?php echo string_attribute( $row['file_path'] ) ?>" />
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

<!-- Submit Button -->
<tr>
	<td>&nbsp;</td>
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
<div class="border-center">
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
		<?php echo lang_get( 'subprojects' ) ?>
                <?php
	                # Check the user's global access level before allowing project creation
	                if ( access_has_global_level ( config_get( 'create_project_threshold' ) ) ) {
	                        print_button( 'manage_proj_create_page.php?parent_id=' . $f_project_id, lang_get( 'create_new_subproject_link' ) );
	                }
                ?>
	</td>
</tr>

<!-- Subprojects -->
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
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td width="30%">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td width="20%">
		<?php echo lang_get( 'actions' ) ?>
	</td>
</tr>

<?php
		foreach ( $t_subproject_ids as $t_subproject_id ) {
			$t_subproject = project_get_row( $t_subproject_id );
?>
<tr <?php echo helper_alternate_class() ?>>
	<td>
		<a href="manage_proj_edit_page.php?project_id=<?php echo $t_subproject['id'] ?>"><?php echo string_display( $t_subproject['name'] ) ?></a>
	</td>
	<td>
		<?php echo get_enum_element( 'project_status', $t_subproject['status'] ) ?>
	</td>
	<td>
		<?php echo trans_bool( $t_subproject['enabled'] ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'project_view_state', $t_subproject['view_state'] ) ?>
	</td>
	<td>
		<?php echo string_display_links( $t_subproject['description'] ) ?>
	</td>
	<td class="center">
		<?php
				print_button( 'manage_proj_edit_page.php?project_id=' . $t_subproject['id'], lang_get( 'edit_link' ) );
				echo '&nbsp;';
				print_button( 'manage_proj_subproj_delete.php?project_id=' . $f_project_id . '&amp;subproject_id=' . $t_subproject['id'] . form_security_param( 'manage_proj_subproj_delete' ), lang_get( 'unlink_link' ) );
		?>
	</td>
</tr>
<?php
		} # End of foreach loop over subprojects
	} # End of hiding subproject listing if there are no subprojects
?>

<!-- Add subproject -->
<tr>
	<td class="left" colspan="2">
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
            ! access_has_project_level( $t_manage_access, $t_project['id'] ) ) {
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
		$t_name = $t_category['category'];

		if ( NO_USER != $t_category['user_id'] && user_exists( $t_category['user_id'] )) {
			$t_user_name = user_get_name( $t_category['user_id'] );
		} else {
			$t_user_name = '';
		}
?>
<!-- Repeated Info Row -->
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<?php echo string_display( $t_name ) ?>
			</td>
			<td>
				<?php echo $t_user_name ?>
			</td>
			<td class="center">
				<?php
					$t_name = urlencode( $t_name );

					print_button( 'manage_proj_cat_edit_page.php?project_id=' . $f_project_id . '&amp;category=' . $t_name, lang_get( 'edit_link' ) );
					echo '&nbsp;';
					print_button( 'manage_proj_cat_delete.php?project_id=' . $f_project_id . '&amp;category=' . $t_name . form_security_param( 'manage_proj_cat_delete' ), lang_get( 'delete_link' ) );
				?>
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
			<input type="text" name="category" size="32" maxlength="64" />
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
	$t_versions = version_get_all_rows( $f_project_id );

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
				<?php echo lang_get( 'timestamp' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'actions' ) ?>
			</td>
		</tr>
<?php
	}

	foreach ( $t_versions as $t_version ) {
		$t_name = $t_version['version'];
		$t_released = $t_version['released'];
		$t_date_order = $t_version['date_order'];
		$t_date_formatted = string_format_complete_date( $t_version['date_order'] );
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
				<?php echo $t_date_formatted ?>
			</td>
			<td class="center">
				<?php
					$t_version_id = version_get_id( $t_name, $f_project_id );

					print_button( 'manage_proj_ver_edit_page.php?version_id=' . $t_version_id, lang_get( 'edit_link' ) );
					echo '&nbsp;';
					print_button( 'manage_proj_ver_delete.php?version_id=' . $t_version_id . form_security_param( 'manage_proj_ver_delete' ), lang_get( 'delete_link' ) );
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

		$t_custom_field_security = form_security_field( 'manage_proj_custom_field_update' );

		foreach( $t_custom_fields as $t_field_id ) {
			$t_desc = custom_field_get_definition( $t_field_id );
	?>
			<tr <?php echo helper_alternate_class() ?>>
				<td>
					<?php echo string_display( $t_desc['name'] ) ?>
				</td>
				<td>
<form method="post" action="manage_proj_custom_field_update.php">
	<?php echo $t_custom_field_security ?>
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
					$t_remove_token = form_security_param( 'manage_proj_custom_field_remove' );
					print_button( "manage_proj_custom_field_remove.php?field_id={$t_field_id}&amp;project_id={$f_project_id}$t_remove_token", lang_get( 'remove_link' ) );
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
if ( access_has_project_level( config_get( 'project_user_threshold' ), $f_project_id ) ) {
?>
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
				<td class="category">&nbsp;  </td>
			</tr>
			<tr class="row-1" valign="top">
				<td>
					<select name="user_id[]" multiple="multiple" size="10">
						<?php print_project_user_list_option_list( $f_project_id ) ?>
					</select>
				</td>
				<td>
					<select name="access_level">
						<?php # only access levels that are less than or equal current user access level for current project ?>
						<?php print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ), $f_project_id ) ?>
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
	$t_users = project_get_all_user_rows( $f_project_id );
	$t_display = array();
	$t_sort = array();
	foreach ( $t_users as $t_user ) {
		$t_user_name = string_attribute( $t_user['username'] );
		$t_sort_name = strtolower( $t_user_name );
		if ( ( isset( $t_user['realname'] ) ) && ( $t_user['realname'] > "" ) && ( ON == config_get( 'show_realname' ) ) ){
			$t_user_name = string_attribute( $t_user['realname'] ) . " (" . $t_user_name . ")";
			if ( ON == config_get( 'sort_by_last_name') ) {
				$t_sort_name_bits = split( ' ', strtolower( $t_user_name ), 2 );
				$t_sort_name = $t_sort_name_bits[1] . ', ' . $t_sort_name_bits[1];
			} else {
				$t_sort_name = strtolower( $t_user_name );
			}
		}
		$t_display[] = $t_user_name;
		$t_sort[] = $t_sort_name;
	}
	array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );

	# reset the class counter
	helper_alternate_class( 0 );

	$t_user_remove_security = form_security_param( 'manage_proj_user_remove' );

	for ($i = 0; $i < count( $t_sort ); $i++ ) {
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
				if ( access_has_project_level( config_get( 'project_user_threshold' ), $f_project_id ) ) {
					if ( project_includes_user( $f_project_id, $t_user['id'] )  ) {
						print_button( 'manage_proj_user_remove.php?project_id=' . $f_project_id . '&amp;user_id=' . $t_user['id'] . $t_user_remove_security, lang_get( 'remove_link' ) );
					}
				}
			?>
			</td>
		</tr>
<?php
	}  # end for
?>
	<tr>
	<td>&nbsp;  </td>
	<td>&nbsp;  </td>
	<td>&nbsp;  </td>
	<td class="center">
	<?php
		# You need global or project-specific permissions to remove users
		#  from this project
		if ( access_has_project_level( config_get( 'project_user_threshold' ), $f_project_id ) ) {
			print_button( 'manage_proj_user_remove.php?project_id=' . $f_project_id . $t_user_remove_security, lang_get( 'remove_all_link' ) );
		}
	?>
	</td>
	</tr>
	</table>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
