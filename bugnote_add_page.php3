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

<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
[ <a href="<? echo $g_view_bug_page ?>?f_id=<? echo $f_id ?>">Back</a> ]
</div>

<p>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table cols=4 width=100% bgcolor=<? echo $g_white_color ?>>
	<tr>
		<td colspan=4 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_viewing_bug_details_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_category_title_color ?> align=center>
		<td width=25%>
			<b><? echo $s_id ?></b>
		</td>
		<td width=25%>
			<b><? echo $s_category ?></b>
		</td>
		<td width=25%>
			<b><? echo $s_severity ?></b>
		</td>
		<td width=25%>
			<b><? echo $s_reproducibility ?></b>
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
	</tr>
	<tr height=5 bgcolor=<? echo $g_white_color ?>>
		<td colspan=4 bgcolor=<? echo $g_white_color ?>>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b><? echo $s_summary ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=3>
			<? echo $v_summary ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b><? echo $s_description ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_light ?> colspan=3>
			<? echo $v2_description ?>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_category_title_color ?> align=center>
			<b><? echo $s_additional ?><br><? echo $s_information ?></b>
		</td>
		<td bgcolor=<? echo $g_primary_color_dark ?> colspan=3>
			<? echo $v2_additional_information ?>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>

<p>
<? include( $g_bugnote_include_file ) ?>

<p>
<div align=center>
<table width=100% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<form method=post action="<? echo $g_bugnote_add ?>">
	<input type=hidden name=f_id value="<? echo $f_id ?>">
	<tr>
		<td bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_add_bugnote_title ?></b>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_primary_color_dark ?> align=center>
			<textarea name=f_bugnote_text cols=80 rows=10></textarea>
		</td>
	</tr>
	<tr>
		<td bgcolor=<? echo $g_primary_color_light ?> align=center>
			<input type=submit value="<? echo $s_add_bugnote_button ?>">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>