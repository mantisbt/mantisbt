<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_report_advanced_page.php,v 1.34 2004-03-26 09:28:54 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This file POSTs data to report_bug.php
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php
	# this page is invalid for the 'All Project' selection
	if ( ALL_PROJECTS == helper_get_current_project() ) {
		print_header_redirect( 'login_select_proj_page.php?ref=bug_report_advanced_page.php' );
	}

	if ( SIMPLE_ONLY == config_get( 'show_report' ) ) {
		print_header_redirect ( 'bug_report_page.php' );
	}

	access_ensure_project_level( config_get( 'report_bug_threshold' ) );

	$f_build				= gpc_get_string( 'build', '' );
	$f_platform				= gpc_get_string( 'platform', '' );
	$f_os					= gpc_get_string( 'os', '' );
	$f_os_build				= gpc_get_string( 'os_build', '' );
	$f_product_version		= gpc_get_string( 'product_version', '' );
	$f_profile_id			= gpc_get_int( 'profile_id', 0 );
	$f_handler_id			= gpc_get_int( 'handler_id', 0 );

	$f_category				= gpc_get_string( 'category', '' );
	$f_reproducibility		= gpc_get_int( 'reproducibility', 0 );
	$f_severity				= gpc_get_int( 'severity', config_get( 'default_bug_severity' ) );
	$f_priority				= gpc_get_int( 'priority', config_get( 'default_bug_priority' ) );
	$f_summary				= gpc_get_string( 'summary', '' );
	$f_description			= gpc_get_string( 'description', '' );
	$f_steps_to_reproduce	= gpc_get_string( 'steps_to_reproduce', '' );
	$f_additional_info		= gpc_get_string( 'additional_info', '' );
	$f_view_state			= gpc_get_int( 'view_state', config_get( 'default_bug_view_status' ) );

	$f_report_stay			= gpc_get_bool( 'report_stay' );

	$t_project_id			= helper_get_current_project();
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<form name="report_bug_form" method="post" <?php if ( file_allow_bug_upload() ) { echo 'enctype="multipart/form-data"'; } ?> action="bug_report.php">
<table class="width75" cellspacing="1">


<!-- Title -->
<tr>
	<td class="form-title">
		<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
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


<!-- Category -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="30%">
		<?php echo lang_get( 'category' ) ?> <?php print_documentation_link( 'category' ) ?>
	</td>
	<td width="70%">
		<select tabindex="1" name="category">
			<?php print_category_option_list( $f_category ) ?>
		</select>
	</td>
</tr>


<!-- Reproducibility -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'reproducibility' ) ?> <?php print_documentation_link( 'reproducibility' ) ?>
	</td>
	<td>
		<select tabindex="2" name="reproducibility">
			<?php print_enum_string_option_list( 'reproducibility', $f_reproducibility ) ?>
		</select>
	</td>
</tr>

<!-- Severity -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'severity' ) ?> <?php print_documentation_link( 'severity' ) ?>
	</td>
	<td>
		<select tabindex="3" name="severity">
			<?php print_enum_string_option_list( 'severity', $f_severity ) ?>
		</select>
	</td>
</tr>


<!-- Priority (if permissions allow) -->
<?php if ( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'priority' ) ?> <?php print_documentation_link( 'priority' ) ?>
	</td>
	<td>
		<select tabindex="4" name="priority">
			<?php print_enum_string_option_list( 'priority', $f_priority ) ?>
		</select>
	</td>
</tr>
<?php } ?>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>


<!-- Profile -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'select_profile' ) ?>
	</td>
	<td>
		<select tabindex="5" name="profile_id">
			<?php print_profile_option_list( auth_get_current_user_id(), $f_profile_id ) ?>
		</select>
	</td>
</tr>


<!-- instructions -->
<tr>
	<td colspan="2">
		<?php echo lang_get( 'or_fill_in' ) ?>
	</td>
</tr>


<!-- Platform -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'platform' ) ?>
	</td>
	<td>
		<input tabindex="6" type="text" name="platform" size="32" maxlength="32" value="<?php echo $f_platform ?>" />
	</td>
</tr>


<!-- Operating System -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'os' ) ?>
	</td>
	<td>
		<input tabindex="7" type="text" name="os" size="32" maxlength="32" value="<?php echo $f_os ?>" />
	</td>
</tr>


