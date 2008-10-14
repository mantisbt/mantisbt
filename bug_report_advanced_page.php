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
	# $Id: bug_report_advanced_page.php,v 1.66.2.1 2007-10-13 22:32:52 giallu Exp $
	# --------------------------------------------------------

	# This file POSTs data to report_bug.php

	$g_allow_browser_cache = 1;
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'file_api.php' );
	require_once( $t_core_path.'custom_field_api.php' );
	require_once( $t_core_path.'last_visited_api.php' );
	require_once( $t_core_path.'projax_api.php' );

	$f_master_bug_id = gpc_get_int( 'm_id', 0 );

	# this page is invalid for the 'All Project' selection except if this is a clone
	if ( ( ALL_PROJECTS == helper_get_current_project() ) && ( 0 == $f_master_bug_id ) ) {
		print_header_redirect( 'login_select_proj_page.php?ref=bug_report_advanced_page.php' );
	}

	if ( SIMPLE_ONLY == config_get( 'show_report' ) ) {
		print_header_redirect ( 'bug_report_page.php' .
						( 0 == $f_master_bug_id ) ? '' : '?m_id=' . $f_master_bug_id );
	}

	if( $f_master_bug_id > 0 ) {
		# master bug exists...
		bug_ensure_exists( $f_master_bug_id );

		# master bug is not read-only...
		if ( bug_is_readonly( $f_master_bug_id ) ) {
			error_parameters( $f_master_bug_id );
			trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
		}

		$t_bug = bug_prepare_edit( bug_get( $f_master_bug_id, true ) );

		# the user can at least update the master bug (needed to add the relationship)...
		access_ensure_bug_level( config_get( 'update_bug_threshold', null, $t_bug->project_id ), $f_master_bug_id );

		#@@@ (thraxisp) Note that the master bug is cloned into the same project as the master, independent of
		#       what the current project is set to.
		if( $t_bug->project_id != helper_get_current_project() ) {
            # in case the current project is not the same project of the bug we are viewing...
            # ... override the current project. This to avoid problems with categories and handlers lists etc.
            $g_project_override = $t_bug->project_id;
            $t_changed_project = true;
        } else {
            $t_changed_project = false;
        }

	    access_ensure_project_level( config_get( 'report_bug_threshold' ) );

		$f_build				= $t_bug->build;
		$f_platform				= $t_bug->platform;
		$f_os					= $t_bug->os;
		$f_os_build				= $t_bug->os_build;
		$f_product_version		= $t_bug->version;
		$f_target_version		= $t_bug->target_version;
		$f_profile_id			= 0;
		$f_handler_id			= $t_bug->handler_id;

		$f_category				= $t_bug->category;
		$f_reproducibility		= $t_bug->reproducibility;
		$f_severity				= $t_bug->severity;
		$f_priority				= $t_bug->priority;
		$f_summary				= $t_bug->summary;
		$f_description			= $t_bug->description;
		$f_steps_to_reproduce	= $t_bug->steps_to_reproduce;
		$f_additional_info		= $t_bug->additional_information;
		$f_view_state			= $t_bug->view_state;

		$t_project_id			= $t_bug->project_id;
	} else {
	    access_ensure_project_level( config_get( 'report_bug_threshold' ) );

		$f_build				= gpc_get_string( 'build', '' );
		$f_platform				= gpc_get_string( 'platform', '' );
		$f_os					= gpc_get_string( 'os', '' );
		$f_os_build				= gpc_get_string( 'os_build', '' );
		$f_product_version		= gpc_get_string( 'product_version', '' );
		$f_target_version		= gpc_get_string( 'target_version', '' );
		$f_profile_id			= gpc_get_int( 'profile_id', 0 );
		$f_handler_id			= gpc_get_int( 'handler_id', 0 );

		$f_category				= gpc_get_string( 'category', config_get( 'default_bug_category' ) );
		$f_reproducibility		= gpc_get_int( 'reproducibility', config_get( 'default_bug_reproducibility' ) );
		$f_severity				= gpc_get_int( 'severity', config_get( 'default_bug_severity' ) );
		$f_priority				= gpc_get_int( 'priority', config_get( 'default_bug_priority' ) );
		$f_summary				= gpc_get_string( 'summary', '' );
		$f_description			= gpc_get_string( 'description', '' );
		$f_steps_to_reproduce	= gpc_get_string( 'steps_to_reproduce', config_get( 'default_bug_steps_to_reproduce' ) );
		$f_additional_info		= gpc_get_string( 'additional_info', config_get ( 'default_bug_additional_info' ) );
		$f_view_state			= gpc_get_int( 'view_state', config_get( 'default_bug_view_status' ) );

		$t_project_id			= helper_get_current_project();

		$t_changed_project		= false;
	}

	$f_report_stay			= gpc_get_bool( 'report_stay', false );

	html_page_top1( lang_get( 'report_bug_link' ) );
	html_page_top2();

	print_recently_visited();
?>

