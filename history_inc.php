<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?php
	# This include file prints out the bug history
?>
<?php # Bug History BEGIN ?>
<a name="history"><p></a>
<?php
	# grab history and display by date_modified then field_name
	$query = "SELECT b.*, UNIX_TIMESTAMP(b.date_modified) as date_modified, u.username
			FROM $g_mantis_bug_history_table b
			LEFT JOIN $g_mantis_user_table u
			ON b.user_id=u.id
			WHERE bug_id='$f_id'
			ORDER BY date_modified $g_history_order, field_name ASC";
	$result = db_query( $query );
	$history_count = db_num_rows( $result );
?>
<table class="width100" cellspacing="0">
<tr>
	<td class="form-title" colspan="4">
		<?php echo $s_bug_history ?>
	</td>
</tr>
<tr class="row-category">
	<td class="small-caption">
		<?php echo $s_date_modified ?>
	</td>
	<td class="small-caption">
		<?php echo $s_username ?>
	</td>
	<td class="small-caption">
		<?php echo $s_field ?>
	</td>
	<td class="small-caption">
		<?php echo $s_old_value ?> => <?php echo $s_new_value ?>
	</td>
</tr>
<?php
	for ( $i=0; $i < $history_count; $i++ ) {
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$status_color = alternate_colors( $i+1, $g_background_color );

		$v_date_modified = date( $g_complete_date_format, $v_date_modified );

		switch ( $v_field_name ) {
		case 'status':			$v_old_value = get_enum_element( 'status', $v_old_value );
								$v_new_value = get_enum_element( 'status', $v_new_value );
				break;
		case 'severity':		$v_old_value = get_enum_element( 'severity', $v_old_value );
								$v_new_value = get_enum_element( 'severity', $v_new_value );
				break;
		case 'reproducibility':	$v_old_value = get_enum_element( 'reproducibility', $v_old_value );
								$v_new_value = get_enum_element( 'reproducibility', $v_new_value );
				break;
		case 'resolution':		$v_old_value = get_enum_element( 'resolution', $v_old_value );
								$v_new_value = get_enum_element( 'resolution', $v_new_value );
				break;
		case 'priority':		$v_old_value = get_enum_element( 'priority', $v_old_value );
								$v_new_value = get_enum_element( 'priority', $v_new_value );
				break;
		case 'eta':				$v_old_value = get_enum_element( 'eta', $v_old_value );
								$v_new_value = get_enum_element( 'eta', $v_new_value );
				break;
		case 'view_state':		$v_old_value = get_enum_element( 'view_state', $v_old_value );
								$v_new_value = get_enum_element( 'view_state', $v_new_value );

				break;
		case 'projection':		$v_old_value = get_enum_element( 'projection', $v_old_value );
								$v_new_value = get_enum_element( 'projection', $v_new_value );
				break;
		case 'project_id':		$v_old_value = get_project_field( $v_old_value, 'name' );
								$v_new_value = get_project_field( $v_new_value, 'name' );
				break;
		case 'handler_id':
		case 'reporter_id':		$v_old_value = get_user_info( $v_old_value, 'username' );
								$v_new_value = get_user_info( $v_new_value, 'username' );
				break;
		}

		if ( NORMAL_TYPE != $v_type ) {
			switch ( $v_type ) {
			case NEW_BUG:					$t_note = $s_new_bug;
											break;
			case BUGNOTE_ADDED:				$t_note = $s_bugnote_added;
											break;
			case BUGNOTE_UPDATED:			$t_note = $s_bugnote_edited;
											break;
			case BUGNOTE_DELETED:			$t_note = $s_bugnote_deleted;
											break;
			case SUMMARY_UPDATED:			$t_note = $s_summary_updated;
											break;
			case DESCRIPTION_UPDATED:		$t_note = $s_description_updated;
											break;
			case ADDITIONAL_INFO_UPDATED:	$t_note = $s_additional_information_updated;
											break;
			case STEP_TO_REPRODUCE_UPDATED:	$t_note = $s_steps_to_reproduce_updated;
											break;
			case FILE_ADDED:				$t_note = $s_file_added.$v_old_value;
											break;
			case FILE_DELETED:				$t_note = $s_file_deleted.$v_old_value;
											break;
			}
		}
		if ( NORMAL_TYPE != $v_type ) {
?>
<tr bgcolor="<?php echo $status_color ?>">
	<td class="small-caption">
		<?php echo $v_date_modified ?>
	</td>
	<td class="small-caption">
		<?php echo $v_username ?>
	</td>
	<td class="small-caption">
		&nbsp;
	</td>
	<td class="small-caption">
		<?php echo $t_note ?>
	</td>
</tr>
<?php
		} else {
?>
<tr bgcolor="<?php echo $status_color ?>">
	<td class="small-caption">
		<?php echo $v_date_modified ?>
	</td>
	<td class="small-caption">
		<?php echo $v_username ?>
	</td>
	<td class="small-caption">
		<?php echo $v_field_name ?>
	</td>
	<td class="small-caption">
		<?php echo $v_old_value ?> => <?php echo $v_new_value ?>
	</td>
</tr>
<?php
		} # end if DEFAULT
	} # end for loop
?>
</table>
<?php # Bug History END ?>