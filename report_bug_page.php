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
		print_header_redirect( $g_login_select_proj_page );
	}

	if ( ADVANCED_ONLY == $g_show_report ) {
		print_header_redirect ( $g_report_bug_advanced_page );
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	# We check to see if the variable exists to avoid warnings

	check_varset( $f_category, '' );
	check_varset( $f_reproducibility, '' );
	check_varset( $f_severity, '' );
	check_varset( $f_priority, NORMAL );
	check_varset( $f_summary, '' );
	check_varset( $f_description, '' );
	check_varset( $f_additional_info, '' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" <?php if ( ON == $g_allow_file_upload ) { echo 'enctype="multipart/form-data"'; } ?> action="<?php echo $g_report_add ?>">
		<input type="hidden" name="f_assign_id" value="0000000">
		<?php echo $s_enter_report_details_title ?>
	</td>
	<td class="right">
		<?php
			if ( BOTH == $g_show_report ) {
				print_bracket_link( $g_report_bug_advanced_page, $s_advanced_report_link );
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
<? if ( access_level_check_greater_or_equal( DEVELOPER ) ) { ?>
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
		<span class="required">*</span><?php echo $s_summary ?> <?php print_documentation_link( 'summary' ) ?>:
	</td>
	<td>
		<input type="text" name="f_summary" size="80" maxlength="128" value="<?php echo $f_summary ?>">
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<span class="required">*</span><?php echo $s_description ?> <?php print_documentation_link( 'description' ) ?>:
	</td>
	<td>
		<textarea name="f_description" cols="60" rows="5" wrap="virtual"><?php echo $f_description ?></textarea>
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
