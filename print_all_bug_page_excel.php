<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Excel (97 and above) export page
	# The bugs displayed in print_all_bug_page.php are saved in a .xls file
	# The IE icon allows to see or directly print the same result
?>
<?php include( 'core_API.php' ) ?>
<?php require( 'print_all_bug_options_inc.php' ) ?>
<?php login_cookie_check() ?>
<?php

	# excel or html export
	if ( $f_type_page != 'html' ) {
		$t_export_title = $g_page_title."_excel";
		$t_export_title = ereg_replace('[\/:*?"<>|]', '', $t_export_title);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="' . $t_export_title . '.xls"');
	}

	# check to see if the cookie does not exist
	if ( empty( $g_view_all_cookie_val ) ) {
		print_header_redirect( 'view_all_set.php?f_type=0&amp;f_print=1' );
	}

	# check to see if new cookie is needed
	$t_setting_arr 			= explode( '#', $g_view_all_cookie_val );
	if ( $t_setting_arr[0] != $g_cookie_version ) {
		print_header_redirect( 'view_all_set.php?f_type=0&amp;f_print=1' );
	}

	check_varset( $f_search, false );
	check_varset( $f_offset, 0 );
	check_varset( $f_type_page, 'excel' );

	# Load preferences
	$f_show_category 		= $t_setting_arr[1];
	$f_show_severity	 	= $t_setting_arr[2];
	$f_show_status 			= $t_setting_arr[3];
	$f_per_page 			= $t_setting_arr[4];
	$f_highlight_changed 	= $t_setting_arr[5];
	$f_hide_closed 			= $t_setting_arr[6];
	$f_user_id 				= $t_setting_arr[7];
	$f_assign_id 			= $t_setting_arr[8];
	$f_sort 				= $t_setting_arr[9];
	$f_dir		 			= $t_setting_arr[10];
	$f_start_month			= $t_setting_arr[11];
	$f_start_day 			= $t_setting_arr[12];
	$f_start_year 			= $t_setting_arr[13];
	$f_end_month 			= $t_setting_arr[14];
	$f_end_day				= $t_setting_arr[15];
	$f_end_year				= $t_setting_arr[16];

	# Clean input
	$c_offset 				= (integer)$f_offset;
	$c_user_id				= (integer)$f_user_id;
	$c_assign_id			= (integer)$f_assign_id;
	$c_per_page				= (integer)$f_per_page;
	$c_show_category		= addslashes($f_show_category);
	$c_show_severity		= addslashes($f_show_severity);
	$c_show_status			= addslashes($f_show_status);
	$c_search				= addslashes($f_search);
	$c_sort					= addslashes($f_sort);

	if ('DESC' == $f_dir) {
		$c_dir = 'DESC';
	} else {
		$c_dir = 'ASC';
	}

	# Limit reporters to only see their reported bugs
	if (( ON == $g_limit_reporters ) &&
		( !access_level_check_greater_or_equal( UPDATER  ) )) {
		$c_user_id = get_current_user_field( 'id' );
	}

	# Build our query string based on our viewing criteria

	$query = 'SELECT DISTINCT *, UNIX_TIMESTAMP(last_updated) as last_updated, UNIX_TIMESTAMP(date_submitted) as date_submitted
			 FROM $g_mantis_bug_table';

	# project selection
	if ( '0000000' == $g_project_cookie_val ) { # ALL projects
		$t_access_level = get_current_user_field( 'access_level' );
		$t_user_id = get_current_user_field( 'id' );

		$t_pub = PUBLIC;
		$t_prv = PRIVATE;
		$query2 = "SELECT DISTINCT( p.id )
			FROM $g_mantis_project_table p, $g_mantis_project_user_list_table u
			WHERE (p.enabled=1 AND
				p.view_state='$t_pub') OR
				(p.enabled=1 AND
				p.view_state='$t_prv' AND
				u.user_id='$t_user_id'  AND
				u.project_id=p.id)
			ORDER BY p.name";
		$result2 = db_query( $query2 );
		$project_count = db_num_rows( $result2 );

		if ( 0 == $project_count ) {
			$t_where_clause = ' WHERE 1=1';
		} else {
			$t_where_clause = ' WHERE (';
			for ($i=0;$i<$project_count;$i++) {
				$row = db_fetch_array( $result2 );
				extract( $row, EXTR_PREFIX_ALL, 'v' );

				$t_where_clause .= "(project_id='$v_id')";
				if ( $i < $project_count - 1 ) {
					$t_where_clause .= ' OR ';
				}
			} # end for
			$t_where_clause .= ')';
		}
	} else {
		$t_where_clause = " WHERE project_id='$g_project_cookie_val'";
	}
	# end project selection

	if ( $c_user_id != 'any' ) {
		$t_where_clause .= " AND reporter_id='$c_user_id'";
	}

	if ( 'none' == $f_assign_id ) {
		$t_where_clause .= ' AND handler_id=0';
	} else if ( $f_assign_id != 'any' ) {
		$t_where_clause .= " AND handler_id='$c_assign_id'";
	}

	$t_clo_val = CLOSED;
	if ( ( 'on' == $f_hide_closed  )&&( 'closed' != $f_show_status )) {
		$t_where_clause = $t_where_clause." AND status<>'$t_clo_val'";
	}

	if ( $f_show_category != 'any' ) {
		$t_where_clause = $t_where_clause." AND category='$c_show_category'";
	}
	if ( $f_show_severity != 'any' ) {
		$t_where_clause = $t_where_clause." AND severity='$c_show_severity'";
	}
	if ( $f_show_status != 'any' ) {
		$t_where_clause = $t_where_clause." AND status='$c_show_status'";
	}

	# Simple Text Search - Thnaks to Alan Knowles
	if ($f_search) {
		$t_columns_clause = " $g_mantis_bug_table.*";

		$t_where_clause .= " AND ((summary LIKE '%$c_search%')
							OR (description LIKE '%$c_search%')
							OR (steps_to_reproduce LIKE '%$c_search%')
							OR (additional_information LIKE '%$c_search%')
							OR ($g_mantis_bug_table.id LIKE '%$c_search%')
							OR ($g_mantis_bugnote_text_table.note LIKE '%$c_search%'))
							AND $g_mantis_bug_text_table.id = $g_mantis_bug_table.bug_text_id";

		$t_from_clause = " FROM $g_mantis_bug_table, $g_mantis_bug_text_table
							LEFT JOIN $g_mantis_bugnote_table      ON $g_mantis_bugnote_table.bug_id  = $g_mantis_bug_table.id
							LEFT JOIN $g_mantis_bugnote_text_table ON $g_mantis_bugnote_text_table.id = $g_mantis_bugnote_table.bugnote_text_id ";
	} else {
		$t_columns_clause = ' *';
		$t_from_clause = " FROM $g_mantis_bug_table";
	}

	if ( empty($c_sort) ) {
		$c_sort='last_updated';
	}
	$query  = 'SELECT DISTINCT '.$t_columns_clause.', UNIX_TIMESTAMP(last_updated) as last_updated, UNIX_TIMESTAMP(date_submitted) as date_submitted';
	$query .= $t_from_clause;
	$query .= $t_where_clause;

	$query = $query." ORDER BY '$c_sort' $c_dir";
	if ( $f_sort != 'priority' ) {
		$query = $query.', priority DESC';
	}

	$query = $query." LIMIT $c_offset, $c_per_page";

	# perform query
    $result = db_query( $query );
	$row_count = db_num_rows( $result );

	#settings for choosing the fields to print
	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count($t_field_name_arr);

	# get printing preferences
	$t_user_id = get_current_user_field( 'id' );
	$query3 ="SELECT print_pref ".
			"FROM $g_mantis_user_print_pref_table ".
			"WHERE user_id='$t_user_id' ";

	$result3 = db_query( $query3 );
	$row = db_fetch_array($result3);
	$t_prefs = $row[0];
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<?php print_page_top1() ?>
<style id="Classeur1_16681_Styles">
</style>


