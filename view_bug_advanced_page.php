<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php # Login check is delayed, since this page can be viewed with no login (CRC needed) ?>
<?php 
	# @@@@ Print - still needs to be implemented for published defects
	# @@@@ Simple - still needs to be implemented for published defects
	# @@@@ Bug History - consider logging the fact that defect was published and by who.

	if ( SIMPLE_ONLY == $g_show_view ) {
		$t_simple_url = 'view_bug_page.php?f_id=' . $f_id;

		if ( isset ( $f_check ) ) {
			$t_simple_url .= '&amp;f_check=' . $f_check;
		}

		print_header_redirect ( $t_simple_url );
	}

	if ( isset( $f_check ) ) {
		if ( $f_check != calc_crc( $f_id, __FILE__ ) ) {
		  access_denied();
		}
	}
	else
	{
		login_cookie_check();
		project_access_check( $f_id );
	}

	$c_id = (integer)$f_id;

    $query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted,
    		UNIX_TIMESTAMP(last_updated) as last_updated
    		FROM $g_mantis_bug_table
    		WHERE id='$c_id'";
    $result = db_query( $query );

	# check bug exists here, rather than calling bug_ensure_exists() and executing
	# the query twice.
	if ( 0 == db_num_rows( $result ) ) {
		print_header_redirect( 'main_page.php' );
	}

	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

	if ( !isset ( $f_check ) ) {
		# if bug is private, make sure user can view private bugs
		access_bug_check( $f_id, $v_view_state );
	}

    $query = "SELECT *
    		FROM $g_mantis_bug_text_table
    		WHERE id='$v_bug_text_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v2' );

	$v_os 						= string_display( $v_os );
	$v_os_build					= string_display( $v_os_build );
	$v_platform					= string_display( $v_platform );
	$v_version 					= string_display( $v_version );
	$v_summary 					= string_display( $v_summary );
	$v2_description 			= string_display( $v2_description );
	$v2_steps_to_reproduce 		= string_display( $v2_steps_to_reproduce );
	$v2_additional_information 	= string_display( $v2_additional_information );
	
	compress_start();

	print_page_top1();

	if ( !isset ( $f_check ) ) {
		print_page_top2();
	} else {
		print_page_top2a();
	}
?>

<p>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="4">
		<?php echo $s_viewing_bug_advanced_details_title ?>
		<span class="small"><?php print_bracket_link( '#bugnotes', $s_jump_to_bugnotes ) ?></span>
	</td>
	<td class="right" colspan="2">

<?php if ( !isset( $f_check ) ) { ?>
	<span class="small"><?php print_bracket_link( 'view_bug_advanced_page.php?f_id='.$f_id.'&amp;f_check=' . calc_crc( $f_id, __FILE__ ), $s_publish )?></span>
<?php }?>

<?php if ( !isset( $f_check ) && ( BOTH == $g_show_view ) ) { ?>
		<span class="small"><?php print_bracket_link( 'view_bug_page.php?f_id='.$f_id, $s_view_simple_link )?></span>
<?php }?>
<?php
	if ( isset ( $f_check ) ) {
		$t_check = '&amp;f_check=' . $f_check;
	} else {
		$t_check = '';
	}
?>
	<span class="small"><?php print_bracket_link( 'view_bug_advanced_page.php?f_id='.$f_id.$t_check.'&amp;f_history=1#history', $s_bug_history ) ?></span>
	<span class="small"><?php print_bracket_link( 'print_bug_page.php?f_id='.$f_id, $s_print ) ?></span>
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
		<?php echo get_enum_element( 'severity', $v_severity ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'reproducibility', $v_reproducibility ) ?>
	</td>
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $v_date_submitted ) ?>
	</td>
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $v_last_updated ) ?>
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
	<td>
		<?php print_user_with_subject( $v_reporter_id, $f_id ) ?>
	</td>
	<td class="category">
		<?php echo $s_view_status ?>
	</td>
	<td>
		<?php echo get_enum_element( 'project_view_state', $v_view_state ) ?>
	</td>
	<td colspan="2">
		&nbsp;
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
		<?php echo get_enum_element( 'priority', $v_priority ) ?>
	</td>
	<td class="category">
		<?php echo $s_resolution ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $v_resolution ) ?>
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
	<td bgcolor="<?php echo get_status_color( $v_status ) ?>">
		<?php echo get_enum_element( 'status', $v_status ) ?>
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
		<?php echo get_enum_element( 'projection', $v_projection ) ?>
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
		<?php echo get_enum_element( 'eta', $v_eta ) ?>
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
		$t_profile_description = '';
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

	if ( isset( $f_check ) ) {
		$t_show_attachments = true;
	} else {
		$t_user_id = current_user_get_field ( 'id' ); 
		$t_show_attachments = ( ( $v_reporter_id == $t_user_id ) || access_level_check_greater_or_equal( $g_view_attachments_threshold ) );
	}

	if ( $t_show_attachments ) {
?>
<tr class="row-2">
	<td class="category">
		<?php echo $s_attached_files ?>
	</td>
	<td colspan="5">
		<?php file_list_attachments ( $f_id ); ?>
	</td>
</tr>
<?php } ?>
<tr align="center">
	<td colspan="6">
		<table width="100%">
			<tr align="center">
