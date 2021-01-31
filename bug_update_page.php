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
 * Display advanced Bug update page
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
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses last_visited_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'last_visited_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'version_api.php' );

require_css( 'status_config.php' );

$f_bug_id = gpc_get_int( 'bug_id' );
$f_reporter_edit = gpc_get_bool( 'reporter_edit' );

$t_bug = bug_get( $f_bug_id, true );

if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

if( bug_is_readonly( $f_bug_id ) ) {
	error_parameters( $f_bug_id );
	trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
}

access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

$t_fields = config_get( 'bug_update_page_fields' );
$t_fields = columns_filter_disabled( $t_fields );

$t_bug_id = $f_bug_id;

$t_action_button_position = config_get( 'action_button_position' );

$t_top_buttons_enabled = $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH;
$t_bottom_buttons_enabled = $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH;

$t_show_id = in_array( 'id', $t_fields );
$t_show_project = in_array( 'project', $t_fields );
$t_show_category = in_array( 'category_id', $t_fields );
$t_show_view_state = in_array( 'view_state', $t_fields );
$t_view_state = $t_show_view_state ? string_display_line( get_enum_element( 'view_state', $t_bug->view_state ) ) : '';
$t_show_date_submitted = in_array( 'date_submitted', $t_fields );
$t_show_last_updated = in_array( 'last_updated', $t_fields );
$t_show_reporter = in_array( 'reporter', $t_fields );
$t_show_handler = in_array( 'handler', $t_fields ) && access_has_bug_level( config_get( 'view_handler_threshold' ), $t_bug_id );
$t_show_priority = in_array( 'priority', $t_fields );
$t_show_severity = in_array( 'severity', $t_fields );
$t_show_reproducibility = in_array( 'reproducibility', $t_fields );
$t_show_status = in_array( 'status', $t_fields );
$t_show_resolution = in_array( 'resolution', $t_fields );
$t_show_projection = in_array( 'projection', $t_fields ) && config_get( 'enable_projection' ) == ON;
$t_show_eta = in_array( 'eta', $t_fields ) && config_get( 'enable_eta' ) == ON;
$t_show_profiles = config_get( 'enable_profiles' ) == ON;
$t_show_platform = $t_show_profiles && in_array( 'platform', $t_fields );
$t_show_os = $t_show_profiles && in_array( 'os', $t_fields );
$t_show_os_build = $t_show_profiles && in_array( 'os_build', $t_fields );
$t_show_versions = version_should_show_product_version( $t_bug->project_id );
$t_show_product_version = $t_show_versions && in_array( 'product_version', $t_fields );
$t_show_product_build = $t_show_versions && in_array( 'product_build', $t_fields ) && ( config_get( 'enable_product_build' ) == ON );
$t_product_build_attribute = $t_show_product_build ? string_attribute( $t_bug->build ) : '';
$t_show_target_version = $t_show_versions && in_array( 'target_version', $t_fields ) && access_has_bug_level( config_get( 'roadmap_update_threshold' ), $t_bug_id );
$t_show_fixed_in_version = $t_show_versions && in_array( 'fixed_in_version', $t_fields );
$t_show_due_date = in_array( 'due_date', $t_fields ) && access_has_bug_level( config_get( 'due_date_view_threshold' ), $t_bug_id );
$t_show_summary = in_array( 'summary', $t_fields );
$t_summary_attribute = $t_show_summary ? string_attribute( $t_bug->summary ) : '';
$t_show_description = in_array( 'description', $t_fields );
$t_description_textarea = $t_show_description ? string_textarea( $t_bug->description ) : '';
$t_show_additional_information = in_array( 'additional_info', $t_fields );
$t_additional_information_textarea = $t_show_additional_information ? string_textarea( $t_bug->additional_information ) : '';
$t_show_steps_to_reproduce = in_array( 'steps_to_reproduce', $t_fields );
$t_steps_to_reproduce_textarea = $t_show_steps_to_reproduce ? string_textarea( $t_bug->steps_to_reproduce ) : '';
if( NO_USER == $t_bug->handler_id ) {
	$t_handler_name =  '';
} else {
	$t_handler_name = string_display_line( user_get_name( $t_bug->handler_id ) );
}

