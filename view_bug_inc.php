<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	### This include file prints out the bug information
	### $f_id MUST be specified before the file is included
?>
<?
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
	$v_os_build					= string_display( $v_os_build );
	$v_platform					= string_display( $v_platform );
	$v_version 					= string_display( $v_version );
	$v_summary 					= string_display( $v_summary );
	$v2_description 			= string_display( $v2_description );
	$v2_steps_to_reproduce 		= string_display( $v2_steps_to_reproduce );
	$v2_additional_information 	= string_display( $v2_additional_information );
?>
<p>
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%" bgcolor="<? echo $g_white_color ?>">
	<tr>
		<td colspan="6" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_viewing_bug_simple_details_title ?></b>
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
			<? echo $v_category ?>
		</td>
		<td>
			<? echo get_enum_element( $s_severity_enum_string, $v_severity ) ?>
		</td>
		<td>
			<? echo get_enum_element( $s_reproducibility_enum_string, $v_reproducibility ) ?>
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
			&nbsp;
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
			<? print_user( $v_handler_id ) ?>
		</td>
	</tr>
	<tr align="center">
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_priority ?></b>
		</td>
		<td bgcolor="<? echo $g_primary_color_dark ?>">
			<? echo get_enum_element( $s_priority_enum_string, $v_priority ) ?>
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
		<td bgcolor="<? echo $g_primary_color_light ?>">
			<? echo get_enum_element( $s_status_enum_string, $v_status ) ?>
		</td>
		<td bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_duplicate_id ?></b>
		</td>
		<td bgcolor="<? echo $g_primary_color_light ?>">
			<? print_duplicate_id( $v_duplicate_id ) ?>
		</td>
		<td colspan="2" bgcolor="<? echo $g_primary_color_light ?>">
			&nbsp;
		</td>
	</tr>
	<tr height="5" bgcolor="<? echo $g_white_color ?>">
		<td colspan="6" bgcolor="<? echo $g_white_color ?>">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_summary ?></b>
		</td>
		<td colspan="5" bgcolor="<? echo $g_primary_color_dark ?>">
			<? echo $v_summary ?>
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_description ?></b>
		</td>
		<td colspan="5" bgcolor="<? echo $g_primary_color_light ?>">
			<? echo $v2_description ?>
		</td>
	</tr>
	<tr>
		<td align="center" bgcolor="<? echo $g_category_title_color ?>">
			<b><? echo $s_additional_information ?></b>
		</td>
		<td colspan="5" bgcolor="<? echo $g_primary_color_dark ?>">
			<? echo $v2_additional_information ?>
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</div>