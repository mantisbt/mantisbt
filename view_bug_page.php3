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

    $query = "SELECT *
    		FROM $g_mantis_bug_table
    		WHERE id='$f_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );

    $query = "SELECT *
    		FROM $g_mantis_bug_text_table
    		WHERE id='$v_bug_text_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v2" );

	$v_summary = string_display( $v_summary );
	$v2_description = string_display_with_br( $v2_description );
	$v2_steps_to_reproduce = string_display_with_br( $v2_steps_to_reproduce );
	$v2_additional_information = string_display_with_br( $v2_additional_information );
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
<? print_top_page( $g_top_include_page ) ?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
[ <a href="<? echo $g_view_bug_advanced_page ?>?f_id=<? echo $f_id?>"><? echo $s_view_advanced_link ?></a> ]
</div>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td colspan=6 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_viewing_bug_details_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_category_title_color ?> align=center>
		<td width=15%>
			<b><? echo $s_id ?></b>
		</td>
		<td width=20%>
			<b><? echo $s_category ?></b>
		</td>
		<td width=15%>
			<b><? echo $s_severity ?></b>
		</td>
		<td width=20%>
			<b><? echo $s_reproducibility ?></b>
		</td>
		<td width=15%>
			<b><? echo $s_date_submitted ?></b>
		</td>
		<td width=15%>
			<b><? echo $s_last_update ?></b>
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
			<? echo date( $g_normal_date_format, sql_to_unix_time( $v_date_submitted ) ) ?>
		</td>
		<td>
			<? echo date( $g_normal_date_format, sql_to_unix_time( $v_last_updated ) ) ?>
		</td>
	</tr>
	<tr height=5 bgcolor=<? echo $g_white_color ?>>
		<td colspan=6 bgcolor=<? echo $g_white_color ?>>
			&nbsp;
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b><? echo $s_reporter ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? print_user( $v_reporter_id ) ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b><? echo $s_assigned_to ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=5>
			<? print_user( $v_handler_id ) ?>
		</td>
	</tr>
	<tr align=center>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b><? echo $s_priority ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v_priority ?>
		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b><? echo $s_resolution ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v_resolution ?>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=2>
			&nbsp;
		</td>
	</tr>
	<tr align=center>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b><? echo $s_status ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<? echo $v_status ?>
		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b><? echo $s_duplicate_id ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?>>
			<? print_duplicate_id( $v_duplicate_id ) ?>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=2>
			&nbsp;
		</td>
	</tr>
	<tr height=5 bgcolor=<? echo $g_white_color ?>>
		<td colspan=6 bgcolor=<? echo $g_white_color ?>>
			&nbsp;
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b><? echo $s_summary ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? echo $v_summary ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b><? echo $s_description ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=5>
			<? echo $v2_description ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b><? echo $s_additional_information ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? echo $v2_additional_information ?>
		</td>
	</tr>
	<tr height=5 bgcolor=<? echo $g_white_color ?>>
		<td colspan=6 bgcolor=<? echo $g_white_color ?>>
			&nbsp;
		</td>
	</tr>
<?
	if ( access_level_check_greater_or_equal( "updater" ) ) {
?>
	<tr align=center>
		<form method=post action="<? echo $g_bug_update_page ?>">
			<input type=hidden name=f_id value="<? echo $f_id ?>">
			<input type=hidden name=f_bug_text_id value="<? echo $v_bug_text_id ?>">
		<td valign=top bgcolor=<? echo $g_white_color ?> colspan=2>
			<input type=submit value="<? echo $s_update_bug_button ?>">
		</td>
		</form>

<?	if ($v_status!='resolved') {
		if ( access_level_check_greater_or_equal( "updater" ) ) {
?>
		<form method=post action="<? echo $g_bug_assign ?>">
			<input type=hidden name=f_id value="<? echo $f_id ?>">
			<input type=hidden name=f_date_submitted value="<? echo $v_date_submitted ?>">
		<td valign=top bgcolor=<? echo $g_white_color ?>>
			<input type=submit value="<? echo $s_bug_assign_button ?>">
		</td>
<?		} else { ?>
		<td valign=top bgcolor=<? echo $g_white_color ?> colspan=2>
		</td>
<?		} ?>
		</form>
		<form method=post action="<? echo $g_bug_resolve_page ?>">
			<input type=hidden name=f_id value="<? echo $f_id ?>">
		<td valign=top bgcolor=<? echo $g_white_color ?>>
			<input type=submit value="<? echo $s_resolve_bug_button ?>">
		</td>
		</form>
<?	} else { ?>
		<td valign=top bgcolor=<? echo $g_white_color ?> colspan=2>
			&nbsp;
		</td>
<?	} ?>
		<form method=post action="<? echo $g_bug_delete_page ?>">
			<input type=hidden name=f_id value="<? echo $f_id ?>">
		<td valign=top bgcolor=<? echo $g_white_color ?> colspan=2>
			<input type=submit value="<? echo $s_delete_bug_button ?>">
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
		if ( access_level_check_greater_or_equal( $g_reopen_bug_threshold ) ) {
			PRINT "<div align=center>";
			PRINT "<form method=post action=\"$g_bug_reopen_page\">";
				PRINT "<input type=hidden name=f_id value=\"$v_id\">";
				PRINT "<input type=submit value=\"$s_reopen_bug_button\">";
			PRINT "</form>";
			PRINT "</div>";
		}
	} else {
		include( $g_bugnote_add_include_file );
	}
?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>