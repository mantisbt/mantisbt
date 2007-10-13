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
	# $Id: print_all_bug_page_word.php,v 1.65.2.1 2007-10-13 22:34:17 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# Word 2000 export page
	# The bugs displayed in print_all_bug_page.php are saved in a .doc file
	# The IE icon allows to see or directly print the same result
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_type_page	= gpc_get_string( 'type_page', 'word' );
	$f_search		= gpc_get_string( 'search', false ); # @@@ need a better default
	$f_offset		= gpc_get_int( 'offset', 0 );
	$f_export		= gpc_get_string( 'export' );
	$f_show_flag	= gpc_get_bool( 'show_flag' );

	helper_begin_long_process();

	# word or html export
	if ( $f_type_page != 'html' ) {
		$t_export_title = helper_get_default_export_filename( '' );
		$t_export_title = ereg_replace( '[\/:*?"<>|]', '', $t_export_title );

		# Make sure that IE can download the attachments under https.
		header( 'Pragma: public' );

		header( 'Content-Type: application/msword' );

		if ( preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"] ) ) {
                        header( 'Content-Disposition: attachment; filename="' . urlencode( $t_export_title ) . '.doc"' );
                } else {
                        header( 'Content-Disposition: attachment; filename="' . $t_export_title . '.doc"' );
                }
	}

	# This is where we used to do the entire actual filter ourselves
	$t_page_number = gpc_get_int( 'page_number', 1 );
	$t_per_page = -1;
	$t_bug_count = null;
	$t_page_count = null;

	$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	$row_count = sizeof( $result );

?>

<?php # Word Export ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<?php html_page_top1() ?>
<?php html_head_end() ?>
<?php html_body_begin() ?>

<?php
	//$t_bug_arr_sort[$row_count]=-1;
	$f_bug_arr = explode_enum_string( $f_export );

	for( $j=0; $j < $row_count; $j++ ) {

		# prefix bug data with v_
		extract( $result[$j], EXTR_PREFIX_ALL, 'v' );

		# display the available and selected bugs
		if ( in_array( $v_id, $f_bug_arr ) || ( $f_show_flag==0 ) ) {

            $t_last_updated = date( $g_short_date_format, $v_last_updated );

            # grab the bugnote count
            $bugnote_count = bug_get_bugnote_count( $v_id );

            # grab the project name
            $t_project_name = project_get_field( $v_project_id, 'name' );

            # bug text infos
            $t_bug_text_table = config_get( 'mantis_bug_text_table' );
            $query3 = "SELECT *
                FROM $t_bug_text_table
                WHERE id='$v_bug_text_id'";
            $result3 = db_query( $query3 );
            $row = db_fetch_array( $result3 );
            extract( $row, EXTR_PREFIX_ALL, 'v2' );

            $v_os 						= string_display( $v_os );
            $v_os_build					= string_display( $v_os_build );
            $v_platform					= string_display( $v_platform );
            $v_version 					= string_display( $v_version );
            $v_summary 					= string_display_links( $v_summary );
            $v2_description 			= string_display_links( $v2_description );
            $v2_steps_to_reproduce 		= string_display_links( $v2_steps_to_reproduce );
            $v2_additional_information 	= string_display_links( $v2_additional_information );
            ### note that dates are converted to unix format in filter_get_bug_rows
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'viewing_bug_advanced_details_title' ) ?>
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" width="100%" />
	</td>
</tr>
<tr class="print-category">
	<td class="print" width="16%">
		<?php echo lang_get( 'id' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'category' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'severity' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'reproducibility' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'date_submitted' ) ?>:
	</td>
	<td class="print" width="16%">
		<?php echo lang_get( 'last_update' ) ?>:
	</td>
</tr>
<tr class="print">
	<td class="print">
		<?php echo $v_id ?>
	</td>
	<td class="print">
		<?php echo "[$t_project_name] $v_category" ?>
	</td>
	<td class="print">
		<?php echo get_enum_element( 'severity', $v_severity ) ?>
	</td>
	<td class="print">
		<?php echo get_enum_element( 'reproducibility', $v_reproducibility ) ?>
	</td>
	<td class="print">
		<?php print_date( config_get( 'normal_date_format' ), $v_date_submitted ) ?>
	</td>
	<td class="print">
		<?php print_date( config_get( 'normal_date_format' ), $v_last_updated ) ?>
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" width="100%" />
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'reporter' ) ?>:
	</td>
	<td class="print">
		<?php print_user_with_subject( $v_reporter_id, $v_id ) ?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'platform' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_platform ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'assigned_to' ) ?>:
	</td>
	<td class="print">
		<?php 
			if ( access_has_bug_level( config_get( 'view_handler_threshold' ), $v_id ) ) {
				print_user_with_subject( $v_handler_id, $v_id ); 
			}
		?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'os' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_os ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'priority' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'priority', $v_priority ) ?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'os_version' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_os_build ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'status' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'status', $v_status ) ?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'product_version' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_version ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'product_build' ) ?>:
	</td>
	<td class="print">
		<?php echo $v_build?>
	</td>
	<td class="print-category">
		<?php echo lang_get( 'resolution' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'resolution', $v_resolution ) ?>
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'projection' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'projection', $v_projection ) ?>
	</td>
	<td class="print-category">
		<?php
			if ( !config_get( 'enable_relationship' ) ) {
				echo lang_get( 'duplicate_id' );
			} # MASC RELATIONSHIP
		?>&nbsp;
	</td>
	<td class="print">
		<?php
			if ( !config_get( 'enable_relationship' ) ) {
				print_duplicate_id( $v_duplicate_id );
			} # MASC RELATIONSHIP
		?>&nbsp;
	</td>
	<td class="print" colspan="2">&nbsp;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'eta' ) ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'eta', $v_eta ) ?>
	</td>
	<td class="print" colspan="4">&nbsp;</td>
