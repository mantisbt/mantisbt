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

	if ( !access_level_check_greater_or_equal( "developer" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

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

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<form method=post action="<? echo $g_manage_project_add ?>">
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=6 width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td colspan=6 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_add_project_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=25%>
			<? echo $s_project_name?>
		</td>
		<td width=75%>
			<input type=text name=f_name size=64 maxlength=128>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_status ?>
		</td>
		<td>
			<select name=f_status>
			<? print_project_status_option_list() ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_view_status ?>
		</td>
		<td>
			<input type=radio name=f_view_state value="public" CHECKED> <? echo $s_public ?>
			<input type=radio name=f_view_state value="private"> <? echo $s_private ?>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_description ?>
		</td>
		<td>
			<textarea name=f_description cols=60 rows=5></textarea>
		</td>
	</tr>
	<tr>
		<td align=center bgcolor=<? echo $g_white_color ?> colspan=6>
			<input type=submit value="Add Project">
		</td>
	</tr>
	</table>
	</td>
</tr>
</form>
</table>
</div>

<p>
<div align=center>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_projects_title ?></b>
		</td>
	</tr>
	<tr align=center bgcolor=<?echo $g_category_title_color2 ?>>
		<td width=15%>
			<b><a href="<? echo $g_project_menu_page ?>?f_sort=name&f_dir=<? echo $f_dir?>"><? echo $s_name ?></a></b>
		</td>
		<td width=10%>
			<b><a href="<? echo $g_project_menu_page ?>?f_sort=name&f_dir=<? echo $f_dir?>"><? echo $s_status ?></a></b>
		</td>
		<td width=8%>
			<b><a href="<? echo $g_project_menu_page ?>?f_sort=name&f_dir=<? echo $f_dir?>"><? echo $s_enabled ?></a></b>
		</td>
		<td width=10%>
			<b><a href="<? echo $g_project_menu_page ?>?f_sort=name&f_dir=<? echo $f_dir?>"><? echo $s_view_status ?></a></b>
		</td>
		<td width=42%>
			<b><a href="<? echo $g_project_menu_page ?>?f_sort=name&f_dir=<? echo $f_dir?>"><? echo $s_description ?></a></b>
		</td>
		<td width=4%>

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

			$v_name = string_display( $v_name );
			$v_description = string_display_with_br( $v_description );

			### alternate row colors
			if ( $i % 2 == 1) {
				$bgcolor=$g_primary_color_dark;
			}
			else {
				$bgcolor=$g_primary_color_light;
			}
	?>
	<tr align=center bgcolor=<?echo $bgcolor ?>>
		<td>
			<? echo $v_name ?>
		</td>
		<td>
			<? echo $v_status ?>
		</td>
		<td>
			<? echo $v_enabled ?>
		</td>
		<td>
			<? echo $v_view_state ?>
		</td>
		<td align=left>
			<? echo $v_description ?>
		</td>
		<td>
			<a href="<? echo $g_manage_project_edit_page?>?f_project_id=<? echo $v_id ?>"><? echo $s_edit_link ?></a>
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