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

	$f_project_id = gpc_get_int( 'f_project_id' );
	$f_action = gpc_get_string( 'f_action', '' );

	# If Deleteing item redirect to delete script
	if ( 'delete' == $f_action ) {
		print_header_redirect( 'manage_proj_delete.php?f_project_id='.$f_project_id );
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
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<form method="post" action="manage_proj_update.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<?php echo $s_edit_project_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_project_name ?>
	</td>
	<td width="75%">
		<input type="text" name="f_name" size="64" maxlength="128" value="<?php echo $v_name ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_status ?>
	</td>
	<td>
		<select name="f_status">
		<?php print_enum_string_option_list( 'project_status', $v_status ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_enabled ?>
	</td>
	<td>
		<input type="checkbox" name="f_enabled" <?php check_checked( $v_enabled, ON ); ?> />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_view_status ?>
	</td>
	<td>
		<select name="f_view_state">
			<?php print_enum_string_option_list( 'view_state', $v_view_state) ?>
		</select>
	</td>
</tr>
<?php
	if ( config_get( 'allow_file_upload' ) ) {
	?>
		<tr class="row-1">
			<td class="category">
				<?php echo $s_upload_file_path ?>
			</td>
			<td>
				<input type="text" name="f_file_path" size="70" maxlength="250" value="<?php echo $v_file_path ?>" />
			</td>
		</tr>
		<?php
	}
?>
<tr class="row-2">
	<td class="category">
		<?php echo $s_description ?>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="5" wrap="virtual"><?php echo $v_description ?></textarea>
	</td>
</tr>
<tr>
	<td class="left">
		<input type="submit" value="<?php echo $s_update_project_button ?>" />
		</form>
	</td>
	<td class="right">
       <?php if ( access_level_check_greater_or_equal ( ADMINISTRATOR ) ) { ?>
		<form method="post" action="manage_proj_delete_page.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="submit" value="<?php echo $s_delete_project_button ?>" />
		</form>
	   <?php } ?>
	</td>
</tr>
</table>
</div>

<br />
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_categories_and_version_title ?>
	</td>
</tr>
<tr class="row-category">
	<td width="100%">
		<?php echo $s_categories ?>
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

				# alternate row colors
				$t_bgcolor = alternate_colors( $i );
		?>
		<tr>
			<td width="50%" bgcolor="<?php echo $t_bgcolor ?>">
				<?php echo $t_category ?>
			</td>
			<td width="25%" bgcolor="<?php echo $t_bgcolor ?>">
				<?php echo $c_user_name ?>
			</td>
			<td class="center" width="25%" bgcolor="<?php echo $t_bgcolor ?>">
				<?php
					print_bracket_link( 'manage_proj_cat_edit_page.php?f_project_id='.$f_project_id.'&amp;f_category='.$t2_category.'&amp;f_assigned_to='.$c_user_id, $s_edit_link );
					PRINT '&nbsp;';
					print_bracket_link( 'manage_proj_cat_del_page.php?f_project_id='.$f_project_id.'&amp;f_category='.$t2_category, $s_delete_link );
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
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="text" name="f_category" size="32" maxlength="64" />
		<input type="submit" value="<?php echo $s_add_category_button ?>" />
		</form>
	</td>
</tr>
<tr>
	<td class="left">
		<form method="post" action="manage_proj_cat_copy.php">
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<select name="f_other_project_id">
			<?php print_project_option_list() ?>
		</select>
		<input type="submit" name="f_copy_from" value="<?php echo $s_copy_categories_from ?>" />
		<input type="submit" name="f_copy_to" value="<?php echo $s_copy_categories_to ?>" />
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
		<?php echo $s_versions ?>
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

				# alternate row colors
				$t_bgcolor = alternate_colors( $i );
		?>
		<tr>
			<td width="50%" bgcolor="<?php echo $t_bgcolor ?>">
				<?php echo $t_version ?>
			</td>
			<td class="center" width="25%" bgcolor="<?php echo $t_bgcolor ?>">
				<?php echo $t_date_order ?>
			</td>
			<td class="center" width="25%" bgcolor="<?php echo $t_bgcolor ?>">
				<?php
					print_bracket_link( 'manage_proj_ver_edit_page.php?f_project_id='.$f_project_id.'&amp;f_version='.$t2_version.'&amp;f_date_order='.$t2_date_order, $s_edit_link );
					PRINT '&nbsp;';
					print_bracket_link( 'manage_proj_ver_del_page.php?f_project_id='.$f_project_id.'&amp;f_version='.$t2_version.'&amp;f_date_order='.$t2_date_order, $s_delete_link );
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
		<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>" />
		<input type="text" name="f_version" size="32" maxlength="64" />
		<input type="submit" value="<?php echo $s_add_version_button ?>" />
		</form>
	</td>
</tr>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>