<?php # UPDATE form BEGIN ?>
<?php if ( access_level_check_greater_or_equal( $g_update_bug_threshold ) && ( $v_status < RESOLVED ) ) { ?>
	<td class="center">
		<form method="post" action="<?php echo get_bug_update_page() ?>">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="hidden" name="f_bug_text_id" value="<?php echo $v_bug_text_id ?>">
		<input type="submit" value="<?php echo $s_update_bug_button ?>">
		</form>
	</td>
<?php
	}
	# UPDATE form END
?>
<?php # ASSIGN form BEGIN ?>
<?php if ( !isset( $f_check ) && access_level_check_greater_or_equal( $g_handle_bug_threshold ) && ( $v_status < RESOLVED ) ) { ?>
	<td class="center">
		<?php #check if current user already assigned to the bug ?>
		<?php if ( $t_user_id != $v_handler_id ) { ?>
		<form method="post" action="bug_assign.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="hidden" name="f_date_submitted" value="<?php echo $v_date_submitted ?>">
		<input type="submit" value="<?php echo $s_bug_assign_button ?>">
		</form>
		<?php } #end of checking if current user already assigned ?>
	</td>
<?php
	} # ASSIGN form END
?>
<?php # RESOLVE form BEGIN ?>
<?php if ( !isset( $f_check ) && access_level_check_greater_or_equal( $g_handle_bug_threshold ) && ( $v_status < RESOLVED ) ) { ?>
	<td class="center">
		<form method="post" action="bug_resolve_page.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="submit" value="<?php echo $s_resolve_bug_button ?>">
		</form>
	</td>
<?php
	} # RESOLVE form END
?>
<?php # REOPEN form BEGIN ?>
<?php if ( !isset( $f_check ) && ( $v_status >= RESOLVED ) &&
		( access_level_check_greater_or_equal( $g_reopen_bug_threshold ) ||
		( $v_reporter_id == $t_user_id )) ) { ?>
	<td class="center">
		<form method="post" action="bug_reopen_page.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="submit" value="<?php echo $s_reopen_bug_button ?>">
		</form>
	</td>
<?php
	} # REOPEN form END
?>
<?php # CLOSE form BEGIN ?>
<?php if ( !isset( $f_check ) && ( access_level_check_greater_or_equal( $g_close_bug_threshold ) ||
		( ON == $g_allow_reporter_close &&
		  $v_reporter_id == $t_user_id ) ) &&
		( RESOLVED == $v_status ) ) { ?>
	<td class="center">
		<form method="post" action="bug_close_page.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="submit" value="<?php echo $s_close_bug_button ?>">
		</form>
	</td>
<?php
	} # CLOSE form END
?>
<?php # MONITOR form BEGIN ?>
<?php
 if ( !isset( $f_check ) && (( PUBLIC == $v_view_state && access_level_check_greater_or_equal( $g_monitor_bug_threshold ) ) || ( PRIVATE == $v_view_state && access_level_check_greater_or_equal( $g_private_bug_threshold ) )) && ! user_is_monitoring_bug( $t_user_id, $f_id ) ) {
?>
	<td class="center">
		<form method="post" action="bug_monitor.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="hidden" name="f_action" value="add">
		<input type="submit" value="<?php echo $s_monitor_bug_button ?>">
		</form>
	</td>
<?php
	}
	# MONITOR form END
?>
<?php # UNMONITOR form BEGIN ?>
<?php if ( !isset( $f_check ) && access_level_check_greater_or_equal( $g_monitor_bug_threshold ) && user_is_monitoring_bug( $t_user_id, $f_id ) ) { ?>
	<td class="center">
		<form method="post" action="bug_monitor.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="hidden" name="f_action" value="delete">
		<input type="submit" value="<?php echo $s_unmonitor_bug_button ?>">
		</form>
	</td>
<?php
	}
	# MONITOR form END
?>
<?php # DELETE form BEGIN ?>
<?php if ( !isset( $f_check ) && access_level_check_greater_or_equal( $g_allow_bug_delete_access_level ) ) { ?>
	<td class="center">
		<form method="post" action="bug_delete_page.php">
		<input type="hidden" name="f_id" value="<?php echo $f_id ?>">
		<input type="hidden" name="f_bug_text_id" value="<?php echo $v_bug_text_id ?>">
		<input type="submit" value="<?php echo $s_delete_bug_button ?>">
		</form>
	</td>
<?php
	} # DELETE form END
?>
			</tr>
		</table>
	</td>
</tr>
</table>

<?php
	if ( !isset( $f_check) && $t_show_attachments ) {
		include( $g_bug_file_upload_inc );
	}

	include( $g_bugnote_include_file );

	if ( isset( $f_history ) ) {
		include( $g_history_include_file );
	}

	print_page_bot1( __FILE__ );
	compress_stop();
?>
