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
?>

<p>
<? print_menu( $g_menu_include_file ) ?>

<?
	if ( $g_show_advanced_report==1 ) {
?>
<p>
<div align=center>
[ <a href="<? echo $g_report_bug_advanced_page ?>">Advanced Report</a> ]
</div>
<?
	}
?>

<p>
<div align=center>
<table width=75% bgcolor=<? echo $g_primary_border_color." ".$g_primary_table_tags ?>>
<tr>
	<td bgcolor=<? echo $g_white_color ?>>
	<table width=100%>
	<form method=post action="<? echo $g_report_add ?>">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b>Enter Report Details</b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td valign=top width=25%>
			Category:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<select name=f_category>
				<option value="" selected>Select Category
				<? print_field_option_list( "category" ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Reproducibility:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<select name=f_reproducibility>
				<option value="" SELECTED>Select Reproducibility
				<? print_field_option_list( "reproducibility" ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Severity:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<select name=f_severity>
				<option value="" selected>Select Severity
				<? print_field_option_list( "severity" ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Summary:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<input type=text name=f_summary size=80 maxlength=128>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			Description:<br>
			<font color=<? echo $g_required_field_color ?> size=-1>[*required*]</font>
		</td>
		<td>
			<textarea name=f_description cols=60 rows=5></textarea>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			Additional Information:
		</td>
		<td>
			<textarea name=f_additional_info cols=60 rows=5></textarea>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value="  Submit Report  ">
		</td>
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
