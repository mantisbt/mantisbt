<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	if ( SIMPLE_ONLY == $g_show_view ) {
		print_header_redirect ( 'view_bug_page.php?f_id='.$f_id );
	}

	project_access_check( $f_id );
	bug_ensure_exists( $f_id );
	$c_id = (integer)$f_id;

    $query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted,
    		UNIX_TIMESTAMP(last_updated) as last_updated
    		FROM $g_mantis_bug_table
    		WHERE id='$c_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'v' );

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
?>
<?php print_page_top1() ?>
<?php
	print_head_bottom();
	print_body_top();
?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo $s_viewing_bug_advanced_details_title ?>
	</td>
	<td class="right" colspan="3">
		<span class="small"><?php print_bracket_link( 'view_bug_page.php?f_id='.$f_id, $s_go_back ) ?></span>
	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" />
	</td>
</tr>
<tr class="print-category">
	<td class="print" width="16%">
		<?php echo $s_id ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $s_category ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $s_severity ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $s_reproducibility ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $s_date_submitted ?>:
	</td>
	<td class="print" width="16%">
		<?php echo $s_last_update ?>:
	</td>
</tr>
<tr class="print">
	<td class="print">
		<?php echo $v_id ?>
	</td>
	<td class="print">
		<?php echo $v_category ?>
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
		<hr size="1" />
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_reporter ?>:
	</td>
	<td class="print">
		<?php print_user_with_subject( $v_reporter_id, $f_id ) ?>
	</td>
	<td class="print-category">
		<?php echo $s_platform ?>:
	</td>
	<td class="print">
		<?php echo $v_platform ?>
	</td>
	<td class="print" colspan="2">&nbsp;

	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_assigned_to ?>:
	</td>
	<td class="print">
		<?php print_user_with_subject( $v_handler_id, $f_id ) ?>
	</td>
	<td class="print-category">
		<?php echo $s_os ?>:
	</td>
	<td class="print">
		<?php echo $v_os ?>
	</td>
	<td class="print" colspan="2">&nbsp;

	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_priority ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'priority', $v_priority ) ?>
	</td>
	<td class="print-category">
		<?php echo $s_os_version ?>:
	</td>
	<td class="print">
		<?php echo $v_os_build ?>
	</td>
	<td class="print" colspan="2">&nbsp;

	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_status ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'status', $v_status ) ?>
	</td>
	<td class="print-category">
		<?php echo $s_product_version ?>:
	</td>
	<td class="print">
		<?php echo $v_version ?>
	</td>
	<td class="print" colspan="2">&nbsp;

	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_product_build ?>:
	</td>
	<td class="print">
		<?php echo $v_build?>
	</td>
	<td class="print-category">
		<?php echo $s_resolution ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'resolution', $v_resolution ) ?>
	</td>
	<td class="print" colspan="2">&nbsp;

	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_projection ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'projection', $v_projection ) ?>
	</td>
	<td class="print-category">
		<?php echo $s_duplicate_id ?>:
	</td>
	<td class="print">
		<?php print_duplicate_id( $v_duplicate_id ) ?>
	</td>
	<td class="print" colspan="2">&nbsp;

	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_eta ?>:
	</td>
	<td class="print">
		<?php echo get_enum_element( 'eta', $v_eta ) ?>
	</td>
	<td class="print" colspan="4">&nbsp;

	</td>
</tr>
<tr>
	<td class="print-spacer" colspan="6">
		<hr size="1" />
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_summary ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v_summary ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_description ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v2_description ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_steps_to_reproduce ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $v2_steps_to_reproduce ?>
	</td>
</tr>
<tr class="print">
	<td class="print-category">
		<?php echo $s_additional_information ?>:
	</td>
	<td class="print" colspan="5">
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
<tr class="print">
	<td class="print-category">
		<?php echo $s_system_profile ?>:
	</td>
	<td class="print" colspan="5">
		<?php echo $t_profile_description ?>
	</td>
</tr>
<?php
	}
?>
<tr class="print">
	<td class="print-category">
		<?php echo $s_attached_files ?>:
	</td>
	<td class="print" colspan="5">
		<?php
			$query = "SELECT *
					FROM $g_mantis_bug_file_table
					WHERE bug_id='$c_id'";
			$result = db_query( $query );
			$num_files = db_num_rows( $result );
			for ($i=0;$i<$num_files;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, 'v2' );
				$v2_filesize = round( $v2_filesize / 1024 );
				$v2_date_added = date( config_get( 'normal_date_format' ), ( $v2_date_added ) );

				switch ( $g_file_upload_method ) {
					case DISK:	PRINT "$v2_filename ($v2_filesize KB) <span class=\"italic\">$v2_date_added</span>";
							break;
					case DATABASE:	PRINT "$v2_filename ($v2_filesize KB) <span class=\"italic\">$v2_date_added</span>";
							break;
				}

				if ( $i != ($num_files - 1) ) {
					PRINT '<br />';
				}
			}
		?>
	</td>
</tr>
</table>

<?php include( $g_print_bugnote_include_file ) ?>
