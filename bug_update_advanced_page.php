<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Show the advanced update bug options
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	if ( SIMPLE_ONLY == $g_show_update ) {
		print_header_redirect ( $g_bug_update_page."?f_id=".$f_id );
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( UPDATER );
	check_bug_exists( $f_id );
	$c_id = (integer)$f_id;

    $query = "SELECT *, UNIX_TIMESTAMP(date_submitted) as date_submitted,
    		UNIX_TIMESTAMP(last_updated) as last_updated
    		FROM $g_mantis_bug_table
    		WHERE id='$c_id'";
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
	$v_os_build 				= string_display( $v_os_build );
	$v_platform					= string_display( $v_platform );
	$v_version 					= string_display( $v_version );
	$v_summary					= string_edit_text( $v_summary );
	$v2_description 			= string_edit_textarea( $v2_description );
	$v2_steps_to_reproduce 		= string_edit_textarea( $v2_steps_to_reproduce );
	$v2_additional_information 	= string_edit_textarea( $v2_additional_information );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<table class="width100" cellspacing="1">
<form method="post" action="<?php echo $g_bug_update ?>">
<input type="hidden" name="f_id" value="<?php echo $v_id ?>">
<input type="hidden" name="f_old_status" value="<?php echo $v_status ?>">
<input type="hidden" name="f_old_handler_id" value="<?php echo $v_handler_id ?>">
<tr>
	<td class="form-title" colspan="3">
		<?php echo $s_updating_bug_advanced_title ?>
	</td>
	<td class="right" colspan="3">
<?php
	switch ( $g_show_view ) {
		case 0: print_bracket_link( $g_view_bug_advanced_page."?f_id=".$f_id, $s_back_to_bug_link );
				break;
		case 1: print_bracket_link( $g_view_bug_page."?f_id=".$f_id, $s_back_to_bug_link );
				break;
		case 2: print_bracket_link( $g_view_bug_advanced_page."?f_id=".$f_id, $s_back_to_bug_link );
				break;
	}

	if ( BOTH == $g_show_update ) {
		print_bracket_link( $g_bug_update_page."?f_id=".$f_id, $s_update_simple_link );
	}
?>
	</td>
</tr>
<tr class="row-category">
	<td width="15%">
		<?php echo $s_id ?>
	</td>
	<td width="20%">
		<?php echo $s_category ?>
	</td>
	<td width="15%">
		<?php echo $s_severity ?>
	</td>
	<td width="20%">
		<?php echo $s_reproducibility ?>
	</td>
	<td width="15%">
		<?php echo $s_date_submitted ?>
	</td>
	<td width="15%">
		<?php echo $s_last_update ?>
	</td>
</tr>
<tr class="row-2">
	<td>
		<?php echo $v_id ?>
	</td>
	<td>
		<select name="f_category">
			<?php print_category_option_list( $v_category ) ?>
		</select>
	</td>
	<td>
		<select name="f_severity">
			<?php print_enum_string_option_list( "severity", $v_severity ) ?>
		</select>
	</td>
	<td>
		<select name="f_reproducibility">
			<?php print_enum_string_option_list( "reproducibility", $v_reproducibility ) ?>
		</select>
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
	<td>
		<?php print_user( $v_reporter_id ) ?>
	</td>
	<td class="category">
		<?php echo $s_view_status ?>
	</td>
	<td>
		<select name="f_view_state">
			<?php print_enum_string_option_list( "project_view_state", $v_view_state) ?>
		</select>
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
		<select name="f_handler_id">
			<option value="0"></option>
			<?php print_assign_to_option_list( $v_handler_id ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_priority ?>
	</td>
	<td align="left">
		<select name="f_priority">
			<?php print_enum_string_option_list( "priority", $v_priority ) ?>
		</select>
	</td>
	<td class="category">
		<?php echo $s_resolution ?>
	</td>
	<td>
		<?php echo get_enum_element( "resolution", $v_resolution ) ?>
	</td>
	<td class="category">
		<?php echo $s_platform ?>
	</td>
	<td>
		<input type="text" name="f_platform" size="16" maxlength="32" value="<?php echo $v_platform ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_status ?>
	</td>
	<td <?php echo get_status_bgcolor( $v_status ) ?>>
		<select name="f_status">
			<?php print_enum_string_option_list( "status", $v_status ) ?>
		</select>
	</td>
	<td class="category">
		<?php echo $s_duplicate_id ?>
	</td>
	<td>
		<?php echo $v_duplicate_id ?>
	</td>
	<td class="category">
		<?php echo $s_os ?>
	</td>
	<td>
		<input type="text" name="f_os" size="16" maxlength="32" value="<?php echo $v_os ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_projection ?>
	</td>
	<td>
		<select name="f_projection">
			<?php print_enum_string_option_list( "projection", $v_projection ) ?>
		</select>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
	<td class="category">
		<?php echo $s_os_version ?>
	</td>
	<td>
		<input type="text" name="f_os_build" size="16" maxlength="16" value="<?php echo $v_os_build ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_eta ?>
	</td>
	<td>
		<select name="f_eta">
			<?php print_enum_string_option_list( "eta", $v_eta ) ?>
		</select>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
	<td class="category">
		<?php echo $s_product_version ?>
	</td>
	<td>
		<select name="f_version">
			<?php print_version_option_list( $f_version, $v_version ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td colspan="4">
		&nbsp;
	</td>
	<td class="category">
		<?php echo $s_build ?>
	</td>
	<td>
		<input type="text" name="f_build" size="16" maxlength="32" value="<?php echo $v_build ?>">
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
		<input type="text" name="f_summary" size="80" maxlength="128" value="<?php echo $v_summary ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_description ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_description" wrap="virtual"><?php echo $v2_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_steps_to_reproduce ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_steps_to_reproduce" wrap="virtual"><?php echo $v2_steps_to_reproduce ?></textarea>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_additional_information ?>
	</td>
	<td colspan="5">
		<textarea cols="60" rows="5" name="f_additional_information" wrap="virtual"><?php echo $v2_additional_information ?></textarea>
	</td>
</tr>
<tr>
	<td class="center" colspan="6"">
		<input type="submit" value="<?php echo $s_update_information_button ?>">
	</td>
</tr>
</form>
</table>

<?php print_page_bot1( __FILE__ ) ?>
