<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	if ( 1 == $g_show_view ) {
		print_header_redirect ( $g_view_bug_page."?f_id=".$f_id );
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

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
	$v_os_build					= string_display( $v_os_build );
	$v_platform					= string_display( $v_platform );
	$v_version 					= string_display( $v_version );
	$v_summary 					= string_display( $v_summary );
	$v2_description 			= string_display( $v2_description );
	$v2_steps_to_reproduce 		= string_display( $v2_steps_to_reproduce );
	$v2_additional_information 	= string_display( $v2_additional_information );
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
<tr>
	<td class="form-title" colspan="3">
		<? echo $s_viewing_bug_advanced_details_title ?>
	</td>
	<td class="right" colspan="3">
<?
	if ( 0 == $g_show_view ) {
		print_bracket_link( $g_view_bug_page."?f_id=".$f_id, $s_view_simple_link );
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
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td align="center" bgcolor="<? echo $g_category_title_color ?>">
		<? echo $s_reporter ?>
	</td>
	<td colspan="5" bgcolor="<? echo $g_primary_color_dark ?>">
		<? print_user_with_subject( $v_reporter_id, $f_id ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_assigned_to ?>
	</td>
	<td colspan="5">
		<? print_user_with_subject( $v_handler_id, $f_id ) ?>
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
		<? echo get_enum_element( $s_status_enum_string, $v_status ) ?>
	</td>
	<td class="category">
		<? echo $s_duplicate_id ?>
	</td>
	<td>
		<? print_duplicate_id( $v_duplicate_id ) ?>
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
		<? echo get_enum_element( $s_projection_enum_string, $v_projection ) ?>
	</td>
	<td colspan="2">

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
		<? echo get_enum_element( $s_eta_enum_string, $v_eta ) ?>
	</td>
	<td colspan="2">

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
		<? echo $s_product_build ?>
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
		<? echo $s_steps_to_reproduce ?>
	</td>
	<td colspan="5">
		<? echo $v2_steps_to_reproduce ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<? echo $s_additional_information ?>
	</td>
	<td colspan="5">
		<? echo $v2_additional_information ?>
	</td>
</tr>
<?
	### account profile description
	if ( $v_profile_id > 0 ) {
		$query = "SELECT description
				FROM $g_mantis_user_profile_table
				WHERE id='$v_profile_id'";
		$result = db_query( $query );
		$t_profile_description = "";
		if ( db_num_rows( $result ) > 0 ) {
			$t_profile_description = db_result( $result, 0 );
		}
		$t_profile_description = string_display( $t_profile_description );

?>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<? echo $s_system_profile ?>
	</td>
	<td colspan="5">
		<? echo $t_profile_description ?>
	</td>
</tr>
<?
	}
?>
<tr class="row-2">
	<td class="category">
		<? echo $s_attached_files ?>
	</td>
	<td colspan="5">
		<?
			$query = "SELECT *
					FROM $g_mantis_bug_file_table
					WHERE bug_id='$f_id'";
			$result = db_query( $query );
			$num_files = db_num_rows( $result );
			for ($i=0;$i<$num_files;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, "v2" );
				$v2_diskfile = str_replace( $DOCUMENT_ROOT, "", $v2_diskfile );
				$v2_filesize = round( $v2_filesize / 1024 );

				switch ( $g_file_upload_method ) {
					case DISK:	PRINT "<a href=\"$v2_diskfile\">$v2_filename</a> ($v2_filesize KB)";
								break;
					case DATABASE:
								PRINT "<a href=\"$g_file_download?f_id=$v2_id&f_type=bug\">$v2_filename</a> ($v2_filesize KB)";
								break;
				}
				if ( $i != ($num_files - 1) ) {
					PRINT "<br>";
				}
			}
		?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<?
	if ( access_level_check_greater_or_equal( UPDATER ) ) {
?>
<tr align="center">

	<form method="post" action="<? echo $g_bug_update_advanced_page ?>">
		<input type="hidden" name="f_id" value="<? echo $f_id ?>">
		<input type="hidden" name="f_bug_text_id" value="<? echo $v_bug_text_id ?>">
	<td colspan="2" valign="top" bgcolor="<? echo $g_white_color ?>">
		<input type="submit" value="<? echo $s_update_bug_button ?>">
	</td>
	</form>

<?		if ($v_status!=RESOLVED) {
			if ( access_level_check_greater_or_equal( DEVELOPER ) ) {
?>
	<form method="post" action="<? echo $g_bug_assign ?>">
		<input type="hidden" name="f_id" value="<? echo $f_id ?>">
		<input type="hidden" name="f_date_submitted" value="<? echo $v_date_submitted ?>">
	<td valign="top" bgcolor="<? echo $g_white_color ?>">
		<input type="submit" value="<? echo $s_bug_assign_button ?>">
	</td>
	</form>
<?			} else { ?>
	<td colspan="2" valign="top" bgcolor="<? echo $g_white_color ?>">
	</td>
<?			} # endif DEVELOPER ?>
	<form method="post" action="<? echo $g_bug_resolve_page ?>">
		<input type="hidden" name="f_id" value="<? echo $f_id ?>">
	<td valign="top" bgcolor="<? echo $g_white_color ?>">
		<input type="submit" value="<? echo $s_resolve_bug_button ?>">
	</td>
	</form>
<?		} else { ?>
	<td colspan="2" valign="top" bgcolor="<? echo $g_white_color ?>">
	</td>
<?		} # endif RESOLVED ?>
<!--
REMEMBER TO IMPLEMENT VOTE ADDING
	<form method=post action="<? echo $g_bug_vote_add ?>">
		<input type=hidden name=f_id value="<? echo $f_id ?>">
		<input type=hidden name=f_vote value="<? echo $v_votes ?>">
	<td valign="top" bgcolor=<? echo $g_white_color ?>>
		<input type=submit value=" Add Vote ">
	</td>
	</form>
-->
<?
		if ( access_level_check_greater_or_equal( DEVELOPER ) ) {
?>
	<form method="post" action="<? echo $g_bug_delete_page ?>">
		<input type="hidden" name="f_id" value="<? echo $f_id ?>">
		<input type="hidden" name="f_bug_text_id" value="<? echo $v_bug_text_id ?>">
	<td valign="top" colspan="2" bgcolor="<? echo $g_white_color ?>">
		<input type="submit" value="<? echo $s_delete_bug_button ?>">
	</td>
	</form>
<?		} else { ?>
	<td valign="top" colspan="2" bgcolor="<? echo $g_white_color ?>">
		&nbsp;
	</td>
<?		} #endif DEVELOPER ?>
</tr>
<?
	} #endif UPDATER
?>
</table>

<? include( $g_bug_file_upload_inc ) ?>
<? include( $g_bugnote_include_file ) ?>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>