<!-- OS Version -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'os_version' ) ?>
	</td>
	<td>
		<input tabindex="8" type="text" name="os_build" size="16" maxlength="16" value="<?php echo $f_os_build ?>">
	</td>
</tr>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>


<!-- Product Version -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'product_version' ) ?>
	</td>
	<td>
		<select tabindex="9" name="product_version">
			<?php print_version_option_list( $f_product_version ) ?>
		</select>
	</td>
</tr>


<!-- Product Build -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'product_build' ) ?>
	</td>
	<td>
		<input tabindex="10" type="text" name="build" size="32" maxlength="32" value="<?php echo $f_build ?>" />
	</td>
</tr>


<!-- Handler (if permissions allow) -->
<?php if ( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) { ?>
<!-- spacer -->
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'assign_to' ) ?>
	</td>
	<td>
		<select tabindex="11" name="handler_id">
			<option value="0" selected="selected"></option>
			<?php print_assign_to_option_list( $f_handler_id ) ?>
		</select>
	</td>
</tr>
<?php } ?>


<!-- spacer -->
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>


<!-- Summary -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'summary' ) ?> <?php print_documentation_link( 'summary' ) ?>
	</td>
	<td>
		<input tabindex="12" type="text" name="summary" size="80" maxlength="128" value="<?php echo $f_summary ?>" />
	</td>
</tr>


<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'description' ) ?> <?php print_documentation_link( 'description' ) ?>
	</td>
	<td>
		<textarea tabindex="13" name="description" cols="60" rows="5" wrap="virtual"><?php echo $f_description ?></textarea>
	</td>
</tr>


<!-- Steps to Reproduce -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?> <?php print_documentation_link( 'steps_to_reproduce' ) ?>
	</td>
	<td>
		<textarea tabindex="14" name="steps_to_reproduce" cols="60" rows="5" wrap="virtual"><?php echo $f_steps_to_reproduce ?></textarea>
	</td>
</tr>


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?> <?php print_documentation_link( 'additional_information' ) ?>
	</td>
	<td>
		<textarea tabindex="15" name="additional_info" cols="60" rows="5" wrap="virtual"><?php echo $f_additional_info ?></textarea>
	</td>
</tr>


<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>


<!-- Custom Fields -->
<?php
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		if( !custom_field_has_write_access_to_project( $t_id, $t_project_id ) ) {
			continue;
		}

		$t_custom_fields_found = true;
		$t_def = custom_field_get_definition( $t_id );
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get_defaulted( $t_def['name'] ) ?>
	</td>
	<td>
		<?php print_custom_field_input( $t_def ) ?>
	</td>
</tr>
<?php
	} # foreach( $t_related_custom_field_ids as $t_id )
?>


<?php if ( $t_custom_fields_found ) { ?>
<!-- spacer -->
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>
<?php } # custom fields found ?>


<!-- File Upload (if enabled) -->
<?php if ( file_allow_bug_upload() ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'upload_file' ) ?>
	</td>
	<td>
		<input type="hidden" name="max_file_size" value="<?php echo config_get( 'max_file_size' ) ?>" />
		<input tabindex="16" name="file" type="file" size="60" />
	</td>
</tr>
<?php } ?>


<!-- View Status -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
<?php 
	if ( access_has_project_level( config_get( 'set_view_status_threshold' ) ) ) {
?>
		<input tabindex="9" type="radio" name="view_state" value="<?php echo VS_PUBLIC ?>" <?php check_checked( $f_view_state, VS_PUBLIC ) ?> /> <?php echo lang_get( 'public' ) ?>
		<input tabindex="10" type="radio" name="view_state" value="<?php echo VS_PRIVATE ?>" <?php check_checked( $f_view_state, VS_PRIVATE ) ?> /> <?php echo lang_get( 'private' ) ?>
<?php
	} else {
		echo get_enum_element( 'project_view_state', $f_view_state );
	}
?>
	</td>
</tr>

<!-- Report Stay (report more bugs) -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'report_stay' ) ?> <?php print_documentation_link( 'report_stay' ) ?>
	</td>
	<td>
		<input tabindex="19" type="checkbox" name="report_stay" <?php check_checked( $f_report_stay ) ?> /> (<?php echo lang_get( 'check_report_more_bugs' ) ?>)
	</td>
</tr>


<!-- Submit Button -->
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
	window.document.report_bug_form.category.focus();
//-->
</script>

<?php html_page_bottom1( __FILE__ ) ?>
