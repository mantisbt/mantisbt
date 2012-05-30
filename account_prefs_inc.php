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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

?>

	<div class="page-header">
		<h1><?php echo lang_get( 'default_account_preferences_title' ) ?></h1>
	</div>
	<div class="row-fluid">
<div class="span3">
        
<form method="post" action="account_prefs_update.php">
<?php echo form_security_field( 'account_prefs_update' ) ?></label><input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
<input type="hidden" name="redirect_url" value="<?php echo $t_redirect_url ?>" />
          <div class="well sidebar-nav" style="padding:19px 0px;">	
		<?php
			if ( $p_accounts_menu ) {
				print_account_menu( 'account_prefs_page.php' );
			}
		?>
</div>
		<input type="submit" class="btn btn-primary btn-large span12" value="<?php echo lang_get( 'update_prefs_button' ) ?>" />
		<br />

	<form method="post" action="account_prefs_reset.php">
	<?php echo form_security_field( 'account_prefs_reset' ) ?><input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
	<input type="hidden" name="redirect_url" value="<?php echo $t_redirect_url ?>" />
	<input type="submit" class="btn btn-danger btn-large span12" value="<?php echo lang_get( 'reset_prefs_button' ) ?>" />
	</form>
</div>
<div class="span9">

		<label><?php echo lang_get( 'default_project' ) ?></label>		<select name="default_project">
			<?php print_project_option_list( $t_pref->default_project ) ?>
		</select>
	

		<label><?php echo lang_get( 'refresh_delay' ) ?></label>	
		<input type="text" name="refresh_delay" size="4" maxlength="4" value="<?php echo $t_pref->refresh_delay ?>" /> <label><?php echo lang_get( 'minutes' ) ?></label>	

		<label><?php echo lang_get( 'redirect_delay' ) ?></label>	
		<input type="text" name="redirect_delay" size="4" maxlength="3" value="<?php echo $t_pref->redirect_delay ?>" /> <label><?php echo lang_get( 'seconds' ) ?></label>	

		<label><?php echo lang_get( 'bugnote_order' ) ?></label>	
		<label><input type="radio" name="bugnote_order" value="ASC" <?php check_checked( $t_pref->bugnote_order, 'ASC' ); ?> /><label><?php echo lang_get( 'bugnote_order_asc' ) ?></label>
		<label><input type="radio" name="bugnote_order" value="DESC" <?php check_checked( $t_pref->bugnote_order, 'DESC' ); ?> /><?php echo lang_get( 'bugnote_order_desc' ) ?></label>
<?php
	if ( ON == config_get( 'enable_email_notification' ) ) {
?>

		<?php echo lang_get( 'email_on_new' ) ?></label>	
		<input type="checkbox" name="email_on_new" <?php check_checked( $t_pref->email_on_new, ON ); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_new_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_new_min_severity ) ?>
		</select>
	<?php echo lang_get( 'email_on_assigned' ) ?></label>	
		<input type="checkbox" name="email_on_assigned" <?php check_checked( $t_pref->email_on_assigned, ON ); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_assigned_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_assigned_min_severity ) ?>
		</select>
		<?php echo lang_get( 'email_on_feedback' ) ?></label>
		<input type="checkbox" name="email_on_feedback" <?php check_checked( $t_pref->email_on_feedback, ON ); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_feedback_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_feedback_min_severity ) ?>
		</select>

		<?php echo lang_get( 'email_on_resolved' ) ?></label>	
		<input type="checkbox" name="email_on_resolved" <?php check_checked( $t_pref->email_on_resolved, ON ); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_resolved_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_resolved_min_severity ) ?>
		</select>
	

		<?php echo lang_get( 'email_on_closed' ) ?></label>	
		<input type="checkbox" name="email_on_closed" <?php check_checked( $t_pref->email_on_closed, ON ); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_closed_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_closed_min_severity ) ?>
		</select>
	

		<?php echo lang_get( 'email_on_reopened' ) ?></label>				<input type="checkbox" name="email_on_reopened" <?php check_checked( $t_pref->email_on_reopened, ON ); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_reopened_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_reopened_min_severity ) ?>
		</select>
	
		<?php echo lang_get( 'email_on_bugnote_added' ) ?></label>	
		<input type="checkbox" name="email_on_bugnote" <?php check_checked( $t_pref->email_on_bugnote, ON ); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_bugnote_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_bugnote_min_severity ) ?>
		</select>
			<?php echo lang_get( 'email_on_status_change' ) ?></label>	
		<input type="checkbox" name="email_on_status" <?php check_checked( $t_pref->email_on_status, ON ); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_status_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_status_min_severity ) ?>
		</select>
	

		<?php echo lang_get( 'email_on_priority_change' ) ?></label>	
		<input type="checkbox" name="email_on_priority" <?php check_checked( $t_pref->email_on_priority , ON); ?> />
		<label><?php echo lang_get( 'with_minimum_severity' ) ?></label>		<select name="email_on_priority_min_severity">
			<option value="<?php echo OFF ?>"><label><?php echo lang_get( 'any' ) ?></option>
			<option disabled="disabled">-----</option>
			<?php print_enum_string_option_list( 'severity', $t_pref->email_on_priority_min_severity ) ?>
		</select>
	

		<?php echo lang_get( 'email_bugnote_limit' ) ?></label>	
		<input type="text" name="email_bugnote_limit" maxlength="2" size="2" value="<?php echo $t_pref->email_bugnote_limit ?>" />

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

		<label><?php echo lang_get( 'timezone' ) ?></label> 
		<select name="timezone">
			<?php print_timezone_option_list( $t_pref->timezone ?  $t_pref->timezone  : config_get_global( 'default_timezone' ) ) ?>
		</select>
	

		<label><?php echo lang_get( 'language' ) ?></label>	
		<select name="language">
			<?php print_language_option_list( $t_pref->language ) ?>
		</select>
	
<?php event_signal( 'EVENT_ACCOUNT_PREF_UPDATE_FORM', array( $p_user_id ) ); ?>


</form>




	</div>
	</div>

<?php
	} # end of edit_account_prefs()
