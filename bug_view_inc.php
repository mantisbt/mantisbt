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
 * This include file prints out the bug information
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses columns_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses event_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses prepare_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

if( !defined( 'BUG_VIEW_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'event_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'prepare_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

require_css( 'status_config.php' );

$f_issue_id = gpc_get_int( 'id' );
$f_history = gpc_get_bool( 'history', config_get( 'history_default_visible' ) );

# compat variables for included pages
$f_bug_id = $f_issue_id;
$t_bug = $t_bug = bug_get( $f_bug_id, true );
$t_data = array(
	'query' => array( 'id' => $f_issue_id ),
	'options' => array( 'force_readonly' => $t_force_readonly )
);

$t_cmd = new IssueViewCommand( $t_data );
$t_result = $t_cmd->execute();

$t_issue = $t_result['issue'];
$t_issue_view = $t_result['issue_view'];
$t_flags = $t_result['flags'];

compress_enable();

if( $t_show_page_header ) {
	layout_page_header( bug_format_summary( $f_issue_id, SUMMARY_CAPTION ), null, 'view-issue-page' );
	layout_page_begin( 'view_all_bug_page.php' );
}

$t_action_button_position = config_get( 'action_button_position' );

$t_bugslist = gpc_get_cookie( config_get_global( 'bug_list_cookie' ), false );

if( $t_flags['history_show'] ) {
	if( $f_history ) {
		$t_history_link = '#history';
		$t_history_label = lang_get( 'jump_to_history' );
	} else {
		$t_history_link = 'view.php?id=' . $f_issue_id . '&history=1#history';
		$t_history_label = lang_get( 'display_history' );
	}
} else {
	$t_history_link = '';
}

$t_top_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH );
$t_bottom_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH );

#
# Start of Template
#

echo '<div class="col-md-12 col-xs-12">';
echo '<div class="widget-box widget-color-blue2">';
echo '<div class="widget-header widget-header-small">';
echo '<h4 class="widget-title lighter">';
echo '<i class="ace-icon fa fa-bars"></i>';
echo string_display_line( $t_issue_view['form_title'] );
echo '</h4>';
echo '</div>';

echo '<div class="widget-body">';

echo '<div class="widget-toolbox padding-8 clearfix noprint">';
echo '<div class="btn-group pull-left">';

# Send Bug Reminder
if( $t_flags['reminder_can_add'] ) {
	print_small_button( 'bug_reminder_page.php?bug_id=' . $f_issue_id, lang_get( 'bug_reminder' ) );
}

if( !isset( $t_issue_view['wiki_link'] ) ) {
	print_small_button( $t_issue_view['wiki_link'], lang_get( 'wiki' ) );
}

# TODO: should be moved to command
foreach ( $t_issue_view['links'] as $t_plugin => $t_hooks ) {
	foreach( $t_hooks as $t_hook ) {
		if( is_array( $t_hook ) ) {
			foreach( $t_hook as $t_label => $t_href ) {
				if( is_numeric( $t_label ) ) {
					print_bracket_link_prepared( $t_href );
				} else {
					print_small_button( $t_href, $t_label );
				}
			}
		} elseif( !empty( $t_hook ) ) {
			print_bracket_link_prepared( $t_hook );
		}
	}
}

# Jump to Bugnotes
print_small_button( '#bugnotes', lang_get( 'jump_to_bugnotes' ) );

# Display or Jump to History
if( !is_blank( $t_history_link ) ) {
	print_small_button( $t_history_link, $t_history_label );
}

echo '</div>';

# prev/next links
echo '<div class="btn-group pull-right">';
if( $t_bugslist ) {
	$t_bugslist = explode( ',', $t_bugslist );
	$t_index = array_search( $f_issue_id, $t_bugslist );
	if( false !== $t_index ) {
		if( isset( $t_bugslist[$t_index-1] ) ) {
			print_small_button( 'view.php?id='.$t_bugslist[$t_index-1], '&lt;&lt;' );
		}

		if( isset( $t_bugslist[$t_index+1] ) ) {
			print_small_button( 'view.php?id='.$t_bugslist[$t_index+1], '&gt;&gt;' );
		}
	}
}
echo '</div>';
echo '</div>';

