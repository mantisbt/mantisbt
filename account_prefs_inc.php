<?php
	function edit_account_prefs($p_user_id = null, $p_error_if_protected = true, $p_accounts_menu = true, $p_redirect_url = '') {
		if ( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}

		$t_redirect_url = $p_redirect_url;
		if ( empty( $t_redirect_url ) ) {
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

	    if ( ! user_pref_exists( $p_user_id ) ) {
			user_pref_create( $p_user_id );
	    }

	    # prefix data with u_
		$row = user_pref_get_row( $p_user_id );
		extract( $row, EXTR_PREFIX_ALL, 'u' );
?>
<?php # Account Preferences Form BEGIN ?>
<br />
<div align="center">
<form method="post" action="account_prefs_update.php">
<input type="hidden" name="f_user_id" value="<?php echo $p_user_id ?>" />
<input type="hidden" name="f_redirect_url" value="<?php echo $t_redirect_url ?>" />
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
<tr class="row-1">
	<td class="category" width="50%">
		<?php echo lang_get( 'default_project' ) ?>
	</td>
	<td width="50%">
		<select name="f_project_id">
			<option value="-1"></option>
			<option value="00000000"><?php echo lang_get( 'all_projects' ) ?></option>
			<?php print_project_option_list( $u_default_project ) ?></option>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'advanced_report' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced_report" <?php check_checked( $u_advanced_report, ON ); ?> />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'advanced_view' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced_view" <?php check_checked( $u_advanced_view, ON ); ?> />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'advanced_update' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced_update" <?php check_checked( $u_advanced_update, ON ); ?> />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'refresh_delay' ) ?>
	</td>
	<td>
		<input type="text" name="f_refresh_delay" size="4" maxlength="4" value="<?php echo $u_refresh_delay ?>" />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'redirect_delay' ) ?>
	</td>
	<td>
		<input type="text" name="f_redirect_delay" size="1" maxlength="1" value="<?php echo $u_redirect_delay ?>" />
	</td>
</tr>
<?php
	if ( ON == config_get( 'enable_email_notification' ) ) {
?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'email_on_new' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_new" <?php check_checked( $u_email_on_new, ON ); ?> />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'email_on_assigned' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_assigned" <?php check_checked( $u_email_on_assigned, ON ); ?> />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'email_on_feedback' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_feedback" <?php check_checked( $u_email_on_feedback, ON ); ?> />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'email_on_resolved' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_resolved" <?php check_checked( $u_email_on_resolved, ON ); ?> />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'email_on_closed' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_closed" <?php check_checked( $u_email_on_closed, ON ); ?> />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'email_on_reopened' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_reopened" <?php check_checked( $u_email_on_reopened, ON ); ?> />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'email_on_bugnote_added' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_bugnote" <?php check_checked( $u_email_on_bugnote, ON ); ?> />
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'email_on_status_change' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_status" <?php check_checked( $u_email_on_status, ON ); ?> />
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'email_on_priority_change' ) ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_priority" <?php check_checked( $u_email_on_priority , ON); ?> />
	</td>
</tr>
<?php } else { ?>
		<input type="hidden" name="f_email_on_new"      value="<?php echo $u_email_on_new ?>" />
		<input type="hidden" name="f_email_on_assigned" value="<?php echo $u_email_on_assigned ?>" />
		<input type="hidden" name="f_email_on_feedback" value="<?php echo $u_email_on_feedback ?>" />
		<input type="hidden" name="f_email_on_resolved" value="<?php echo $u_email_on_resolved ?>" />
		<input type="hidden" name="f_email_on_closed"   value="<?php echo $u_email_on_closed ?>" />
		<input type="hidden" name="f_email_on_reopened" value="<?php echo $u_email_on_reopened ?>" />
		<input type="hidden" name="f_email_on_bugnote"  value="<?php echo $u_email_on_bugnote ?>" />
		<input type="hidden" name="f_email_on_status"   value="<?php echo $u_email_on_status ?>" />
		<input type="hidden" name="f_email_on_priority" value="<?php echo $u_email_on_priority ?>" />
<?php } ?>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'language' ) ?>
	</td>
	<td>
		<select name=f_language>
			<?php print_language_option_list( $u_language ) ?>
		</select>
	</td>
</tr>
<tr>
	<td colspan="2" class="center">
		<input type="submit" value="<?php echo lang_get( 'update_prefs_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border-center">
	<form method="post" action="account_prefs_reset.php">
	<input type="hidden" name="f_user_id" value="<?php echo $p_user_id ?>" />
	<input type="hidden" name="f_redirect_url" value="<?php echo $t_redirect_url ?>" />
	<input type="submit" value="<?php echo lang_get( 'reset_prefs_button' ) ?>" />
	</form>
</div>

<?php
	} # end of edit_account_prefs()
?>