<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	# This include file prints out the bug information
	# $f_id MUST be specified before the file is included
?>
<?
    $query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted,
    		UNIX_TIMESTAMP(last_updated) as last_updated
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
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="6">
		<? echo $s_viewing_bug_simple_details_title ?>
	</td>
</tr>
<tr>
	<td class="category" width="15%">
		<? echo $s_id ?>
	</td>
	<td class="category" width="20%">
		<? echo $s_category ?>
	</td>
	<td class="category" width="15%">
		<? echo $s_severity ?>
	</td>
	<td class="category" width="20%">
		<? echo $s_reproducibility ?>
	</td>
	<td class="category" width="15%">
		<? echo $s_date_submitted ?>
	</td>
	<td class="category" width="15%">
		<? echo $s_last_update ?>
	</td>
</tr>
<tr class="row-2">
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
		<? print_date( $g_normal_date_format, $v_last_updated ) ?>
	</td>
</tr>
<tr height="5" class="white">
	<td colspan="6">
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
		<? print_user( $v_handler_id ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_priority ?>
	</td>
	<td>
		<? echo get_enum_element( $s_priority_enum_string, $v_priority ) ?>
	</td>
	<td class="category">
		<? echo $s_resolution ?>
	</td>
	<td>
		<? echo get_enum_element( $s_resolution_enum_string, $v_resolution ) ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_status ?>
	</td>
	<td>
		<? echo get_enum_element( $s_status_enum_string, $v_status ) ?>
	</td>
	<td class="category">
		<? echo $s_duplicate_id ?>
	</td>
	<td>
		<? print_duplicate_id( $v_duplicate_id ) ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr height="5" class="white">
	<td colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_summary ?>
	</td>
	<td colspan="5">
		<? echo $v_summary ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_description ?>
	</td>
	<td colspan="5">
		<? echo $v2_description ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_additional_information ?>
	</td>
	<td colspan="5">
		<? echo $v2_additional_information ?>
	</td>
</tr>
</table>