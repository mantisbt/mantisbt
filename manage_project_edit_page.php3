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

	### If Deleteing item redirect to delete script
	if ( $f_action=="delete" ) {
		header( "Location: $g_project_delete_page?f_project_id=$f_project_id" );
		exit;
	}

	$query = "SELECT *
			FROM $g_mantis_project_table
			WHERE id='$f_project_id'";
	$result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
	[ <a href="<? echo $g_path.$g_manage_project_menu_page ?>"><? echo $s_projects_link ?></a> ]
</div>

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<form method=post action="<? echo $g_manage_project_update ?>">
<input type=hidden name=f_project_id value="<? echo $f_project_id ?>">
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=6 width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td colspan=6 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_edit_project_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=25%>
			<? echo $s_project_name ?>
		</td>
		<td width=75%>
			<input type=text name=f_name size=64 maxlength=128 value="<? echo $v_name ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_status ?>
		</td>
		<td>
			<select name=f_status>
			<? print_project_status_option_list( $v_status ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_enabled ?>
		</td>
		<td>
			<input type=checkbox name=f_enabled <? if ( $v_enabled=="on" ) echo "CHECKED" ?>>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_view_status ?>
		</td>
		<td>
			<input type=radio name=f_view_state value="public" <? if ($v_view_state=="public") echo "CHECKED" ?>> <? echo $s_public ?>
			<input type=radio name=f_view_state value="private" <? if ($v_view_state=="private") echo "CHECKED" ?>> <? echo $s_private ?>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_description ?>
		</td>
		<td>
			<textarea name=f_description cols=60 rows=5><? echo $v_description ?></textarea>
		</td>
	</tr>
	<tr>
		<td align=left bgcolor=<? echo $g_white_color ?>>
			<input type=submit value="<? echo $s_update_project_button ?>">
		</td>
			</form>
			<form method=post action="<? echo $g_manage_project_delete_page?>">
			<input type=hidden name=f_project_id value="<? echo $f_project_id ?>">
		<td align=right bgcolor=<? echo $g_white_color ?>>
			<input type=submit value="<? echo $s_delete_project_button ?>">
		</td>
			</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=6 width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td colspan=6 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_categories_and_versions ?></b>
		</td>
	</tr>
	<tr align=center bgcolor=<? echo $g_category_title_color2 ?>>
		<td width=50%>
			<? echo $s_categories ?>
		</td>
		<td width=50%>
			<? echo $s_versions ?>
		</td>
	</tr>
	<tr valign=top>
		<td width=50%>
		<table width=100%>
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

				### alternate row colors
				if ( $i % 2 == 1) {
					$bgcolor=$g_primary_color_dark;
				}
				else {
					$bgcolor=$g_primary_color_light;
				}
				PRINT "<tr bgcolor=$bgcolor>";
					PRINT "<td width=75%>";
						echo $t_category;
					PRINT "</td>";
					PRINT "<td width=25% align=center>";
						PRINT "<a href=\"$g_manage_project_category_edit_page?f_project_id=$f_project_id&f_category=$t_category\">$s_edit_link</a>";
					PRINT "</td>";
				PRINT "</tr>";
			}
		?>
		</table>
		</td>
		<td width=50%>
		<table width=100%>
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

				### alternate row colors
				if ( $i % 2 == 1) {
					$bgcolor=$g_primary_color_dark;
				}
				else {
					$bgcolor=$g_primary_color_light;
				}
				PRINT "<tr bgcolor=$bgcolor>";
					PRINT "<td width=75%>";
						echo $t_version;
					PRINT "</td>";
					PRINT "<td width=25% align=center>";
						PRINT "<a href=\"$g_manage_project_version_edit_page?f_project_id=$f_project_id&f_version=$t_version\">$s_edit_link</a>";
					PRINT "</td>";
				PRINT "</tr>";
			}
		?>
		</table>
		</td>
	</tr>
	<tr>
	<form method=post action="<? echo $g_manage_project_category_add ?>">
	<input type=hidden name=f_project_id value="<? echo $f_project_id ?>">
		<td align=center bgcolor=<? echo $g_white_color ?>>
			<input type=text name=f_category size=32 maxlength=32><input type=submit value="<? echo $s_add_category_button ?>">
		</td>
	</form>
	<form method=post action="<? echo $g_manage_project_version_add ?>">
	<input type=hidden name=f_project_id value="<? echo $f_project_id ?>">
		<td align=center bgcolor=<? echo $g_white_color ?>>
			<input type=text name=f_version size=32 maxlength=32><input type=submit value="<? echo $s_add_version_button ?>">
		</td>
	</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>