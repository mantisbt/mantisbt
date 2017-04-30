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
 * Handling of Bug Status change
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 * @uses sponsorship_api.php
 * @uses version_api.php
 */

$g_allow_browser_cache = 1;

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'relationship_api.php' );
require_api( 'sponsorship_api.php' );
require_api( 'version_api.php' );

$f_bug_id = gpc_get_int( 'id' );
$t_bug = bug_get( $f_bug_id );

$t_file = __FILE__;
$t_mantis_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
$t_show_page_header = false;
$t_force_readonly = true;
$t_fields_config_option = 'bug_change_status_page_fields';

if( $t_bug->project_id != helper_get_current_project() ) {
	# in case the current project is not the same project of the bug we are viewing...
	# ... override the current project. This to avoid problems with categories and handlers lists etc.
	$g_project_override = $t_bug->project_id;
}

$f_new_status = gpc_get_int( 'new_status' );
$f_change_type = gpc_get_string( 'change_type', BUG_UPDATE_TYPE_CHANGE_STATUS );

$t_reopen = config_get( 'bug_reopen_status', null, null, $t_bug->project_id );
$t_resolved = config_get( 'bug_resolved_status_threshold', null, null, $t_bug->project_id );
$t_closed = config_get( 'bug_closed_status_threshold', null, null, $t_bug->project_id );
$t_resolution_fixed = config_get( 'bug_resolution_fixed_threshold', null, null, $t_bug->project_id );
$t_current_user_id = auth_get_current_user_id();

# Ensure user has proper access level before proceeding
if( $f_new_status == $t_reopen && $f_change_type == BUG_UPDATE_TYPE_REOPEN ) {
	access_ensure_can_reopen_bug( $t_bug, $t_current_user_id );
} else if( $f_new_status == $t_closed ) {
	access_ensure_can_close_bug( $t_bug, $t_current_user_id );
} else if( bug_is_readonly( $f_bug_id )
	|| !access_has_bug_level( access_get_status_threshold( $f_new_status, $t_bug->project_id ), $f_bug_id, $t_current_user_id ) ) {
	access_denied();
}

$t_can_update_due_date = access_has_bug_level( config_get( 'due_date_update_threshold' ), $f_bug_id );

# get new issue handler if set, otherwise default to original handler
$f_handler_id = gpc_get_int( 'handler_id', $t_bug->handler_id );

if( config_get( 'bug_assigned_status' ) == $f_new_status ) {
	$t_bug_sponsored = config_get( 'enable_sponsorship' )
		&& sponsorship_get_amount( sponsorship_get_all_ids( $f_bug_id ) ) > 0;
	if( $t_bug_sponsored && !access_has_bug_level( config_get( 'assign_sponsored_bugs_threshold' ), $f_bug_id ) ) {
		trigger_error( ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW, ERROR );
	}

	if( $f_handler_id != NO_USER ) {
		if( !access_has_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id, $f_handler_id ) ) {
			trigger_error( ERROR_HANDLER_ACCESS_TOO_LOW, ERROR );
		}

		if( $t_bug_sponsored && !access_has_bug_level( config_get( 'handle_sponsored_bugs_threshold' ), $f_bug_id, $f_handler_id ) ) {
			trigger_error( ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW, ERROR );
		}
	}
}

$t_status_label = str_replace( ' ', '_', MantisEnum::getLabel( config_get( 'status_enum_string' ), $f_new_status ) );

layout_page_header( bug_format_summary( $f_bug_id, SUMMARY_CAPTION ) );

layout_page_begin();
?>

<div class="col-md-12 col-xs-12">

	<div id="bug-change-status-div" class="form-container">
	<form id="bug-change-status-form" name="bug_change_status_form" method="post" action="bug_update.php">

	<fieldset>

	<?php echo form_security_field( 'bug_update' ) ?>
	<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php echo lang_get( $t_status_label . '_bug_title' ) ?>
		</h4>
	</div>

	<div class="widget-body">
	<div class="widget-main no-padding">

	<div class="table-responsive">
	<table class="table table-bordered table-condensed table-striped">
		<thead>
			<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
			<input type="hidden" name="status" value="<?php echo $f_new_status ?>" />
			<input type="hidden" name="last_updated" value="<?php echo $t_bug->last_updated ?>" />
			<?php
				if( $f_new_status >= $t_resolved ) {
					if( relationship_can_resolve_bug( $f_bug_id ) == false ) {
						echo '<tr><td colspan="2">' . lang_get( 'relationship_warning_blocking_bugs_not_resolved_2' ) . '</td></tr>';
					}
				}
			?>
		</thead>
		<tbody>
