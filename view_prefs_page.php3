<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

    $query = "SELECT *
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = db_mysql_query($query);
	$row = mysql_fetch_array($result);
	extract( $row, EXTR_PREFIX_ALL, "u" );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
[ <a href="<? echo $g_bug_view_all_page ?>">View Bugs</a> ]
</div>

<p>
<div align=center>
<table bgcolor=<? echo $g_primary_border_color ?> width=50%>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% cols=2>
	<form method=post action="<? echo $g_view_prefs_update ?>">
	<input type=hidden name=f_action value="update">
	<tr>
		<td>
			<b>Viewing Preferences</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=40%>
			Hide Resolved
		</td>
		<td width=60%>
			<input type=checkbox name=f_hide_resolved <? if ( $g_hide_resolved_val=="on" ) echo "CHECKED"?>>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td width=40%>
			Limit
		</td>
		<td width=60%>
			<input type=text name=f_view_limit size=7 maxlength=7 value="<? echo $g_view_limit_val ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td width=40%>
			Save Prefs
		</td>
		<td width=60%>
			<input type=checkbox name=f_save_prefs>
		</td>
	</tr>
	<tr align=center>
		<td>
			<input type=submit value=" Update Prefs ">
		</td>
		</form>
		<form method=post action="<? echo $g_view_prefs_update ?>">
		<input type=hidden name=f_action value="reset">
		<td>
			<input type=submit value=" Reset Prefs ">
		</td>
		</form>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>