<br />
<div align="center">
<form name="report_bug_form" method="post" <?php if ( file_allow_bug_upload() ) { echo 'enctype="multipart/form-data"'; } ?> action="bug_report.php">
<?php echo form_security_field( 'bug_report' ) ?>
<table class="width75" cellspacing="1">


<!-- Title -->
<tr>
	<td class="form-title">
		<input type="hidden" name="m_id" value="<?php echo $f_master_bug_id ?>" />
		<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
		<?php echo lang_get( 'enter_report_details_title' ) ?>
	</td>
	<td class="right">
		<?php
			if ( BOTH == config_get( 'show_report' ) ) {
				print_bracket_link( 'bug_report_page.php' .
					( $f_master_bug_id > 0 ? '?m_id=' . $f_master_bug_id : '' ), lang_get( 'simple_report_link' ) );
			}
		?>
	</td>
</tr>


<!-- Category -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="30%">
		<?php echo '<span class="required">*</span>', lang_get( 'category' ) ?> <?php print_documentation_link( 'category' ) ?>
	</td>
	<td width="70%">
		<?php if ( $t_changed_project ) {
			echo "[" . project_get_field( $t_bug->project_id, 'name' ) . "] ";
		} ?>
		<select <?php echo helper_get_tab_index() ?> name="category">
			<?php 
				if ( is_blank( $f_category ) ) {
					echo '<option value="" selected="selected">', string_attribute( lang_get( 'select_option' ) ), '</option>';
				}

				print_category_option_list( $f_category ); 
			?>
		</select>
	</td>
</tr>


<!-- Reproducibility -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'reproducibility' ) ?> <?php print_documentation_link( 'reproducibility' ) ?>
	</td>
	<td>
		<select <?php echo helper_get_tab_index() ?> name="reproducibility">
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
		<select <?php echo helper_get_tab_index() ?> name="severity">
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
		<select <?php echo helper_get_tab_index() ?> name="priority">
			<?php print_enum_string_option_list( 'priority', $f_priority ) ?>
		</select>
	</td>
</tr>
<?php } ?>


<!-- spacer -->
<tr class="spacer">
	<td colspan="2"></td>
</tr>


<!-- Profile -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'select_profile' ) ?>
	</td>
	<td>
		<select <?php echo helper_get_tab_index() ?> name="profile_id">
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
		<?php
			if ( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
		?>
				<select name="platform">
					<option value=""></option>
				<?php
						print_platform_option_list( $f_platform );
				?>
				</select>
		<?php
			} else {
				projax_autocomplete( 'platform_get_with_prefix', 'platform', array( 'value' => $f_platform, 'size' => '32', 'maxlength' => '32', 'tabindex' => helper_get_tab_index_value() ) );
			}
		?>
	</td>
</tr>


<!-- Operating System -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'os' ) ?>
	</td>
	<td>
		<?php
			if ( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
		?>
				<select name="os">
					<option value=""></option>
				<?php
						print_os_option_list( $f_os );
				?>
				</select>
		<?php
			} else {
				projax_autocomplete( 'os_get_with_prefix', 'os', array( 'value' => $f_os, 'size' => '32', 'maxlength' => '32', 'tabindex' => helper_get_tab_index_value() ) );
			}
		?>
	</td>
</tr>


<!-- OS Version -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'os_version' ) ?>
	</td>
	<td>
		<?php
			if ( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
		?>
				<select name="os_build">
					<option value=""></option>
				<?php
						print_os_build_option_list( $f_os_build );
				?>
				</select>
		<?php
			} else {
				projax_autocomplete( 'os_build_get_with_prefix', 'os_build', array( 'value' => $f_os_build, 'size' => '16', 'maxlength' => '16', 'tabindex' => helper_get_tab_index_value() ) );
			}
		?>
	</td>
</tr>


<!-- spacer -->
<tr class="spacer">
	<td colspan="2"></td>
</tr>


<?php
	$t_show_version = ( ON == config_get( 'show_product_version' ) )
			|| ( ( AUTO == config_get( 'show_product_version' ) )
						&& ( count( version_get_all_rows( $t_project_id ) ) > 0 ) );
	if ( $t_show_version ) {
?>
<!-- Product Version -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'product_version' ) ?>
	</td>
	<td>
		<select <?php echo helper_get_tab_index() ?> name="product_version">
			<?php print_version_option_list( $f_product_version, $t_project_id, VERSION_RELEASED ) ?>
		</select>
	</td>
</tr>
<?php
	}
?>

<!-- Product Build -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'product_build' ) ?>
	</td>
	<td>
		<input <?php echo helper_get_tab_index() ?> type="text" name="build" size="32" maxlength="32" value="<?php echo $f_build ?>" />
	</td>
</tr>


<!-- Handler (if permissions allow) -->
<?php if ( access_has_project_level( config_get( 'update_bug_assign_threshold' ) ) ) { ?>
<!-- spacer -->
<tr class="spacer">
	<td colspan="2"></td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'assign_to' ) ?>
	</td>
	<td>
		<select <?php echo helper_get_tab_index() ?> name="handler_id">
			<option value="0" selected="selected"></option>
			<?php print_assign_to_option_list( $f_handler_id ) ?>
		</select>
	</td>
