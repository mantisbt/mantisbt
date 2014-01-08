<?php
# MantisBT - a php based bugtracking system

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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */

	require_once( 'user_pref_api.php' );

	function edit_account_prefs($p_user_id = null, $p_error_if_protected = true, $p_accounts_menu = true, $p_redirect_url = '') {
		if ( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		$t_redirect_url = $p_redirect_url;
		if ( is_blank( $t_redirect_url ) ) {
			$t_redirect_url = 'account_prefs_page.php';
		}

		# protected account check
		if ( user_is_protected( $p_user_id ) ) {
			if ( $p_error_if_protected ) {
				trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
			} else {
				return;
			}
		}

	    # prefix data with u_
		$t_pref = user_pref_get( $p_user_id );

	# Account Preferences Form BEGIN
?>
<br />
<div align="center">
<form method="post" action="account_prefs_update.php">
<?php echo form_security_field( 'account_prefs_update' ) ?>
<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
<input type="hidden" name="redirect_url" value="<?php echo $t_redirect_url ?>" />
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'default_account_preferences_title' ) ?>
	</td>
	<td class="right">
		<?php
			if ( $p_accounts_menu ) {
				print_account_menu( 'account_prefs_page.php' );
			}
		?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category" width="50%">
		<?php echo lang_get( 'default_project' ) ?>
	</td>
	<td width="50%">
		<select name="default_project">
<?php
	# Count number of available projects
	$t_projects = current_user_get_accessible_projects();
	$t_num_proj = count( $t_projects );
	if( $t_num_proj == 1 ) {
		$t_num_proj += count( current_user_get_accessible_subprojects( $t_projects[0] ) );
	}
	# Don't display "All projects" in selection list if there is only 1
	print_project_option_list( $t_pref->default_project, $t_num_proj != 1 );
?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'refresh_delay' ) ?>
	</td>
	<td>
		<input type="text" name="refresh_delay" size="4" maxlength="4" value="<?php echo $t_pref->refresh_delay ?>" /> <?php echo lang_get( 'minutes' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'redirect_delay' ) ?>
	</td>
	<td>
		<input type="text" name="redirect_delay" size="4" maxlength="3" value="<?php echo $t_pref->redirect_delay ?>" /> <?php echo lang_get( 'seconds' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'bugnote_order' ) ?>
	</td>
	<td>
		<label><input type="radio" name="bugnote_order" value="ASC" <?php check_checked( $t_pref->bugnote_order, 'ASC' ); ?> /><?php echo lang_get( 'bugnote_order_asc' ) ?></label>
		<label><input type="radio" name="bugnote_order" value="DESC" <?php check_checked( $t_pref->bugnote_order, 'DESC' ); ?> /><?php echo lang_get( 'bugnote_order_desc' ) ?></label>
	</td>
</tr>
<?php
	if ( ON == config_get( 'enable_email_notification' ) ) {
?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_new' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_new" <?php check_checked( $t_pref->email_on_new, ON ); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_new_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_new_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_assigned' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_assigned" <?php check_checked( $t_pref->email_on_assigned, ON ); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_assigned_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_assigned_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_feedback' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_feedback" <?php check_checked( $t_pref->email_on_feedback, ON ); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_feedback_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_feedback_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_resolved' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_resolved" <?php check_checked( $t_pref->email_on_resolved, ON ); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_resolved_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_resolved_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_closed' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_closed" <?php check_checked( $t_pref->email_on_closed, ON ); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_closed_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_closed_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_reopened' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_reopened" <?php check_checked( $t_pref->email_on_reopened, ON ); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_reopened_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_reopened_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_bugnote_added' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_bugnote" <?php check_checked( $t_pref->email_on_bugnote, ON ); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_bugnote_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_bugnote_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_status_change' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_status" <?php check_checked( $t_pref->email_on_status, ON ); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_status_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_status_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_on_priority_change' ) ?>
	</td>
	<td>
		<input type="checkbox" name="email_on_priority" <?php check_checked( $t_pref->email_on_priority , ON); ?> />
		<?php echo lang_get( 'with_minimum_severity' ) ?>
		<select name="email_on_priority_min_severity">
			<option value="<?php echo OFF ?>"><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_priority_min_severity ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'email_bugnote_limit' ) ?>
	</td>
	<td>
		<input type="text" name="email_bugnote_limit" maxlength="2" size="2" value="<?php echo $t_pref->email_bugnote_limit ?>" />
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
<?php } ?>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'timezone' ) ?>
	</td>
	<td>
		<select name="timezone">
			<?php print_timezone_option_list( $t_pref->timezone ?  $t_pref->timezone  : config_get_global( 'default_timezone' ) ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'language' ) ?>
	</td>
	<td>
		<select name="language">
			<?php print_language_option_list( $t_pref->language ) ?>
		</select>
	</td>
</tr>
<?php event_signal( 'EVENT_ACCOUNT_PREF_UPDATE_FORM', array( $p_user_id ) ); ?>
<tr>
	<td colspan="2" class="center">
		<input type="submit" class="button" value="<?php echo lang_get( 'update_prefs_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border center">
	<form method="post" action="account_prefs_reset.php">
	<?php echo form_security_field( 'account_prefs_reset' ) ?>
	<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
	<input type="hidden" name="redirect_url" value="<?php echo $t_redirect_url ?>" />
	<input type="submit" class="button" value="<?php echo lang_get( 'reset_prefs_button' ) ?>" />
	</form>
</div>

<?php
	} # end of edit_account_prefs()