$t_can_change_view_state = $t_show_view_state && access_has_project_level( config_get( 'change_view_status_threshold' ) );

if( $t_show_product_version ) {
	$t_product_version_released_mask = VERSION_RELEASED;

	if( access_has_project_level( config_get( 'report_issues_for_unreleased_versions_threshold' ) ) ) {
		$t_product_version_released_mask = VERSION_ALL;
	}
}

$t_formatted_bug_id = $t_show_id ? bug_format_id( $f_bug_id ) : '';
$t_project_name = $t_show_project ? string_display_line( project_get_name( $t_bug->project_id ) ) : '';

layout_page_header( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );

layout_page_begin();

?>
<div class="col-md-12 col-xs-12">
<div id="bug-update" class="form-container">
	<form id="update_bug_form" method="post" action="bug_update.php">
		<?php echo form_security_field( 'bug_update' ); ?>
		<input type="hidden" name="bug_id" value="<?php echo $t_bug_id ?>" />
        <input type="hidden" name="last_updated" value="<?php echo $t_bug->last_updated ?>" />

		<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-comments', 'ace-icon' ); ?>
				<?php echo lang_get( 'updating_bug_advanced_title' ) ?>
			</h4>
			<div class="widget-toolbar no-border">
				<div class="widget-menu">
					<?php print_small_button( string_get_bug_view_url( $t_bug_id ), lang_get( 'back_to_bug_link' ) ); ?>
				</div>
			</div>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">

<?php
# Submit Button
if( $t_top_buttons_enabled ) {
?>
				<div class="widget-toolbox padding-8 clearfix">
					<input <?php helper_get_tab_index(); ?>
						type="submit" class="btn btn-primary btn-white btn-round"
						value="<?php echo lang_get( 'update_information_button' ); ?>" />
				</div>
<?php
}
?>
			<tbody>
<?php
event_signal( 'EVENT_UPDATE_BUG_FORM_TOP', array( $t_bug_id ) );

if( $t_show_id || $t_show_project || $t_show_category || $t_show_view_state || $t_show_date_submitted | $t_show_last_updated ) {
	#
	# Titles for Bug Id, Project Name, Category, View State, Date Submitted, Last Updated
	#

	echo '<tr>';
	echo '<td width="15%" class="category">', $t_show_id ? lang_get( 'id' ) : '', '</td>';
	echo '<td width="20%" class="category">', $t_show_project ? lang_get( 'email_project' ) : '', '</td>';
	echo '<td width="15%" class="category">';
	if( $t_show_category ) {
		$t_allow_no_category = config_get( 'allow_no_category' );
		echo $t_allow_no_category ? '' : '<span class="required">*</span> ';
		echo '<label for="category_id">' . lang_get( 'category' ) . '</label>';
	}
	echo '</td>';
	echo '<td width="20%" class="category">', $t_show_view_state ? '<label for="view_state">' . lang_get( 'view_status' ) . '</label>' : '', '</td>';
	echo '<td width="15%" class="category">', $t_show_date_submitted ? lang_get( 'date_submitted' ) : '', '</td>';
	echo '<td width="15%" class="category">', $t_show_last_updated ? lang_get( 'last_update' ) : '', '</td>';
	echo '</tr>';

	#
	# Values for Bug Id, Project Name, Category, View State, Date Submitted, Last Updated
	#

	echo '<tr>';

	# Bug ID
	echo '<td>', $t_formatted_bug_id, '</td>';

	# Project Name
	echo '<td>', $t_project_name, '</td>';

	# Category
	echo '<td>';

	if( $t_show_category ) {
		echo '<select ' . helper_get_tab_index()
			. ( $t_allow_no_category ? '' : ' required' )
			. ' id="category_id" name="category_id" class="input-sm">';
		print_category_option_list( $t_bug->category_id, $t_bug->project_id );
		echo '</select>';
	}

	echo '</td>';

	# View State
	echo '<td>';

	if( $t_can_change_view_state ) {
		echo '<select ' . helper_get_tab_index() . ' id="view_state" name="view_state" class="input-sm">';
		print_enum_string_option_list( 'view_state', (int)$t_bug->view_state );
		echo '</select>';
	} else if( $t_show_view_state ) {
		echo $t_view_state;
	}

	echo '</td>';

	# Date Submitted
	echo '<td>', $t_show_date_submitted ? date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) : '', '</td>';

	# Date Updated
	echo '<td>', $t_show_last_updated ? date( config_get( 'normal_date_format' ), $t_bug->last_updated ) : '', '</td>';

	echo '</tr>';

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