</tr>
<?php } ?>

<!-- Target Version (if permissions allow) -->
<?php if ( access_has_project_level( config_get( 'roadmap_update_threshold' ) ) ) { ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'target_version' ) ?>
	</td>
	<td>
		<select <?php echo helper_get_tab_index() ?> name="target_version">
			<?php print_version_option_list() ?>
		</select>
	</td>
</tr>
<?php } ?>


<!-- spacer -->
<tr class="spacer">
	<td colspan="2"></td>
</tr>


<!-- Summary -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'summary' ) ?> <?php print_documentation_link( 'summary' ) ?>
	</td>
	<td>
		<input <?php echo helper_get_tab_index() ?> type="text" name="summary" size="105" maxlength="128" value="<?php echo $f_summary ?>" />
	</td>
</tr>


<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<span class="required">*</span><?php echo lang_get( 'description' ) ?> <?php print_documentation_link( 'description' ) ?>
	</td>
	<td>
		<textarea <?php echo helper_get_tab_index() ?> name="description" cols="80" rows="10"><?php echo $f_description ?></textarea>
	</td>
</tr>


<!-- Steps to Reproduce -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'steps_to_reproduce' ) ?> <?php print_documentation_link( 'steps_to_reproduce' ) ?>
	</td>
	<td>
		<textarea <?php echo helper_get_tab_index() ?> name="steps_to_reproduce" cols="80" rows="10"><?php echo $f_steps_to_reproduce ?></textarea>
	</td>
</tr>


<!-- Additional Information -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?> <?php print_documentation_link( 'additional_information' ) ?>
	</td>
	<td>
		<textarea <?php echo helper_get_tab_index() ?> name="additional_info" cols="80" rows="10"><?php echo $f_additional_info ?></textarea>
	</td>
</tr>


<tr class="spacer">
	<td colspan="2"></td>
</tr>


<!-- Custom Fields -->
<?php
	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( ( $t_def['display_report'] || $t_def['require_report']) && custom_field_has_write_access_to_project( $t_id, $t_project_id ) ) {
			$t_custom_fields_found = true;
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php if($t_def['require_report']) {?><span class="required">*</span><?php } ?><?php echo string_display( lang_get_defaulted( $t_def['name'] ) ) ?>
	</td>
	<td>
		<?php print_custom_field_input( $t_def, ( $f_master_bug_id === 0 ) ? null : $f_master_bug_id ) ?>
	</td>
</tr>
<?php
		}
	} # foreach( $t_related_custom_field_ids as $t_id )
?>


<?php if ( $t_custom_fields_found ) { ?>
<!-- spacer -->
<tr class="spacer">
	<td colspan="2">&nbsp;</td>
</tr>
<?php } # custom fields found ?>


<!-- File Upload (if enabled) -->
<?php if ( file_allow_bug_upload() ) {
	$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'upload_file' ) ?>
		<?php echo '<span class="small">(' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)</span>'?>
	</td>
	<td>
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
		<input <?php echo helper_get_tab_index() ?> name="file" type="file" size="60" />
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
		<input <?php echo helper_get_tab_index() ?> type="radio" name="view_state" value="<?php echo VS_PUBLIC ?>" <?php check_checked( $f_view_state, VS_PUBLIC ) ?> /> <?php echo lang_get( 'public' ) ?>
		<input <?php echo helper_get_tab_index() ?> type="radio" name="view_state" value="<?php echo VS_PRIVATE ?>" <?php check_checked( $f_view_state, VS_PRIVATE ) ?> /> <?php echo lang_get( 'private' ) ?>
<?php
	} else {
		echo get_enum_element( 'project_view_state', $f_view_state );
	}
?>
	</td>
</tr>

<!-- Relationship (in case of cloned bug creation...) -->
<?php
	if( $f_master_bug_id > 0 ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'relationship_with_parent' ) ?>
	</td>
	<td>
		<?php relationship_list_box( /* none */ -2, "rel_type", false, true ) ?>
		<?php PRINT '<b>' . lang_get( 'bug' ) . ' ' . bug_format_id( $f_master_bug_id ) . '</b>' ?>
	</td>
</tr>
<?php
	}
?>

<!-- Report Stay (report more bugs) -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'report_stay' ) ?> <?php print_documentation_link( 'report_stay' ) ?>
	</td>
	<td>
		<input <?php echo helper_get_tab_index() ?> type="checkbox" name="report_stay" <?php check_checked( $f_report_stay ) ?> /> (<?php echo lang_get( 'check_report_more_bugs' ) ?>)
	</td>
</tr>


<!-- Submit Button -->
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<input <?php echo helper_get_tab_index() ?> type="submit" class="button" value="<?php echo lang_get( 'submit_report_button' ) ?>" />
	</td>
</tr>


</table>
</form>
</div>

<!-- Autofocus JS -->
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/javascript" language="JavaScript">
<!--
	window.document.report_bug_form.category.focus();
-->
</script>
<?php } ?>

<?php html_page_bottom1( __FILE__ ) ?>
