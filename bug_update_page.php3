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
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

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
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<?
	if ( $g_show_advanced_update==1 ) {
?>
<p>
<div align=center>
[ <a href="<? echo $g_view_bug_page ?>?f_id=<? echo $f_id ?>"><? echo $s_back_to_bug ?></a> ]
[ <a href="<? echo $g_bug_update_advanced_page ?>?f_id=<? echo $f_id ?>&f_bug_text_id=<? echo $f_bug_text_id ?>"><? echo $s_update_advanced_link ?></a> ]
</div>
<?
	}
?>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<form method=post action="<? echo $g_bug_update ?>">
	<input type=hidden name=f_id value="<? echo $v_id ?>">
	<input type=hidden name=f_projection value="<? echo $v_projection ?>">
	<input type=hidden name=f_eta value="<? echo $v_eta ?>">
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=6 width=100% bgcolor=<? echo $g_white_color ?>>
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
			<select name=f_category>
				<? print_field_option_list( "category", $v_category ) ?>
			</select>
		</td>
		<td>
			<select name=f_severity>
				<? print_field_option_list( "severity", $v_severity ) ?>
			</select>
		</td>
		<td>
			<select name=f_reproducibility>
				<? print_field_option_list( "reproducibility", $v_reproducibility ) ?>
			</select>
		</td>
		<td>
			<input type=hidden name=f_date_submitted value="<? echo $v_date_submitted ?>">
			<? echo date( "m-d H:i", sql_to_unix_time( $v_date_submitted ) ) ?>
		</td>
		<td>
			<? echo date( "m-d H:i", sql_to_unix_time( $v_last_updated ) ) ?>
		</td>
	</tr>
	<tr height=5 bgcolor=<? echo $g_white_color ?>>
		<td colspan=6 bgcolor=<? echo $g_white_color ?>>
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
			<select name=f_handler_id>
				<option value="">
				<? print_handler_option_list( $v_handler_id ) ?>
			</select>
		</td>
	</tr>
	<tr align=center>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b><? echo $s_priority ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<select name=f_priority>
				<? print_field_option_list( "priority", $v_priority ) ?>
			</select>
		</td>
		<td bgcolor=<? echo $g_category_title_color ?>>
			<b><? echo $s_resolution ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?>>
			<? echo $v_resolution ?>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=2>

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
			<? echo $v_duplicate_id ?>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=2>

		</td>
	</tr>
	<tr height=5 bgcolor=<? echo $g_white_color ?>>
		<td colspan=6 bgcolor=<? echo $g_white_color ?>>
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
			<b><? echo $s_additional ?><br><? echo $s_information ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=5>
			<? echo $v2_additional_information ?>
		</td>
	</tr>
	<tr>
		<td align=center bgcolor=<? echo $g_white_color ?> colspan=6>
			<input type=submit value="<? echo $s_update_information_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</form>
</table>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>