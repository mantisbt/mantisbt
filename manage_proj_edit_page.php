<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_edit_page.php,v 1.77 2004-05-27 23:53:10 int2str Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path . 'category_api.php' );
	require_once( $t_core_path . 'version_api.php' );
	require_once( $t_core_path . 'custom_field_api.php' );
	require_once( $t_core_path . 'icon_api.php' );
?>
<?php
	$f_project_id = gpc_get_int( 'project_id' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	$row = project_get_row( $f_project_id );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<?php print_manage_menu( 'manage_proj_edit_page.php' ) ?>

<br />


<!-- PROJECT PROPERTIES -->
<div align="center">
<form method="post" action="manage_proj_update.php">
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
		<textarea name="description" cols="60" rows="5" wrap="virtual"><?php echo string_textarea( $row['description'] ) ?></textarea>
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

<!-- PROJECT CATEGORIES -->
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

					print_bracket_link( 'manage_proj_cat_edit_page.php?project_id=' . $f_project_id . '&amp;category=' . $t_name, lang_get( 'edit_link' ) );
					echo ' ';
					print_bracket_link( 'manage_proj_cat_delete.php?project_id=' . $f_project_id . '&amp;category=' . $t_name, lang_get( 'delete_link' ) );
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
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<select name="other_project_id">
				<?php print_project_option_list( null, false ) ?>
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
		$t_date_order = $t_version['date_order'];
		$t_date_formatted = string_format_complete_date( $t_version['date_order'] );
?>
<!-- Repeated Info Rows -->
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<?php echo string_display( $t_name ) ?>
			</td>
			<td class="center">
				<?php echo $t_date_formatted ?>
			</td>
			<td class="center">
				<?php
					$t_name = urlencode( $t_name );
					$t_date_order = urlencode( $t_date_order );

					print_bracket_link( 'manage_proj_ver_edit_page.php?project_id=' . $f_project_id . '&amp;version=' . $t_name . '&amp;date_order=' . $t_date_order, lang_get( 'edit_link' ) );
					echo '&nbsp;';
					print_bracket_link( 'manage_proj_ver_delete.php?project_id=' . $f_project_id . '&amp;version=' . $t_name . '&amp;date_order=' . $t_date_order, lang_get( 'delete_link' ) );
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
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<input type="text" name="version" size="32" maxlength="64" />
			<input type="submit" class="button" value="<?php echo lang_get( 'add_version_button' ) ?>" />
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
		}

		foreach( $t_custom_fields as $t_field_id ) {
			$t_desc = custom_field_get_definition( $t_field_id );
	?>
			<tr <?php echo helper_alternate_class() ?>>
				<td>
					<?php echo $t_desc['name'] ?>
				</td>
				<td>
<form method="post" action="manage_proj_custom_field_update.php">
	<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
	<input type="hidden" name="field_id" value="<?php echo $t_field_id ?>" />
	<input type="text" name="sequence" value="<?php echo custom_field_get_sequence( $t_field_id, $f_project_id ) ?>" size="2" />
	<input type="submit" class="button" value="<?php echo lang_get( 'update' ) ?>" />
</form>
				</td>
				<td class="center">
				<?php
					# You need global permissions to edit custom field defs
					print_bracket_link( "manage_proj_custom_field_remove.php?field_id=$t_field_id&amp;project_id=$f_project_id", lang_get( 'remove_link' ) );
				?>
				</td>
			</tr>
	<?php
		} # end for loop
	?>
	<tr>
		<td class="left" colspan="3">
			<form method="post" action="manage_proj_custom_field_add_existing.php">
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
	<form method="post" action="manage_proj_user_add.php">
		<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
		<table class="width75" cellspacing="1">
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
				<td class="category"> &nbsp; </td>
			</tr>
			<tr class="row-1" valign="top">
				<td>
					<select name="user_id[]" multiple="multiple" size="10">
						<?php print_project_user_list_option_list( $f_project_id ) ?>
					</select>
				</td>
				<td>
					<select name="access_level">
						<?php # No administrator choice ?>
						<?php print_project_access_levels_option_list( config_get( 'default_new_account_access_level' ) ) ?>
					</select>
				</td>
				<td>
					<input type="submit" class="button" value="<?php echo lang_get( 'add_user_button' ) ?>" />
				</td>
			</tr>
		</table>
	</form>
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

	# reset the class counter
	helper_alternate_class( 0 );

	foreach ( $t_users as $t_user ) {
?>
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<?php
					echo $t_user['username'];
					if ( isset( $t_user['realname'] ) && $t_user['realname'] > "" ) {
						echo " (" . $t_user['realname'] . ")";
					}
				?>
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
						print_bracket_link( 'manage_proj_user_remove.php?project_id=' . $f_project_id . '&amp;user_id=' . $t_user['id'], lang_get( 'remove_link' ) );
					}
				}
			?>
			</td>
		</tr>
<?php
	}  # end for
?>
	</table>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
