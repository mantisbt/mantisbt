<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# This page allows the user to set his/her preferences
	# Update is POSTed to acount_prefs_update.php3
	# Reset is POSTed to acount_prefs_reset.php3
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# grab the user id
	$u_id = get_current_user_field( "id " );

	# Grab the data
    $query = "SELECT *
    		FROM $g_mantis_user_pref_table
			WHERE user_id='$u_id'";
    $result = db_query($query);

    ## OOPS, No entry in the database yet.  Lets make one
    if ( 0 == db_num_rows( $result ) ) {

		# Create row # @@@@@ Add the rest of the fields
	    $query = "INSERT
	    		INTO $g_mantis_user_pref_table
	    		(id, user_id, advanced_report, advanced_view, language)
	    		VALUES
	    		(null, '$u_id',
	    		'$g_default_advanced_report', '$g_default_advanced_view', 'english')";
	    $result = db_query($query);

		# Rerun select query
	    $query = "SELECT *
	    		FROM $g_mantis_user_pref_table
				WHERE user_id='$u_id'";
	    $result = db_query($query);
    }

    # prefix data with u_
	$row = db_fetch_array($result);
	extract( $row, EXTR_PREFIX_ALL, "u" );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<?php # Account Preferences Form BEGIN ?>
<p>
<div align="center">
<table class="width75" cellspacing="1">
<form method="post" action="<?php echo $g_account_prefs_update ?>">
<tr>
	<td class="form-title">
		<?php echo $s_default_account_preferences_title ?>
	</td>
	<td class="right">
		<?php print_account_menu( $g_account_prefs_page ) ?>
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
		<input type="checkbox" name="f_advanced_report" <?php if ( ON == $u_advanced_report ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_advanced_view ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced_view" <?php if ( ON == $u_advanced_view ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_advanced_update ?>
	</td>
	<td>
		<input type="checkbox" name="f_advanced_update" <?php if ( ON == $u_advanced_update ) echo "CHECKED" ?>>
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
<?
	if ( ON == $g_enable_email_notification ) {
?>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_new ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_new" <?php if ( ON == $u_email_on_new ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email_on_assigned ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_assigned" <?php if ( ON == $u_email_on_assigned ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_feedback ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_feedback" <?php if ( ON == $u_email_on_feedback ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email_on_resolved ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_resolved" <?php if ( ON == $u_email_on_resolved ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_closed ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_closed" <?php if ( ON == $u_email_on_closed ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email_on_reopened ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_reopened" <?php if ( ON == $u_email_on_reopened ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_bugnote_added ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_bugnote" <?php if ( ON == $u_email_on_bugnote ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo $s_email_on_status_change ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_status" <?php if ( ON == $u_email_on_status ) echo "CHECKED" ?>>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo $s_email_on_priority_change ?>
	</td>
	<td>
		<input type="checkbox" name="f_email_on_priority" <?php if ( ON == $u_email_on_priority ) echo "CHECKED" ?>>
	</td>
</tr>
<?	} else { ?>
		<input type="hidden" name="f_email_on_new"      value="<?php echo $u_email_on_new ?>">
		<input type="hidden" name="f_email_on_assigned" value="<?php echo $u_email_on_assigned ?>">
		<input type="hidden" name="f_email_on_feedback" value="<?php echo $u_email_on_feedback ?>">
		<input type="hidden" name="f_email_on_resolved" value="<?php echo $u_email_on_resolved ?>">
		<input type="hidden" name="f_email_on_closed"   value="<?php echo $u_email_on_closed ?>">
		<input type="hidden" name="f_email_on_reopened" value="<?php echo $u_email_on_reopened ?>">
		<input type="hidden" name="f_email_on_bugnote"  value="<?php echo $u_email_on_bugnote ?>">
		<input type="hidden" name="f_email_on_status"   value="<?php echo $u_email_on_status ?>">
		<input type="hidden" name="f_email_on_priority" value="<?php echo $u_email_on_priority ?>">
<?	} ?>
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
	</td>
	</form>
	<form method="post" action="<?php echo $g_account_prefs_reset ?>">
		<input type="hidden" name="f_id" value="<?php echo $u_id ?>">
	<td class="center">
		<input type="submit" value="<?php echo $s_reset_prefs_button ?>">
	</td>
	</form>
</tr>
</table>
</div>
<?php # Account Preferences Form END ?>

<?php print_page_bot1( __FILE__ ) ?>