</tr>
<?php
$t_related_custom_field_ids = custom_field_get_linked_ids( $v_project_id );
foreach( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
?>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get_defaulted( $t_def['name'] ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php print_custom_field_value( $t_def, $t_id, $v_id ); ?>
	</td>
</tr>
<?php
}       // foreach
?>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" width="100%" />
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'summary' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v_summary ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'description' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v2_description ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v2_steps_to_reproduce ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'additional_information' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v2_additional_information ?>
	</td>
</tr>
<?php
	# account profile description
	if ( $v_profile_id > 0 ) {
	   $t_user_prof_table = config_get( 'mantis_user_profile_table' );
		$query4 = "SELECT description
				FROM $t_user_prof_table
				WHERE id='$v_profile_id'";
		$result4 = db_query( $query4 );
		$t_profile_description = '';
		if ( db_num_rows( $result4 ) > 0 ) {
			$t_profile_description = db_result( $result4, 0 );
		}
		$t_profile_description = string_display( $t_profile_description );

?>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'system_profile' ) ?>
	</td>
	<td class="print" colspan="5">
		<?php echo $t_profile_description ?>
	</td>
</tr>
<?php
	} # profile description
?>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get( 'attached_files' ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php
	        $t_bug_file_table = config_get( 'mantis_bug_file_table' );
			$query5 = "SELECT filename, filesize, date_added
					FROM $t_bug_file_table
					WHERE bug_id='$v_id'";
			$result5 = db_query( $query5 );
			$num_files = db_num_rows( $result5 );
			for ( $i=0;$i<$num_files;$i++ ) {
				$row = db_fetch_array( $result5 );
				extract( $row, EXTR_PREFIX_ALL, 'v2' );
				$v2_filename = file_get_display_name( $v2_filename );
				$v2_filesize = round( $v2_filesize / 1024 );
				$v2_date_added = date( config_get( 'normal_date_format' ), db_unixtimestamp( $v2_date_added ) );

				switch ( $g_file_upload_method ) {
					case DISK:	PRINT "$v2_filename ($v2_filesize KB) <span class=\"italic\">$v2_date_added</span>";
							break;
					case DATABASE:	PRINT "$v2_filename ($v2_filesize KB) <span class=\"italic\">$v2_date_added</span>";
							break;
				}

				if ( $i != ( $num_files - 1 ) ) {
					PRINT '<br />';
				}
			}
		?>
	</td>
</tr>
<?php
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
?>

<?php # Bugnotes BEGIN ?>
<br />
<table class="width100" cellspacing="1">
<?php
	# no bugnotes
	if ( 0 == $num_notes ) {
	?>
<tr>
	<td class="print" colspan="2">
		<?php echo lang_get( 'no_bugnotes_msg' ) ?>
	</td>
</tr>
	<?php }
		else { # print bugnotes ?>
<tr>
	<td class="form-title" colspan="2">
			<?php echo lang_get( 'bug_notes_title' ) ?>
	</td>
</tr>
	<?php
		for ( $k=0; $k < $num_notes; $k++ ) {
			# prefix all bugnote data with v3_
			$row = db_fetch_array( $result6 );
			extract( $row, EXTR_PREFIX_ALL, 'v3' );
			$v3_date_submitted = date( config_get( 'normal_date_format' ), ( db_unixtimestamp( $v3_date_submitted ) ) );
			$v3_last_modified = date( config_get( 'normal_date_format' ), ( db_unixtimestamp( $v3_last_modified ) ) );

			# grab the bugnote text and id and prefix with v3_
			$query6 = "SELECT note, id
					FROM $t_bugnote_text_table
					WHERE id='$v3_bugnote_text_id'";
			$result7 = db_query( $query6 );
			$v3_note = db_result( $result7, 0, 0 );
			$v3_bugnote_text_id = db_result( $result7, 0, 1 );

			$v3_note = string_display_links( $v3_note );
	?>
<tr>
	<td class="print-spacer" colspan="2">
		<hr size="1" width="100%" />
	</td>
</tr>
<tr>
	<td class="nopad" valign="top" width="20%">
		<table class="hide" cellspacing="1">
		<tr>
			<td class="print">
				(<?php echo bugnote_format_id( $v3_id ) ?>)
			</td>
		</tr>
		<tr>
			<td class="print">
				<?php print_user( $v3_reporter_id ) ?>&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="print">
				<?php echo $v3_date_submitted ?>&nbsp;&nbsp;&nbsp;
				<?php if ( db_unixtimestamp( $v3_date_submitted ) != db_unixtimestamp( $v3_last_modified ) ) {
					echo '<br />(' . lang_get( 'edited_on').' '. $v3_last_modified . ')';
				} ?>
			</td>
		</tr>
		</table>
	</td>
	<td class="nopad" valign="top" width="85%">
		<table class="hide" cellspacing="1">
		<tr>
			<td class="print">
				<?php
					switch ( $v3_note_type ) {
						case REMINDER:
							echo lang_get( 'reminder_sent_to' ) . ': ';
							$v3_note_attr = substr( $v3_note_attr, 1, strlen( $v3_note_attr ) - 2 );
							$t_to = array();
							foreach ( explode( '|', $v3_note_attr ) as $t_recipient ) {
								$t_to[] = prepare_user_name( $t_recipient );
							}
							echo implode( ', ', $t_to ) . '<br />';
						default:
							echo $v3_note;
					}
				?>
			</td>
		</tr>
		</table>
	</td>
</tr>
<?php
		} # end for
	} # end else
?>

</table>
<?php # Bugnotes END ?>
</table>


<?php
echo '<br /><br />';
		} # end in_array
}  # end main loop
?>