<?php
	$t_current_resolution = $t_bug->resolution;
	$t_bug_resolution_is_fixed = $t_current_resolution >= $t_resolution_fixed;

	if( $f_new_status >= $t_resolved && ( $f_new_status < $t_closed || !$t_bug_resolution_is_fixed ) ) {
?>
<!-- Resolution -->
			<tr>
				<th class="category">
					<?php echo lang_get( 'resolution' ) ?>
				</th>
				<td>
					<select name="resolution" class="input-sm">
			<?php
				$t_resolution = $t_bug_resolution_is_fixed ? $t_current_resolution : $t_resolution_fixed;

				$t_relationships = relationship_get_all_src( $f_bug_id );
				foreach( $t_relationships as $t_relationship ) {
					if( $t_relationship->type == BUG_DUPLICATE ) {
						$t_resolution = config_get( 'bug_duplicate_resolution' );
						break;
					}
				}

				print_enum_string_option_list( 'resolution', $t_resolution );
			?>
					</select>
				</td>
			</tr>
<?php
		if( $t_resolution != config_get( 'bug_duplicate_resolution' ) ) {
?>
<!-- Duplicate ID -->
			<tr>
				<th class="category">
					<?php echo lang_get( 'duplicate_id' ) ?>
				</th>
				<td>
					<input type="text" class="input-sm" name="duplicate_id" maxlength="10" />
				</td>
			</tr>

<?php
		}
	}

	if( access_has_bug_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ), $f_bug_id ) ) {
		$t_suggested_handler_id = $t_bug->handler_id;

		if( $t_suggested_handler_id == NO_USER && access_has_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id ) ) {
			$t_suggested_handler_id = $t_current_user_id;
		}

?>
<!-- Assigned To -->
			<tr>
				<th class="category">
					<?php echo lang_get( 'assigned_to' ) ?>
				</th>
				<td>
					<select name="handler_id" class="input-sm">
						<option value="0"></option>
						<?php print_assign_to_option_list( $t_suggested_handler_id, $t_bug->project_id ) ?>
					</select>
				</td>
			</tr>
<?php } ?>

<?php
	if( $t_can_update_due_date ) {
		$t_date_to_display = '';

		if( !date_is_null( $t_bug->due_date ) ) {
			$t_date_to_display = date( config_get( 'normal_date_format' ), $t_bug->due_date );
		}
?>
	<!-- Due date -->
	<tr>
		<th class="category">
			<?php echo lang_get( 'due_date' ) ?>
		</th>
		<td>
			<input type="text" id="due_date" name="due_date" class="datetimepicker input-sm" size="16" maxlength="16"
				data-picker-locale="<?php lang_get_current_datetime_locale() ?>"
				data-picker-format="<?php echo config_get( 'datetime_picker_format' ) ?>"
				<?php helper_get_tab_index() ?> value="<?php echo $t_date_to_display ?>" />
			<i class="fa fa-calendar fa-xlg datetimepicker"></i>
		</td>
	</tr>

<?php
	}
?>

	<!-- Custom Fields -->
