<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# This file POSTs data to report_bug.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	# these pages are invalid for the "All Project" selection
	if ( "0000000" == $g_project_cookie_val ) {
		print_header_redirect( $g_login_select_proj_page );
	}

	if ( SIMPLE_ONLY == $g_show_report ) {
		print_header_redirect ( $g_report_bug_page );
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	# We check to see if the variable exists to avoid warnings

	if ( !isset( $f_category ) ) {
		$f_category = "";
	}

	if ( !isset( $f_reproducibility ) ) {
		$f_reproducibility = "";
	}

	if ( !isset( $f_severity ) ) {
		$f_severity = "";
	}

	if ( !isset( $f_profile_id ) ) {
		$f_profile_id = "";
	}

	if ( !isset( $f_platform ) ) {
		$f_platform = "";
	}

	if ( !isset( $f_os ) ) {
		$f_os = "";
	}

	if ( !isset( $f_osbuild ) ) {
		$f_osbuild = "";
	}

	if ( !isset( $f_product_version ) ) {
		$f_product_version = "";
	}

	if ( !isset( $f_build ) ) {
		$f_build = "";
	}

	if ( !isset( $f_assign_id ) ) {
		$f_assign_id = "";
	}

	if ( !isset( $f_summary ) ) {
		$f_summary = "";
	}

	if ( !isset( $f_description ) ) {
		$f_description = "";
	}


	if ( !isset( $f_steps_to_reproduce ) ) {
		$f_steps_to_reproduce = "";
	}

	if ( !isset( $f_additional_info ) ) {
		$f_additional_info = "";
	}
?>
<? print_page_top1() ?>
<? print_page_top2() ?>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" enctype="multipart/form-data" action="<? echo $g_report_add ?>">
<tr>
	<td class="form-title">
		<? echo $s_enter_report_details_title ?>
	</td>
	<td class="right">
		<?
			if ( BOTH == $g_show_report ) {
				print_bracket_link( $g_report_bug_page, $s_simple_report_link );
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
			<option value="" SELECTED><? echo $s_select_severity ?></option>
			<? print_enum_string_option_list( $s_severity_enum_string, $f_severity ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_select_profile ?>:
	</td>
	<td>
		<select name="f_profile_id">
			<? print_profile_option_list( get_current_user_field( "id " ), $f_profile_id ) ?>
		</select>
	</td>
</tr>
<tr class="spacer">
	<td colspan="2">
		<? echo $s_or_fill_in ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_platform ?>:
	</td>
	<td>
		<input type="text" name="f_platform" size="32" maxlength="32" value="<? echo $f_platform ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_os ?>:
	</td>
	<td>
		<input type="text" name="f_os" size="32" maxlength="32" value="<? echo $f_os ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_os_version ?>:
	</td>
	<td>
		<input type="text" name="f_osbuild" size="16" maxlength="16" value="<? echo $f_osbuild ?>">
	</td>
</tr>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_product_version ?>
	</td>
	<td>
		<select name="f_product_version">
			<? print_version_option_list( $f_product_version ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_product_build ?>
	</td>
	<td>
		<input type="text" name="f_build" size="32" maxlength="32" value="<? echo $f_build ?>">
	</td>
</tr>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<? # reporters should not be able to assign to develoeprs ?>
<? if ( get_current_user_field( "access_level" ) > REPORTER ) { ?>
<tr class="row-2">
	<td class="category">
		<? echo $s_assign_to ?>
	</td>
	<td>
		<select name="f_assign_id">
			<option value="0000000" SELECTED></option>
			<? print_assign_to_option_list( $f_assign_id ) ?>
		</select>
	</td>
</tr>
<? } else { ?>
<input type="hidden" name="f_assign_id" value="0000000">
<? } ?>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_summary ?> <? print_documentaion_link( "summary" ) ?>:<br>
		<span class="required">*<? echo $s_required ?>*</span>
	</td>
	<td>
		<input type="text" name="f_summary" size="80" maxlength="128" value="<? echo $f_summary ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_description ?> <? print_documentaion_link( "description" ) ?>:<br>
		<span class="required">*<? echo $s_required ?>*</span>
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="5" wrap="virtual"><? echo $f_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_steps_to_reproduce ?> <? print_documentaion_link( "steps_to_reproduce" ) ?>:
	</td>
	<td>
		<textarea name="f_steps_to_reproduce" cols="60" rows="5" wrap="virtual"><? echo $f_steps_to_reproduce ?></textarea>
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
<? if ( ON == $g_allow_file_upload ) { ?>
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
</table>
</div>

<? print_page_bot1( __FILE__ ) ?>