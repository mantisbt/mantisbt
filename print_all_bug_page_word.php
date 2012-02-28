<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * Word 2000 export page
	 * The bugs displayed in print_all_bug_page.php are saved in a .doc file
	 * The IE icon allows to see or directly print the same result
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );
	require_once( 'bug_api.php' );
	require_once( 'custom_field_api.php' );
	require_once( 'string_api.php' );
	require_once( 'date_api.php' );

	auth_ensure_user_authenticated();

	$f_type_page	= gpc_get_string( 'type_page', 'word' );
	$f_search		= gpc_get_string( 'search', false ); /** @todo need a better default */
	$f_offset		= gpc_get_int( 'offset', 0 );
	$f_export		= gpc_get_string( 'export' );
	$f_show_flag	= gpc_get_bool( 'show_flag' );

	helper_begin_long_process();

	# word or html export
	if ( $f_type_page != 'html' ) {
		$t_export_title = helper_get_default_export_filename( '' );
		$t_export_title = preg_replace( '/[\/:*?"<>|]/', '', $t_export_title );
		$t_export_title .= '.doc';

		# Make sure that IE can download the attachments under https.
		header( 'Pragma: public' );

		header( 'Content-Type: application/msword' );

		http_content_disposition_header( $t_export_title );
	}

	# This is where we used to do the entire actual filter ourselves
	$t_page_number = gpc_get_int( 'page_number', 1 );
	$t_per_page = -1;
	$t_bug_count = null;
	$t_page_count = null;

	$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	$t_row_count = count( $result );
?>

<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">

