<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	if ( !isset( $f_action ) ) {
			$f_action = "";
	}

	# If Deleteing item redirect to delete script
	if ( "delete" == $f_action ) {
		print_header_redirect( "$g_project_delete_page?f_project_id=$f_project_id" );
		exit;
	}

	$query = "SELECT *
			FROM $g_mantis_project_table
			WHERE id='$f_project_id'";
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );

	$v_name 		= string_edit_text( $v_name );
	$v_description 	= string_edit_textarea( $v_description );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php print_manage_menu( $g_manage_project_edit_page ) ?>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="<?php echo $g_manage_project_update ?>">
<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
<tr>
	<td class="form-title" colspan="2">
		<?php echo $s_edit_project_title ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo $s_project_name ?>
	</td>
	<td width="75%">
		<input type="text" name="f_name" size="64" maxlength="128" value="<?php echo $v_name ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_status ?>
	</td>
	<td>
		<select name="f_status">
		<?php print_enum_string_option_list( "project_status", $v_status ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_enabled ?>
	</td>
	<td>
		<input type="checkbox" name="f_enabled" <?php if ( ON == $v_enabled ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_view_status ?>
	</td>
	<td>
		<input type="radio" name="f_view_state" value="10" <?php if ( PUBLIC == $v_view_state ) echo "CHECKED" ?>> <?php echo $s_public ?>
		<input type="radio" name="f_view_state" value="50" <?php if ( PRIVATE == $v_view_state ) echo "CHECKED" ?>> <?php echo $s_private ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_upload_file_path ?>
	</td>
	<td>
		<input type="text" name="f_file_path" size="70" maxlength="250" value="<?php echo $v_file_path ?>">
	</td>
</tr>
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
		<input type="submit" value="<?php echo $s_update_project_button ?>">
	</td>
	</form>
	<form method="post" action="<?php echo $g_manage_project_delete_page?>">
	<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
	<td class="right">
		<input type="submit" value="<?php echo $s_delete_project_button ?>">
	</td>
	</form>
</tr>
</table>
</div>

<p>
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
			$query = "SELECT category
					FROM $g_mantis_project_category_table
					WHERE project_id='$f_project_id'
					ORDER BY category";
			$result = db_query( $query );
			$category_count = db_num_rows( $result );
			for ($i=0;$i<$category_count;$i++) {
				$row = db_fetch_array( $result );
				$t_category = $row["category"];
				$t2_category = urlencode( $t_category );

				# alternate row colors
				$t_bgcolor = alternate_colors( $i );
		?>
		<tr>
			<td width="75%" bgcolor="<?php echo $t_bgcolor ?>">
				<?php echo $t_category ?>
			</td>
			<td class="center" width="25%" bgcolor="<?php echo $t_bgcolor ?>">
				<?php print_bracket_link( $g_manage_project_category_edit_page."?f_project_id=".$f_project_id."&f_category=".$t2_category, $s_edit_link ) ?>
			</td>
		</tr>
		<?php 	} # end for loop ?>
		</table>
	</td>
</tr>
<tr>
<form method="post" action="<?php echo $g_manage_project_category_add ?>">
<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
	<td class="left">
		<input type="text" name="f_category" size="32" maxlength="64">
		<input type="submit" value="<?php echo $s_add_category_button ?>">
	</td>
</form>
</tr>
<tr>
<form method="post" action="<?php echo $g_manage_project_category_copy ?>">
<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
	<td class="left">
		<select name="f_new_project_id">
			<?php print_project_option_list() ?>
		</select>
		<input type="submit" value="<?php echo $s_copy_categories ?>">
	</td>
</form>
</tr>
<tr>
	<td>
		<hr>
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
			$query = "SELECT version, date_order
					FROM $g_mantis_project_version_table
					WHERE project_id='$f_project_id'
					ORDER BY date_order DESC";
			$result = db_query( $query );
			$version_count = db_num_rows( $result );
			for ($i=0;$i<$version_count;$i++) {
				$row = db_fetch_array( $result );
				$t_version = $row["version"];
				$t2_version = urlencode( $t_version );
				$t_date_order = $row["date_order"];
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
				<?php print_bracket_link( $g_manage_project_version_edit_page."?f_project_id=".$f_project_id."&f_version=".$t2_version."&f_date_order=".$t2_date_order, $s_edit_link ) ?>
			</td>
		</tr>
		<?php } # end for loop ?>
		</table>
	</td>
</tr>
<tr>
<form method="post" action="<?php echo $g_manage_project_version_add ?>">
<input type="hidden" name="f_project_id" value="<?php echo $f_project_id ?>">
	<td class="center">
		<input type="text" name="f_version" size="32" maxlength="64">
		<input type="submit" value="<?php echo $s_add_version_button ?>">
	</td>
</form>
</tr>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>