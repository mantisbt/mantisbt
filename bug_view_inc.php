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

$t_cmd = new IssueViewPageCommand( $t_data );
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

$t_top_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_TOP || $t_action_button_position == POSITION_BOTH );
$t_bottom_buttons_enabled = !$t_force_readonly && ( $t_action_button_position == POSITION_BOTTOM || $t_action_button_position == POSITION_BOTH );

#
# Start of Template
#

echo '<div class="col-md-12 col-xs-12">';
echo '<div class="widget-box widget-color-blue2">';
echo '<div class="widget-header widget-header-small">';
echo '<h4 class="widget-title lighter">';
print_icon( 'fa-bars', 'ace-icon' );
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

if( isset( $t_issue_view['wiki_link'] ) ) {
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
if( $t_flags['history_show'] ) {
	if( $f_history ) {
		$t_history_link = '#history';
		$t_history_label = lang_get( 'jump_to_history' );
	} else {
		$t_history_link = 'view.php?id=' . $f_issue_id . '&history=1#history';
		$t_history_label = lang_get( 'display_history' );
	}
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
	bug_view_action_buttons( $f_issue_id, $t_flags );
	echo '</td>';
	echo '</tr>';
	echo '</thead>';
}

if( $t_bottom_buttons_enabled ) {
	echo '<tfoot>';
	echo '<tr class="noprint"><td colspan="6">';
	bug_view_action_buttons( $f_issue_id, $t_flags );
	echo '</td></tr>';
	echo '</tfoot>';
}

echo '<tbody>';

if( $t_flags['id_show'] || $t_flags['project_show'] || $t_flags['category_show'] ||
    $t_flags['view_state_show'] || $t_flags['created_at_show'] || $t_flags['updated_at_show']
) {

	# Labels
	echo '<tr class="bug-header">';
	echo '<th class="bug-id category" width="15%">', $t_flags['id_show'] ? lang_get( 'id' ) : '', '</th>';
	echo '<th class="bug-project category" width="20%">', $t_flags['project_show'] ? lang_get( 'email_project' ) : '', '</th>';
	echo '<th class="bug-category category" width="15%">', $t_flags['category_show'] ? lang_get( 'category' ) : '', '</th>';
	echo '<th class="bug-view-status category" width="15%">', $t_flags['view_state_show'] ? lang_get( 'view_status' ) : '', '</th>';
	echo '<th class="bug-date-submitted category" width="15%">', $t_flags['created_at_show'] ? lang_get( 'date_submitted' ) : '', '</th>';
	echo '<th class="bug-last-modified category" width="20%">', $t_flags['updated_at_show'] ? lang_get( 'last_update' ) : '','</th>';
	echo '</tr>';

	echo '<tr class="bug-header-data">';

	# Bug ID
	echo '<td class="bug-id">', $t_flags['id_show'] ? $t_issue_view['id_formatted'] : '', '</td>';

	# Project
	echo '<td class="bug-project">', $t_flags['project_show'] && isset( $t_issue['project']['name'] ) ? string_display_line( $t_issue['project']['name'] ) : '', '</td>';

	# Category
	echo '<td class="bug-category">',
		$t_flags['category_show'] && isset( $t_issue['category']['name'] )
			? string_display_line( $t_issue['category']['name'] )
			: '',
		'</td>';

	# View Status
	echo '<td class="bug-view-status">', $t_flags['view_state_show'] && isset( $t_issue['view_state']['label'] ) ? string_display_line( $t_issue['view_state']['label'] ) : '', '</td>';

	# Date Submitted
	echo '<td class="bug-date-submitted">', $t_flags['created_at_show'] ? $t_issue_view['created_at'] : '', '</td>';

	# Date Updated
	echo '<td class="bug-last-modified">',  $t_flags['updated_at_show'] ? $t_issue_view['updated_at'] : '', '</td>';

	echo '</tr>';

	# spacer
	echo '<tr class="spacer"><td colspan="6"></td></tr>';
	echo '<tr class="hidden"></tr>';
}

#
# Reporter, Handler, Due Date
#

if( $t_flags['reporter_show'] || $t_flags['handler_show'] || $t_flags['due_date_show'] ) {
	echo '<tr>';

	$t_spacer = 0;

	# Reporter
	if( $t_flags['reporter_show'] ) {
		echo '<th class="bug-reporter category">', lang_get( 'reporter' ), '</th>';
		echo '<td class="bug-reporter">';
		print_user_with_subject( $t_issue['reporter']['id'], $f_issue_id );
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# Handler
	if( $t_flags['handler_show'] ) {
		echo '<th class="bug-assigned-to category">', lang_get( 'assigned_to' ), '</th>';
		echo '<td class="bug-assigned-to">';
		if( isset( $t_issue['handler'] ) ) {
			print_user_with_subject( $t_issue['handler']['id'], $f_issue_id );
		}
		echo '</td>';
	} else {
		$t_spacer += 2;
	}

	# Due Date
	if( $t_flags['due_date_show'] ) {
		echo '<th class="bug-due-date category">', lang_get( 'due_date' ), '</th>';

		$t_css = 'bug-due-date';
		if( $t_issue_view['overdue'] !== false ) {
			$t_css .= ' due-' . $t_issue_view['overdue'];
		}
		echo '<td class="' . $t_css . '">', $t_issue_view['due_date'], '</td>';
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

if( $t_flags['priority_show'] || $t_flags['severity_show'] || $t_flags['reproducibility_show'] ) {
	echo '<tr>';

	$t_spacer = 0;

	# Priority
	if( $t_flags['priority_show'] ) {
		echo '<th class="bug-priority category">', lang_get( 'priority' ), '</th>';
		echo '<td class="bug-priority">', string_display_line( $t_issue['priority']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Severity
	if( $t_flags['severity_show'] ) {
		echo '<th class="bug-severity category">', lang_get( 'severity' ), '</th>';
		echo '<td class="bug-severity">', string_display_line( $t_issue['severity']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Reproducibility
	if( $t_flags['reproducibility_show'] ) {
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

if( $t_flags['status_show'] || $t_flags['resolution_show'] ) {
	echo '<tr>';

	$t_spacer = 2;

	# Status
	if( $t_flags['status_show'] ) {
		echo '<th class="bug-status category">', lang_get( 'status' ), '</th>';

		# choose color based on status
		$t_status_css = html_get_status_css_fg( $t_issue['status']['id'] );

		echo '<td class="bug-status">';
		print_icon( 'fa-square', 'fa-status-box ' . $t_status_css );
		echo ' ' . string_display_line( $t_issue['status']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# Resolution
	if( $t_flags['resolution_show'] ) {
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

if( $t_flags['projection_show'] || $t_flags['eta_show'] ) {
	echo '<tr>';

	$t_spacer = 2;

	if( $t_flags['projection_show'] ) {
		# Projection
		echo '<th class="bug-projection category">', lang_get( 'projection' ), '</th>';
		echo '<td class="bug-projection">', string_display_line( $t_issue['projection']['label'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# ETA
	if( $t_flags['eta_show'] ) {
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
    ( $t_flags['profiles_os_build_show'] && isset( $t_issue['os_build'] ) && !is_blank( $t_issue['os_build'] ) ) ) {
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
	if( $t_flags['profiles_os_build_show'] && isset( $t_issue['os_build'] ) && !is_blank( $t_issue['os_build'] ) ) {
		echo '<th class="bug-os-build category">', lang_get( 'os_build' ), '</th>';
		echo '<td class="bug-os-build">', string_display_line( $t_issue['os_build'] ), '</td>';
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
    ( $t_flags['versions_product_build_show'] && isset( $t_issue['build'] ) ) ) {
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
	if( $t_flags['versions_product_build_show'] && isset( $t_issue['build'] ) ) {
		echo '<th class="bug-product-build category">', lang_get( 'product_build' ), '</th>';
		echo '<td class="bug-product-build">', string_display_line( $t_issue['build'] ), '</td>';
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
		echo '<td class="bug-target-version">', string_display_line( $t_issue['target_version']['name'] ), '</td>';
	} else {
		$t_spacer += 2;
	}

	# fixed in version
	if( $t_flags['versions_fixed_in_version_show'] && isset( $t_issue['fixed_in_version'] ) ) {
		echo '<th class="bug-fixed-in-version category">', lang_get( 'fixed_in_version' ), '</th>';
		echo '<td class="bug-fixed-in-version">', string_display_line( $t_issue['fixed_in_version']['name'] ), '</td>';
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
	echo '<td class="bug-summary" colspan="5">', string_display_line( bug_format_id( $f_issue_id ) . ': ' . $t_issue['summary'] ), '</td>';
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
	bug_view_relationship_view_box( $f_issue_id, /* can_update */ $t_flags['relationships_can_update'] );
}

# User list monitoring the bug
if( $t_flags['monitor_show'] ) {
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
				<?php print_icon( 'fa-users', 'ace-icon' ); ?>
				<?php echo lang_get( 'users_monitoring_bug' ) ?>
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<?php print_icon( $t_block_icon, '1 ace-icon bigger-125' ); ?>
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
			if( !isset( $t_issue['monitors'] ) || count( $t_issue['monitors'] ) == 0 ) {
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
						echo ' <a class="btn btn-xs btn-primary btn-white btn-round" '
							. 'href="' . helper_mantis_url( 'bug_monitor_delete.php' )
							. '?bug_id=' . $f_issue_id . '&amp;user_id=' . $t_monitor_user['id']
							. htmlspecialchars(form_security_param( 'bug_monitor_delete' ))
							. '">'
							. icon_get( 'fa-times' )
							. '</a>';
					}
				 }
			}
	
			if( $t_flags['monitor_can_add'] ) {
	?>
			<br /><br />
			<form method="get" action="bug_monitor_add.php" class="form-inline noprint">
			<?php echo form_security_field( 'bug_monitor_add' ) ?>
				<input type="hidden" name="bug_id" value="<?php echo (integer)$f_issue_id; ?>" />
				<input type="text" class="input-sm" id="bug_monitor_list_user_to_add" name="user_to_add" />
				<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'add' ) ?>" />
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
if( $t_flags['history_show'] && $f_history ) {
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
			<?php print_icon( 'fa-history', 'ace-icon' ); ?>
			<?php echo lang_get( 'bug_history' ) ?>
		</h4>
		<div class="widget-toolbar">
			<a data-action="collapse" href="#">
				<?php print_icon( $t_block_icon, '1 ace-icon bigger-125' ); ?>
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

/**
 * return formatted string with all the details on the requested relationship
 * @param integer             $p_bug_id       A bug identifier.
 * @param BugRelationshipData $p_relationship A bug relationship object.
 * @param boolean             $p_html_preview Whether to include style/hyperlinks - if preview is false, we prettify the output.
 * @param boolean             $p_show_project Show Project details.
 * @return string
 */
function bug_view_relationship_get_details( $p_bug_id, BugRelationshipData $p_relationship, $p_html_preview = false, $p_show_project = false ) {
	if( $p_bug_id == $p_relationship->src_bug_id ) {
		# root bug is in the source side, related bug in the destination side
		$t_related_project_id = $p_relationship->dest_bug_id;
		$t_related_project_name = project_get_name( $p_relationship->dest_project_id );
		$t_related_bug_id = $p_relationship->dest_bug_id;
		$t_relationship_descr = relationship_get_description_src_side( $p_relationship->type );
	} else {
		# root bug is in the dest side, related bug in the source side
		$t_related_project_id = $p_relationship->src_bug_id;
		$t_related_bug_id = $p_relationship->src_bug_id;
		$t_related_project_name = project_get_name( $p_relationship->src_project_id );
		$t_relationship_descr = relationship_get_description_dest_side( $p_relationship->type );
	}

	# related bug not existing...
	if( !bug_exists( $t_related_bug_id ) ) {
		return '';
	}

	# user can access to the related bug at least as a viewer
	if( !access_has_bug_level( config_get( 'view_bug_threshold', null, null, $t_related_project_id ), $t_related_bug_id ) ) {
		return '';
	}

	if( $p_html_preview == false ) {
		$t_td = '<td>';
	} else {
		$t_td = '<td class="print">';
	}

	# get the information from the related bug and prepare the link
	$t_bug = bug_get( $t_related_bug_id, false );
	$t_status_string = get_enum_element( 'status', $t_bug->status, auth_get_current_user_id(), $t_bug->project_id );
	$t_resolution_string = get_enum_element( 'resolution', $t_bug->resolution, auth_get_current_user_id(), $t_bug->project_id );

	$t_relationship_info_html = $t_td . string_no_break( $t_relationship_descr ) . '&#160;</td>';
	if( $p_html_preview == false ) {
		# choose color based on status
		$t_status_css = html_get_status_css_fg( $t_bug->status, auth_get_current_user_id(), $t_bug->project_id );
		$t_relationship_info_html .= '<td><a href="' . string_get_bug_view_url( $t_related_bug_id ) . '">' . string_display_line( bug_format_id( $t_related_bug_id ) ) . '</a></td>';
		$t_relationship_info_html .= '<td>' . icon_get( 'fa-square', 'fa-status-box ' . $t_status_css );
		$t_relationship_info_html .= ' <span class="issue-status" title="' . string_attribute( $t_resolution_string ) . '">' . string_display_line( $t_status_string ) . '</span></td>';
	} else {
		$t_relationship_info_html .= $t_td . string_display_line( bug_format_id( $t_related_bug_id ) ) . '</td>';
		$t_relationship_info_html .= $t_td . string_display_line( $t_status_string ) . '&#160;</td>';
	}

	# get the handler name of the related bug
	$t_relationship_info_html .= $t_td;
	if( $t_bug->handler_id > 0 ) {
		$t_relationship_info_html .= string_no_break( prepare_user_name( $t_bug->handler_id ) );
	}

	$t_relationship_info_html .= '&#160;</td>';

	# add project name
	if( $p_show_project ) {
		$t_relationship_info_html .= $t_td . string_display_line( $t_related_project_name ) . '&#160;</td>';
	}

	# add summary
	$t_relationship_info_html .= $t_td . string_display_line_links( $t_bug->summary );
	if( VS_PRIVATE == $t_bug->view_state ) {
		$t_relationship_info_html .= icon_get( 'fa-lock', '', lang_get( 'private' ) );
	}

	# add delete link if bug not read only and user has access level
	if( !bug_is_readonly( $p_bug_id ) && !current_user_is_anonymous() && ( $p_html_preview == false ) ) {
		if( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
			$t_relationship_info_html .= ' <a class="red noprint zoom-130"'
				. 'href="bug_relationship_delete.php?bug_id=' . $p_bug_id
				. '&amp;rel_id=' . $p_relationship->id
				. htmlspecialchars( form_security_param( 'bug_relationship_delete' ) )
				. '">'
				. icon_get( 'fa-trash-o', 'ace-icon bigger-115' )
				. '</a>';
		}
	}

	$t_relationship_info_html .= '&#160;</td>';
	$t_relationship_info_html = '<tr>' . $t_relationship_info_html . '</tr>';

	return $t_relationship_info_html;
}

/**
 * print ALL the RELATIONSHIPS OF A SPECIFIC BUG
 * @param integer $p_bug_id A bug identifier.
 * @return string
 */
function bug_view_relationship_get_summary_html( $p_bug_id ) {
	$t_summary = '';

	# A variable that will be set by the following call to indicate if relationships belong
	# to multiple projects.
	$t_show_project = false;

	$t_relationship_all = relationship_get_all( $p_bug_id, $t_show_project );
	$t_relationship_all_count = count( $t_relationship_all );

	# prepare the relationships table
	for( $i = 0; $i < $t_relationship_all_count; $i++ ) {
		$t_summary .= bug_view_relationship_get_details( $p_bug_id, $t_relationship_all[$i], /* html_preview */ false, $t_show_project );
	}

	if( !is_blank( $t_summary ) ) {
		if( relationship_can_resolve_bug( $p_bug_id ) == false ) {
			$t_summary .= '<tr><td colspan="' . ( 5 + $t_show_project ) . '"><strong>' .
				lang_get( 'relationship_warning_blocking_bugs_not_resolved' ) . '</strong></td></tr>';
		}
		$t_summary = '<table class="table table-bordered table-condensed table-hover">' . $t_summary . '</table>';
	}

	return $t_summary;
}

/**
 * print HTML relationship form
 * @param integer $p_bug_id A bug identifier.
 * @param bool $p_can_update Can update relationships?
 * @return void
 */
function bug_view_relationship_view_box( $p_bug_id, $p_can_update ) {
	$t_relationships_html = bug_view_relationship_get_summary_html( $p_bug_id );

	if( !$p_can_update && empty( $t_relationships_html ) ) {
		return;
	}

	$t_relationship_graph = ON == config_get( 'relationship_graph_enable' );
	$t_event_buttons = event_signal( 'EVENT_MENU_ISSUE_RELATIONSHIP', $p_bug_id );
	$t_show_top_div = $p_can_update || $t_relationship_graph || !empty( $t_event_buttons );
?>
	<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
<?php
	$t_collapse_block = is_collapsed( 'relationships' );
	$t_block_css = $t_collapse_block ? 'collapsed' : '';
	$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
?>
	<div id="relationships" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php print_icon( 'fa-sitemap', 'ace-icon' ); ?>
			<?php echo lang_get( 'bug_relationships' ) ?>
		</h4>
		<div class="widget-toolbar">
			<a data-action="collapse" href="#">
				<?php print_icon( $t_block_icon, '1 ace-icon bigger-125' ); ?>
			</a>
		</div>
	</div>
	<div class="widget-body">
<?php
	if( $t_show_top_div ) {
?>
		<div class="widget-toolbox padding-8 clearfix">
<?php
		# Default relationship buttons
		$t_buttons = array();
		if( $t_relationship_graph ) {
			$t_buttons[lang_get( 'relation_graph' )] =
				'bug_relationship_graph.php?bug_id=' . $p_bug_id . '&graph=relation';
			$t_buttons[lang_get( 'dependency_graph' )] =
				'bug_relationship_graph.php?bug_id=' . $p_bug_id . '&graph=dependency';
		}

		# Plugin-added buttons
		foreach( $t_event_buttons as $t_plugin => $t_plugin_buttons ) {
			foreach( $t_plugin_buttons as $t_callback => $t_callback_buttons ) {
				if( is_array( $t_callback_buttons ) ) {
					$t_buttons = array_merge( $t_buttons, $t_callback_buttons );
				}
			}
		}
?>
		<div class="btn-group pull-right noprint">
<?php
		# Print the buttons, if any
		foreach( $t_buttons as $t_label => $t_url ) {
			print_small_button( $t_url, $t_label );
		}
?>
		</div>

<?php
		if( $p_can_update ) {
?>
		<form method="post" action="bug_relationship_add.php" class="form-inline noprint">
		<?php echo form_security_field( 'bug_relationship_add' ) ?>
		<input type="hidden" name="src_bug_id" value="<?php echo $p_bug_id?>" />
		<label class="inline"><?php echo lang_get( 'this_bug' ) ?>&#160;&#160;</label>
		<?php print_relationship_list_box( config_get( 'default_bug_relationship' ) )?>
		<input type="text" class="input-sm" name="dest_bug_id" value="" />
		<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" name="add_relationship" value="<?php echo lang_get( 'add' )?>" />
		</form>
<?php
		} # can update
?>
		</div>
<?php
	} # show top div
?>

		<div class="widget-main no-padding">
			<div class="table-responsive">
				<?php echo $t_relationships_html; ?>
			</div>
		</div>
	</div>
	</div>
	</div>
<?php
}

/**
 * Print Change Status to: button
 * This code is similar to print_status_option_list except
 * there is no masking, except for the current state
 *
 * @param BugData $p_bug A valid bug object.
 * @return void
 */
function bug_view_button_bug_change_status( BugData $p_bug ) {
	$t_current_access = access_get_project_level( $p_bug->project_id );

	$t_enum_list = get_status_option_list(
		$t_current_access,
		$p_bug->status,
		false,
		# Add close if user is bug's reporter, still has rights to report issues
		# (to prevent users downgraded to viewers from updating issues) and
		# reporters are allowed to close their own issues
		(  bug_is_user_reporter( $p_bug->id, auth_get_current_user_id() )
		&& access_has_bug_level( config_get( 'report_bug_threshold' ), $p_bug->id )
		&& ON == config_get( 'allow_reporter_close' )
		),
		$p_bug->project_id );

	if( count( $t_enum_list ) > 0 ) {
		# resort the list into ascending order after noting the key from the first element (the default)
		$t_default = key( $t_enum_list );
		ksort( $t_enum_list );

		echo '<form method="post" action="bug_change_status_page.php" class="form-inline">';
		# CSRF protection not required here - form does not result in modifications

		$t_button_text = lang_get( 'bug_status_to_button' );
		echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="' . $t_button_text . '" />';

		echo ' <select name="new_status" class="input-sm">';

		# space at beginning of line is important
		foreach( $t_enum_list as $t_key => $t_val ) {
			echo '<option value="' . $t_key . '" ';
			check_selected( $t_key, $t_default );
			echo '>' . $t_val . '</option>';
		}
		echo '</select>';

		$t_bug_id = string_attribute( $p_bug->id );
		echo '<input type="hidden" name="id" value="' . $t_bug_id . '" />' . "\n";
		echo '<input type="hidden" name="change_type" value="' . BUG_UPDATE_TYPE_CHANGE_STATUS . '" />' . "\n";

		echo '</form>' . "\n";
	}
}

/**
 * Print Assign To: combo box of possible handlers
 * @param BugData $p_bug Bug object.
 * @return void
 */
function bug_view_button_bug_assign_to( BugData $p_bug ) {
	$t_current_user_id = auth_get_current_user_id();
	$t_options = array();
	$t_default_assign_to = null;

	if( ( $p_bug->handler_id != $t_current_user_id )
		&& access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug->id, $t_current_user_id )
	) {
		$t_options[] = array(
			$t_current_user_id,
			'[' . lang_get( 'myself' ) . ']',
		);
		$t_default_assign_to = $t_current_user_id;
	}

	if( ( $p_bug->handler_id != $p_bug->reporter_id )
		&& user_exists( $p_bug->reporter_id )
		&& access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug->id, $p_bug->reporter_id )
	) {
		$t_options[] = array(
			$p_bug->reporter_id,
			'[' . lang_get( 'reporter' ) . ']',
		);

		if( $t_default_assign_to === null ) {
			$t_default_assign_to = $p_bug->reporter_id;
		}
	}

	echo '<form method="post" action="bug_update.php" class="form-inline">';
	echo form_security_field( 'bug_update' );
	echo '<input type="hidden" name="last_updated" value="' . $p_bug->last_updated . '" />';
	echo '<input type="hidden" name="action_type" value="' . BUG_UPDATE_TYPE_ASSIGN . '" />';

	$t_button_text = lang_get( 'bug_assign_to_button' );
	echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="' . $t_button_text . '" />';

	echo ' <select class="input-sm" name="handler_id">';

	# space at beginning of line is important

	$t_already_selected = false;

	foreach( $t_options as $t_entry ) {
		$t_id = (int)$t_entry[0];
		$t_caption = string_attribute( $t_entry[1] );

		# if current user and reporter can't be selected, then select the first
		# user in the list.
		if( $t_default_assign_to === null ) {
			$t_default_assign_to = $t_id;
		}

		echo '<option value="' . $t_id . '" ';

		if( ( $t_id == $t_default_assign_to ) && !$t_already_selected ) {
			check_selected( $t_id, $t_default_assign_to );
			$t_already_selected = true;
		}

		echo '>' . $t_caption . '</option>';
	}

	# allow un-assigning if already assigned.
	if( $p_bug->handler_id != 0 ) {
		echo '<option value="0"></option>';
	}

	# 0 means currently selected
	print_assign_to_option_list( 0, $p_bug->project_id );
	echo '</select>';

	$t_bug_id = string_attribute( $p_bug->id );
	echo '<input type="hidden" name="bug_id" value="' . $t_bug_id . '" />' . "\n";

	echo '</form>' . "\n";
}

/**
 * Print all buttons for view bug pages
 * @param integer $p_bug_id A valid bug identifier.
 * @param array $p_flags Flags from issue view command
 * @return void
 */
function bug_view_action_buttons( $p_bug_id, $p_flags ) {
	$t_bug = bug_get( $p_bug_id );

	echo '<div class="btn-group">';
	# UPDATE button
	if( $p_flags['can_update'] ) {
		echo '<div class="pull-left padding-right-8">';
		html_button( string_get_bug_update_page(), lang_get( 'edit' ), array( 'bug_id' => $p_bug_id ) );
		echo '</div>';
	}

	# ASSIGN button
	if( $p_flags['can_assign'] ) {
		echo '<div class="pull-left padding-right-8">';
		bug_view_button_bug_assign_to( $t_bug );
		echo '</div>';
	}

	# Change status button/dropdown
	if( $p_flags['can_change_status'] ) {
		echo '<div class="pull-left padding-right-8">';
		bug_view_button_bug_change_status( $t_bug );
		echo '</div>';
	}

	# Unmonitor
	if( $p_flags['can_unmonitor'] ) {
		echo '<div class="pull-left padding-right-2">';
		html_button( 'bug_monitor_delete.php', lang_get( 'unmonitor_bug_button' ), array( 'bug_id' => $p_bug_id ) );
		echo '</div>';
	}

	# Monitor
	if( $p_flags['can_monitor'] ) {
		echo '<div class="pull-left padding-right-2">';
		html_button( 'bug_monitor_add.php', lang_get( 'monitor_bug_button' ), array( 'bug_id' => $p_bug_id ) );
		echo '</div>';
	}

	# Stick
	if( $p_flags['can_sticky'] ) {
		echo '<div class="pull-left padding-right-2">';
		html_button( 'bug_stick.php', lang_get( 'stick_bug_button' ), array( 'bug_id' => $p_bug_id, 'action' => 'stick' ) );
		echo '</div>';
	}

	# Unstick
	if( $p_flags['can_unsticky'] ) {
		echo '<div class="pull-left padding-right-2">';
		html_button( 'bug_stick.php', lang_get( 'unstick_bug_button' ), array( 'bug_id' => $p_bug_id, 'action' => 'unstick' ) );
		echo '</div>';
	}

	# CLONE button
	if( $p_flags['can_clone'] ) {
		echo '<div class="pull-left padding-right-2">';
		html_button( string_get_bug_report_url(), lang_get( 'create_child_bug_button' ), array( 'm_id' => $p_bug_id ) );
		echo '</div>';
	}

	# REOPEN button
	if( $p_flags['can_reopen'] ) {
		echo '<div class="pull-left padding-right-2">';
		$t_reopen_status = config_get( 'bug_reopen_status', null, null, $t_bug->project_id );
		html_button(
			'bug_change_status_page.php',
			lang_get( 'reopen_bug_button' ),
			array( 'id' => $t_bug->id, 'new_status' => $t_reopen_status, 'change_type' => BUG_UPDATE_TYPE_REOPEN ) );
		echo '</div>';
	}

	# CLOSE button
	if( $p_flags['can_close'] ) {
		$t_closed_status = config_get( 'bug_closed_status_threshold', null, null, $t_bug->project_id );
		echo '<div class="pull-left padding-right-2">';
		html_button(
			'bug_change_status_page.php',
			lang_get( 'close' ),
			array( 'id' => $t_bug->id, 'new_status' => $t_closed_status, 'change_type' => BUG_UPDATE_TYPE_CLOSE ) );
		echo '</div>';
	}

	# MOVE button
	if( $p_flags['can_move'] ) {
		echo '<div class="pull-left padding-right-2">';
		html_button( 'bug_actiongroup_page.php', lang_get( 'move' ), array( 'bug_arr[]' => $p_bug_id, 'action' => 'MOVE' ) );
		echo '</div>';
	}

	# DELETE button
	if( $p_flags['can_delete'] ) {
		echo '<div class="pull-left padding-right-2">';
		html_button( 'bug_actiongroup_page.php', lang_get( 'delete' ), array( 'bug_arr[]' => $p_bug_id, 'action' => 'DELETE' ) );
		echo '</div>';
	}

	helper_call_custom_function( 'print_bug_view_page_custom_buttons', array( $p_bug_id ) );

	echo '</div>';
}

