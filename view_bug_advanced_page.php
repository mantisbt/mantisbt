<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	if ( SIMPLE_ONLY == $g_show_view ) {
		print_header_redirect ( $g_view_bug_page."?f_id=".$f_id );
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_bug_exists( $f_id );

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
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo $s_viewing_bug_advanced_details_title ?>
	</td>
	<td class="right" colspan="3">
<?php
	if ( BOTH == $g_show_view ) {
		print_bracket_link( $g_view_bug_page."?f_id=".$f_id, $s_view_simple_link );
	}
?>
	</td>
</tr>
<tr class="row-category">
	<td width="16%">
		<?php echo $s_id ?>
	</td>
	<td width="16%">
		<?php echo $s_category ?>
	</td>
	<td width="16%">
		<?php echo $s_severity ?>
	</td>
	<td width="16%">
		<?php echo $s_reproducibility ?>
	</td>
	<td width="16%">
		<?php echo $s_date_submitted ?>
	</td>
	<td width="16%">
		<?php echo $s_last_update ?>
	</td>
</tr>
<tr class="row-2">
	<td>
		<?php echo $v_id ?>
	</td>
	<td>
		<?php echo $v_category ?>
	</td>
	<td>
		<?php echo get_enum_element( $s_severity_enum_string, $v_severity ) ?>
	</td>
	<td>
		<?php echo get_enum_element( $s_reproducibility_enum_string, $v_reproducibility ) ?>
	</td>
	<td>
		<?php print_date( $g_normal_date_format, $v_date_submitted ) ?>
	</td>
	<td>
		<?php print_date( $g_normal_date_format, $v_last_updated ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_reporter ?>
	</td>
	<td colspan="5">
		<?php print_user_with_subject( $v_reporter_id, $f_id ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_assigned_to ?>
	</td>
	<td colspan="5">
		<?php print_user_with_subject( $v_handler_id, $f_id ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_priority ?>
	</td>
	<td>
		<?php echo get_enum_element( $s_priority_enum_string, $v_priority ) ?>
	</td>
	<td class="category">
		<?php echo $s_resolution ?>
	</td>
	<td>
		<?php echo get_enum_element( $s_resolution_enum_string, $v_resolution ) ?>
	</td>
	<td class="category">
		<?php echo $s_platform ?>
	</td>
	<td>
		<?php echo $v_platform ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_status ?>
	</td>
	<td>
		<?php echo get_enum_element( $s_status_enum_string, $v_status ) ?>
	</td>
	<td class="category">
		<?php echo $s_duplicate_id ?>
	</td>
	<td>
		<?php print_duplicate_id( $v_duplicate_id ) ?>
	</td>
	<td class="category">
		<?php echo $s_os ?>
	</td>
	<td>
		<?php echo $v_os ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_projection ?>
	</td>
	<td>
		<?php echo get_enum_element( $s_projection_enum_string, $v_projection ) ?>
	</td>
	<td colspan="2">

	</td>
	<td class="category">
		<?php echo $s_os_version ?>
	</td>
	<td>
		<?php echo $v_os_build ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_eta ?>
	</td>
	<td>
		<?php echo get_enum_element( $s_eta_enum_string, $v_eta ) ?>
	</td>
	<td colspan="2">

	</td>
	<td class="category">
		<?php echo $s_product_version ?>
	</td>
	<td>
		<?php echo $v_version ?>
	</td>
</tr>
<tr class="row-1">
	<td colspan="4">
		&nbsp;
	</td>
	<td class="category">
		<?php echo $s_product_build ?>
	</td>
	<td>
		<?php echo $v_build?>
	</td>
</tr>
<tr class="row-2">
	<td colspan="4">
		&nbsp;
	</td>
	<td class="category">
		<?php echo $s_votes ?>
	</td>
	<td>
		<?php echo $v_votes ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_summary ?>
	</td>
	<td colspan="5">
		<?php echo $v_summary ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_description ?>
	</td>
	<td colspan="5">
		<?php echo $v2_description ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_steps_to_reproduce ?>
	</td>
	<td colspan="5">
		<?php echo $v2_steps_to_reproduce ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_additional_information ?>
	</td>
	<td colspan="5">
		<?php echo $v2_additional_information ?>
	</td>
</tr>
<?php
	# account profile description
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
		<?php echo $s_system_profile ?>
	</td>
	<td colspan="5">
		<?php echo $t_profile_description ?>
	</td>
</tr>
<?php
	}
?>
<tr class="row-2">
	<td class="category">
		<?php echo $s_attached_files ?>
	</td>
	<td colspan="5">
		<?php
			$query = "SELECT *, UNIX_TIMESTAMP(date_added) as date_added
					FROM $g_mantis_bug_file_table
					WHERE bug_id='$f_id'";
			$result = db_query( $query );
			$num_files = db_num_rows( $result );
			for ($i=0;$i<$num_files;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, "v2" );
				$v2_diskfile = str_replace( $DOCUMENT_ROOT, "", $v2_diskfile );
				$v2_diskfile = dirname( $v2_diskfile )."/".rawurlencode( $v2_filename );
				$v2_filesize = round( $v2_filesize / 1024 );
				$v2_date_added = date( $g_normal_date_format, ( $v2_date_added ) );

				switch ( $g_file_upload_method ) {
					case DISK:	PRINT "<a href=\"$v2_diskfile\">$v2_filename</a> ($v2_filesize KB) <span class=\"italic\">$v2_date_added</span>";
								break;
					case DATABASE:
								PRINT "<a href=\"$g_file_download?f_id=$v2_id&f_type=bug\">$v2_filename</a> ($v2_filesize KB) <span class=\"italic\">$v2_date_added</span>";
								break;
				}

				if ( access_level_check_greater_or_equal( DEVELOPER ) ) {
					PRINT " [<a class=\"small-link\" href=\"$g_bug_file_delete?f_id=$f_id&f_file_id=$v2_id\">$s_delete_link</a>]";
				}
				if ( $i != ($num_files - 1) ) {
					PRINT "<br>";
				}
			}
		?>
	</td>
</tr>
<tr align="center">
<?php # UPDATE form BEGIN ?>
<?php	if ( access_level_check_greater_or_equal( UPDATER ) && ( $v_status < RESOLVED ) ) { ?>
	<form method="post" action="<?php echo $g_bug_update_page ?>">
	<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
	<input type="hidden" name="f_bug_text_id" value="<?php echo $v_bug_text_id ?>">
	<td class="center">
		<input type="submit" value="<?php echo $s_update_bug_button ?>">
	</td>
	</form>
<?php } else { ?>
	<td>&nbsp;</td>
<?php } # UPDATE form END ?>
<?php # ASSIGN form BEGIN ?>
<?php	if ( access_level_check_greater_or_equal( DEVELOPER ) && ( $v_status < RESOLVED ) ) { ?>
	<form method="post" action="<?php echo $g_bug_assign ?>">
	<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
	<input type="hidden" name="f_date_submitted" value="<?php echo $v_date_submitted ?>">
	<td class="center">
		<?php #check if current user already assigned to the bug ?>
		<?php $t_user_id = get_current_user_field ( "id" ); ?>
		<?php if ($t_user_id != $v_handler_id) { ?>
		<input type="submit" value="<?php echo $s_bug_assign_button ?>">
		<?php } #end of checking if current user already assigned ?>&nbsp;
	</td>
	</form>
<?php } else { ?>
	<td>&nbsp;</td>
<?php } # ASSIGN form END ?>
<?php # RESOLVE form BEGIN ?>
<?php	if ( access_level_check_greater_or_equal( DEVELOPER ) && ( $v_status < RESOLVED ) ) { ?>
	<form method="post" action="<?php echo $g_bug_resolve_page ?>">
	<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
	<td class="center">
		<input type="submit" value="<?php echo $s_resolve_bug_button ?>">
	</td>
	</form>
<?php } else { ?>
	<td>&nbsp;</td>
<?php } # RESOLVE form END ?>
<?php # REOPEN form BEGIN ?>
<?php	if ( access_level_check_greater_or_equal( DEVELOPER ) && ( $v_status >= RESOLVED ) &&
		(( access_level_check_greater_or_equal( $g_reopen_bug_threshold ) ) ||
		( $v_reporter_id == $t_user_id )) ) { ?>
	<form method="post" action="<?php echo $g_bug_reopen_page ?>">
	<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
	<td class="center">
		<input type="submit" value="<?php echo $s_reopen_bug_button ?>">
	</td>
	</form>
<?php } else { ?>
	<td>&nbsp;</td>
<?php } # REOPEN form END ?>
<?php # CLOSE form BEGIN ?>
<?php	if ( access_level_check_greater_or_equal( DEVELOPER ) && ( RESOLVED == $v_status ) ) { ?>
	<form method="post" action="<?php echo $g_bug_close ?>">
	<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
	<td class="center">
		<input type="submit" value="<?php echo $s_close_bug_button ?>">
	</td>
	</form>
<?php } else { ?>
	<td>&nbsp;</td>
<?php } # CLOSE form END ?>
<?php # DELETE form BEGIN ?>
<?php	if ( access_level_check_greater_or_equal( DEVELOPER ) ) { ?>
	<form method="post" action="<?php echo $g_bug_delete_page ?>">
	<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
	<input type="hidden" name="f_bug_text_id" value="<?php echo $v_bug_text_id ?>">
	<td class="center">
		<input type="submit" value="<?php echo $s_delete_bug_button ?>">
	</td>
	</form>
<?php } else {
	PRINT "<td>&nbsp;</td>";
	} # DELETE form END ?>
</tr>
</table>

<?php include( $g_bug_file_upload_inc ) ?>
<?php include( $g_bugnote_include_file ) ?>

<?php print_page_bot1( __FILE__ ) ?>