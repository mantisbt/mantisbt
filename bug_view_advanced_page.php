<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_view_advanced_page.php,v 1.8 2002-10-29 09:27:47 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php # Login check is delayed, since this page can be viewed with no login (CRC needed) ?>
<?php
	# @@@@ Print - still needs to be implemented for published defects
	# @@@@ Simple - still needs to be implemented for published defects
	# @@@@ Bug History - consider logging the fact that defect was published and by who.

	$f_bug_id		= gpc_get_int( 'f_bug_id' );
	$f_check		= gpc_get_string( 'f_check', '' );
	$f_history		= gpc_get_bool( 'f_history' );

	if ( SIMPLE_ONLY == config_get( 'show_view' ) ) {
		$t_simple_url = 'bug_view_page.php?f_bug_id=' . $f_bug_id;

		if ( !empty( $f_check ) ) {
			$t_simple_url .= '&amp;f_check=' . $f_check;
		}

		print_header_redirect ( $t_simple_url );
	}

	if ( !empty( $f_check ) ) {
		if ( $f_check != helper_calc_crc( $f_bug_id, __FILE__ ) ) {
		  access_denied();
		}
	}
	else
	{
		login_cookie_check();
		project_access_check( $f_bug_id );
		access_bug_check( $f_bug_id );
	}

	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );

	compress_start();

	print_page_top1();

	if ( empty ( $f_check ) ) {
		print_page_top2();
	} else {
		print_page_top2a();
	}
?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="4">
		<?php echo lang_get( 'viewing_bug_advanced_details_title' ) ?>
		<span class="small"><?php print_bracket_link( '#bugnotes', lang_get( 'jump_to_bugnotes' ) ) ?></span>
	</td>
	<td class="right" colspan="2">

<?php if ( empty( $f_check ) ) { ?>
	<span class="small"><?php print_bracket_link( 'bug_view_advanced_page.php?f_bug_id=' . $f_bug_id . '&amp;f_check=' . helper_calc_crc( $f_bug_id, __FILE__ ), lang_get( 'publish' ) )?></span>
<?php }?>

<?php if ( empty( $f_check ) && ( BOTH == config_get( 'show_view' ) ) ) { ?>
		<span class="small"><?php print_bracket_link( 'bug_view_page.php?f_bug_id=' . $f_bug_id, lang_get( 'view_simple_link' ) ) ?></span>
<?php } ?>

<?php
	if ( !empty ( $f_check ) ) {
		$t_check = '&amp;f_check=' . $f_check;
	} else {
		$t_check = '';
	}
?>
	<span class="small"><?php print_bracket_link( 'bug_view_advanced_page.php?f_bug_id=' . $f_bug_id . $t_check . '&amp;f_history=1#history', lang_get( 'bug_history' ) ) ?></span>
	<span class="small"><?php print_bracket_link( 'print_bug_page.php?f_bug_id=' . $f_bug_id, lang_get( 'print' ) ) ?></span>
	</td>
