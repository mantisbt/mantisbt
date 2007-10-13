<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: print_all_bug_page_excel.php,v 1.56.2.1 2007-10-13 22:34:16 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# Excel (97 and above) export page
	# The bugs displayed in print_all_bug_page.php are saved in a .xls file
	# The IE icon allows to see or directly print the same result
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'string_api.php' );
?>
<?php require( 'print_all_bug_options_inc.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_type_page	= gpc_get_string( 'type_page', 'excel' );
	$f_search		= gpc_get_string( 'search', false ); # @@@ need a better default
	$f_offset		= gpc_get_int( 'offset', 0 );
	$f_export		= gpc_get_string( 'export' );
	$f_show_flag	= gpc_get_bool( 'show_flag' );

	helper_begin_long_process();

	# excel or html export
	if ( $f_type_page != 'html' ) {
		$t_export_title = helper_get_default_export_filename( '' );
		$t_export_title = ereg_replace( '[\/:*?"<>|]', '', $t_export_title );

		# Make sure that IE can download the attachments under https.
		header( 'Pragma: public' );

		header( 'Content-Type: application/vnd.ms-excel' );

                if ( preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"] ) ) {
                        header( 'Content-Disposition: attachment; filename="' . urlencode( $t_export_title ) . '.xls"' ) ;
                } else {
                        header( 'Content-Disposition: attachment; filename="' . $t_export_title . '.xls"' );
                }
	}

	#settings for choosing the fields to print
	# get the fields list
	$t_field_name_arr = get_field_names();

	# This is where we used to do the entire actual filter ourselves
	$t_page_number = gpc_get_int( 'page_number', 1 );
	$t_per_page = -1;
	$t_bug_count = null;
	$t_page_count = null;

	$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	if ( $result === false ) {
		print_header_redirect( 'view_all_set.php?type=0&amp;print=1' );
	}
	$row_count = sizeof( $result );

	#settings for choosing the fields to print
	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count( $t_field_name_arr );

	# get printing preferences
	$t_user_id = auth_get_current_user_id();
	$t_user_print_pref_table = config_get( 'mantis_user_print_pref_table' );
	$query3 ="SELECT print_pref ".
			"FROM $t_user_print_pref_table ".
			"WHERE user_id='$t_user_id' ";

	$result3 = db_query( $query3 );
	$row = db_fetch_array( $result3 );
	$t_prefs = $row['print_pref'];
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<?php html_page_top1() ?>
<style id="Classeur1_16681_Styles">
</style>

<?php html_head_end() ?>
<?php html_body_begin() ?>

<div id="Classeur1_16681" align=center x:publishsource="Excel">

<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style='border-collapse:
 collapse'>

<tr>
	<?php
		# titles desactivated for html pages
		if ( $f_type_page != 'html' ) {
			for ( $i=0 ; $i <$field_name_count ; $i++ ) {
				if ( isset( $t_prefs[$i] ) && ( 1 == $t_prefs[$i] ) ) {  ?>
	<td class=xl2316681 style='border-left:none'><?php echo lang_get( $t_field_name_arr[$i] ) ?></td>
<?php
				} //if isset
			} //for
		} //if
?>
</tr>

<?php
	$field_name_count = $field_name_count;

	$f_bug_arr = explode_enum_string( $f_export );

	# @@debug var_dump($t_field_name_arr);
	for( $i=0; $i < $row_count; $i++ ) {

		# prefix bug data with v_
		extract( $result[$i], EXTR_PREFIX_ALL, 'v' );

		if ( in_array( $v_id, $f_bug_arr ) || ( $f_show_flag==0 ) ) {
            $t_last_updated = date( $g_short_date_format, $v_last_updated );

            # grab the bugnote count
            $bugnote_count = bug_get_bugnote_count( $v_id );

            # grab the project name
            $project_name = project_get_field( $v_project_id, 'name' );

            $t_bug_text_table = config_get( 'mantis_bug_text_table' );
            $query4 = "SELECT *
                FROM $t_bug_text_table
                WHERE id='$v_bug_text_id'";
            $result4 = db_query( $query4 );
            $row = db_fetch_array( $result4 );
            extract( $row, EXTR_PREFIX_ALL, 'v2' );

            $v_os 						= string_display( $v_os );
            $v_os_build					= string_display( $v_os_build );
            $v_platform					= string_display( $v_platform );
            $v_version 					= string_display( $v_version );
            $v_summary 					= string_display_links( $v_summary );

            # line feeds are desactivated in case of excel export, to avoid multiple lines
            if ( $f_type_page != 'html' ) {
				$v2_description = stripslashes( htmlspecialchars( str_replace( '\n',' ',$v2_description ) ) );
				$v2_steps_to_reproduce  = stripslashes( htmlspecialchars( str_replace( '\n',' ',$v2_steps_to_reproduce ) ) );
				$v2_additional_information = stripslashes( htmlspecialchars( str_replace( '\n',' ',$v2_additional_information ) ) );
            } else {
                $v2_description 			= string_display_links( $v2_description );
                $v2_steps_to_reproduce 		= string_display_links( $v2_steps_to_reproduce );
                $v2_additional_information 	= string_display_links( $v2_additional_information );
            }

            # an index for incrementing the array position
            $name_index=0;

?>
<tr>
	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_id;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_category;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element( 'severity', $v_severity );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element( 'reproducibility', $v_reproducibility );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo date( config_get( 'normal_date_format' ), $v_date_submitted );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo date( config_get( 'normal_date_format' ),$v_last_updated );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php print_user_with_subject( $v_reporter_id, $v_id ) ;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php      if ( access_has_bug_level( config_get( 'view_handler_threshold' ), $v_id ) ) {
				    print_user_with_subject( $v_handler_id, $v_id ); 
                }
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element( 'priority', $v_priority );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element( 'status', $v_status );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_build;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element( 'projection', $v_projection );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element( 'eta', $v_eta );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_platform;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_os;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_os_build;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_version;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo get_enum_element( 'resolution', $v_resolution );
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_duplicate_id;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v_summary;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v2_description ;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v2_steps_to_reproduce;
		  echo "</td>";
			}
	$name_index++;  ?>

	<?php if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v2_additional_information;
		  echo "</td>";
			}
	$name_index++;  ?>

<?php
	if ( ( $name_index < $field_name_count ) && ( !isset( $t_prefs[$name_index] ) || ( 1 == $t_prefs[$name_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
<?php
        	$t_file_table = config_get( 'mantis_bug_file_table' );
			$query5 = "SELECT filename, filesize, date_added
					FROM $t_file_table
					WHERE bug_id='$v_id'";
			$result5 = db_query( $query5 );
			$num_files = db_num_rows( $result5 );
			for ( $j=0;$j<$num_files;$j++ ) {
				$row = db_fetch_array( $result5 );
				extract( $row, EXTR_PREFIX_ALL, 'v2' );
				$v2_filesize = round( $v2_filesize / 1024 );
				$v2_date_added = date( config_get( 'normal_date_format' ), db_unixtimestamp( $v2_date_added ) );
				echo "$v2_filename ($v2_filesize KB) $v2_date_added";

				if ( $j != ( $num_files - 1 ) &&  ( $f_type_page == 'html' ) ) {
					PRINT '<br />';
				}
				else {
					PRINT ' ';
				} #if
			} #for loop

			echo "</td>";
		}# if index
	$name_index++;
?>
<?php # Bugnotes BEGIN (3 rows) ?>
<td colspan="3">
<?php  # print bugnotes
		# get the bugnote data
 		if ( !access_has_bug_level( config_get( 'private_bugnote_threshold' ), $v_id ) ) {
 			$t_restriction = 'AND view_state=' . VS_PUBLIC;
 		} else {
 			$t_restriction = '';
 		}

		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_bugnote_order = current_user_get_pref( 'bugnote_order' );

		$query6 = "SELECT *
				FROM $t_bugnote_table
				WHERE bug_id='$v_id' $t_restriction
				ORDER BY date_submitted $t_bugnote_order";
		$result6 = db_query( $query6 );
		$num_notes = db_num_rows( $result6 );

		# save the index, and use an own bugnote_index
		$bugnote_index = $name_index ;

		for ( $k=0; $k < $num_notes; $k++ ) {
			# prefix all bugnote data with v3_
			$row = db_fetch_array( $result6 );
			extract( $row, EXTR_PREFIX_ALL, 'v3' );
			$v3_date_submitted = date( config_get( 'normal_date_format' ), ( db_unixtimestamp( $v3_date_submitted ) ) );

			# grab the bugnote text and id and prefix with v3_
			$query6 = "SELECT note, id
					FROM $t_bugnote_text_table
					WHERE id='$v3_bugnote_text_id'";
			$result7 = db_query( $query6 );
			$v3_note = db_result( $result7, 0, 0 );
			$v3_bugnote_text_id = db_result( $result7, 0, 1 );
			$t_note = '';

			switch ( $v3_note_type ) {
				case REMINDER:
					$t_note .= lang_get( 'reminder_sent_to' ) . ': ';
					$v3_note_attr = substr( $v3_note_attr, 1, strlen( $v3_note_attr ) - 2 );
					$t_to = array();
					foreach ( explode( '|', $v3_note_attr ) as $t_recipient ) {
						$t_to[] = prepare_user_name( $t_recipient );
					}
					$t_note .=  implode( ', ', $t_to ) . '|';
				default:
					$t_note .=  $v3_note;
			}
			if ( $f_type_page != 'html' ) {
				$v3_note = stripslashes( str_replace( '\n','|',$t_note ));
			} else {
				$v3_note = string_display_links( $t_note );
			}
	?>
<table>
<tr>
	<?php if ( ( $bugnote_index < $field_name_count ) && ( !isset( $t_prefs[$bugnote_index] )||( 1 == $t_prefs[$bugnote_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php print_user( $v3_reporter_id ) ;
		  echo "</td>";
			}
	$bugnote_index++;  ?>

	<?php if ( ( $bugnote_index < $field_name_count ) && ( !isset( $t_prefs[$bugnote_index] )||( 1 == $t_prefs[$bugnote_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v3_date_submitted;
		  echo "</td>";
			}
	$bugnote_index++;  ?>

	<?php if ( ( $bugnote_index < $field_name_count ) && ( !isset( $t_prefs[$bugnote_index] )||( 1 == $t_prefs[$bugnote_index] ) ) ) { ?>
	<td class=xl2216681 nowrap style='border-top:none;border-left:none'>
	<?php echo $v3_note;
		  echo "</td>";
			}
	$bugnote_index++;  ?>
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
		} #in_array
} #for loop
?>
</table>
</div>
