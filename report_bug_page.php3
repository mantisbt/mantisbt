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

<?
	if ( $g_show_advanced_report==1 ) {
?>
<p>
<div align=center>
[ <a href="<? echo $g_report_bug_advanced_page ?>"><? echo $s_advanced_report_link ?></a> ]
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
	<input type=hidden name=f_assign_id value="0000000">
	<tr>
		<td colspan=2 bgcolor=<? echo $g_table_title_color ?>>
			<b><? echo $s_enter_report_details_title ?></b>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td valign=top width=25%>
			<? echo $s_category ?> <? print_documentaion_link( "category" ) ?>:<br>
			<span class="required">*<? echo $s_required ?>*</span>
		</td>
		<td>
			<select name=f_category>
				<option value="" selected><? echo $s_select_category ?>
				<? print_category_option_list( $f_category ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_reproducibility ?> <? print_documentaion_link( "reproducibility" ) ?>:<br>
			<span class="required">*<? echo $s_required ?>*</span>
		</td>
		<td>
			<select name=f_reproducibility>
				<option value="" SELECTED><? echo $s_select_reproducibility ?>
				<? print_field_option_list( "reproducibility",  $f_reproducibility  ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_severity ?> <? print_documentaion_link( "severity" ) ?>:<br>
			<span class="required">*<? echo $s_required ?>*</span>
		</td>
		<td>
			<select name=f_severity>
				<option value="" selected><? echo $s_select_severity ?>
				<? print_field_option_list( "severity", $f_severity ) ?>
			</select>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_summary ?> <? print_documentaion_link( "summary" ) ?>:<br>
			<span class="required">*<? echo $s_required ?>*</span>
		</td>
		<td>
			<input type=text name=f_summary size=80 maxlength=128 value="<? echo $f_summary ?>">
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_dark ?>>
		<td>
			<? echo $s_description ?> <? print_documentaion_link( "description" ) ?>:<br>
			<span class="required">*<? echo $s_required ?>*</span>
		</td>
		<td>
			<textarea name=f_description cols=60 rows=5><? echo $f_description ?></textarea>
		</td>
	</tr>
	<tr bgcolor=<? echo $g_primary_color_light ?>>
		<td>
			<? echo $s_additional_information ?> <? print_documentaion_link( "additional_information" ) ?>:
		</td>
		<td>
			<textarea name=f_additional_info cols=60 rows=5><? echo $f_additional_info ?></textarea>
		</td>
	</tr>
	<tr>
		<td align=center colspan=2>
			<input type=submit value="<? echo $s_submit_report_button ?>">
		</td>
	</tr>
	</form>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>