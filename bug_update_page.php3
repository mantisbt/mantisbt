<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Show the simple update bug options
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( UPDATER );
	check_bug_exists( $f_id );

	### grab data
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

	### prepare strings for display
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

<? if ( $g_show_update==1 ) { ?>
<p>
<div align="center">
<?
	if ( $g_show_view==0 ) {
		print_bracket_link( $g_view_bug_page."?f_id=".$f_id, $s_back_to_bug_link );
	}

	if ( $g_show_update==0 ) {
		print_bracket_link( $g_bug_update_advanced_page."?f_id=".$f_id, $s_update_advanced_link );
	}
?>
</div>
<? } ?>

<p>
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<form method="post" action="<? echo $g_bug_update ?>">
	<input type="hidden" name="f_id" value="<? echo $v_id ?>">
	<input type="hidden" name="f_old_status" value="<? echo $v_status ?>">
	<input type="hidden" name="f_resolution" value="<? echo $v_resolution ?>">
	<input type="hidden" name="f_projection" value="<? echo $v_projection ?>">
	<input type="hidden" name="f_eta" value="<? echo $v_eta ?>">

	<input type="hidden" name="f_os" value="<? echo $v_os ?>">
	<input type="hidden" name="f_os_build" value="<? echo $v_os_build ?>">
	<input type="hidden" name="f_platform" value="<? echo $v_platform ?>">
	<input type="hidden" name="f_version" value="<? echo $v_version ?>">

	<input type="hidden" name="f_steps_to_reproduce" value="<? echo $v2_steps_to_reproduce ?>">
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table cols="6" width="100%" bgcolor="<? echo $g_white_color ?>">
	<tr>
		<td colspan="6" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_updating_bug_simple_title ?></b>
		</td>
	</tr>
	<tr align="center" bgcolor="<? echo $g_category_title_color ?>">
		<td width="15%">
			<b><? echo $s_id ?></b>
		</td>
		<td width="20%">
			<b><? echo $s_category ?></b>
		</td>
		<td width="15%">
			<b><? echo $s_severity ?></b>
		</td>
		<td width="20%">
			<b><? echo $s_reproducibility ?></b>
		</td>
		<td width="15%">
			<b><? echo $s_date_submitted ?></b>
		</td>
		<td width="15%">
			<b><? echo $s_last_update ?></b>
		</td>
	</tr>
	<tr align="center" bgcolor="<? echo $g_primary_color_light ?>">
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
	<tr height="5" bgcolor="<? echo $g_white_color ?>">
		<td colspan="6" bgcolor="<? echo $g_white_color ?>">
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_reporter ?></b>
		</td>
		<td colspan="5" bgcolor="<? echo $g_primary_color_dark ?>">
			<? print_user( $v_reporter_id ) ?>
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_assigned_to ?></b>
		</td>
		<td colspan="5" bgcolor="<? echo $g_primary_color_light ?>">
			<select name="f_handler_id">
				<option value="">
				<? print_handler_option_list( $v_handler_id ) ?>
			</select>
		</td>
	</tr>
	<tr align="center">
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_priority ?></b>
		</td>
		<td align="left" bgcolor="<? echo $g_primary_color_dark ?>">
			<select name="f_priority">
				<? print_enum_string_option_list( $s_priority_enum_string, $v_priority ) ?>
			</select>
		</td>
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_resolution ?></b>
		</td>
		<td bgcolor="<? echo $g_primary_color_dark ?>">
			<? echo get_enum_element( $s_resolution_enum_string, $v_resolution ) ?>
		</td>
		<td colspan="2" bgcolor="<? echo $g_primary_color_dark ?>">
			&nbsp;
		</td>
	</tr>
	<tr align="center">
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_status ?></b>
		</td>
		<td align="left" bgcolor="<? echo $g_primary_color_light ?>">
			<select name="f_status">
				<? print_enum_string_option_list( $s_status_enum_string, $v_status ) ?>
			</select>
		</td>
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_duplicate_id ?></b>
		</td>
		<td bgcolor="<? echo $g_primary_color_light ?>">
			<? echo $v_duplicate_id ?>
		</td>
		<td colspan="2" bgcolor="<? echo $g_primary_color_light ?>">
			&nbsp;
		</td>
	</tr>
	<tr height="5" bgcolor="<? echo $g_white_color ?>">
		<td colspan="6" bgcolor="<? echo $g_white_color ?>">
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_summary ?></b>
		</td>
		<td colspan="5" bgcolor="<? echo $g_primary_color_dark ?>">
			<input type="text" name="f_summary" size="80" maxlength="128" value="<? echo $v_summary ?>">
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_description ?></b>
		</td>
		<td colspan="5" bgcolor="<? echo $g_primary_color_light ?>">
			<textarea cols="60" rows="5" name="f_description" wrap="virtual"><? echo $v2_description ?></textarea>
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_additional_information ?></b>
		</td>
		<td colspan="5" bgcolor="<? echo $g_primary_color_dark ?>">
			<textarea cols="60" rows="5" name="f_additional_information" wrap="virtual"><? echo $v2_additional_information ?></textarea>
		</td>
	</tr>
	<tr>
		<td align="center" colspan="6" bgcolor="<? echo $g_white_color ?>">
			<input type="submit" value="<? echo $s_update_information_button ?>">
		</td>
	</tr>
	</table>
	</td>
</tr>
</form>
</table>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>