#
# Reporter, Assigned To, Due Date
#

if( $t_show_reporter || $t_show_handler || $t_show_due_date ) {
	echo '<tr>';

	$t_spacer = 0;

	if( $t_show_reporter ) {
		# Reporter
		echo '<th class="category"><label for="reporter_id">' . lang_get( 'reporter' ) . '</label></th>';
		echo '<td>';

		# Do not allow the bug's reporter to edit the Reporter field
		# when limit_reporters is ON
		if( access_has_limited_view( $t_bug->project_id ) ) {
			echo string_attribute( user_get_name( $t_bug->reporter_id ) );
		} else {
			if ( $f_reporter_edit ) {
				echo '<select ' . helper_get_tab_index() . ' id="reporter_id" name="reporter_id">';
				print_reporter_option_list( $t_bug->reporter_id, $t_bug->project_id );
				echo '</select>';
			} else {
				echo string_attribute( user_get_name( $t_bug->reporter_id ) );
				echo ' [<a href="#reporter_edit" class="click-url" url="' . string_get_bug_update_url( $f_bug_id ) . '&amp;reporter_edit=true">' . lang_get( 'edit' ) . '</a>]';
			}
		}
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if ( $t_show_handler ) {
		# Assigned To
		echo '<th class="category"><label for="handler_id">' . lang_get( 'assigned_to' ) . '</label></th>';
		echo '<td>';

		if( access_has_project_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ) ) ) {
			echo '<select ' . helper_get_tab_index() . ' id="handler_id" name="handler_id" class="input-sm">';
			echo '<option value="0"></option>';
			print_assign_to_option_list( $t_bug->handler_id, $t_bug->project_id );
			echo '</select>';
		} else {
			echo $t_handler_name;
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_due_date ) {
		# Due Date
		echo '<th class="category"><label for="due_date">' . lang_get( 'due_date' ) . '</label></th>';

		$t_level = bug_overdue_level( $t_bug_id );
		if( $t_level === false ) {
			echo '<td>';
		} else {
			echo '<td class="due-', $t_level, '">';
		}

		if( access_has_bug_level( config_get( 'due_date_update_threshold' ), $t_bug_id ) ) {
			$t_date_to_display = '';

			if( !date_is_null( $t_bug->due_date ) ) {
				$t_date_to_display = date( config_get( 'normal_date_format' ), $t_bug->due_date );
			}
			echo '<input ' . helper_get_tab_index() . ' type="text" id="due_date" name="due_date" class="datetimepicker input-sm" size="16" ' .
				'data-picker-locale="' . lang_get_current_datetime_locale() .  '" data-picker-format="' . config_get( 'datetime_picker_format' ) . '" ' .
				'maxlength="16" value="' . $t_date_to_display . '" />';
			print_icon( 'fa-calendar', 'fa-xlg datetimepicker' );
		} else {
			if( !date_is_null( $t_bug->due_date ) ) {
				echo date( config_get( 'short_date_format' ), $t_bug->due_date );
			}
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}
	echo '</tr>';
}

#
# Priority, Severity, Reproducibility
#

if( $t_show_priority || $t_show_severity || $t_show_reproducibility ) {
	echo '<tr>';

	$t_spacer = 0;

	if( $t_show_priority ) {
		# Priority
		echo '<th class="category"><label for="priority">' . lang_get( 'priority' ) . '</label></th>';
		echo '<td><select ' . helper_get_tab_index() . ' id="priority" name="priority" class="input-sm">';
		print_enum_string_option_list( 'priority', $t_bug->priority );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_severity ) {
		# Severity
		echo '<th class="category"><label for="severity">' . lang_get( 'severity' ) . '</label></th>';
		echo '<td><select ' . helper_get_tab_index() . ' id="severity" name="severity" class="input-sm">';
		print_enum_string_option_list( 'severity', $t_bug->severity );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_reproducibility ) {
		# Reproducibility
		echo '<th class="category"><label for="reproducibility">' . lang_get( 'reproducibility' ) . '</label></th>';
		echo '<td><select ' . helper_get_tab_index() . ' id="reproducibility" name="reproducibility" class="input-sm">';
		print_enum_string_option_list( 'reproducibility', $t_bug->reproducibility );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Status, Resolution
#

if( $t_show_status || $t_show_resolution ) {
	echo '<tr>';

	$t_spacer = 2;

	if( $t_show_status ) {
		# Status
		echo '<th class="category"><label for="status">' . lang_get( 'status' ) . '</label></th>';

		# choose color based on status
		$t_status_css = html_get_status_css_fg( $t_bug->status );

		echo '<td class="bug-status">';
		print_icon( 'fa-square', 'fa-status-box ' . $t_status_css );
		echo '&nbsp;';
		print_status_option_list( 'status', $t_bug->status,
			access_can_close_bug( $t_bug ),
			$t_bug->project_id );
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_resolution ) {
		# Resolution
		echo '<th class="category"><label for="resolution">' . lang_get( 'resolution' ) . '</label></th>';
		echo '<td><select ' . helper_get_tab_index() . ' id="resolution" name="resolution" class="input-sm">';
		print_enum_string_option_list( 'resolution', $t_bug->resolution );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Projection, ETA
#

if( $t_show_projection || $t_show_eta ) {
	echo '<tr>';

	$t_spacer = 2;

	if( $t_show_projection ) {
		# Projection
		echo '<th class="category"><label for="projection">' . lang_get( 'projection' ) . '</label></th>';
		echo '<td><select ' . helper_get_tab_index() . ' id="projection" name="projection" class="input-sm">';
		print_enum_string_option_list( 'projection', $t_bug->projection );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# ETA
	if( $t_show_eta ) {
		echo '<th class="category"><label for="eta">' . lang_get( 'eta' ) . '</label></th>';
		echo '<td><select ' . helper_get_tab_index() . ' id="eta" name="eta" class="input-sm">';
		print_enum_string_option_list( 'eta', (int)$t_bug->eta );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Platform, OS, OS Version
#

if( $t_show_platform || $t_show_os || $t_show_os_build ) {
	echo '<tr>';

	$t_spacer = 0;

	if( $t_show_platform ) {
		# Platform
		echo '<th class="category"><label for="platform">' . lang_get( 'platform' ) . '</label></th>';
		echo '<td>';

		if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . helper_get_tab_index() . ' id="platform" name="platform" class="input-sm"><option value=""></option>';
			print_platform_option_list( $t_bug->platform );
			echo '</select>';
		} else {
			echo '<input type="text" id="platform" name="platform" class="typeahead input-sm" autocomplete = "off" size="16" maxlength="32" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $t_bug->platform ) . '" />';
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_os ) {
		# Operating System
		echo '<th class="category"><label for="os">' . lang_get( 'os' ) . '</label></th>';
		echo '<td>';

		if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . helper_get_tab_index() . ' id="os" name="os" class="input-sm"><option value=""></option>';
			print_os_option_list( $t_bug->os );
			echo '</select>';
		} else {
			echo '<input type="text" id="os" name="os" class="typeahead input-sm" autocomplete = "off" size="16" maxlength="32" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $t_bug->os ) . '" />';
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_os_build ) {
		# OS Version
		echo '<th class="category"><label for="os_build">' . lang_get( 'os_build' ) . '</label></th>';
		echo '<td>';

		if( config_get( 'allow_freetext_in_profile_fields' ) == OFF ) {
			echo '<select ' . helper_get_tab_index() . ' id="os_build" name="os_build" class="input-sm"><option value=""></option>';
			print_os_build_option_list( $t_bug->os_build );
			echo '</select>';
		} else {
			echo '<input type="text" id="os_build" name="os_build" class="typeahead input-sm" autocomplete = "off" size="16" maxlength="16" tabindex="' . helper_get_tab_index_value() . '" value="' . string_attribute( $t_bug->os_build ) . '" />';
		}

		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Product Version, Product Build
#

if( $t_show_product_version || $t_show_product_build ) {
	echo '<tr>';

	$t_spacer = 2;

	# Product Version  or Product Build, if version is suppressed
	if( $t_show_product_version ) {
		echo '<th class="category"><label for="version">' . lang_get( 'product_version' ) . '</label></th>';
		echo '<td>', '<select ', helper_get_tab_index(), ' id="version" name="version" class="input-sm">';
		print_version_option_list( $t_bug->version, $t_bug->project_id, $t_product_version_released_mask );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_show_product_build ) {
		echo '<th class="category"><label for="build">' . lang_get( 'product_build' ) . '</label></th>';
		echo '<td>';
		echo '<input type="text" id="build" name="build" class="input-sm" size="16" maxlength="32" ' . helper_get_tab_index() . ' value="' . $t_product_build_attribute . '" />';
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Target Version, Fixed in Version
#

if( $t_show_target_version || $t_show_fixed_in_version ) {
	echo '<tr>';

	$t_spacer = 2;

	# Target Version
	if( $t_show_target_version ) {
		echo '<th class="category"><label for="target_version">' . lang_get( 'target_version' ) . '</label></th>';
		echo '<td><select ' . helper_get_tab_index() . ' id="target_version" name="target_version" class="input-sm">';
		print_version_option_list( $t_bug->target_version, $t_bug->project_id, VERSION_FUTURE );
		echo '</select></td>';
	} else {
		$t_spacer += 2;
	}

	# Fixed in Version
	if( $t_show_fixed_in_version ) {
		echo '<th class="category"><label for="fixed_in_version">' . lang_get( 'fixed_in_version' ) . '</label></th>';
		echo '<td>';
		echo '<select ' . helper_get_tab_index() . ' id="fixed_in_version" name="fixed_in_version" class="input-sm">';
		print_version_option_list( $t_bug->fixed_in_version, $t_bug->project_id, VERSION_ALL );
		echo '</select>';
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

event_signal( 'EVENT_UPDATE_BUG_FORM', array( $t_bug_id ) );

# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

# Summary
if( $t_show_summary ) {
	echo '<tr>';
	echo '<th class="category">';
	echo '<span class="required">*</span> ';
	echo '<label for="summary">' . lang_get( 'summary' ) . '</label>';
	echo '</th>';
	echo '<td colspan="5">';
	echo '<input ', helper_get_tab_index(),
		' type="text" required id="summary" name="summary" size="105" maxlength="128" value="',
		$t_summary_attribute, '" />';
	echo '</td></tr>';
}

# Description
if( $t_show_description ) {
	echo '<tr>';
	echo '<th class="category">';
	echo '<span class="required">*</span> ';
	echo '<label for="description">' . lang_get( 'description' ) . '</label>';
	echo '</th>';
	echo '<td colspan="5">';
	echo '<textarea class="form-control" required ', helper_get_tab_index(),
		' cols="80" rows="10" id="description" name="description">', "\n",
		$t_description_textarea, '</textarea>';
	echo '</td></tr>';
}

# Steps to Reproduce
if( $t_show_steps_to_reproduce ) {
	echo '<tr>';
	echo '<th class="category"><label for="steps_to_reproduce">' . lang_get( 'steps_to_reproduce' ) . '</label></th>';
	echo '<td colspan="5">';
	echo '<textarea class="form-control" ', helper_get_tab_index(),
		' cols="80" rows="10" id="steps_to_reproduce" name="steps_to_reproduce">', "\n",
		$t_steps_to_reproduce_textarea, '</textarea>';
	echo '</td></tr>';
}

# Additional Information
if( $t_show_additional_information ) {
	echo '<tr>';
	echo '<th class="category"><label for="additional_information">' . lang_get( 'additional_information' ) . '</label></th>';
	echo '<td colspan="5">';
	echo '<textarea class="form-control" ', helper_get_tab_index(),
		' cols="80" rows="10" id="additional_information" name="additional_information">', "\n",
		$t_additional_information_textarea, '</textarea>';
	echo '</td></tr>';
}

echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

# Custom Fields
$t_custom_fields_found = false;
$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );

foreach ( $t_related_custom_field_ids as $t_id ) {
	$t_def = custom_field_get_definition( $t_id );
	if( ( $t_def['display_update'] || $t_def['require_update'] ) && custom_field_has_write_access( $t_id, $t_bug_id ) ) {
		$t_custom_fields_found = true;

		$t_required_class = $t_def['require_update'] ? ' class="required" ' : '';

		if( $t_def['type'] != CUSTOM_FIELD_TYPE_RADIO && $t_def['type'] != CUSTOM_FIELD_TYPE_CHECKBOX ) {
			$t_label_for = ' for="custom_field_' . string_attribute( $t_def['id'] ) . '" ';
		} else {
			$t_label_for = '';
		}

		echo '<tr>';
		echo '<td class="category">';
		echo '<label', $t_required_class, $t_label_for, '>';
		echo '<span>', string_display_line( lang_get_defaulted( $t_def['name'] ) ), '</span>';
		echo '</label>';
		echo '</td><td colspan="5">';
		print_custom_field_input( $t_def, $t_bug_id, $t_def['require_update'] );
		echo '</td></tr>';
	}
} # foreach( $t_related_custom_field_ids as $t_id )

if( $t_custom_fields_found ) {
	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

# Bugnote Text Box
$t_default_bugnote_view_status = config_get( 'default_bugnote_view_status' );
$t_bugnote_private = $t_default_bugnote_view_status == VS_PRIVATE;
$t_bugnote_class = $t_bugnote_private ? 'form-control bugnote-private' : 'form-control';

echo '<tr>';
echo '<th class="category"><label for="bugnote_text">' . lang_get( 'add_bugnote_title' ) . '</label></th>';
echo '<td colspan="5"><textarea ', helper_get_tab_index(), ' id="bugnote_text" name="bugnote_text" class="', $t_bugnote_class, '" cols="80" rows="7"></textarea></td></tr>';

# Bugnote Private Checkbox (if permitted)
if( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $t_bug_id ) ) {
	echo '<tr>';
	echo '<th class="category">' . lang_get( 'private' ) . '</th>';
	echo '<td colspan="5">';

	if( access_has_bug_level( config_get( 'set_view_status_threshold' ), $t_bug_id ) ) {
		echo '<label>';
		echo '<input ', helper_get_tab_index(), ' type="checkbox" class="ace" id="private" name="private" ', check_checked( config_get( 'default_bugnote_view_status' ), VS_PRIVATE ), ' />';
		echo '<span class="lbl"></span>';
		echo '</label>';
	} else {
		echo get_enum_element( 'view_state', $t_default_bugnote_view_status );
	}

	echo '</td></tr>';
}

# Time Tracking (if permitted)
if( config_get( 'time_tracking_enabled' ) ) {
	if( access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $t_bug_id ) ) {
		echo '<tr>';
		echo '<th class="category"><label for="time_tracking">' . lang_get( 'time_tracking' ) . '</label></th>';
		echo '<td colspan="5"><input type="text" id="time_tracking" name="time_tracking" class="input-sm" size="5" placeholder="hh:mm" /></td></tr>';
	}
}

event_signal( 'EVENT_BUGNOTE_ADD_FORM', array( $t_bug_id ) );

echo '</table>';
echo '</div>';
echo '</div>';
echo '</div>';

# Submit Button
if( $t_bottom_buttons_enabled ) {
?>
	<div class="widget-toolbox padding-8 clearfix">
		<input <?php helper_get_tab_index(); ?>
			type="submit" class="btn btn-primary btn-white btn-round"
			value="<?php echo lang_get( 'update_information_button' ); ?>" />
	</div>
<?php
}
?>

</div>
</form>
</div>
</div>

<?php
define( 'BUGNOTE_VIEW_INC_ALLOW', true );
include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'bugnote_view_inc.php' );
layout_page_end();

last_visited_issue( $t_bug_id );
