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

	if ( !isset( $f_sort ) ) {
		$f_sort = "name";
	}

	### basically we toggle between ASC and DESC if the user clicks the
	### same sort order
	if ( isset( $f_dir ) ) {
		if ( $f_dir=="ASC" ) {
			$f_dir = "DESC";
		}
		else {
			$f_dir = "ASC";
		}
	}
	else {
		$f_dir = "ASC";
	}

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

<? print_manage_menu( $g_manage_project_menu_page ) ?>

<p>
<div align="center">
<table width="75%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<form method="post" action="<? echo $g_manage_project_add ?>">
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table cols="6" width="100%" bgcolor="<? echo $g_white_color ?>">
	<tr>
		<td colspan="6" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_add_project_title ?></b>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td width="25%">
			<? echo $s_project_name?>
		</td>
		<td width="75%">
			<input type="text" name="f_name" size="64" maxlength="128">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_status ?>
		</td>
		<td>
			<select name="f_status">
			<? print_enum_string_option_list( $g_project_status_enum_string ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_view_status ?>
		</td>
		<td>
			<input type="radio" name="f_view_state" value="10" CHECKED> <? echo $s_public ?>
			<input type="radio" name="f_view_state" value="50"> <? echo $s_private ?>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_access_threshold ?>
		</td>
		<td>
			<select name="f_access_min">
			<? print_enum_string_option_list( $g_access_levels_enum_string, VIEWER ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_dark ?>">
		<td>
			<? echo $s_upload_file_path ?>
		</td>
		<td>
			<input type="text" name="f_file_path" size="70" maxlength="250">
		</td>
	</tr>
	<tr bgcolor="<? echo $g_primary_color_light ?>">
		<td>
			<? echo $s_description ?>
		</td>
		<td>
			<textarea name="f_description" cols="60" rows="5"></textarea>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="6" bgcolor="<? echo $g_white_color ?>">
			<input type="submit" value="<? echo $s_add_project_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</form>
</table>
</div>

<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_projects_title ?></b>
		</td>
	</tr>
	<tr align="center" bgcolor="<?echo $g_category_title_color2 ?>">
		<td width="15%">
			<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_name, "name", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "name" ) ?>
		</td>
		<td width="10%">
			<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_status, "status", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "status" ) ?>
		</td>
		<td width="8%">
			<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_enabled, "enabled", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "enabled" ) ?>
		</td>
		<td width="12%">
			<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_view_status, "view_state", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "view_state" ) ?>
		</td>
		<td width="40%">
			<? print_manage_project_sort_link(  $g_manage_project_menu_page, $s_description, "description", $f_dir ) ?>
			<? print_sort_icon( $f_dir, $f_sort, "description" ) ?>
		</td>
		<td width="4%">
			&nbsp;
		</td>
	</tr>
	<?
		$query = "SELECT *
				FROM $g_mantis_project_table
				ORDER BY '$f_sort' $f_dir";
		$result = db_query( $query );
		$project_count = db_num_rows( $result );
		for ($i=0;$i<$project_count;$i++) {
			$row = db_fetch_array( $result );
			extract( $row, EXTR_PREFIX_ALL, "v" );

			$v_name 		= string_display( $v_name );
			$v_description 	= string_display( $v_description );

			### alternate row colors
			$t_bgcolor = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );
	?>
	<tr align="center" bgcolor="<? echo $t_bgcolor ?>">
		<td>
			<? echo $v_name ?>
		</td>
		<td>
			<? echo get_enum_element( $g_project_status_enum_string, $v_status ) ?>
		</td>
		<td>
			<? echo trans_bool( $v_enabled ) ?>
		</td>
		<td>
			<? echo get_enum_element( $g_project_view_state_enum_string, $v_view_state ) ?>
		</td>
		<td align="left">
			<? echo $v_description ?>
		</td>
		<td>
			<? print_bracket_link( $g_manage_project_edit_page."?f_project_id=".$v_id,  $s_edit_link ) ?>
		</td>
	</tr>
	<?
		}
	?>
	</table>
	</td>
</tr>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>