<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_report_advanced_page.php,v 1.8 2002-12-23 01:51:55 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This file POSTs data to report_bug.php
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# this page is invalid for the 'All Project' selection
	if ( 0 == helper_get_current_project() ) {
		print_header_redirect( 'login_select_proj_page.php?ref=' . string_get_bug_report_url() );
	}

	if ( SIMPLE_ONLY == config_get( 'show_report' ) ) {
		print_header_redirect ( 'bug_report_page.php' );
	}

	check_access( config_get( 'report_bug_threshold' ) );

	$f_build				= gpc_get_string( 'f_build', '' );
	$f_platform				= gpc_get_string( 'f_platform', '' );
	$f_os					= gpc_get_string( 'f_os', '' );
	$f_os_build				= gpc_get_string( 'f_os_build', '' );
	$f_product_version		= gpc_get_string( 'f_product_version', '' );
	$f_profile_id			= gpc_get_int( 'f_profile_id', 0 );
	$f_handler_id			= gpc_get_int( 'f_handler_id', 0 );

	$f_category				= gpc_get_string( 'f_category', 0 );
	$f_reproducibility		= gpc_get_int( 'f_reproducibility', 0 );
	$f_severity				= gpc_get_int( 'f_severity', 0 );
	$f_priority				= gpc_get_int( 'f_priority', NORMAL );
	$f_summary				= gpc_get_string( 'f_summary', '' );
	$f_description			= gpc_get_string( 'f_description', '' );
	$f_steps_to_reproduce	= gpc_get_string( 'f_steps_to_reproduce', '' );
	$f_additional_info		= gpc_get_string( 'f_additional_info', '' );

	$f_report_stay			= gpc_get_bool( 'f_report_stay' );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<div align="center">
<form name="report_bug_form" method="post" <?php if ( ON == config_get( 'allow_file_upload' ) ) { echo 'enctype="multipart/form-data"'; } ?> action="bug_report.php">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'enter_report_details_title' ) ?>
	</td>
	<td class="right">
		<?php
			if ( BOTH == config_get( 'show_report' ) ) {
				print_bracket_link( 'bug_report_page.php', lang_get( 'simple_report_link' ) );
			}
		?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="30%">
		<?php echo lang_get( 'category' ) ?> <?php print_documentation_link( 'category' ) ?>
	</td>
	<td width="70%">
		<select tabindex="1" name="f_category">
			<?php print_category_option_list( $f_category ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'reproducibility' ) ?> <?php print_documentation_link( 'reproducibility' ) ?>
	</td>
	<td>
		<select tabindex="2" name="f_reproducibility">
			<?php print_enum_string_option_list( 'reproducibility', $f_reproducibility ) ?>
		</select>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'severity' ) ?> <?php print_documentation_link( 'severity' ) ?>
	</td>
	<td>
		<select tabindex="3" name="f_severity">
			<?php print_enum_string_option_list( 'severity', $f_severity ) ?>
		</select>
	</td>
</tr>

<?php if ( access_level_check_greater_or_equal( config_get( 'handle_bug_threshold' ) ) ) { ?>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'priority' ) ?> <?php print_documentation_link( 'priority' ) ?>
	</td>
	<td>
		<select tabindex="4" name="f_priority">
			<?php print_enum_string_option_list( 'priority', $f_priority ) ?>
		</select>
	</td>
</tr>
<?php } ?>

<?php if( ON == config_get( 'use_experimental_custom_fields' ) ) { ?>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<?php
$t_related_custom_field_ids = custom_field_get_bound_ids( helper_get_current_project() );
foreach( $t_related_custom_field_ids as $id ) {
	$t_def = custom_field_get_definition($id);
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get_defaulted( $t_def['name'] ) ?>
	</td>
	<td>
		<?php
			print_custom_field_input( $t_def );
		?>
	</td>
</tr>
<?php
}   // foreach
?>
<?php } // ON = config_get( 'use_experimental_custom_fields' ) ?>

