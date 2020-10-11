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
 * Account preferences include page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 */

if( !defined( 'ACCOUNT_PREFS_INC_ALLOW' ) ) {
	return;
}

require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );
require_api( 'user_pref_api.php' );
require_api( 'utility_api.php' );

/**
 * Display html form to edit account preferences
 *
 * @param integer $p_user_id            A valid user identifier.
 * @param boolean $p_error_if_protected Whether to error if the account is protected.
 * @param boolean $p_accounts_menu      Display account preferences menu.
 * @param string  $p_redirect_url       Redirect URI.
 * @return void
 */
function edit_account_prefs( $p_user_id = null, $p_error_if_protected = true, $p_accounts_menu = true, $p_redirect_url = '' ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_redirect_url = $p_redirect_url;
	if( is_blank( $t_redirect_url ) ) {
		$t_redirect_url = 'account_prefs_page.php';
	}

	# protected account check
	if( user_is_protected( $p_user_id ) ) {
		if( $p_error_if_protected ) {
			trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
		} else {
			return;
		}
	}

	$t_pref = user_pref_get( $p_user_id );
	$t_email_full_issue = (int)config_get( 'email_notifications_verbose', /* default */ null, $p_user_id, ALL_PROJECTS );

# Account Preferences Form BEGIN
?>

<?php
	if( $p_accounts_menu ) {
		print_account_menu( 'account_prefs_page.php' );
		echo '<div class="col-md-12 col-xs-12">';
	}
?>

<div class="space-10"></div>
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php print_icon( 'fa-sliders', 'ace-icon' ); ?>
			<?php echo lang_get( 'default_account_preferences_title' ) ?>
		</h4>
	</div>

	<div class="widget-body">
		<div class="widget-main no-padding">
			<div id="account-prefs-update-div" class="form-container">
				<form id="account-prefs-update-form" method="post" action="account_prefs_update.php" class="form-inline">
					<fieldset>
						<?php echo form_security_field( 'account_prefs_update' ) ?>
						<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
						<input type="hidden" name="redirect_url" value="<?php echo $t_redirect_url ?>" />

						<div class="table-responsive">
	<table class="table table-bordered table-condensed table-striped">

	<tr>
		<td class="category">
			<?php echo lang_get( 'default_project' ) ?>
		</td>
		<td>
			<select id="default-project-id" name="default_project" class="input-sm">
<?php
	# Count number of available projects
	$t_projects = current_user_get_accessible_projects();
	$t_num_proj = count( $t_projects );
	if( $t_num_proj == 1 ) {
		$t_num_proj += count( current_user_get_accessible_subprojects( $t_projects[0] ) );
	}
	# Don't display "All projects" in selection list if there is only 1
	print_project_option_list( (int)$t_pref->default_project, $t_num_proj != 1 );
?>
					</select>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'refresh_delay' ) ?>
		</td>
		<td>
			<input id="refresh-delay" type="text" name="refresh_delay" class="input-sm" size="4" maxlength="4" value="<?php echo $t_pref->refresh_delay ?>" /> <?php echo lang_get( 'minutes' ) ?>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'redirect_delay' ) ?>
		</td>
		<td>
		<input id="redirect-delay" type="text" name="redirect_delay" class="input-sm" size="4" maxlength="3" value="<?php echo $t_pref->redirect_delay ?>" /> <?php echo lang_get( 'seconds' ) ?>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'bugnote_order' ) ?>
		</td>
		<td>
			<label for="bugnote-order-desc" class="inline padding-right-8">
				<input type="radio" class="ace input-sm" id="bugnote-order-desc" name="bugnote_order" value="DESC" <?php check_checked( $t_pref->bugnote_order, 'DESC' ); ?> />
				<span class="lbl padding-6"><?php echo lang_get( 'bugnote_order_desc' ) ?></span>
			</label>
			<label for="bugnote-order-asc" class="inline padding-right-8">
				<input type="radio" class="ace input-sm" id="bugnote-order-asc" name="bugnote_order" value="ASC" <?php check_checked( $t_pref->bugnote_order, 'ASC' ); ?> />
				<span class="lbl padding-6"><?php echo lang_get( 'bugnote_order_asc' ) ?></span>
			</label>
		</td>
	</tr>
	<?php if( ON == config_get( 'enable_email_notification' ) ) { ?>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_new' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm" id="email-on-new" name="email_on_new" <?php check_checked( (int)$t_pref->email_on_new, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-new-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-new-min-severity" name="email_on_new_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_new_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_assigned' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace" id="email-on-assigned" name="email_on_assigned" <?php check_checked( (int)$t_pref->email_on_assigned, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-assigned-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-assigned-min-severity" name="email_on_assigned_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_assigned_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_feedback' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm" id="email-on-feedback" name="email_on_feedback" <?php check_checked( (int)$t_pref->email_on_feedback, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-feedback-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-feedback-min-severity" name="email_on_feedback_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_feedback_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_resolved' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm" id="email-on-resolved" name="email_on_resolved" <?php check_checked( (int)$t_pref->email_on_resolved, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-resolved-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-resolved-min-severity" name="email_on_resolved_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_resolved_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_closed' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm" id="email-on-closed" name="email_on_closed" <?php check_checked( (int)$t_pref->email_on_closed, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-closed-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-closed-min-severity" name="email_on_closed_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_closed_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_reopened' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm" id="email-on-reopened" name="email_on_reopened" <?php check_checked( (int)$t_pref->email_on_reopened, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-reopened-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-reopened-min-severity" name="email_on_reopened_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_reopened_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_bugnote_added' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm" id="email-on-bugnote-added" name="email_on_bugnote" <?php check_checked( (int)$t_pref->email_on_bugnote, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-bugnote-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-bugnote-min-severity" name="email_on_bugnote_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_bugnote_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_status_change' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm" id="email-on-status" name="email_on_status" <?php check_checked( (int)$t_pref->email_on_status, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-status-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-status-min-severity" name="email_on_status_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_status_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_on_priority_change' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm" id="email-on-priority-change" name="email_on_priority" <?php check_checked( (int)$t_pref->email_on_priority, ON ); ?> />
					<span class="lbl"></span>
				</label>
				<label for="email-on-priority-min-severity" class="email-on-severity-label"><span><?php echo lang_get( 'with_minimum_severity' ) ?></span></label>
				<select id="email-on-priority-min-severity" name="email_on_priority_min_severity" class="input-sm">
						<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
						<option disabled="disabled">-----</option>
						<?php print_enum_string_option_list( 'severity', (int)$t_pref->email_on_priority_min_severity ) ?>
					</select>
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_bugnote_limit' ) ?>
			</td>
			<td>
				<input id="email-bugnote-limit" type="text" name="email_bugnote_limit" class="input-sm" maxlength="2" size="2" value="<?php echo $t_pref->email_bugnote_limit ?>" />
			</td>
		</tr>
		<tr>
			<td class="category">
				<?php echo lang_get( 'email_full_issue_details' ) ?>
			</td>
			<td>
				<label class="inline">
					<input type="checkbox" class="ace input-sm"
						id="email-full-issue" name="email_full_issue"
						<?php check_checked( $t_email_full_issue, ON ); ?> />
					<span class="lbl"></span>
				</label>
			</td>
		</tr>
<?php } else { ?>
			<input type="hidden" name="email_on_new"      value="<?php echo $t_pref->email_on_new ?>" />
			<input type="hidden" name="email_on_assigned" value="<?php echo $t_pref->email_on_assigned ?>" />
			<input type="hidden" name="email_on_feedback" value="<?php echo $t_pref->email_on_feedback ?>" />
			<input type="hidden" name="email_on_resolved" value="<?php echo $t_pref->email_on_resolved ?>" />
			<input type="hidden" name="email_on_closed"   value="<?php echo $t_pref->email_on_closed ?>" />
			<input type="hidden" name="email_on_reopened" value="<?php echo $t_pref->email_on_reopened ?>" />
			<input type="hidden" name="email_on_bugnote"  value="<?php echo $t_pref->email_on_bugnote ?>" />
			<input type="hidden" name="email_on_status"   value="<?php echo $t_pref->email_on_status ?>" />
			<input type="hidden" name="email_on_priority" value="<?php echo $t_pref->email_on_priority ?>" />
			<input type="hidden" name="email_on_new_min_severity"      value="<?php echo $t_pref->email_on_new_min_severity ?>" />
			<input type="hidden" name="email_on_assigned_min_severity" value="<?php echo $t_pref->email_on_assigned_min_severity ?>" />
			<input type="hidden" name="email_on_feedback_min_severity" value="<?php echo $t_pref->email_on_feedback_min_severity ?>" />
			<input type="hidden" name="email_on_resolved_min_severity" value="<?php echo $t_pref->email_on_resolved_min_severity ?>" />
			<input type="hidden" name="email_on_closed_min_severity"   value="<?php echo $t_pref->email_on_closed_min_severity ?>" />
			<input type="hidden" name="email_on_reopened_min_severity" value="<?php echo $t_pref->email_on_reopened_min_severity ?>" />
			<input type="hidden" name="email_on_bugnote_min_severity"  value="<?php echo $t_pref->email_on_bugnote_min_severity ?>" />
			<input type="hidden" name="email_on_status_min_severity"   value="<?php echo $t_pref->email_on_status_min_severity ?>" />
			<input type="hidden" name="email_on_priority_min_severity" value="<?php echo $t_pref->email_on_priority_min_severity ?>" />
			<input type="hidden" name="email_bugnote_limit" value="<?php echo $t_pref->email_bugnote_limit ?>" />
			<input type="hidden" name="email_full_issue" value="<?php echo $t_email_full_issue ?>" />
<?php } ?>
	<tr>
		<td class="category">
			<?php echo lang_get( 'timezone' ) ?>
		</td>
		<td>
					<select id="timezone" name="timezone" class="inout-sm">
						<?php print_timezone_option_list( $t_pref->timezone ?  $t_pref->timezone  : config_get_global( 'default_timezone' ) ) ?>
					</select>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'language' ) ?>
		</td>
		<td>
					<select id="language" name="language" class="input-sm">
						<?php print_language_option_list( $t_pref->language ) ?>
					</select>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'font_family' ) ?>
		</td>
		<td>
			<select id="font_family" name="font_family" class="input-sm">
				<?php print_font_option_list( config_get( 'font_family',null, $p_user_id, ALL_PROJECTS ) ) ?>
			</select>
		</td>
	</tr>
	<?php event_signal( 'EVENT_ACCOUNT_PREF_UPDATE_FORM', array( $p_user_id ) ); ?>
	</table>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<button form="account-prefs-update-form"
					class="btn btn-primary btn-white btn-round">
				<?php echo lang_get( 'update_prefs_button' ) ?>
			</button>

<?php
	print_form_button(
		'account_prefs_reset.php',
		lang_get( 'reset_prefs_button' ),
		array( 'user_id' => $p_user_id, 'redirect_url' => $t_redirect_url ),
		null,
		'btn btn-primary btn-white btn-round'
	);
?>
		</div>
	</div>
</div>

<?php
	if( $p_accounts_menu ) {
		echo '</div>';
	}
} # end of edit_account_prefs()
