<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_edit_page.php,v 1.53 2003-02-08 22:47:00 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path . 'category_api.php' );
	require_once( $t_core_path . 'version_api.php' );
	require_once( $t_core_path . 'custom_field_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_project_threshold' ) );

	$f_project_id	= gpc_get_int( 'project_id' );

	$row = project_get_row( $f_project_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

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
		<input type="text" name="name" size="64" maxlength="128" value="<?php echo string_edit_text( $row['name'] ) ?>" />
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
		<input type="text" name="file_path" size="70" maxlength="250" value="<?php echo string_edit_text( $row['file_path'] ) ?>" />
	</td>
</tr>
<?php } ?>

<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="5" wrap="virtual"><?php echo string_edit_textarea( $row['description'] ) ?></textarea>
	</td>
</tr>

<!-- Submit Button -->
<tr>
	<td>&nbsp;</td>
	<td>
		<input type="submit" value="<?php echo lang_get( 'update_project_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>


<br />



<!-- PROJECT DELETE -->
<?php if ( access_level_check_greater_or_equal ( config_get( 'delete_project_threshold' ) ) ) { ?>
<div class="border-center">
	<form method="post" action="manage_proj_delete.php">
		<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
		<input type="submit" value="<?php echo lang_get( 'delete_project_button' ) ?>" />
	</form>
</div>
<?php } ?>


<br />



<!-- PROJECT CATEGORIES -->
<div align="center">
<table class="width75" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'categories' ) ?>
	</td>
</tr>

<!-- Repeated Info Row -->
<tr>
	<td width="100%">
		<table width="100%" cellspacing="1">
		<?php
			$rows = category_get_all_rows( $f_project_id );

			foreach ( $rows as $row ) {
				$t_category = $row['category'];
				$t2_category = urlencode( $t_category );

				if ( $row['user_id'] != 0  && user_exists( $row['user_id'] )) {
					$t_user_name = user_get_name( $row['user_id'] );
				} else {
					$t_user_name = '';
				}

		?>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="50%">
				<?php echo string_display( $t_category ) ?>
			</td>
			<td width="25%">
				<?php echo $t_user_name ?>
			</td>
			<td class="center" width="25%">
				<?php
					print_bracket_link( 'manage_proj_cat_edit_page.php?project_id=' . $f_project_id . '&amp;category=' . $t2_category , lang_get( 'edit_link' ) );
					echo '&nbsp;';
					print_bracket_link( 'manage_proj_cat_delete.php?project_id=' . $f_project_id . '&amp;category=' . $t2_category, lang_get( 'delete_link' ) );
				?>
			</td>
		</tr>
		<?php 	} # end for loop ?>
		</table>
	</td>
</tr>

<!-- Add Category Form -->
<tr>
	<td class="left">
		<form method="post" action="manage_proj_cat_add.php">
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<input type="text" name="category" size="32" maxlength="64" />
			<input type="submit" value="<?php echo lang_get( 'add_category_button' ) ?>" />
		</form>
	</td>
</tr>

<!-- Copy Categories Form -->
<tr>
	<td class="left">
		<form method="post" action="manage_proj_cat_copy.php">
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<select name="other_project_id">
				<?php print_project_option_list( null, false ) ?>
			</select>
			<input type="submit" name="copy_from" value="<?php echo lang_get( 'copy_categories_from' ) ?>" />
			<input type="submit" name="copy_to" value="<?php echo lang_get( 'copy_categories_to' ) ?>" />
		</form>
	</td>
</tr>
</table>


<br />



<!-- PROJECT VERSIONS -->
<table class="width75" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'versions' ) ?>
	</td>
</tr>

