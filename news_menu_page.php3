<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_greater( "developer" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

	### Get user information and prefix with u_
	$query = "SELECT *
		FROM $g_mantis_user_table
		WHERE cookie_string='$g_string_cookie_val'";
    $result = db_mysql_query($query);
	$row = mysql_fetch_array($result);
	if ( $row ) {
		extract( $row, EXTR_PREFIX_ALL, "u" );
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

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<form method=post action="<? echo $g_news_add ?>">
	<input type=hidden name=f_poster_id value="<? echo $u_id ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b>Add News</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=25%>
			Headline<br>
			Do not use "
		</td>
		<td width=75%>
			<input type=text name=f_headline size=64 maxlength=64>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Body
		</td>
		<td>
			<textarea name=f_body cols=60 rows=8></textarea>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value="  Post News  ">
		</td>
	</tr>
	</form>
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
	<table width=100%>
	<form method=post action="<? echo $g_news_edit_page ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b>Edit or Delete News</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td align=center colspan=2>
			<input type=radio name=f_action value="edit" CHECKED> Edit Post
			<input type=radio name=f_action value="delete"> Delete Post
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?> align=center>
		<td valign=top width=25%>
			Select Post
		</td>
		<td width=75%>
			<select name=f_id>
			<?
				### Get current headlines and id  prefix with v_
				$query = "SELECT id, headline
					FROM $g_mantis_news_table
					ORDER BY id DESC";
			    $result = db_mysql_query( $query );
			    $news_count = mysql_num_rows( $result );

				for ($i=0;$i<$news_count;$i++) {
					$row = mysql_fetch_array( $result );
					extract( $row, EXTR_PREFIX_ALL, "v" );
					$v_headline = string_unsafe( $v_headline );

					PRINT "<option value=\"$v_id\">$v_headline";
				}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value=" Submit ">
		</td>
		</form>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>