<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This file POSTs data to report_bug.php3
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# these pages are invalid for the 'All Project' selection
	if ( '0000000' == $g_project_cookie_val ) {
		print_header_redirect( 'login_select_proj_page.php' );
	}

	if ( SIMPLE_ONLY == $g_show_report ) {
		print_header_redirect ( 'report_bug_page.php' );
	}

	check_access( REPORTER );

	# We check to see if the variable exists to avoid warnings

	check_varset( $f_category, '' );
	check_varset( $f_reproducibility, '' );
	check_varset( $f_severity, '' );
	check_varset( $f_priority, NORMAL );
	check_varset( $f_profile_id, '' );
	check_varset( $f_platform, '' );
	check_varset( $f_os, '' );
	check_varset( $f_os_build, '' );
	check_varset( $f_product_version, '' );
	check_varset( $f_build, '' );
	check_varset( $f_assign_id, '' );
	check_varset( $f_summary, '' );
	check_varset( $f_description, '' );
	check_varset( $f_steps_to_reproduce, '' );
	check_varset( $f_additional_info, '' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" <?php if ( ON == $g_allow_file_upload ) { echo 'enctype="multipart/form-data"'; } ?> action="report_add.php">
		<?php echo $s_enter_report_details_title ?>
	</td>
	<td class="right">
		<?php
			if ( BOTH == $g_show_report ) {
				print_bracket_link( 'report_bug_page.php', $s_simple_report_link );
			}
		?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo $s_category ?> <?php print_documentation_link( 'category' ) ?>:
	</td>
	<td width="70%">
		<select name="f_category">
			<?php print_category_option_list( $f_category ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_reproducibility ?> <?php print_documentation_link( 'reproducibility' ) ?>:
	</td>
	<td>
		<select name="f_reproducibility">
			<?php print_enum_string_option_list( 'reproducibility', $f_reproducibility ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_severity ?> <?php print_documentation_link( 'severity' ) ?>:
	</td>
	<td>
		<select name="f_severity">
			<?php print_enum_string_option_list( 'severity', $f_severity ) ?>
		</select>
	</td>
</tr>
<? if ( access_level_check_greater_or_equal( $g_handle_bug_threshold ) ) { ?>
<tr class="row-2">
	<td class="category">
		<?php echo $s_priority ?> <?php print_documentation_link( 'priority' ) ?>:
	</td>
	<td>
		<select name="f_priority">
			<?php print_enum_string_option_list( 'priority', $f_priority ) ?>
		</select>
	</td>
</tr>
<? } ?>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_select_profile ?>:
	</td>
	<td>
		<select name="f_profile_id">
			<?php print_profile_option_list( get_current_user_field( 'id' ), $f_profile_id ) ?>
		</select>
	</td>
</tr>
<tr class="spacer">
	<td colspan="2">
		<?php echo $s_or_fill_in ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_platform ?>:
	</td>
	<td>
		<input type="text" name="f_platform" size="32" maxlength="32" value="<?php echo $f_platform ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_os ?>:
	</td>
	<td>
		<input type="text" name="f_os" size="32" maxlength="32" value="<?php echo $f_os ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_os_version ?>:
	</td>
	<td>
		<input type="text" name="f_os_build" size="16" maxlength="16" value="<?php echo $f_os_build ?>">
	</td>
</tr>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_product_version ?>
	</td>
	<td>
		<select name="f_product_version">
			<?php print_version_option_list( $f_product_version ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_product_build ?>
	</td>
	<td>
		<input type="text" name="f_build" size="32" maxlength="32" value="<?php echo $f_build ?>">
	</td>
</tr>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<?php # reporters should not be able to assign to develoeprs ?>
<?php if ( get_current_user_field( 'access_level' ) > REPORTER ) { ?>
<tr class="row-2">
	<td class="category">
		<?php echo $s_assign_to ?>
	</td>
	<td>
		<select name="f_assign_id">
			<option value="0000000" SELECTED></option>
			<?php print_assign_to_option_list( $f_assign_id ) ?>
		</select>
	</td>
</tr>
<?php } else { ?>
<input type="hidden" name="f_assign_id" value="0000000">
<?php } ?>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<span class="required">*</span><?php echo $s_summary ?> <?php print_documentation_link( 'summary' ) ?>:
	</td>
	<td>
		<input type="text" name="f_summary" size="80" maxlength="128" value="<?php echo $f_summary ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<span class="required">*</span><?php echo $s_description ?> <?php print_documentation_link( 'description' ) ?>:
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="5" wrap="virtual"><?php echo $f_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_steps_to_reproduce ?> <?php print_documentation_link( 'steps_to_reproduce' ) ?>:
	</td>
	<td>
		<textarea name="f_steps_to_reproduce" cols="60" rows="5" wrap="virtual"><?php echo $f_steps_to_reproduce ?></textarea>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_additional_information ?> <?php print_documentation_link( 'additional_information' ) ?>:
	</td>
	<td>
		<textarea name="f_additional_info" cols="60" rows="5" wrap="virtual"><?php echo $f_additional_info ?></textarea>
	</td>
</tr>
<?php if ( ( ON == $g_allow_file_upload ) &&
			access_level_check_greater_or_equal( REPORTER ) ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo $s_upload_file ?>
	</td>
	<td>
		<input type="hidden" name="max_file_size" value="<?php echo $g_max_file_size ?>">
		<input name="f_file" type="file" size="60">
	</td>
</tr>
<?php } ?>
<tr class="row-2">
	<td class="category">
		<?php echo $s_view_status ?>
	</td>
	<td>
		<input type="radio" name="f_view_state" value="10" CHECKED> <?php echo $s_public ?>
		<input type="radio" name="f_view_state" value="50"> <?php echo $s_private ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_report_stay ?> <?php print_documentation_link( 'report_stay' ) ?>:
	</td>
	<td>
		<input type="checkbox" name="f_report_stay" <?php if ( isset($f_report_stay) ) echo 'CHECKED' ?>> (<?php echo $s_check_report_more_bugs ?>)
	</td>
</tr>
<tr>
	<td class="left">
		<span class="required"> * <?php echo $s_required ?></span>
	</td>
	<td class="center">
		<input type="submit" value="<?php echo $s_submit_report_button ?>">
		</form>
	</td>
</tr>
</table>
</div>

<?php print_page_bot1( __FILE__ ) ?>