<?php
	/**
	 * @todo thraxisp - I undid part of the change for #5068 for #5527
	 * We really need to say what fields are shown in which statusses. For now,
	 * this page will show required custom fields in update mode, or
	 * display or required fields on resolve or close
	 */
	$t_custom_status_label = 'update'; # Don't show custom fields by default
	if( ( $f_new_status >= $t_resolved ) && ( $f_new_status < $t_closed ) ) {
		$t_custom_status_label = 'resolved';
	}
	if( $t_closed == $f_new_status ) {
		$t_custom_status_label = 'closed';
	}

	$t_related_custom_field_ids = custom_field_get_linked_ids( $t_bug->project_id );

	foreach( $t_related_custom_field_ids as $t_id ) {
		$t_def = custom_field_get_definition( $t_id );
		$t_display = $t_def['display_' . $t_custom_status_label];
		$t_require = $t_def['require_' . $t_custom_status_label];

		if( ( 'update' == $t_custom_status_label ) && ( !$t_require ) ) {
			continue;
		}
		if( in_array( $t_custom_status_label, array( 'resolved', 'closed' ) ) && !( $t_display || $t_require ) ) {
			continue;
		}
		$t_has_write_access = custom_field_has_write_access( $t_id, $f_bug_id );
?>
	<tr>
		<th class="category">
			<?php if( $t_require && $t_has_write_access ) {?><span class="required">*</span><?php } ?>
			<?php echo lang_get_defaulted( $t_def['name'] ) ?>
		</th>
		<td>
<?php
			if( $t_has_write_access ) {
				print_custom_field_input( $t_def, $f_bug_id );
			} elseif( custom_field_has_read_access( $t_id, $f_bug_id ) ) {
				print_custom_field_value( $t_def, $t_id, $f_bug_id );
			}
?>
		</td>
	</tr>

<?php
	} # foreach( $t_related_custom_field_ids as $t_id )

	if( ( $f_new_status >= $t_resolved ) ) {
		if( version_should_show_product_version( $t_bug->project_id )
			&& !bug_is_readonly( $f_bug_id )
			&& access_has_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id )
		) {
?>
			<!-- Fixed in Version -->
			<tr>
				<th class="category">
					<?php echo lang_get( 'fixed_in_version' ) ?>
				</th>
				<td>
					<select name="fixed_in_version" class="input-sm">
						<?php print_version_option_list( $t_bug->fixed_in_version, $t_bug->project_id, VERSION_ALL ) ?>
					</select>
				</td>
			</tr>
<?php
		}
	}

	event_signal( 'EVENT_UPDATE_BUG_STATUS_FORM', array( $f_bug_id, $f_new_status ) );

	if( $f_change_type == BUG_UPDATE_TYPE_REOPEN ) {
?>
	<!-- Bug was re-opened -->
<?php
		printf( '	<input type="hidden" name="resolution" value="%s" />' . "\n", config_get( 'bug_reopen_resolution' ) );
	}
?>
<?php if( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) { ?>
			<tr>
				<th class="category">
					<?php echo lang_get( 'view_status' ) ?>
				</th>
				<td>
<?php
		$t_default_bugnote_view_status = config_get( 'default_bugnote_view_status' );
		if( access_has_bug_level( config_get( 'set_view_status_threshold' ), $f_bug_id ) ) {
?>
			<input type="checkbox" id="bugnote_add_view_status" class="ace" name="private"
				<?php check_checked( $t_default_bugnote_view_status, VS_PRIVATE ); ?> />
			<label class="lbl" for="bugnote_add_view_status"> <?php echo lang_get( 'private' ) ?> </label>
<?php
		} else {
			echo get_enum_element( 'project_view_state', $t_default_bugnote_view_status );
		}
?>
				</td>
			</tr>
<?php } ?>
			<!-- Bugnote -->
			<tr id="bug-change-status-note">
				<th class="category">
					<?php echo lang_get( 'add_bugnote_title' ) ?>
				</th>
				<td>
					<textarea class="form-control" name="bugnote_text" id="bugnote_text" cols="80" rows="10"></textarea>
				</td>
			</tr>
<?php
	if( config_get( 'time_tracking_enabled' )
		&& access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id )
		&& access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $f_bug_id )
	) {
	?>
			<tr>
				<th class="category">
					<?php echo lang_get( 'time_tracking' ) ?>
				</th>
				<td>
					<input type="text" name="time_tracking" class="input-sm" size="5" placeholder="hh:mm" />
				</td>
			</tr>

<?php
	}

	event_signal( 'EVENT_BUGNOTE_ADD_FORM', array( $f_bug_id ) );
?>

</tbody>
</table>
<input type="hidden" name="action_type" value="<?php echo string_attribute( $f_change_type ); ?>" />

</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( $t_status_label . '_bug_button' ) ?>" />
</div>
</div>
</div>
</div>
</form>
<div class="space-10"></div>
</div>
<?php
define( 'BUG_VIEW_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/bug_view_inc.php' );