<?php print_head_bottom() ?>
<?php print_body_top() ?>

<div id="Classeur1_16681" align=center x:publishsource="Excel">

<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style='border-collapse:
 collapse'>

<tr>
	<?php
		# titles desactivated for html pages
		if ( $f_type_page != 'html' ) {
			for ($i=0 ; $i <$field_name_count ; $i++) {
				if ( isset( $t_prefs[$i] ) && ( 1 == $t_prefs[$i] ) ) {  ?>
	<td class=xl2316681 style='border-left:none'><?php echo $t_field_name_arr[$i] ?></td>
<?php
				} //if isset
			} //for
		} //if
?>
</tr>

<?php
	$field_name_count = $field_name_count;

	$f_bug_arr = explode_enum_string( $f_export );

	for($i=0; $i < $row_count; $i++) {
		if ( isset($f_bug_arr[$i]) ) {
			$index = $f_bug_arr[$i];
			$t_bug_arr_sort[$index]=1;
		}
	}

	for($i=0; $i < $row_count; $i++) {

		# prefix bug data with v_
		$row = db_fetch_array($result);

		extract( $row, EXTR_PREFIX_ALL, 'v' );

		$v_summary = string_display( $v_summary );
		$t_last_updated = date( $g_short_date_format, $v_last_updated );

		# grab the bugnote count
		$bugnote_count = get_bugnote_count( $v_id );

		# grab the project name
		$project_name = get_project_field( $v_project_id, 'name' );

		$query4 = "SELECT *
			FROM $g_mantis_bug_text_table
			WHERE id='$v_bug_text_id'";
		$result4 = db_query( $query4 );
		$row = db_fetch_array( $result4 );
		extract( $row, EXTR_PREFIX_ALL, 'v2' );

		$v_os 						= string_display( $v_os );
		$v_os_build					= string_display( $v_os_build );
		$v_platform					= string_display( $v_platform );
		$v_version 					= string_display( $v_version );
		$v_summary 					= string_display( $v_summary );

		# line feeds are desactivated in case of excel export, to avoid multiple lines
		if ($f_type_page != 'html' ) {
				$v2_description = stripslashes(str_replace('\n',' ',$v2_description));
				$v2_steps_to_reproduce  = stripslashes(str_replace('\n',' ',$v2_steps_to_reproduce ));
				$v2_additional_information = stripslashes(str_replace('\n',' ',$v2_additional_information));
		}
		else {
			$v2_description 			= string_display( $v2_description );
			$v2_steps_to_reproduce 		= string_display( $v2_steps_to_reproduce );
			$v2_additional_information 	= string_display( $v2_additional_information );
		}

		# an index for incrementing the array position
		$name_index=0;

		if (isset($t_bug_arr_sort[$i])||($f_show_flag==0)) {
?>
<tr>
	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_id;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_category;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element ('severity', $v_severity);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element ('reproducibility', $v_reproducibility);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo date($g_normal_date_format,$v_date_submitted);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo date($g_normal_date_format,$v_last_updated);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php print_user_with_subject( $v_reporter_id, $v_id ) ;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php print_user_with_subject( $v_handler_id, $v_id ) ;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element ('priority', $v_priority);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element ('status', $v_status);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_build;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element ('projection', $v_projection);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element ('eta', $v_eta);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_platform;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_os;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_os_build;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_version;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element ('resolution', $v_resolution);
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_duplicate_id;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_summary;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v2_description ;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v2_steps_to_reproduce;
			}
	$name_index++;  ?>
	</td>

	<?php if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v2_additional_information;
			}
	$name_index++;  ?>
	</td>

