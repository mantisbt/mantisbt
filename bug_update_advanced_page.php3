<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Show the advanced update bug options
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	if ( 1 == $g_show_update ) {
		print_header_redirect ( $g_bug_update_page."?f_id=".$f_id );
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( UPDATER );
	check_bug_exists( $f_id );

    $query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted
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

	$v_os 						= string_display( $v_os );
	$v_os_build 				= string_display( $v_os_build );
	$v_platform					= string_display( $v_platform );
	$v_version 					= string_display( $v_version );
	$v_summary					= string_edit_text( $v_summary );
	$v2_description 			= string_edit_textarea( $v2_description );
	$v2_steps_to_reproduce 		= string_edit_textarea( $v2_steps_to_reproduce );
	$v2_additional_information 	= string_edit_textarea( $v2_additional_information );
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
<table class="width100" cellspacing="1">
<form method="post" action="<? echo $g_bug_update ?>">
<input type="hidden" name="f_id" value="<? echo $v_id ?>">
<input type="hidden" name="f_old_status" value="<? echo $v_status ?>">
<input type="hidden" name="f_old_handler_id" value="<? echo $v_handler_id ?>">
<input type="hidden" name="f_resolution" value="<? echo $v_resolution ?>">
<tr>
	<td class="form-title" colspan="3">
		<? echo $s_updating_bug_advanced_title ?>
	</td>
	<td class="right" colspan="3">
<?
	switch ( $g_show_view ) {
		case 0: print_bracket_link( $g_view_bug_advanced_page."?f_id=".$f_id, $s_back_to_bug_link );
				break;
		case 1: print_bracket_link( $g_view_bug_page."?f_id=".$f_id, $s_back_to_bug_link );
				break;
		case 2: print_bracket_link( $g_view_bug_advanced_page."?f_id=".$f_id, $s_back_to_bug_link );
				break;
	}

	if ( 0 == $g_show_update ) {
		print_bracket_link( $g_bug_update_page."?f_id=".$f_id, $s_update_simple_link );
	}
?>
	</td>
</tr>
<tr class="row-category">
	<td width="15%">
		<? echo $s_id ?>
	</td>
	<td width="20%">
		<? echo $s_category ?>
	</td>
	<td width="15%">
		<? echo $s_severity ?>
	</td>
	<td width="20%">
		<? echo $s_reproducibility ?>
	</td>
	<td width="15%">
		<? echo $s_date_submitted ?>
	</td>
	<td width="15%">
		<? echo $s_last_update ?>
	</td>
</tr>
<tr class="row-2">
	<td>
		<? echo $v_id ?>
	</td>
	<td>
		<select name="f_category">
			<? print_category_option_list( $v_category ) ?>
		</select>
	</td>
	<td>
		<select name="f_severity">
			<? print_enum_string_option_list( $s_severity_enum_string, $v_severity ) ?>
		</select>
	</td>
	<td>
		<select name="f_reproducibility">
			<? print_enum_string_option_list( $s_reproducibility_enum_string, $v_reproducibility ) ?>
		</select>
	</td>
	<td>
		<? print_date( $g_normal_date_format, $v_date_submitted ) ?>
	</td>
	<td>
		<? print_date( $g_normal_date_format, sql_to_unix_time( $v_last_updated ) ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_reporter ?>
	</td>
	<td colspan="5">
		<? print_user( $v_reporter_id ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_assigned_to ?>
	</td>
	<td colspan="5">
		<select name="f_handler_id">
			<option value="0"></option>
			<? print_assign_to_option_list( $v_handler_id ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_priority ?>
	</td>
	<td align="left">
		<select name="f_priority">
			<? print_enum_string_option_list( $s_priority_enum_string, $v_priority ) ?>
		</select>
	</td>
	<td class="category">
		<? echo $s_resolution ?>
	</td>
	<td>
		<? echo get_enum_element( $s_resolution_enum_string, $v_resolution ) ?>
	</td>
	<td class="category">
		<? echo $s_platform ?>
	</td>
	<td>
		<? echo $v_platform ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_status ?>
	</td>
	<td>
		<select name="f_status">
			<? print_enum_string_option_list( $s_status_enum_string, $v_status ) ?>
		</select>
	</td>
	<td class="category">
		<? echo $s_duplicate_id ?>
	</td>
	<td>
		<? echo $v_duplicate_id ?>
	</td>
	<td class="category">
		<? echo $s_os ?>
	</td>
	<td>
		<? echo $v_os ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_projection ?>
	</td>
	<td>
		<select name="f_projection">
			<? print_enum_string_option_list( $s_projection_enum_string, $v_projection ) ?>
		</select>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
	<td class="category">
		<? echo $s_os_version ?>
	</td>
	<td>
		<? echo $v_os_build ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_eta ?>
	</td>
	<td>
		<select name="f_eta">
			<? print_enum_string_option_list( $s_eta_enum_string, $v_eta ) ?>
		</select>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
	<td class="category">
		<? echo $s_product_version ?>
	</td>
	<td>
		<? echo $v_version ?>
	</td>
</tr>
<tr class="row-1">
	<td colspan="4">
		&nbsp;
	</td>
	<td class="category">
		<? echo $s_build ?>
	</td>
	<td>
		<? echo $v_build?>
	</td>
</tr>
<tr class="row-2">
	<td colspan="4">
		&nbsp;
	</td>
	<td class="category">
		<? echo $s_votes ?>
	</td>
	<td>
		<? echo $v_votes ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_summary ?>
	</td>
	<td colspan="5">
		<input type="text" name="f_summary" size="80" maxlength="128" value="<? echo $v_summary ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_description ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_description" wrap="virtual"><? echo $v2_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_steps_to_reproduce ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_steps_to_reproduce" wrap="virtual"><? echo $v2_steps_to_reproduce ?></textarea>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_additional_information ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_additional_information" wrap="virtual"><? echo $v2_additional_information ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="6" bgcolor="<? echo $g_white_color ?>">
		<input type="submit" value="<? echo $s_update_information_button ?>">
	</td>
</tr>
</form>
</table>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>