echo '<div class="widget-main no-padding">';
echo '<div class="table-responsive">';
echo '<table class="table table-bordered table-condensed">';

if( $t_top_buttons_enabled ) {
	echo '<thead><tr class="bug-nav">';
	echo '<tr class="top-buttons noprint">';
	echo '<td colspan="6">';
	html_buttons_view_bug_page( $f_issue_id );
	echo '</td>';
	echo '</tr>';
	echo '</thead>';
}

if( $t_bottom_buttons_enabled ) {
	echo '<tfoot>';
	echo '<tr class="noprint"><td colspan="6">';
	html_buttons_view_bug_page( $f_issue_id );
	echo '</td></tr>';
	echo '</tfoot>';
}

echo '<tbody>';

if( isset( $t_issue['id'] ) || isset( $t_issue['project'] ) || isset( $t_issue['category'] ) ||
    isset( $t_issue['view_state'] ) || isset( $t_issue_view['created_at'] ) || isset( $t_issue_view['updated_at'] ) ) {
	# Labels
	echo '<tr class="bug-header">';
	echo '<th class="bug-id category" width="15%">', isset( $t_issue['id'] ) ? lang_get( 'id' ) : '', '</th>';
	echo '<th class="bug-project category" width="20%">', isset( $t_issue['project'] ) && isset( $t_issue['project']['name'] ) ? lang_get( 'email_project' ) : '', '</th>';
	echo '<th class="bug-category category" width="15%">', isset( $t_issue['category'] ) && isset( $t_issue['category']['name'] ) ? lang_get( 'category' ) : '', '</th>';
	echo '<th class="bug-view-status category" width="15%">', isset( $t_issue['view_state'] ) ? lang_get( 'view_status' ) : '', '</th>';
	echo '<th class="bug-date-submitted category" width="15%">', isset( $t_issue_view['created_at'] ) ? lang_get( 'date_submitted' ) : '', '</th>';
	echo '<th class="bug-last-modified category" width="20%">', isset( $t_issue_view['updated_at'] ) ? lang_get( 'last_update' ) : '','</th>';
	echo '</tr>';

	echo '<tr class="bug-header-data">';

	# Bug ID
	echo '<td class="bug-id">', isset( $t_issue_view['id'] ) ? $t_issue_view['id_formatted'] : '', '</td>';

	# Project
	echo '<td class="bug-project">', isset( $t_issue['project'] ) && isset( $t_issue['project']['name'] ) ? string_display_line( $t_issue['project']['name'] ) : '', '</td>';

	# Category
	echo '<td class="bug-category">', isset( $t_issue['category'] ) ? string_display_line( $t_issue['category'] ) : '', '</td>';

	# View Status
	echo '<td class="bug-view-status">', isset( $t_issue['view_state'] ) && isset( $t_issue['view_state']['label'] ) ? string_display_line( $t_issue['view_state']['label'] ) : '', '</td>';

	# Date Submitted
	echo '<td class="bug-date-submitted">', isset( $t_issue_view['created_at'] ) ? $t_issue_view['created_at'] : '', '</td>';

	# Date Updated
	echo '<td class="bug-last-modified">',  isset( $t_issue_view['updated_at'] ) ? $t_issue_view['updated_at'] : '', '</td>';

	echo '</tr>';

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

#
# Reporter, Handler, Due Date
#

if( isset( $t_issue['reporter'] ) || isset( $t_issue['handler'] ) || isset( $t_issue_view['due_date'] ) ) {
	echo '<tr>';

	$t_spacer = 0;

	# Reporter
	if( isset( $t_issue['reporter'] ) ) {
		echo '<th class="bug-reporter category">', lang_get( 'reporter' ), '</th>';
		echo '<td class="bug-reporter">';
		print_user_with_subject( $t_issue['reporter']['id'], $f_issue_id );
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# Handler
	if( isset( $t_issue['handler'] ) ) {
		echo '<th class="bug-assigned-to category">', lang_get( 'assigned_to' ), '</th>';
		echo '<td class="bug-assigned-to">';
		print_user_with_subject( $t_issue['handler']['id'], $f_issue_id );
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# Due Date
	if( isset( $t_issue_view['due_date'] ) ) {
		echo '<th class="bug-due-date category">', lang_get( 'due_date' ), '</th>';

		if( $t_issue_view['overdue'] ) {
			echo '<td class="bug-due-date overdue">', $t_issue_view['due_date'], '</td>';
		} else {
			echo '<td class="bug-due-date">', $t_issue_view['due_date'], '</td>';
		}
	} else {
		$t_spacer += 2;
	}

	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Priority, Severity, Reproducibility
#

if( isset( $t_issue['priority'] ) || isset( $t_issue['severity'] ) || isset( $t_issue['reproducibility'] ) ) {
	echo '<tr>';

	$t_spacer = 0;

	# Priority
	if( isset( $t_issue['priority'] ) ) {
		echo '<th class="bug-priority category">', lang_get( 'priority' ), '</th>';
		echo '<td class="bug-priority">', string_display_line( $t_issue['priority']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Severity
	if( isset( $t_issue['severity'] ) ) {
		echo '<th class="bug-severity category">', lang_get( 'severity' ), '</th>';
		echo '<td class="bug-severity">', string_display_line( $t_issue['severity']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Reproducibility
	if( isset( $t_issue['reproducibility'] ) ) {
		echo '<th class="bug-reproducibility category">', lang_get( 'reproducibility' ), '</th>';
		echo '<td class="bug-reproducibility">', string_display_line( $t_issue['reproducibility']['label'] ), '</td>';
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

if( isset( $t_issue['status'] ) || isset( $t_issue['resolution'] ) ) {
	echo '<tr>';

	$t_spacer = 2;

	# Status
	if( isset( $t_issue['status'] ) ) {
		echo '<th class="bug-status category">', lang_get( 'status' ), '</th>';

		# choose color based on status
		$t_status_css = html_get_status_css_fg( $t_issue['status']['name'] );

		echo '<td class="bug-status">';
		echo '<i class="fa fa-square fa-status-box ' . $t_status_css . '"></i> ';
		echo string_display_line( $t_issue['status']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Resolution
	if( isset( $t_issue['resolution'] ) ) {
		echo '<th class="bug-resolution category">', lang_get( 'resolution' ), '</th>';
		echo '<td class="bug-resolution">', string_display_line( $t_issue['resolution']['label'] ), '</td>';
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

if( isset( $t_issue['projection'] ) || isset( $t_issue['eta'] ) ) {
	echo '<tr>';

	$t_spacer = 2;

	if( isset( $t_issue['projection'] ) ) {
		# Projection
		echo '<th class="bug-projection category">', lang_get( 'projection' ), '</th>';
		echo '<td class="bug-projection">', string_display_line( $t_issue['projection']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# ETA
	if( isset( $t_issue['eta'] ) ) {
		echo '<th class="bug-eta category">', lang_get( 'eta' ), '</th>';
		echo '<td class="bug-eta">', string_display_line( $t_issue['eta']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	echo '<td colspan="', $t_spacer, '">&#160;</td>';
	echo '</tr>';
}

#
# Platform, OS, OS Version
#

if( ( $t_flags['profiles_platform_show'] && isset( $t_issue['platform'] ) && !is_blank( $t_issue['platform'] ) ) ||
	( $t_flags['profiles_os_show'] && isset( $t_issue['os'] ) && !is_blank( $t_issue['os'] ) ) ||
    ( $t_flags['profiles_os_version_show'] && isset( $t_issue['os_version'] ) && !is_blank( $t_issue['os_version'] ) ) ) {
	$t_spacer = 0;

	echo '<tr>';

	# Platform
	if( $t_flags['profiles_platform_show'] && isset( $t_issue['platform'] ) && !is_blank( $t_issue['platform'] ) ) {
		echo '<th class="bug-platform category">', lang_get( 'platform' ), '</th>';
		echo '<td class="bug-platform">', string_display_line( $t_issue['platform'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Operating System
	if( $t_flags['profiles_os_show'] && isset( $t_issue['os'] ) && !is_blank( $t_issue['os'] ) ) {
		echo '<th class="bug-os category">', lang_get( 'os' ), '</th>';
		echo '<td class="bug-os">', string_display_line( $t_issue['os'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# OS Version
	if( $t_flags['profiles_os_version_show'] && isset( $t_issue['os_version'] ) && !is_blank( $t_issue['os_version'] ) ) {
		echo '<th class="bug-os-version category">', lang_get( 'os_version' ), '</th>';
		echo '<td class="bug-os-version">', string_display_line( $t_issue['os_version'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	if( $t_spacer > 0 ) {
		echo '<td colspan="', $t_spacer, '">&#160;</td>';
	}

	echo '</tr>';
}

#
# Product Version, Product Build
#

if( ( $t_flags['versions_product_version_show'] && isset( $t_issue['version'] ) ) ||
    ( $t_flags['versions_product_build_show'] && isset( $t_issue['product_build'] ) ) ) {
	$t_spacer = 2;

	echo '<tr>';

	# Product Version
	if( $t_flags['versions_product_version_show'] && isset( $t_issue['version'] ) ) {
		echo '<th class="bug-product-version category">', lang_get( 'product_version' ), '</th>';
		echo '<td class="bug-product-version">', string_display_line( $t_issue['version']['name'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Product Build
	if( $t_flags['versions_product_build_show'] && isset( $t_issue['product_build'] ) ) {
		echo '<th class="bug-product-build category">', lang_get( 'product_build' ), '</th>';
		echo '<td class="bug-product-build">', string_display_line( $t_issue['product_build'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Target Version, Fixed In Version
#

if( ( $t_flags['versions_target_version_show'] && isset( $t_issue['target_version'] ) ) ||
    ( $t_flags['versions_fixed_in_version_show'] && isset( $t_issue['fixed_in_version'] ) ) ) {
	$t_spacer = 2;

	echo '<tr>';

	# target version
	if( $t_flags['versions_target_version_show'] && isset( $t_issue['target_version'] ) ) {
		# Target Version
		echo '<th class="bug-target-version category">', lang_get( 'target_version' ), '</th>';
		echo '<td class="bug-target-version">', string_display_line( $t_issue['target_version'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# fixed in version
	if( $t_flags['versions_fixed_in_version_show'] && isset( $t_issue['fixed_in_version'] ) ) {
		echo '<th class="bug-fixed-in-version category">', lang_get( 'fixed_in_version' ), '</th>';
		echo '<td class="bug-fixed-in-version">', string_display_line( $t_issue['fixed_in_version'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# spacer
	echo '<td colspan="', $t_spacer, '">&#160;</td>';

	echo '</tr>';
}

#
# Bug Details Event Signal
#

event_signal( 'EVENT_VIEW_BUG_DETAILS', array( $f_issue_id ) );

# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

#
# Bug Details (screen wide fields)
#

# Summary
if( $t_flags['summary_show'] && isset( $t_issue['summary'] ) ) {
	echo '<tr>';
	echo '<th class="bug-summary category">', lang_get( 'summary' ), '</th>';
	echo '<td class="bug-summary" colspan="5">', string_display_line( $t_issue['summary'] ), '</td>';
	echo '</tr>';
}

# Description
if( $t_flags['description_show'] && isset( $t_issue['description'] ) ) {
	echo '<tr>';
	echo '<th class="bug-description category">', lang_get( 'description' ), '</th>';
	echo '<td class="bug-description" colspan="5">', string_display_links( $t_issue['description'] ), '</td>';
	echo '</tr>';
}

# Steps to Reproduce
if( $t_flags['steps_to_reproduce_show'] && isset( $t_issue['steps_to_reproduce'] ) ) {
	echo '<tr>';
	echo '<th class="bug-steps-to-reproduce category">', lang_get( 'steps_to_reproduce' ), '</th>';
	echo '<td class="bug-steps-to-reproduce" colspan="5">', string_display_links( $t_issue['steps_to_reproduce'] ), '</td>';
	echo '</tr>';
}

# Additional Information
if( $t_flags['additional_information_show'] && isset( $t_issue['additional_information'] ) ) {
	echo '<tr>';
	echo '<th class="bug-additional-information category">', lang_get( 'additional_information' ), '</th>';
	echo '<td class="bug-additional-information" colspan="5">', string_display_links( $t_issue['additional_information'] ), '</td>';
	echo '</tr>';
}

# Tagging
if( $t_flags['tags_show'] ) {
	echo '<tr>';
	echo '<th class="bug-tags category">', lang_get( 'tags' ), '</th>';
	echo '<td class="bug-tags" colspan="5">';
	tag_display_attached( $f_issue_id );
	echo '</td></tr>';
}

# Attach Tags
if( $t_flags['tags_can_attach'] ) {
	echo '<tr class="noprint">';
	echo '<th class="bug-attach-tags category">', lang_get( 'tag_attach_long' ), '</th>';
	echo '<td class="bug-attach-tags" colspan="5">';
	print_tag_attach_form( $f_issue_id );
	echo '</td></tr>';
}

# spacer
echo '<tr class="spacer"><td colspan="6"></td></tr>';
echo '<tr class="hidden"></tr>';

# Custom Fields
if( isset( $t_issue['custom_fields'] ) ) {
	foreach( $t_issue['custom_fields'] as $t_custom_field ) {
		$t_def = custom_field_get_definition( $t_custom_field['field']['id'] );

		echo '<tr>';
		echo '<th class="bug-custom-field category">', string_display_line( lang_get_defaulted( $t_def['name'] ) ), '</th>';
		echo '<td class="bug-custom-field" colspan="5">';
		print_custom_field_value( $t_def, $t_custom_field['field']['id'], $f_issue_id );
		echo '</td></tr>';
	}

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

echo '</tbody></table>';
echo '</div></div></div></div></div>';

# User list sponsoring the bug
if( $t_flags['sponsorships_show'] ) {
	define( 'BUG_SPONSORSHIP_LIST_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bug_sponsorship_list_view_inc.php' );
}

# Bug Relationships
if( $t_flags['relationships_show'] ) {
	relationship_view_box( $f_issue_id, /* can_update */ $t_flags['relationships_can_update'] );
}

# User list monitoring the bug
if( $t_flags['monitor_show'] && isset( $t_issue['monitors'] ) ) {
	$t_num_users = sizeof( $t_issue['monitors'] );

	echo '<div class="col-md-12 col-xs-12">';
	echo '<a id="monitors"></a>';
	echo '<div class="space-10"></div>';
	
	$t_collapse_block = is_collapsed( 'monitoring' );
	$t_block_css = $t_collapse_block ? 'collapsed' : '';
	$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
	?>
	<div id="monitoring" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-users"></i>
				<?php echo lang_get( 'users_monitoring_bug' ) ?>
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
				</a>
			</div>
		</div>
	
		<div class="widget-body">
			<div class="widget-main no-padding">
	
				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-striped">
	<tr>
		<th class="category" width="15%">
			<?php echo lang_get( 'monitoring_user_list' ); ?>
		</th>
		<td>
	<?php
			if( 0 == $t_num_users ) {
				echo lang_get( 'no_users_monitoring_bug' );
			} else {
				$t_first_user = true;
				foreach( $t_issue['monitors'] as $t_monitor_user ) {
					if( $t_first_user ) {
						$t_first_user = false;
					} else {
						echo ', ';
					}

					print_user( $t_monitor_user['id'] );
					if( $t_flags['monitor_can_delete'] ) {
						echo ' <a class="btn btn-xs btn-primary btn-white btn-round" href="' . helper_mantis_url( 'bug_monitor_delete.php' ) . '?bug_id=' . $f_issue_id . '&amp;user_id=' . $t_monitor_user['id'] . htmlspecialchars(form_security_param( 'bug_monitor_delete' )) . '"><i class="fa fa-times"></i></a>';
					}
				 }
			}
	
			if( $t_flags['monitor_can_add'] ) {
	?>
			<br /><br />
			<form method="get" action="bug_monitor_add.php" class="form-inline noprint">
			<?php echo form_security_field( 'bug_monitor_add' ) ?>
				<input type="hidden" name="bug_id" value="<?php echo (integer)$f_issue_id; ?>" />
				<label for="bug_monitor_list_username"><?php echo lang_get( 'username' ) ?></label>
				<input type="text" class="input-sm" id="bug_monitor_list_username" name="username" />
				<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'add_user_to_monitor' ) ?>" />
			</form>
			<?php } ?>
		</td>
	</tr>
	</table>
	</div>
	</div>
	</div>
	</div>
	</div>
	
	<?php
}

# Bugnotes and "Add Note" box
if( 'ASC' == current_user_get_pref( 'bugnote_order' ) ) {
	define( 'BUGNOTE_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );

	if( !$t_force_readonly ) {
		define( 'BUGNOTE_ADD_INC_ALLOW', true );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	}
} else {
	if( !$t_force_readonly ) {
		define( 'BUGNOTE_ADD_INC_ALLOW', true );
		include( $t_mantis_dir . 'bugnote_add_inc.php' );
	}

	define( 'BUGNOTE_VIEW_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_view_inc.php' );
}

# Allow plugins to display stuff after notes
event_signal( 'EVENT_VIEW_BUG_EXTRA', array( $f_issue_id ) );

# Time tracking statistics
if( config_get( 'time_tracking_enabled' ) &&
	access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $f_issue_id ) ) {
	define( 'BUGNOTE_STATS_INC_ALLOW', true );
	include( $t_mantis_dir . 'bugnote_stats_inc.php' );
}

# History
if( $t_flags['history_show'] ) {
?>
	<div class="col-md-12 col-xs-12">
		<a id="history"></a>
		<div class="space-10"></div>
	
	<?php
		$t_collapse_block = is_collapsed( 'history' );
		$t_block_css = $t_collapse_block ? 'collapsed' : '';
		$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
		$t_history = history_get_events_array( $f_issue_id );
	?>
	<div id="history" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-history"></i>
			<?php echo lang_get( 'bug_history' ) ?>
		</h4>
		<div class="widget-toolbar">
			<a data-action="collapse" href="#">
				<i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
			</a>
		</div>
	</div>
	<div class="widget-body">
	<div class="widget-main no-padding">
	<div class="table-responsive">
	<table class="table table-bordered table-condensed table-hover table-striped">
		<thead>
			<tr>
				<th class="small-caption">
					<?php echo lang_get( 'date_modified' ) ?>
				</th>
				<th class="small-caption">
					<?php echo lang_get( 'username' ) ?>
				</th>
				<th class="small-caption">
					<?php echo lang_get( 'field' ) ?>
				</th>
				<th class="small-caption">
					<?php echo lang_get( 'change' ) ?>
				</th>
			</tr>
		</thead>
	
		<tbody>
	<?php
		foreach( $t_history as $t_item ) {
	?>
			<tr>
				<td class="small-caption">
					<?php echo $t_item['date'] ?>
				</td>
				<td class="small-caption">
					<?php print_user( $t_item['userid'] ) ?>
				</td>
				<td class="small-caption">
					<?php echo string_display_line( $t_item['note'] ) ?>
				</td>
				<td class="small-caption">
					<?php echo ( $t_item['raw'] ? string_display_line_links( $t_item['change'] ) : $t_item['change'] ) ?>
				</td>
			</tr>
	<?php
		} # end for loop
	?>
		</tbody>
	</table>
	</div>
	</div>
	</div>
	</div>
	</div>
	
	<?php
}

layout_page_end();
