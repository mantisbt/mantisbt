<?php

function edit_account_prefs($p_user_id = 0, $p_error_if_protected = true, $p_accounts_menu = true, $p_redirect_url = '')
{
	global	$g_mantis_user_pref_table, $g_default_advanced_report,
			$g_default_advanced_view,
			$g_enable_email_notification,
			$s_default_account_preferences_title,
			$s_default_project, $s_all_projects, $s_advanced_report,
			$s_advanced_view, $s_advanced_update, $s_refresh_delay,
			$s_reset_prefs_button, $s_redirect_delay, $s_email_on_new,
			$s_email_on_assigned, $s_email_on_feedback, $s_email_on_resolved,
			$s_email_on_closed, $s_email_on_reopened, $s_email_on_bugnote_added,
			$s_email_on_status_change, $s_email_on_priority_change,
			$s_language, $s_update_prefs_button;

	$c_user_id = (integer)$p_user_id;

	if ($c_user_id == 0) {
		$c_user_id = current_user_get_field( 'id' );
	}

	$t_redirect_url = $p_redirect_url;
	if ( strlen ($t_redirect_url)== 0 ) {
		$t_redirect_url = 'account_prefs_page.php';
	}

	# get protected state
	$t_protected = user_get_field( $c_user_id, 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		if ( $p_error_if_protected ) {
			print_mantis_error( ERROR_PROTECTED_ACCOUNT );
		} else {
			return;
		}
	}

	# Grab the data
    $query = "SELECT *
    		FROM $g_mantis_user_pref_table
			WHERE user_id='$c_user_id'";
    $result = db_query($query);

    ## OOPS, No entry in the database yet.  Lets make one
    if ( 0 == db_num_rows( $result ) ) {

		# Create row # @@@@@ Add the rest of the fields
	    $query = "INSERT
	    		INTO $g_mantis_user_pref_table
	    		(id, user_id, project_id, advanced_report, advanced_view, language)
	    		VALUES
	    		(null, '$c_user_id', '0000000',
	    		'$g_default_advanced_report', '$g_default_advanced_view', 'english')";
	    $result = db_query($query);

		# Rerun select query
	    $query = "SELECT *
	    		FROM $g_mantis_user_pref_table
				WHERE user_id='$c_user_id'";
	    $result = db_query($query);
    }

    # prefix data with u_
	$row = db_fetch_array($result);
	extract( $row, EXTR_PREFIX_ALL, 'u' );
?>

<?php # Account Preferences Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<form method="post" action="account_prefs_update.php">
		<input type="hidden" name="f_user_id" value="<?php echo $c_user_id ?>">
		<input type="hidden" name="f_redirect_url" value="<?php echo $t_redirect_url ?>">
		<?php echo $s_default_account_preferences_title ?>
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
		<?php echo $s_default_project ?>
	</td>
	<td width="50%">
		<select name="f_project_id">
			<option value="-1"></option>
			<option value="00000000"><?php echo $s_all_projects ?></option>
			<?php print_project_option_list( $u_default_project ) ?></option>
		</select>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_advanced_report ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced_report" <?php check_checked( $u_advanced_report, ON ); ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_advanced_view ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced_view" <?php check_checked( $u_advanced_view, ON ); ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_advanced_update ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced_update" <?php check_checked( $u_advanced_update, ON ); ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_refresh_delay ?>
	</td>
	<td>
		<input type="text" name="f_refresh_delay" size="4" maxlength="4" value="<?php echo $u_refresh_delay ?>">
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_redirect_delay ?>
	</td>
	<td>
		<input type="text" name="f_redirect_delay" size="1" maxlength="1" value="<?php echo $u_redirect_delay ?>">
	</td>
</tr>
<?php
	if ( ON == $g_enable_email_notification ) {
?>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_new ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_new" <?php check_checked( $u_email_on_new, ON ); ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email_on_assigned ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_assigned" <?php check_checked( $u_email_on_assigned, ON ); ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_feedback ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_feedback" <?php check_checked( $u_email_on_feedback, ON ); ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email_on_resolved ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_resolved" <?php check_checked( $u_email_on_resolved, ON ); ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_closed ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_closed" <?php check_checked( $u_email_on_closed, ON ); ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email_on_reopened ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_reopened" <?php check_checked( $u_email_on_reopened, ON ); ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_bugnote_added ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_bugnote" <?php check_checked( $u_email_on_bugnote, ON ); ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email_on_status_change ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_status" <?php check_checked( $u_email_on_status, ON ); ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_priority_change ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_priority" <?php check_checked( $u_email_on_priority , ON); ?>>
	</td>
</tr>
<?php } else { ?>
		<input type="hidden" name="f_email_on_new"      value="<?php echo $u_email_on_new ?>">
		<input type="hidden" name="f_email_on_assigned" value="<?php echo $u_email_on_assigned ?>">
		<input type="hidden" name="f_email_on_feedback" value="<?php echo $u_email_on_feedback ?>">
		<input type="hidden" name="f_email_on_resolved" value="<?php echo $u_email_on_resolved ?>">
		<input type="hidden" name="f_email_on_closed"   value="<?php echo $u_email_on_closed ?>">
		<input type="hidden" name="f_email_on_reopened" value="<?php echo $u_email_on_reopened ?>">
		<input type="hidden" name="f_email_on_bugnote"  value="<?php echo $u_email_on_bugnote ?>">
		<input type="hidden" name="f_email_on_status"   value="<?php echo $u_email_on_status ?>">
		<input type="hidden" name="f_email_on_priority" value="<?php echo $u_email_on_priority ?>">
<?php } ?>
<tr class="row-2">
	<td class="category">
		<?php echo $s_language ?>
	</td>
	<td>
		<select name=f_language>
			<?php print_language_option_list( $u_language ) ?>
		</select>
	</td>
</tr>
<tr>
	<td class="center">
		<input type="submit" value="<?php echo $s_update_prefs_button ?>">
		</form>
	</td>
	<td class="center">
		<form method="post" action="account_prefs_reset.php">
		<input type="hidden" name="f_id" value="<?php echo $c_user_id ?>">
		<input type="submit" value="<?php echo $s_reset_prefs_button ?>">
		</form>
	</td>
</tr>
</table>
</div>
<?php } # end of edit_account_prefs() ?>