<?php
	if (( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
<?php
			$query5 = "SELECT *
					FROM $g_mantis_bug_file_table
					WHERE bug_id='$v_id'";
			$result5 = db_query( $query5 );
			$num_files = db_num_rows( $result5 );
			for ($j=0;$j<$num_files;$j++) {
				$row = db_fetch_array( $result5 );
				extract( $row, EXTR_PREFIX_ALL, 'v2' );
				$v2_filesize = round( $v2_filesize / 1024 );
				$v2_date_added = date( $g_normal_date_format, ( $v2_date_added ) );
					switch ( $g_file_upload_method ) {
					case DISK:	PRINT "$v2_filename ($v2_filesize KB) $v2_date_added";
							break;
					case DATABASE:	PRINT "$v2_filename ($v2_filesize KB) $v2_date_added";
							break;
				} #case
				if ( $j != ($num_files - 1) &&  ( $f_type_page == 'html' ) ) {
					PRINT '<br />';
				}
				else {
					PRINT '&nbsp';
				} #if
			} #for loop
		}# if index
	$name_index++;
?>
	</td>
<?php # Bugnotes BEGIN (3 rows) ?>
<td colspan=3>
<?php  # print bugnotes
		# get the bugnote data
		$query6 = "SELECT *,UNIX_TIMESTAMP(date_submitted) as date_submitted
				FROM $g_mantis_bugnote_table
				WHERE bug_id='$v_id'
				ORDER BY date_submitted $g_bugnote_order";
		$result6 = db_query($query6);
		$num_notes = db_num_rows($result6);

		# save the index, and use an own bugnote_index
		$bugnote_index = $name_index ;

		for ( $k=0; $k < $num_notes; $k++ ) {
			# prefix all bugnote data with v3_
			$row = db_fetch_array( $result6 );
			extract( $row, EXTR_PREFIX_ALL, 'v3' );
			$v3_date_submitted = date( $g_normal_date_format, ( $v3_date_submitted ) );

			# grab the bugnote text and id and prefix with v3_
			$query6 = "SELECT note, id
					FROM $g_mantis_bugnote_text_table
					WHERE id='$v3_bugnote_text_id'";
			$result7 = db_query( $query6 );
			$v3_note = db_result( $result7, 0, 0 );
			$v3_bugnote_text_id = db_result( $result7, 0, 1 );

			if ($f_type_page != 'html' ) {
				$v3_note = stripslashes(str_replace('\n','|',$v3_note));
				}
			else {
					$v3_note = string_display( $v3_note );
				}
	?>
<table>
<tr>
	<?php if (( $bugnote_index < $field_name_count ) && ( !isset( $t_prefs[$bugnote_index] )||( 1 == $t_prefs[$bugnote_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php print_user( $v3_reporter_id ) ;
			}
	$bugnote_index++;  ?>
	</td>

	<?php if (( $bugnote_index < $field_name_count ) && ( !isset( $t_prefs[$bugnote_index] )||( 1 == $t_prefs[$bugnote_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v3_date_submitted;
			}
	$bugnote_index++;  ?>
	</td>

	<?php if (( $bugnote_index < $field_name_count ) && ( !isset( $t_prefs[$bugnote_index] )||( 1 == $t_prefs[$bugnote_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v3_note;
			}
	$bugnote_index++;  ?>
	</td>
</tr>
</table>
	<?php
		# get back
		$bugnote_index = $name_index ;
			} # end for bugnote
	?>
<?php # Bugnotes END ?>

</tr>
<?php
		} #isset
} #for loop
?>
</table>
</div>