<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'select_profile' ) ?>
	</td>
	<td>
		<select tabindex="5" name="f_profile_id">
			<?php print_profile_option_list( auth_get_current_user_id(), $f_profile_id ) ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2">
		<?php echo lang_get( 'or_fill_in' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'platform' ) ?>
	</td>
	<td>
		<input tabindex="6" type="text" name="f_platform" size="32" maxlength="32" value="<?php echo $f_platform ?>" />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'os' ) ?>
	</td>
	<td>
		<input tabindex="7" type="text" name="f_os" size="32" maxlength="32" value="<?php echo $f_os ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'os_version' ) ?>
	</td>
	<td>
		<input tabindex="8" type="text" name="f_os_build" size="16" maxlength="16" value="<?php echo $f_os_build ?>">
	</td>
</tr>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'product_version' ) ?>
	</td>
	<td>
		<select tabindex="9" name="f_product_version">
			<?php print_version_option_list( $f_product_version ) ?>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'product_build' ) ?>
	</td>
	<td>
		<input tabindex="10" type="text" name="f_build" size="32" maxlength="32" value="<?php echo $f_build ?>" />
	</td>
</tr>
<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>

<?php if ( current_user_get_field( 'access_level' ) > REPORTER ) { ?>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'assign_to' ) ?>
	</td>
	<td>
		<select tabindex="11" name="f_handler_id">
			<option value="0000000" selected="selected"></option>
			<?php print_assign_to_option_list( $f_handler_id ) ?>
		</select>
	</td>
</tr>
<?php
	} else {
?>
<input type="hidden" name="f_handler_id" value="0" />
<?php } ?>

<tr>
	<td class="spacer" colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'summary' ) ?> <?php print_documentation_link( 'summary' ) ?>
	</td>
	<td>
		<input tabindex="12" type="text" name="f_summary" size="80" maxlength="128" value="<?php echo $f_summary ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'description' ) ?> <?php print_documentation_link( 'description' ) ?>
	</td>
	<td>
		<textarea tabindex="13" name="f_description" cols="60" rows="5" wrap="virtual"><?php echo $f_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?> <?php print_documentation_link( 'steps_to_reproduce' ) ?>
	</td>
	<td>
		<textarea tabindex="14" name="f_steps_to_reproduce" cols="60" rows="5" wrap="virtual"><?php echo $f_steps_to_reproduce ?></textarea>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?> <?php print_documentation_link( 'additional_information' ) ?>
	</td>
	<td>
		<textarea tabindex="15" name="f_additional_info" cols="60" rows="5" wrap="virtual"><?php echo $f_additional_info ?></textarea>
	</td>
</tr>

<?php if ( file_allow_bug_upload() ) { ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'upload_file' ) ?>
	</td>
	<td>
		<input type="hidden" name="max_file_size" value="<?php echo config_get( 'max_file_size' ) ?>" />
		<input tabindex="16" name="f_file" type="file" size="60" />
	</td>
</tr>
<?php } ?>

<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<input tabindex="17" type="radio" name="f_view_state" value="10" checked="checked" /> <?php echo lang_get( 'public' ) ?>
		<input tabindex="18" type="radio" name="f_view_state" value="50" /> <?php echo lang_get( 'private' ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'report_stay' ) ?> <?php print_documentation_link( 'report_stay' ) ?>
	</td>
	<td>
		<input tabindex="19" type="checkbox" name="f_report_stay" <?php if ( $f_report_stay ) echo 'checked="checked"' ?> /> (<?php echo lang_get( 'check_report_more_bugs' ) ?>)
	</td>
</tr>
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<input tabindex="20" type="submit" value="<?php echo lang_get( 'submit_report_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<script language="JavaScript">
<!--
	window.document.report_bug_form.f_category.focus();
//-->
</script>

<?php print_page_bot1( __FILE__ ) ?>