<?php
	html_page_top1();
	html_head_end();
	html_body_begin();

	$f_bug_arr = explode( ',', $f_export );
	$t_count_exported = 0;
	$t_date_format = config_get( 'normal_date_format' );
	$t_short_date_format = config_get( 'short_date_format' );

	$t_lang_bug_view_title = lang_get( 'bug_view_title' );
	$t_lang_id = lang_get( 'id' );
	$t_lang_category = lang_get( 'category' );
	$t_lang_severity = lang_get( 'severity' );
	$t_lang_reproducibility = lang_get( 'reproducibility' );
	$t_lang_date_submitted = lang_get( 'date_submitted' );
	$t_lang_last_update = lang_get( 'last_update' );
	$t_lang_reporter = lang_get( 'reporter' );
	$t_lang_assigned_to = lang_get( 'assigned_to' );
	$t_lang_platform = lang_get( 'platform' );
	$t_lang_due_date = lang_get( 'due_date' );
	$t_lang_os = lang_get( 'os' );
	$t_lang_os_version = lang_get( 'os_version' );
	$t_lang_fixed_in_version = lang_get( 'fixed_in_version' );
	$t_lang_resolution = lang_get( 'resolution' );
	$t_lang_priority = lang_get( 'priority' );
	$t_lang_product_build = lang_get( 'product_build' );
	$t_lang_eta = lang_get( 'eta' );
	$t_lang_status = lang_get( 'status' );
	$t_lang_product_version = lang_get( 'product_version' );
	$t_lang_no_bugnotes_msg = lang_get( 'no_bugnotes_msg' );
	$t_lang_projection = lang_get( 'projection' );
	$t_lang_target_version = lang_get( 'target_version' );
	$t_lang_summary = lang_get( 'summary' );
	$t_lang_description = lang_get( 'description' );
	$t_lang_steps_to_reproduce = lang_get( 'steps_to_reproduce' );
	$t_lang_additional_information = lang_get( 'additional_information' );
	$t_lang_bug_notes_title = lang_get( 'bug_notes_title' );
	$t_lang_system_profile = lang_get( 'system_profile' );
	$t_lang_attached_files = lang_get( 'attached_files' );

	$t_current_user_id = auth_get_current_user_id();
	$t_user_bugnote_order = user_pref_get_pref ( $t_current_user_id, 'bugnote_order' );

	for( $j=0; $j < $t_row_count; $j++ ) {
		$t_bug = $result[$j];
		$t_id = $t_bug->id;

		if ( $j % 50 == 0 ) {
			# to save ram as report will list data once, clear cache after 50 bugs
			bug_text_clear_cache();
			bug_clear_cache();
			bugnote_clear_cache();
		}

		# display the available and selected bugs
		if ( in_array( $t_id, $f_bug_arr ) || !$f_show_flag ) {
			if ( $t_count_exported > 0 ) {
				echo "<br clear=all style='mso-special-character:line-break; page-break-before:always'>";
			}

			$t_count_exported++;

			$t_last_updated = date( $g_short_date_format, $t_bug->last_updated );

            # grab the project name
            $t_project_name = project_get_field( $t_bug->project_id, 'name' );
			$t_category_name = category_full_name( $t_bug->category_id, false );
?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo $t_lang_bug_view_title ?>
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" width="100%" />
	</td>
</tr>
<tr class="print-category">
	<td class="print" width="16%">
		<?php echo $t_lang_id ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $t_lang_category ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $t_lang_severity ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $t_lang_reproducibility ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $t_lang_date_submitted ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $t_lang_last_update ?>:
	</td>
</tr>
<tr class="print">
	<td class="print">
		<?php echo $t_id ?>
	</td>
	<td class="print">
		<?php echo '[' . string_display_line( $t_project_name ) . '] ' . string_display_line( $t_category_name ) ?>
	</td>
	<td class="print">
		<?php echo get_enum_element( 'severity', $t_bug->severity, auth_get_current_user_id(), $t_bug->project_id ) ?>
	</td>
	<td class="print">
		<?php echo get_enum_element( 'reproducibility', $t_bug->reproducibility, auth_get_current_user_id(), $t_bug->project_id ) ?>
	</td>
	<td class="print">
		<?php echo date( $t_date_format, $t_bug->date_submitted ) ?>
	</td>
	<td class="print">
		<?php echo date( $t_date_format, $t_bug->last_updated ) ?>
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" width="100%" />
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_reporter ?>:
	</td>
	<td class="print">
		<?php print_user_with_subject( $t_bug->reporter_id, $t_id ) ?>
	</td>
	<td class="print-category">
		<?php echo $t_lang_platform ?>:
	</td>
	<td class="print">
		<?php echo string_display_line( $t_bug->platform ) ?>
	</td>
<?php if ( access_has_bug_level( config_get( 'due_date_view_threshold' ), $t_id ) ) { ?>
	<td class="print-category">
		<?php echo $t_lang_due_date ?>:
	</td>
<?php
		if ( bug_is_overdue( $t_id ) ) { ?>
		<td class="print-overdue">
<?php
		} else	{ ?>
		<td class="print">
<?php
		}
		if ( !date_is_null( $t_bug->due_date ) ) {
				echo date( $t_short_date_format, $t_bug->due_date );
		print "\t\t</td>\n";
		}
	} else {
?>
	<td class="print" colspan="2">&#160;</td>
<?php } ?>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_assigned_to ?>:
	</td>
	<td class="print">
		<?php
			if ( access_has_bug_level( config_get( 'view_handler_threshold' ), $t_id ) ) {
				print_user_with_subject( $t_bug->handler_id, $t_id );
			}
		?>
	</td>
	<td class="print-category">
		<?php echo $t_lang_os ?>:
	</td>
	<td class="print">
		<?php echo string_display_line( $t_bug->os ) ?>
	</td>
	<td class="print" colspan="2">&#160;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_priority ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'priority', $t_bug->priority, auth_get_current_user_id(), $t_bug->project_id ) ?>
	</td>
	<td class="print-category">
		<?php echo $t_lang_os_version ?>:
	</td>
	<td class="print">
		<?php echo string_display_line( $t_bug->os_build ) ?>
	</td>
	<td class="print" colspan="2">&#160;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_status ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'status', $t_bug->status, auth_get_current_user_id(), $t_bug->project_id ) ?>
	</td>
	<td class="print-category">
		<?php echo $t_lang_product_version ?>:
	</td>
	<td class="print">
		<?php echo string_display_line( $t_bug->version ) ?>
	</td>
	<td class="print" colspan="2">&#160;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_product_build ?>:
	</td>
	<td class="print">
		<?php echo string_display_line( $t_bug->build ) ?>
	</td>
	<td class="print-category">
		<?php echo $t_lang_resolution ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'resolution', $t_bug->resolution, auth_get_current_user_id(), $t_bug->project_id ) ?>
	</td>
	<td class="print" colspan="2">&#160;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_projection ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'projection', $t_bug->projection, auth_get_current_user_id(), $t_bug->project_id ) ?>
	</td>
	<td class="print-category">
		&#160;
	</td>
	<td class="print">
		&#160;
	</td>
	<td class="print" colspan="2">&#160;</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_eta ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'eta', $t_bug->eta, auth_get_current_user_id(), $t_bug->project_id ) ?>
	</td>
	<td class="print-category">
		<?php echo $t_lang_fixed_in_version ?>:
	</td>
	<td class="print">
		<?php echo string_display_line( $t_bug->fixed_in_version ) ?>
	</td>
	<td class="print" colspan="2">&#160;</td>

</tr>
<tr class="print">
	<td class="print-category">
		&#160;
	</td>
	<td class="print">
		&#160;
	</td>
	<td class="print-category">
		<?php echo $t_lang_target_version ?>:
	</td>
	<td class="print">
		<?php echo string_display_line( $t_bug->target_version ) ?>
	</td>
	<td class="print" colspan="2">&#160;</td>
</tr>
<?php
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );
foreach( $t_related_custom_field_ids as $t_custom_field_id ) {
	$t_def = custom_field_get_definition( $t_custom_field_id );
?>
<tr class="print">
	<td class="print-category">
		<?php echo lang_get_defaulted( $t_def['name'] ) ?>:
	</td>
	<td class="print" colspan="5">
		<?php print_custom_field_value( $t_def, $t_custom_field_id, $t_id ); ?>
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
		<?php echo $t_lang_summary ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo string_display_line_links( $t_bug->summary ) ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_description ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo string_display_links( $t_bug->description ) ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_steps_to_reproduce ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo string_display_links( $t_bug->steps_to_reproduce ) ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_additional_information ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo string_display_links( $t_bug->additional_information ) ?>
	</td>
</tr>
<?php
	# account profile description
	if ( $t_bug->profile_id > 0 ) {
		$t_profile_row = profile_get_row_direct( $t_bug->profile_id );
		$t_profile_description = string_display( $t_profile_row['description'] );

?>
<tr class="print">
	<td class="print-category">
		<?php echo $t_lang_system_profile ?>
	</td>
	<td class="print" colspan="5">
		<?php echo $t_profile_description ?>
	</td>
</tr>
<?php
	} # profile description
?>
<tr class="print">
	<td class="print-category" valign="top">
		<?php echo $t_lang_attached_files ?>:
	</td>
	<td class="print" colspan="5">
		<?php
			$t_attachments = file_get_visible_attachments( $t_id );
			$t_first_attachment = true;
			$t_path = config_get_global( 'path' );

			foreach ( $t_attachments as $t_attachment  ) {
				if ( $t_first_attachment ) {
					$t_first_attachment = false;
				} else {
					echo '<br />';
				}

				$c_filename = string_display_line( $t_attachment['display_name'] );
				$c_download_url = $t_path . htmlspecialchars( $t_attachment['download_url'] );
				$c_filesize = number_format( $t_attachment['size'] );
				$c_date_added = date( $t_date_format, $t_attachment['date_added'] );
				echo "$c_filename ($c_filesize) <span class=\"italic\">$c_date_added</span><br />$c_download_url";

				if ( $t_attachment['preview'] && $t_attachment['type'] == 'image' && $f_type_page == 'html' ) {
					echo '<br /><img src="', $t_attachment['download_url'], '" alt="', $t_attachment['alt'], '" border="0" /><br />';
				}
			}
		?>
	</td>
</tr>
<?php
	$t_user_bugnote_limit = 0;

	$t_bugnotes = bugnote_get_all_visible_bugnotes( $t_id, $t_user_bugnote_order, $t_user_bugnote_limit );
?>
<br />
<table class="width100" cellspacing="1">
<?php
	# no bugnotes
	if ( 0 == count( $t_bugnotes ) ) {
	?>
<tr>
	<td class="print" colspan="2">
		<?php echo $t_lang_no_bugnotes_msg ?>
	</td>
</tr>
<?php
	} else { # print bugnotes ?>
<tr>
	<td class="form-title" colspan="2">
			<?php echo $t_lang_bug_notes_title ?>
	</td>
</tr>
	<?php
		foreach ( $t_bugnotes as $t_bugnote ) {
			# prefix all bugnote data with v3_
			$t_date_submitted = date( $t_date_format, $t_bugnote->date_submitted );
			$t_last_modified = date( $t_date_format, $t_bugnote->last_modified );

			# grab the bugnote text and id and prefix with v3_
			$t_note = string_display_links( $t_bugnote->note );
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
				(<?php echo bugnote_format_id( $t_bugnote->id ) ?>)
			</td>
		</tr>
		<tr>
			<td class="print">
				<?php print_user( $t_bugnote->reporter_id ) ?>&#160;&#160;&#160;
			</td>
		</tr>
		<tr>
			<td class="print">
				<?php echo $t_date_submitted ?>&#160;&#160;&#160;
				<?php if ( $t_bugnote->date_submitted != $t_bugnote->last_modified ) {
					echo '<br />(' . lang_get( 'edited_on') . lang_get( 'word_separator' ) . $t_last_modified . ')';
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
					switch ( $t_bugnote->note_type ) {
						case REMINDER:
							echo lang_get( 'reminder_sent_to' ) . ': ';
							$t_note_attr = utf8_substr( $t_bugnote->note_attr, 1, utf8_strlen( $t_bugnote->note_attr ) - 2 );
							$t_to = array();
							foreach ( explode( '|', $t_note_attr ) as $t_recipient ) {
								$t_to[] = prepare_user_name( $t_recipient );
							}
							echo implode( ', ', $t_to ) . '<br />';
						default:
							echo string_display_links( $t_bugnote->note );
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
