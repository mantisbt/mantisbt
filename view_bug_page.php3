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
    		FROM $g_mantis_bug_table
    		WHERE id='$f_id'";
    $result = db_mysql_query( $query );
	$row = mysql_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );

    $query = "SELECT *
    		FROM $g_mantis_bug_text_table
    		WHERE id='$v_bug_text_id'";
    $result = db_mysql_query( $query );
	$row = mysql_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v2" );

	$v_summary = string_display( $v_summary );
	$v2_description = string_display_with_br( $v2_description );
	$v2_steps_to_reproduce = string_display_with_br( $v2_steps_to_reproduce );
	$v2_additional_information = string_display_with_br( $v2_additional_information );
	$v_date_submitted = date( "m-d H:i", sql_to_unix_time( $v_date_submitted ) );
	$v_last_updated = date( "m-d H:i", sql_to_unix_time( $v_last_updated ) );
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
[ <a href="<? echo $g_view_bug_advanced_page ?>?f_id=<? echo $f_id?>">Advanced View</a> ]
</div>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=6 width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td colspan=6 bgcolor=<? echo $g_table_title_color ?>>
			<b>Viewing Bug Details</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_category_title_color ?> align=center>
		<td width=15%>
			<b>ID</b>
		</td>
		<td width=20%>
			<b>Category</b>
		</td>
		<td width=15%>
			<b>Severity</b>
		</td>
		<td width=20%>
			<b>Reproducibility</b>
		</td>
		<td width=15%>
			<b>Date Submitted</b>
		</td>
		<td width=15%>
			<b>Last Update</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?> align=center>
		<td>
			<? echo $v_id ?>
		</td>
		<td>
			<? echo $v_category ?>
		</td>
		<td>
			<? echo $v_severity ?>
		</td>
		<td>
			<? echo $v_reproducibility ?>
		</td>
		<td>
			<? echo $v_date_submitted ?>
		</td>
		<td>
			<? echo $v_last_updated ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b>Reporter</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? print_user( $v_reporter_id ) ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b>Assigned To</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=5>
			<? print_user( $v_handler_id ) ?>
		</td>
	</tr>
	<tr align=center>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Priority</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v_priority ?>
		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Resolution</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v_resolution ?>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=2>

		</td>
	</tr>
	<tr align=center>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Status</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<? echo $v_status ?>
		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b>Duplicate ID</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<? print_duplicate_id( $v_duplicate_id ) ?>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=2>

		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b>Summary</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? echo $v_summary ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b>Description</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=5>
			<? echo $v2_description ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b>Additional<br>Information</b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? echo $v2_additional_information ?>
		</td>
	</tr>
<?
	if ( access_level_check_greater_or_equal( "updater" ) ) {
?>
	<tr align=center>
		<form method=post action="<? echo $g_bug_update_page ?>">
			<input type=hidden name=f_id value="<? echo $f_id ?>">
			<input type=hidden name=f_bug_text_id value="<? echo $v_bug_text_id ?>">
		<td valign=top bgcolor=<? echo $g_white_color ?> colspan=3>
			<input type=submit value=" Update Bug ">
		</td>
		</form>
		<form method=post action="<? echo $g_bug_delete_page ?>">
			<input type=hidden name=f_id value="<? echo $f_id ?>">
		<td valign=top bgcolor=<? echo $g_white_color ?> colspan=3>
			<input type=submit value=" Delete Bug ">
		</td>
	</form>
	</tr>
<?
	}
?>
	</table>
	</td>
</tr>
</table>

<p>
<?
	include( $g_bugnote_include_file );
	PRINT "<p>";

	if ( $v_status=="resolved" ) {
		PRINT "<div align=center>";
		PRINT "<form method=post action=\"$g_bug_reopen\">";
			PRINT "<input type=hidden name=f_id value=\"$v_id\">";
			PRINT "<input type=submit value=\" Reopen Bug \">";
		PRINT "</form>";
		PRINT "</div>";
	} else {
		include( $g_bugnote_add_include_file );
	}
?>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>