</tr>
<tr class="row-category">
	<td width="16%">
		<?php echo lang_get( 'id' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'severity' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'reproducibility' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'date_submitted' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'last_update' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td>
		<?php echo bug_format_id( $f_bug_id ) ?>
	</td>
	<td>
		<?php echo $t_bug->category ?>
	</td>
	<td>
		<?php echo get_enum_element( 'severity', $t_bug->severity ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'reproducibility', $t_bug->reproducibility ) ?>
	</td>
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) ?>
	</td>
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->last_updated ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'reporter' ) ?>
	</td>
	<td>
		<?php print_user_with_subject( $t_bug->reporter_id, $f_bug_id ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'project_view_state', $t_bug->view_state ) ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<?php print_user_with_subject( $t_bug->handler_id, $f_bug_id ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'priority' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'priority', $t_bug->priority ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $t_bug->resolution ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'platform' ) ?>
	</td>
	<td>
		<?php echo $t_bug->platform ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<?php echo get_enum_element( 'status', $t_bug->status ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<?php print_duplicate_id( $t_bug->duplicate_id ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'os' ) ?>
	</td>
	<td>
		<?php echo $t_bug->os ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'projection' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'projection', $t_bug->projection ) ?>
	</td>
	<td colspan="2">

	</td>
	<td class="category">
		<?php echo lang_get( 'os_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->os_build ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'eta' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'eta', $t_bug->eta ) ?>
	</td>
	<td colspan="2">

	</td>
	<td class="category">
		<?php echo lang_get( 'product_version' ) ?>
	</td>
	<td>
		<?php echo $t_bug->version ?>
	</td>
</tr>
<tr class="row-1">
	<td colspan="4">
		&nbsp;
	</td>
	<td class="category">
		<?php echo lang_get( 'product_build' ) ?>
	</td>
	<td>
		<?php echo $t_bug->build?>
	</td>
</tr>
<tr class="row-2">
	<td colspan="4">
		&nbsp;
	</td>
	<td class="category">
		<?php echo lang_get( 'votes' ) ?>
	</td>
	<td>
		<?php echo $t_bug->votes ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'summary' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->summary ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->description ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->steps_to_reproduce ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->additional_information ?>
	</td>
</tr>
<?php
#@@@ REMOVE THIS CODE!!
	$t_user_profile_table = config_get( 'mantis_user_profile_table' );

	# account profile description
	$t_profile_id = bug_get_field( $f_bug_id, 'profile_id' );
	if ( $t_profile_id > 0 ) {
		$query = "SELECT description
				FROM $t_user_profile_table
				WHERE id='$t_profile_id'";
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
		<?php echo lang_get( 'system_profile' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_profile_description ?>
	</td>
</tr>
<?php
	}

	if ( !empty( $f_check ) ) {
		$t_show_attachments = true;
	} else {
		$t_show_attachments = ( ( $t_bug->reporter_id == auth_get_current_user_id() ) || access_level_check_greater_or_equal( config_get( 'view_attachments_threshold' ) ) );
	}

	if ( $t_show_attachments ) {
?>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'attached_files' ) ?>
	</td>
	<td colspan="5">
		<?php file_list_attachments ( $f_bug_id ); ?>
	</td>
</tr>
<?php } ?>
<tr align="center">
	<td colspan="6">
		<table width="100%">
			<tr align="center">
<?php # UPDATE form BEGIN ?>
<?php if ( access_level_check_greater_or_equal( config_get( 'update_bug_threshold' ) ) && ( $t_bug->status < RESOLVED ) ) { ?>
	<td class="center">
		<form method="post" action="<?php echo string_get_bug_update_page() ?>">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="submit" value="<?php echo lang_get( 'update_bug_button' ) ?>" />
		</form>
	</td>
<?php
	}
	# UPDATE form END
?>
<?php # ASSIGN form BEGIN ?>
<?php if ( empty( $f_check ) && access_level_check_greater_or_equal( config_get( 'handle_bug_threshold' ) ) && ( $t_bug->status < RESOLVED ) ) { ?>
	<td class="center">
		<?php #check if current user already assigned to the bug ?>
		<?php if ( auth_get_current_user_id() != $t_bug->handler_id ) { ?>
		<form method="post" action="bug_assign.php">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="f_date_submitted" value="<?php echo $t_bug->date_submitted ?>" />
		<input type="submit" value="<?php echo lang_get( 'bug_assign_button' ) ?>" />
		</form>
		<?php } #end of checking if current user already assigned ?>
	</td>
<?php
	} # ASSIGN form END
?>
<?php # RESOLVE form BEGIN ?>
<?php if ( empty( $f_check ) && access_level_check_greater_or_equal( config_get( 'handle_bug_threshold' ) ) && ( $t_bug->status < RESOLVED ) ) { ?>
	<td class="center">
		<form method="post" action="bug_resolve_page.php">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="submit" value="<?php echo lang_get( 'resolve_bug_button' ) ?>" />
		</form>
	</td>
<?php
	} # RESOLVE form END
?>
<?php # REOPEN form BEGIN ?>
<?php if ( empty( $f_check ) && ( $t_bug->status >= RESOLVED ) &&
		( access_level_check_greater_or_equal( config_get( 'reopen_bug_threshold' ) ) ||
		( $t_bug->reporter_id == $t_user_id  && ON == config_get( 'allow_reporter_reopen' ) ) ) ) { ?>
	<td class="center">
		<form method="post" action="bug_reopen_page.php">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="submit" value="<?php echo lang_get( 'reopen_bug_button' ) ?>" />
		</form>
	</td>
<?php
	} # REOPEN form END
?>
<?php # CLOSE form BEGIN ?>
<?php if ( empty( $f_check ) && ( access_level_check_greater_or_equal( config_get( 'close_bug_threshold' ) ) ||
		( ON == config_get( 'allow_reporter_close' ) &&
		  $t_bug->reporter_id == $t_user_id ) ) &&
		( RESOLVED == $t_bug->status ) ) { ?>
	<td class="center">
		<form method="post" action="bug_close_page.php">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="submit" value="<?php echo lang_get( 'close_bug_button' ) ?>" />
		</form>
	</td>
<?php
	} # CLOSE form END
?>
<?php # MONITOR form BEGIN ?>
<?php
 if ( empty( $f_check ) && (( PUBLIC == $t_bug->view_state && access_level_check_greater_or_equal( config_get( 'monitor_bug_threshold' ) ) ) || ( PRIVATE == $t_bug->view_state && access_level_check_greater_or_equal( config_get( 'private_bug_threshold' ) ) )) && ! user_is_monitoring_bug( auth_get_current_user_id(), $f_bug_id ) ) {
?>
	<td class="center">
		<form method="post" action="bug_monitor.php">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="f_action" value="add" />
		<input type="submit" value="<?php echo lang_get( 'monitor_bug_button' ) ?>" />
		</form>
	</td>
<?php
	}
	# MONITOR form END
?>
<?php # UNMONITOR form BEGIN ?>
<?php if ( empty( $f_check ) && access_level_check_greater_or_equal( config_get( 'monitor_bug_threshold' ) ) && user_is_monitoring_bug( auth_get_current_user_id(), $f_bug_id ) ) { ?>
	<td class="center">
		<form method="post" action="bug_monitor.php">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="f_action" value="delete" />
		<input type="submit" value="<?php echo lang_get( 'unmonitor_bug_button' ) ?>" />
		</form>
	</td>
<?php
	}
	# MONITOR form END
?>
<?php # DELETE form BEGIN ?>
<?php if ( empty( $f_check ) && access_level_check_greater_or_equal( config_get( 'allow_bug_delete_access_level' ) ) ) { ?>
	<td class="center">
		<form method="post" action="bug_delete.php">
		<input type="hidden" name="f_bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="submit" value="<?php echo lang_get( 'delete_bug_button' ) ?>" />
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
	if ( empty( $f_check ) && $t_show_attachments && $t_bug->status < RESOLVED && access_level_check_greater_or_equal( REPORTER ) ) {
		include( config_get( 'bug_file_upload_inc' ) );
	}

	include( config_get( 'bugnote_include_file' ) );

	if ( $f_history ) {
		include( config_get( 'history_include_file' ) );
	}

	print_page_bot1( __FILE__ );
	compress_stop();
?>
