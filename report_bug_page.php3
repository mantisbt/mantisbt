<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### This file POSTs data to report_bug.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	# these pages are invalid for the "All Project" selection
	if ( "0000000" == $g_project_cookie_val ) {
		print_header_redirect( $g_login_select_proj_page );
	}

	if ( 2 == $g_show_report ) {
		print_header_redirect ( $g_report_bug_advanced_page );
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	### We check to see if the variable exists to avoid warnings

	if ( !isset( $f_category ) ) {
		$f_category = "";
	}

	if ( !isset( $f_reproducibility ) ) {
		$f_reproducibility = "";
	}

	if ( !isset( $f_severity ) ) {
		$f_severity = "";
	}

	if ( !isset( $f_summary ) ) {
		$f_summary = "";
	}

	if ( !isset( $f_description ) ) {
		$f_description = "";
	}

	if ( !isset( $f_additional_info ) ) {
		$f_additional_info = "";
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
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" enctype="multipart/form-data" action="<? echo $g_report_add ?>">
<input type="hidden" name="f_assign_id" value="0000000">
<tr>
	<td class="form-title">
		<? echo $s_enter_report_details_title ?>
	</td>
	<td class="right">
		<?
			if ( $g_show_report==0 ) {
				print_bracket_link( $g_report_bug_advanced_page, $s_advanced_report_link );
			}
		?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<? echo $s_category ?> <? print_documentaion_link( "category" ) ?>:<br>
		<span class="required">*<? echo $s_required ?>*</span>
	</td>
	<td width="70%">
		<select name="f_category">
			<option value="" SELECTED><? echo $s_select_category ?></option>
			<? print_category_option_list( $f_category ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_reproducibility ?> <? print_documentaion_link( "reproducibility" ) ?>:<br>
		<span class="required">*<? echo $s_required ?>*</span>
	</td>
	<td>
		<select name="f_reproducibility">
			<option value="" SELECTED><? echo $s_select_reproducibility ?></option>
			<? print_enum_string_option_list( $s_reproducibility_enum_string, $f_reproducibility ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_severity ?> <? print_documentaion_link( "severity" ) ?>:<br>
		<span class="required">*<? echo $s_required ?>*</span>
	</td>
	<td>
		<select name="f_severity">
			<option value="" selected><? echo $s_select_severity ?></option>
			<? print_enum_string_option_list( $s_severity_enum_string, $f_severity ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_summary ?> <? print_documentaion_link( "summary" ) ?>:<br>
		<span class="required">*<? echo $s_required ?>*</span>
	</td>
	<td>
		<input type="text" name="f_summary" size="80" maxlength="128" value="<? echo $f_summary ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_description ?> <? print_documentaion_link( "description" ) ?>:<br>
		<span class="required">*<? echo $s_required ?>*</span>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="5" wrap="virtual"><? echo $f_description ?></textarea>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_additional_information ?> <? print_documentaion_link( "additional_information" ) ?>:
	</td>
	<td>
		<textarea name="f_additional_info" cols="60" rows="5" wrap="virtual"><? echo $f_additional_info ?></textarea>
	</td>
</tr>
<? if ( $g_allow_file_upload==1 ) { ?>
<tr class="row-1">
	<td class="category">
		<? echo $s_upload_file ?>
	</td>
	<td>
		<input type="hidden" name="max_file_size" value="<? echo $g_max_file_size ?>">
		<input name="f_file" type="file" size="60">
	</td>
</tr>
<? } ?>
<tr class="row-2">
	<td class="category">
		<? echo $s_report_stay ?> <? print_documentaion_link( "report_stay" ) ?>:
	</td>
	<td>
		<input type="checkbox" name="f_report_stay" <? if ( isset($f_report_stay) ) echo "CHECKED"?>> (<? echo $s_check_report_more_bugs ?>)
	</td>
</tr>
<tr>
	<td class="center" colspan="2">
		<input type="submit" value="<? echo $s_submit_report_button ?>">
	</td>
</tr>
</form>
</table>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>