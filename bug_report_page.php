<?php
# MantisBT - A PHP based bugtracking system

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
 * This file POSTs data to report_bug.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses collapse_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses profile_api.php
 * @uses project_api.php
 * @uses relationship_api.php
 * @uses string_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

$g_allow_browser_cache = 1;

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'collapse_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'profile_api.php' );
require_api( 'project_api.php' );
require_api( 'relationship_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

$f_master_bug_id = gpc_get_int( 'm_id', 0 );

if( $f_master_bug_id > 0 ) {
	# master bug exists...
	bug_ensure_exists( $f_master_bug_id );

	# master bug is not read-only...
	if( bug_is_readonly( $f_master_bug_id ) ) {
		error_parameters( $f_master_bug_id );
		trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
	}

	# User can view the master bug
	access_ensure_bug_level( config_get( 'view_bug_threshold' ), $f_master_bug_id );

	$t_bug = bug_get( $f_master_bug_id, true );

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

	$f_category_id			= $t_bug->category_id;
	$f_reproducibility		= $t_bug->reproducibility;
	$f_eta					= $t_bug->eta;
	$f_severity				= $t_bug->severity;
	$f_priority				= $t_bug->priority;
	$f_summary				= $t_bug->summary;
	$f_description			= $t_bug->description;
	$f_steps_to_reproduce	= $t_bug->steps_to_reproduce;
	$f_additional_info		= $t_bug->additional_information;
	$f_view_state			= (int)$t_bug->view_state;
	$f_due_date				= $t_bug->due_date;

	$t_project_id			= $t_bug->project_id;
} else {
	# Get Project Id and set it as current
	$t_current_project = helper_get_current_project();
	$t_project_id = gpc_get_int( 'project_id', $t_current_project );

	# If all projects, use default project if set
	$t_default_project = user_pref_get_pref( auth_get_current_user_id(), 'default_project' );
	if( ALL_PROJECTS == $t_project_id && ALL_PROJECTS != $t_default_project ) {
		$t_project_id = $t_default_project;
	}

	if( ( ALL_PROJECTS == $t_project_id || project_exists( $t_project_id ) )
		&& $t_project_id != $t_current_project
		&& project_enabled( $t_project_id ) ) {
		helper_set_current_project( $t_project_id );
		# Reloading the page is required so that the project browser
		# reflects the new current project
		print_header_redirect( $_SERVER['REQUEST_URI'], true, false, true );
	}

	# New issues cannot be reported for the 'All Project' selection
	if( ALL_PROJECTS == $t_current_project ) {
		print_header_redirect( 'login_select_proj_page.php?ref=bug_report_page.php' );
	}

	# Check for bug report threshold
	if( !access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
		# If can't report on current project, show project selector if there is any other allowed project
		access_ensure_any_project_level( 'report_bug_threshold' );
		print_header_redirect( 'login_select_proj_page.php?ref=bug_report_page.php' );
	}
	access_ensure_project_level( config_get( 'report_bug_threshold' ) );

	$f_build				= gpc_get_string( 'build', '' );
	$f_platform				= gpc_get_string( 'platform', '' );
	$f_os					= gpc_get_string( 'os', '' );
	$f_os_build				= gpc_get_string( 'os_build', '' );
	$f_product_version		= gpc_get_string( 'product_version', '' );
	$f_target_version		= gpc_get_string( 'target_version', '' );
	$f_profile_id			= gpc_get_int( 'profile_id', 0 );
	$f_handler_id			= gpc_get_int( 'handler_id', 0 );

	$f_category_id			= gpc_get_int( 'category_id', 0 );
	$f_reproducibility		= gpc_get_int( 'reproducibility', (int)config_get( 'default_bug_reproducibility' ) );
	$f_eta					= gpc_get_int( 'eta', (int)config_get( 'default_bug_eta' ) );
	$f_severity				= gpc_get_int( 'severity', (int)config_get( 'default_bug_severity' ) );
	$f_priority				= gpc_get_int( 'priority', (int)config_get( 'default_bug_priority' ) );
	$f_summary				= gpc_get_string( 'summary', '' );
	$f_description			= gpc_get_string( 'description', config_get( 'default_bug_description' ) );
	$f_steps_to_reproduce	= gpc_get_string( 'steps_to_reproduce', config_get( 'default_bug_steps_to_reproduce' ) );
	$f_additional_info		= gpc_get_string( 'additional_info', config_get( 'default_bug_additional_info' ) );
	$f_view_state			= gpc_get_int( 'view_state', (int)config_get( 'default_bug_view_status' ) );
	$f_due_date				= gpc_get_string( 'due_date', date_strtotime( config_get( 'due_date_default' ) ) );

	if( $f_due_date == '' ) {
		$f_due_date = date_get_null();
	}

	$t_changed_project		= false;
}

$f_report_stay			= gpc_get_bool( 'report_stay', false );
$f_copy_notes_from_parent         = gpc_get_bool( 'copy_notes_from_parent', false );
$f_copy_attachments_from_parent   = gpc_get_bool( 'copy_attachments_from_parent', false );

$t_fields = config_get( 'bug_report_page_fields' );
$t_fields = columns_filter_disabled( $t_fields );

$t_show_category = in_array( 'category_id', $t_fields );
$t_show_reproducibility = in_array( 'reproducibility', $t_fields );
$t_show_eta = in_array( 'eta', $t_fields );
$t_show_severity = in_array( 'severity', $t_fields );
$t_show_priority = in_array( 'priority', $t_fields );
$t_show_steps_to_reproduce = in_array( 'steps_to_reproduce', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && access_has_project_level( config_get( 'update_bug_assign_threshold' ) );
$t_show_profiles = config_get( 'enable_profiles' );
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_show_os_version = $t_show_profiles && in_array( 'os_version', $t_fields );
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_show_status = in_array( 'status', $t_fields );
$t_show_tags =
	in_array( 'tags', $t_fields ) &&
	access_has_project_level(
		config_get( 'tag_attach_threshold', /* default */ null, /* user */ null, $t_project_id ),
		$t_project_id );

$t_show_versions = version_should_show_product_version( $t_project_id );
$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields ) && config_get( 'enable_product_build' ) == ON;
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields ) && access_has_project_level( config_get( 'roadmap_update_threshold' ) );
$t_show_additional_info = in_array( 'additional_info', $t_fields );
$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_project_level( config_get( 'due_date_update_threshold' ), helper_get_current_project(), auth_get_current_user_id() );
$t_show_attachments = in_array( 'attachments', $t_fields ) && file_allow_bug_upload();
$t_show_view_state = in_array( 'view_state', $t_fields ) && access_has_project_level( config_get( 'set_view_status_threshold' ) );