<!-- Repeated Info Rows -->
<tr>
	<td width="100%">
		<table width="100%">
		<?php
			$rows = version_get_all_rows( $f_project_id );
			foreach ( $rows as $row ) {
				$t_version = $row['version'];
				$t2_version = urlencode( $t_version );
				$t_date_order = $row['date_order'];
				$t2_date_order = urlencode( $t_date_order );

		?>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="50%">
				<?php echo $t_version ?>
			</td>
			<td class="center" width="25%">
				<?php echo $t_date_order ?>
			</td>
			<td class="center" width="25%">
				<?php
					print_bracket_link( 'manage_proj_ver_edit_page.php?project_id=' . $f_project_id . '&amp;version=' . $t2_version . '&amp;date_order=' . $t2_date_order, lang_get( 'edit_link' ) );
					echo '&nbsp;';
					print_bracket_link( 'manage_proj_ver_delete.php?project_id=' . $f_project_id . '&amp;version=' . $t2_version . '&amp;date_order=' . $t2_date_order, lang_get( 'delete_link' ) );
				?>
			</td>
		</tr>
		<?php } # end for loop ?>
		</table>
	</td>
</tr>

<!-- Version Add Form -->
<tr>
	<td class="left">
		<form method="post" action="manage_proj_ver_add.php">
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<input type="text" name="version" size="32" maxlength="64" />
			<input type="submit" value="<?php echo lang_get( 'add_version_button' ) ?>" />
		</form>
	</td>
</tr>
</table>
</div>


<!-- PROJECT CUSTOM FIELD -->
<?php if( ON == config_get( 'use_experimental_custom_fields' ) ) { ?>
<?php
if ( access_level_check_greater_or_equal( config_get( 'custom_field_link_threshold' ) ) ) {
?>
	<br />
	<div align="center">
	<table class="width75" cellspacing="1">
	<tr>
		<td class="form-title" colspan="2">
			<?php echo lang_get( 'custom_fields_setup' ) ?>
		</td>
	</tr>
	<tr class="row-category">
		<td width="100%">
			<?php echo lang_get( 'custom_fields' ) ?>
		</td>
	</tr>
	<tr>
		<td width="100%">
			<table width="100%" cellspacing="1">
			<?php
				$t_custom_fields = custom_field_get_linked_ids( $f_project_id );

				foreach( $t_custom_fields as $t_field_id ) {
					$t_desc = custom_field_get_definition( $t_field_id );
			?>
			<tr <?php echo helper_alternate_class() ?>>
				<td width="50%">
					<?php echo $t_desc['name'] ?>
				</td>
				<td width="25%">
					<?php echo $t_field_id ?>
				</td>
				<td class="center" width="25%">
					<?php
						if ( access_level_check_greater_or_equal( config_get( 'manage_custom_fields' ) ) ) {
							print_bracket_link( "manage_custom_field_edit_page.php?field_id=$t_field_id&amp;return=manage_proj_edit_page.php?project_id=$f_project_id", lang_get( 'edit_link' ) );
							echo '&nbsp;';
						}
						print_bracket_link( "manage_proj_custom_field_remove.php?field_id=$t_field_id&amp;project_id=$f_project_id", lang_get( 'remove_link' ) );
					?>
				</td>
			</tr>
			<?php 	} # end for loop ?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="left">
			<form method="post" action="manage_proj_custom_field_add_existing.php">
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<select name="field_id">
				<?php
					$t_custom_fields = custom_field_get_ids();

					foreach( $t_custom_fields as $t_field_id )
					{
						if( !custom_field_is_linked( $t_field_id, $f_project_id ) ) {
							$t_desc = custom_field_get_definition( $t_field_id );
							echo "<option value=\"$t_field_id\">" . $t_desc['name'] . '</option>' ;
						}
					}
				?>
			</select>
			<input type="submit" value="<?php echo lang_get( 'add_existing_custom_field' ) ?>" />
			</form>
		</td>
	</tr>
	</table>
	</div>
<?php
}
?>
<?php } // ON = config_get( 'use_experimental_custom_fields' ) ?>

<?php print_page_bot1( __FILE__ ) ?>
