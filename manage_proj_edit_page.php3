<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	if ( !isset( $f_action ) ) {
			$f_action = "";
	}

	### If Deleteing item redirect to delete script
	if ( $f_action=="delete" ) {
		print_header_redirect( "$g_project_delete_page?f_project_id=$f_project_id" );
		exit;
	}

	$query = "SELECT *
			FROM $g_mantis_project_table
			WHERE id='$f_project_id'";
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );

	$v_name 		= string_display( $v_name );
	$v_description 	= string_display( $v_description );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<? print_menu( $g_menu_include_file ) ?>

<? print_manage_menu( $g_manage_project_edit_page ) ?>

<p>
<div align="center">
<table width="75%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<form method="post" action="<? echo $g_manage_project_update ?>">
<input type="hidden" name="f_project_id" value="<? echo $f_project_id ?>">
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table cols="6" width="100%" bgcolor="<? echo $g_white_color ?>">
	<tr>
		<td colspan="6" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_edit_project_title ?></b>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td width="25%">
			<? echo $s_project_name ?>
		</td>
		<td width="75%">
			<input type="text" name="f_name" size="64" maxlength="128" value="<? echo $v_name ?>">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_status ?>
		</td>
		<td>
			<select name="f_status">
			<? print_enum_string_option_list( $s_project_status_enum_string, $v_status ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_enabled ?>
		</td>
		<td>
			<input type="checkbox" name="f_enabled" <? if ( $v_enabled==1 ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_view_status ?>
		</td>
		<td>
			<input type="radio" name="f_view_state" value="10" <? if ($v_view_state=="10") echo "CHECKED" ?>> <? echo $s_public ?>
			<input type="radio" name="f_view_state" value="50" <? if ($v_view_state=="50") echo "CHECKED" ?>> <? echo $s_private ?>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_access_threshold ?>
		</td>
		<td>
			<select name="f_access_min">
			<? print_enum_string_option_list( $s_access_levels_enum_string, $v_access_min ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_upload_file_path ?>
		</td>
		<td>
			<input type="text" name="f_file_path" size="70" maxlength="250" value="<? echo $v_file_path ?>">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_description ?>
		</td>
		<td>
			<textarea name="f_description" cols="60" rows="5" wrap="virtual"><? echo $v_description ?></textarea>
		</td>
	</tr>
	<tr>
		<td align="left" bgcolor="<? echo $g_white_color ?>">
			<input type="submit" value="<? echo $s_update_project_button ?>">
		</td>
		</form>
		<form method="post" action="<? echo $g_manage_project_delete_page?>">
		<input type="hidden" name="f_project_id" value="<? echo $f_project_id ?>">
		<td align="right" bgcolor="<? echo $g_white_color ?>">
			<input type="submit" value="<? echo $s_delete_project_button ?>">
		</td>
		</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<p>
<div align="center">
<table width="75%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table cols="6" width="100%" bgcolor="<? echo $g_white_color ?>">
	<tr>
		<td colspan="6" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_categories_and_version_title ?></b>
		</td>
	</tr>
	<tr align="center" bgcolor="<? echo $g_category_title_color2 ?>">
		<td width="50%">
			<? echo $s_categories ?>
		</td>
		<td width="50%">
			<? echo $s_versions ?>
		</td>
	</tr>
	<tr valign="top">
		<td width="50%">
		<table width="100%">
		<?
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

				### alternate row colors
				$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );
		?>
		<tr bgcolor="<? echo $t_bgcolor ?>">
			<td width="75%">
				<? echo $t_category ?>
			</td>
			<td width="25%" align="center">
				<? print_bracket_link( $g_manage_project_category_edit_page."?f_project_id=".$f_project_id."&f_category=".$t2_category, $s_edit_link ) ?>
			</td>
		</tr>
		<? 	} ### end for loop ?>
		</table>
		</td>
		<td width="50%">
		<table width="100%">
		<?
			$query = "SELECT version
					FROM $g_mantis_project_version_table
					WHERE project_id='$f_project_id'
					ORDER BY version";
			$result = db_query( $query );
			$version_count = db_num_rows( $result );
			for ($i=0;$i<$version_count;$i++) {
				$row = db_fetch_array( $result );
				$t_version = $row["version"];
				$t2_version = urlencode( $t_version );

				### alternate row colors
				$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );
		?>
		<tr bgcolor="<? echo $t_bgcolor ?>">
			<td width="75%">
				<? echo $t_version ?>
			</td>
			<td width="25%" align="center">
				<? print_bracket_link( $g_manage_project_version_edit_page."?f_project_id=".$f_project_id."&f_version=".$t2_version, $s_edit_link ) ?>
			</td>
		</tr>
		<?	} ### end for loop ?>
		</table>
		</td>
	</tr>
	<tr>
	<form method="post" action="<? echo $g_manage_project_category_add ?>">
	<input type="hidden" name="f_project_id" value="<? echo $f_project_id ?>">
		<td align="center" bgcolor="<? echo $g_white_color ?>">
			<input type="text" name="f_category" size="32" maxlength="32">
			<input type="submit" value="<? echo $s_add_category_button ?>">
		</td>
	</form>
	<form method="post" action="<? echo $g_manage_project_version_add ?>">
	<input type="hidden" name="f_project_id" value="<? echo $f_project_id ?>">
		<td align="center" bgcolor="<? echo $g_white_color ?>">
			<input type="text" name="f_version" size="32" maxlength="32">
			<input type="submit" value="<? echo $s_add_version_button ?>">
		</td>
	</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>