# don't index bug report page
html_robots_noindex();

layout_page_header( lang_get( 'report_bug_link' ) );

layout_page_begin( __FILE__ );

$t_form_encoding = '';
if( $t_show_attachments ) {
	$t_form_encoding = 'enctype="multipart/form-data"';
}
?>
<div class="col-md-12 col-xs-12">
<form id="report_bug_form"
	method="post" <?php echo $t_form_encoding; ?>
	action="bug_report.php?posted=1">
<?php echo form_security_field( 'bug_report' ) ?>
<input type="hidden" name="m_id" value="<?php echo $f_master_bug_id ?>" />
<input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-edit"></i>
				<?php echo lang_get( 'enter_report_details_title' ) ?>
		</h4>
	</div>
<div class="widget-body dz-clickable">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed">
<?php
	event_signal( 'EVENT_REPORT_BUG_FORM_TOP', array( $t_project_id ) );

	if( $t_show_category ) {
?>
	<tr>
		<th class="category" width="30%">
			<?php
			echo config_get( 'allow_no_category' ) ? '' : '<span class="required">*</span> ';
			echo '<label for="category_id">';
			print_documentation_link( 'category' );
			echo '</label>';
			?>
		</th>
		<td width="70%">
			<?php if( $t_changed_project ) {
				echo '[' . project_get_field( $t_bug->project_id, 'name' ) . '] ';
			} ?>
			<select <?php echo helper_get_tab_index() ?> id="category_id" name="category_id" class="autofocus input-sm">
				<?php
					print_category_option_list( $f_category_id );
				?>
			</select>
		</td>
	</tr>
<?php }

	if( $t_show_reproducibility ) {
?>

	<tr>
		<th class="category">
			<label for="reproducibility"><?php print_documentation_link( 'reproducibility' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="reproducibility" name="reproducibility" class="input-sm">
				<?php print_enum_string_option_list( 'reproducibility', $f_reproducibility ) ?>
			</select>
		</td>
	</tr>
<?php
	}

	if( $t_show_eta ) {
?>

	<tr>
		<th class="category">
			<label for="eta"><?php print_documentation_link( 'eta' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="eta" name="eta" class="input-sm">
				<?php print_enum_string_option_list( 'eta', $f_eta ) ?>
			</select>
		</td>
	</tr>
<?php
	}

	if( $t_show_severity ) {
?>
	<tr>
		<th class="category">
			<label for="severity"><?php print_documentation_link( 'severity' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="severity" name="severity" class="input-sm">
				<?php print_enum_string_option_list( 'severity', $f_severity ) ?>
			</select>
		</td>
	</tr>
<?php
	}

	if( $t_show_priority ) {
?>
	<tr>
		<th class="category">
			<label for="priority"><?php print_documentation_link( 'priority' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="priority" name="priority" class="input-sm">
				<?php print_enum_string_option_list( 'priority', $f_priority ) ?>
			</select>
		</td>
	</tr>
<?php
	}

	if( $t_show_due_date ) {
		$t_date_to_display = '';

		if( !date_is_null( $f_due_date ) ) {
			$t_date_to_display = date( config_get( 'normal_date_format' ), $f_due_date );
		}
?>
	<tr>
		<th class="category">
			<label for="due_date"><?php print_documentation_link( 'due_date' ) ?></label>
		</th>
		<td>
			<?php echo '<input ' . helper_get_tab_index() . ' type="text" id="due_date" name="due_date" class="datetimepicker input-sm" ' .
				'data-picker-locale="' . lang_get_current_datetime_locale() .
				'" data-picker-format="' . config_get( 'datetime_picker_format' ) . '" ' .
				'size="20" maxlength="16" value="' . $t_date_to_display . '" />' ?>
			<i class="fa fa-calendar fa-xlg datetimepicker"></i>
		</td>
	</tr>
<?php } ?>
<?php if( $t_show_platform || $t_show_os || $t_show_os_version ) { ?>
	<tr>
		<th class="category">
			<label for="profile_id"><?php echo lang_get( 'select_profile' ) ?></label>
		</th>
		<td>
			<?php if( count( profile_get_all_for_user( auth_get_current_user_id() ) ) > 0 ) { ?>
				<select <?php echo helper_get_tab_index() ?> id="profile_id" name="profile_id" class="input-sm">
					<?php print_profile_option_list( auth_get_current_user_id(), $f_profile_id ) ?>
				</select>
			<?php } ?>
			<?php collapse_open( 'profile' ); collapse_icon( 'profile' ); ?>
			<?php echo lang_get( 'or_fill_in' ); ?>
			<table class="table-bordered table-condensed">
				<tr>
					<th class="category" width="30%">
						<label for="platform"><?php echo lang_get( 'platform' ) ?></label>
					</th>
					<td>
						<?php if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) { ?>
						<select id="platform" name="platform" class="input-sm">
							<option value=""></option>
							<?php print_platform_option_list( $f_platform ); ?>
						</select>
						<?php
							} else {
								echo '<input type="text" id="platform" name="platform" class="typeahead input-sm" autocomplete = "off" size="32" maxlength="32" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $f_platform ) . '" />';
							}
						?>
					</td>
				</tr>
				<tr>
					<th class="category">
						<label for="os"><?php echo lang_get( 'os' ) ?></label>
					</th>
					<td>
						<?php if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) { ?>
						<select id="os" name="os" class="input-sm">
							<option value=""></option>
							<?php print_os_option_list( $f_os ); ?>
						</select>
						<?php
							} else {
								echo '<input type="text" id="os" name="os" class="typeahead input-sm" autocomplete = "off" size="32" maxlength="32" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $f_os ) . '" />';
							}
						?>
					</td>
				</tr>
				<tr>
					<th class="category">
						<label for="os_build"><?php echo lang_get( 'os_version' ) ?></label>
					</th>
					<td>
						<?php
							if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
						?>
						<select id="os_build" name="os_build" class="input-sm">
							<option value=""></option>
								<?php print_os_build_option_list( $f_os_build ); ?>
							</select>
						<?php
							} else {
								echo '<input type="text" id="os_build" name="os_build" class="typeahead input-sm" autocomplete = "off" size="16" maxlength="16" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $f_os_build ) . '" />';
							}
						?>
					</td>
				</tr>
			</table>
			<?php collapse_closed( 'profile' ); collapse_icon( 'profile' ); ?>
			<?php echo lang_get( 'or_fill_in' ); ?>
			<?php collapse_end( 'profile' ); ?>
		</td>
	</tr>
<?php } ?>
<?php
	if( $t_show_product_version ) {
		$t_product_version_released_mask = VERSION_RELEASED;

		if( access_has_project_level( config_get( 'report_issues_for_unreleased_versions_threshold' ) ) ) {
			$t_product_version_released_mask = VERSION_ALL;
		}
?>
	<tr>
		<th class="category">
			<label for="product_version"><?php echo lang_get( 'product_version' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="product_version" name="product_version" class="input-sm">
				<?php print_version_option_list( $f_product_version, $t_project_id, $t_product_version_released_mask ) ?>
			</select>
		</td>
	</tr>
<?php
	}
?>
<?php if( $t_show_product_build ) { ?>
	<tr>
		<th class="category">
			<label for="build"><?php echo lang_get( 'product_build' ) ?></label>
		</th>
		<td>
			<input <?php echo helper_get_tab_index() ?> type="text" id="build" name="build" size="32" maxlength="32" value="<?php echo string_attribute( $f_build ) ?>" />
		</td>
	</tr>
<?php } ?>

<?php if( $t_show_handler ) { ?>
	<tr>
		<th class="category">
			<label for="handler_id"><?php echo lang_get( 'assign_to' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="handler_id" name="handler_id" class="input-sm">
				<option value="0" selected="selected"></option>
				<?php print_assign_to_option_list( $f_handler_id ) ?>
			</select>
		</td>
	</tr>
<?php } ?>

<?php if( $t_show_status ) { ?>
	<tr>
		<th class="category">
			<label for="status"><?php echo lang_get( 'status' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> name="status" class="input-sm">
			<?php
			$t_resolution_options = get_status_option_list(
				access_get_project_level( $t_project_id ),
				config_get( 'bug_submit_status' ),
				true,
				ON == config_get( 'allow_reporter_close' ),
				$t_project_id );
			foreach ( $t_resolution_options as $t_key => $t_value ) {
			?>
				<option value="<?php echo $t_key ?>" <?php check_selected( $t_key, config_get( 'bug_submit_status' ) ); ?> >
					<?php echo $t_value ?>
				</option>
			<?php } ?>
			</select>
		</td>
	</tr>
<?php } ?>

<?php if( $t_show_resolution ) { ?>
	<tr>
		<th class="category">
			<label for="resolution"><?php echo lang_get( 'resolution' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> name="resolution" class="input-sm">
				<?php
				print_enum_string_option_list( 'resolution', config_get( 'default_bug_resolution' ) );
				?>
			</select>
		</td>
	</tr>
<?php } ?>

<?php # Target Version (if permissions allow)
	if( $t_show_target_version ) { ?>
	<tr>
		<th class="category">
			<label for="target_version"><?php echo lang_get( 'target_version' ) ?></label>
		</th>
		<td>
			<select <?php echo helper_get_tab_index() ?> id="target_version" name="target_version" class="input-sm">
				<?php print_version_option_list( '', null, VERSION_FUTURE ) ?>
			</select>
		</td>
	</tr>
<?php } ?>
<?php event_signal( 'EVENT_REPORT_BUG_FORM', array( $t_project_id ) ) ?>
	<tr>
		<th class="category">
			<span class="required">*</span><label for="summary"><?php print_documentation_link( 'summary' ) ?></label>
		</th>
		<td>
			<input <?php echo helper_get_tab_index() ?> type="text" id="summary" name="summary" size="105" maxlength="128" value="<?php echo string_attribute( $f_summary ) ?>" required />
		</td>
	</tr>
	<tr>
		<th class="category">
			<span class="required">*</span><label for="description"><?php print_documentation_link( 'description' ) ?></label>
		</th>
		<td>
			<textarea class="form-control" <?php echo helper_get_tab_index() ?> id="description" name="description" cols="80" rows="10" required><?php echo string_textarea( $f_description ) ?></textarea>
		</td>
	</tr>

<?php if( $t_show_steps_to_reproduce ) { ?>
		<tr>
			<th class="category">
				<label for="steps_to_reproduce"><?php print_documentation_link( 'steps_to_reproduce' ) ?></label>
			</th>
			<td>
				<textarea class="form-control" <?php echo helper_get_tab_index() ?> id="steps_to_reproduce" name="steps_to_reproduce" cols="80" rows="10"><?php echo string_textarea( $f_steps_to_reproduce ) ?></textarea>
			</td>
		</tr>
<?php } ?>

<?php if( $t_show_additional_info ) { ?>
	<tr>
		<th class="category">
			<label for="additional_info"><?php print_documentation_link( 'additional_information' ) ?></label>
		</th>
		<td>
			<textarea class="form-control" <?php echo helper_get_tab_index() ?> id="additional_info" name="additional_info" cols="80" rows="10"><?php echo string_textarea( $f_additional_info ) ?></textarea>
		</td>
	</tr>
<?php } ?>
<?php if( $t_show_tags ) { ?>
	<tr>
		<th class="category">
			<label for="attach_tag"><?php echo lang_get( 'tag_attach_long' ) ?></label>
		</th>
		<td>
			<?php print_tag_input( '' ); ?>
		</td>
	</tr>
<?php
	}

	$t_custom_fields_found = false;
	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		if( ( $t_def['display_report'] || $t_def['require_report']) && custom_field_has_write_access_to_project( $t_id, $t_project_id ) ) {
			$t_custom_fields_found = true;

			if( $t_def['type'] != CUSTOM_FIELD_TYPE_RADIO && $t_def['type'] != CUSTOM_FIELD_TYPE_CHECKBOX ) {
				$t_label_for = 'for="custom_field_' . string_attribute( $t_def['id'] ) . '" ';
			} else {
				$t_label_for = '';
			}
?>
	<tr>
		<th class="category">
			<?php if( $t_def['require_report'] ) {?><span class="required">*</span><?php } ?>
			<?php if( $t_def['type'] != CUSTOM_FIELD_TYPE_RADIO && $t_def['type'] != CUSTOM_FIELD_TYPE_CHECKBOX ) { ?>
				<label for="custom_field_<?php echo string_attribute( $t_def['id'] ) ?>">
					<?php echo string_display_line( lang_get_defaulted( $t_def['name'] ) ) ?>
				</label>
			<?php } else { echo string_display_line( lang_get_defaulted( $t_def['name'] ) ); } ?>
		</th>
		<td>
			<?php print_custom_field_input( $t_def, ( $f_master_bug_id === 0 ) ? null : $f_master_bug_id, $t_def['require_report'] ) ?>
		</td>
	</tr>
<?php
		}
	} # foreach( $t_related_custom_field_ids as $t_id )
?>
<?php
	# File Upload (if enabled)
	if( $t_show_attachments ) {
		$t_max_file_size = file_get_max_file_size();
		$t_file_upload_max_num = max( 1, config_get( 'file_upload_max_num' ) );
?>
	<tr>
		<th class="category">
			<label for="ufile[]"><?php echo lang_get( $t_file_upload_max_num == 1 ? 'upload_file' : 'upload_files' ) ?></label>
			<br />
			<?php print_max_filesize( $t_max_file_size ); ?>
		</th>
		<td>
			<?php print_dropzone_template() ?>
			<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
			<div class="dropzone center" <?php print_dropzone_form_data() ?>>
				<i class="upload-icon ace-icon fa fa-cloud-upload blue fa-3x"></i><br>
				<span class="bigger-150 grey"><?php echo lang_get( 'dropzone_default_message' ) ?></span>
				<div id="dropzone-previews-box" class="dropzone-previews dz-max-files-reached"></div>
			</div>
			<div class="fallback">
				<div class="dz-message" data-dz-message></div>
				<input <?php echo helper_get_tab_index() ?> id="ufile[]" name="ufile[]" type="file" size="60" />
			</div>
		</td>
	</tr>

<?php
	}

	if( $t_show_view_state ) {
?>
	<tr>
		<th class="category">
			<?php echo lang_get( 'view_status' ) ?>
		</th>
		<td>
			<label>
				<input <?php echo helper_get_tab_index() ?> type="radio" class="ace" name="view_state" value="<?php echo VS_PUBLIC ?>" <?php check_checked( $f_view_state, VS_PUBLIC ) ?> />
				<span class="lbl padding-6"><?php echo lang_get( 'public' ) ?></span>
			</label>
			&#160;&#160;&#160;&#160;
			<label>
				<input <?php echo helper_get_tab_index() ?> type="radio" class="ace" name="view_state" value="<?php echo VS_PRIVATE ?>" <?php check_checked( $f_view_state, VS_PRIVATE ) ?> />
				<span class="lbl padding-6"><?php echo lang_get( 'private' ) ?></span>
			</label>
		</td>
	</tr>
<?php
	}

	# Relationship (in case of cloned bug creation...)
	if( $f_master_bug_id > 0 ) {
?>
	<tr>
		<th class="category">
			<?php echo lang_get( 'relationship_with_parent' ) ?>
		</th>
		<td>
			<?php relationship_list_box( config_get( 'default_bug_relationship_clone' ), "rel_type", false, true ) ?>
			<?php echo '<strong>' . lang_get( 'bug' ) . ' ' . bug_format_id( $f_master_bug_id ) . '</strong>' ?>
		</td>
	</tr>

	<tr>
		<td class="category">
			<?php echo lang_get( 'copy_from_parent' ) ?>
		</td>
		<td>
			<label>
				<input <?php echo helper_get_tab_index() ?> type="checkbox" class="ace" id="copy_notes_from_parent" name="copy_notes_from_parent" <?php check_checked( $f_copy_notes_from_parent ) ?> />
				<span class="lbl padding-6"><?php echo lang_get( 'copy_notes_from_parent' ) ?></span>
			</label>
			&#160;&#160;&#160;&#160;
			<label>
				<input <?php echo helper_get_tab_index() ?> type="checkbox" class="ace" id="copy_attachments_from_parent" name="copy_attachments_from_parent" <?php check_checked( $f_copy_attachments_from_parent ) ?> />
				<span class="lbl padding-6"><?php echo lang_get( 'copy_attachments_from_parent' ) ?></span>
			</label>
		</td>
	</tr>
<?php
	}
?>
	<tr>
		<th class="category">
			<?php print_documentation_link( 'report_stay' ) ?>
		</th>
		<td>
			<label>
				<input <?php echo helper_get_tab_index() ?> type="checkbox" class="ace" id="report_stay" name="report_stay" <?php check_checked( $f_report_stay ) ?> />
				<span class="lbl padding-6"><?php echo lang_get( 'check_report_more_bugs' ) ?></span>
			</label>
		</td>
	</tr>
</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
	<input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'submit_report_button' ) ?>" />
</div>
</div>
</div>
</form>
</div>
<?php
layout_page_end();
