<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
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

	$f_project_id = gpc_get_int( 'project_id' );
	$f_action = gpc_get_string( 'action', '' );

	# If Deleteing item redirect to delete script
	if ( 'delete' == $f_action ) {
		print_header_redirect( 'manage_proj_delete.php?project_id='.$f_project_id );
	}

	$c_project_id = db_prepare_int( $f_project_id );

	$query = "SELECT *
			FROM $g_mantis_project_table
			WHERE id='$c_project_id'";
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

	$v_name 		= string_edit_text( $v_name );
	$v_description 	= string_edit_textarea( $v_description );
	$v_file_path    = string_edit_text( $v_file_path );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu( 'manage_proj_edit_page.php' ) ?>

<br />
<div align="center">
<form method="post" action="manage_proj_update.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
		<?php echo lang_get( 'edit_project_title' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'project_name' ) ?>
	</td>
	<td width="75%">
		<input type="text" name="name" size="64" maxlength="128" value="<?php echo $v_name ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td>
		<select name="status">
		<?php print_enum_string_option_list( 'project_status', $v_status ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'enabled' ) ?>
	</td>
	<td>
		<input type="checkbox" name="enabled" <?php check_checked( $v_enabled, ON ); ?> />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<select name="view_state">
			<?php print_enum_string_option_list( 'view_state', $v_view_state) ?>
		</select>
	</td>
</tr>
<?php
	if ( config_get( 'allow_file_upload' ) ) {
	?>
		<tr class="row-1">
			<td class="category">
				<?php echo lang_get( 'upload_file_path' ) ?>
			</td>
			<td>
				<input type="text" name="file_path" size="70" maxlength="250" value="<?php echo $v_file_path ?>" />
			</td>
		</tr>
		<?php
	}
?>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="5" wrap="virtual"><?php echo $v_description ?></textarea>
	</td>
</tr>
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

<?php if ( access_level_check_greater_or_equal ( ADMINISTRATOR ) ) { ?>
<div class="border-center">
	<form method="post" action="manage_proj_delete.php">
	<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
	<input type="submit" value="<?php echo lang_get( 'delete_project_button' ) ?>" />
	</form>
</div>
<?php } ?>

<br />
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'categories_and_version_title' ) ?>
	</td>
</tr>
<tr class="row-category">
	<td width="100%">
		<?php echo lang_get( 'categories' ) ?>
	</td>
</tr>
<tr>
	<td width="100%">
		<table width="100%" cellspacing="1">
		<?php
			$result = category_get_all( $f_project_id );
			$category_count = db_num_rows( $result );
			for ($i=0;$i<$category_count;$i++) {
				$row = db_fetch_array( $result );
				$t_category = $row['category'];
				$t2_category = urlencode( $t_category );
				$c_user_id = (integer)$row['user_id'];

				if ( $c_user_id != 0 ) {
					$c_user_name = user_get_field( $c_user_id, 'username' );
				} else {
					$c_user_name = '';
				}

		?>
		<tr <?php echo helper_alternate_class() ?>>
			<td width="50%">
				<?php echo $t_category ?>
			</td>
			<td width="25%">
				<?php echo $c_user_name ?>
			</td>
			<td class="center" width="25%">
				<?php
					print_bracket_link( 'manage_proj_cat_edit_page.php?project_id='.$f_project_id.'&amp;category='.$t2_category.'&amp;assigned_to='.$c_user_id, lang_get( 'edit_link' ) );
					PRINT '&nbsp;';
					print_bracket_link( 'manage_proj_cat_delete.php?project_id='.$f_project_id.'&amp;category='.$t2_category, lang_get( 'delete_link' ) );
				?>
			</td>
		</tr>
		<?php 	} # end for loop ?>
		</table>
	</td>
</tr>
<tr>
	<td class="left">
		<form method="post" action="manage_proj_cat_add.php">
		<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
		<input type="text" name="category" size="32" maxlength="64" />
		<input type="submit" value="<?php echo lang_get( 'add_category_button' ) ?>" />
		</form>
	</td>
</tr>
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
<tr>
	<td>
		<hr />
	</td>
</tr>
<tr class="row-category">
	<td width="100%">
		<?php echo lang_get( 'versions' ) ?>
	</td>
</tr>
<tr>
	<td width="100%">
		<table width="100%">
		<?php
			$result = version_get_all( $f_project_id );
			$version_count = db_num_rows( $result );
			for ($i=0;$i<$version_count;$i++) {
				$row = db_fetch_array( $result );
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
					print_bracket_link( 'manage_proj_ver_edit_page.php?project_id='.$f_project_id.'&amp;version='.$t2_version.'&amp;date_order='.$t2_date_order, lang_get( 'edit_link' ) );
					PRINT '&nbsp;';
					print_bracket_link( 'manage_proj_ver_delete.php?project_id='.$f_project_id.'&amp;version='.$t2_version.'&amp;date_order='.$t2_date_order, lang_get( 'delete_link' ) );
				?>
			</td>
		</tr>
		<?php } # end for loop ?>
		</table>
	</td>
</tr>
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
