<?php
# this function only gets the field names, by appending strings
function get_field_names()
{
	global	$s_printing_preferences_title,
			$s_id, $s_category, $s_severity, $s_reproducibility,
			$s_date_submitted, $s_last_update, $s_reporter, $s_assigned_to,
			$s_priority, $s_resolution, $s_status, $s_duplicate_id,
			$s_os, $s_projection, $s_os_version, $s_eta, $s_product_version,
			$s_build, $s_summary, $s_description,
			$s_steps_to_reproduce, $s_additional, $s_information,
			$s_platform, $s_attached_files, $s_bugnote_title,
			$s_bugnote_date, $s_bugnote_description ;

	#currently 27 fields
	return $t_arr = array (		$s_id,
							    $s_category,
							    $s_severity,
							    $s_reproducibility,
								$s_date_submitted,
								$s_last_update,
								$s_reporter,
								$s_assigned_to,
								$s_priority,
								$s_status,
								$s_build,
								$s_projection,
								$s_eta,
								$s_platform,
								$s_os,
								$s_os_version,
								$s_product_version,
								$s_resolution,
								$s_duplicate_id,
								$s_summary,
								$s_description,
								$s_steps_to_reproduce,
								$s_additional.'_'.$s_information,
								$s_attached_files,
								$s_bugnote_title,
								$s_bugnote_date,
								$s_bugnote_description) ;
}


function edit_printing_prefs($p_user_id = 0, $p_error_if_protected = true, $p_redirect_url = '')
{
	global	$g_mantis_user_print_pref_table, $g_default_advanced_report,
			$g_default_advanced_view,
			$g_enable_email_notification,
			$s_printing_preferences_title,
			$s_id, $s_category, $s_severity, $s_reproducibility,
			$s_date_submitted, $s_last_update, $s_reporter, $s_assigned_to,
			$s_priority, $s_resolution, $s_status, $s_duplicate_id,
			$s_os, $s_projection, $s_os_version, $s_eta, $s_product_version,
			$s_build, $s_summary, $s_description,
			$s_steps_to_reproduce, $s_additional, $s_information,
			$s_platform, $s_attached_files, $s_update_prefs_button, $s_reset_prefs_button;

	$c_user_id = (integer)$p_user_id;

	if ($c_user_id == 0) {
		$c_user_id = current_user_get_field( 'id' );
	}

	$t_redirect_url = $p_redirect_url;
	if ( strlen ($t_redirect_url)== 0 ) {
		$t_redirect_url = 'print_all_bug_page.php';
	}

	# protected account check
	if ( $p_error_if_protected ) {
		user_ensure_unprotected( $p_user_id );
	}

	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count($t_field_name_arr);

	# Grab the data
    $query = "SELECT print_pref
    		FROM $g_mantis_user_print_pref_table
			WHERE user_id='$c_user_id'";
    $result = db_query($query);

    ## OOPS, No entry in the database yet.  Lets make one
    if ( 0 == db_num_rows( $result ) ) {

		# create a default array, same size than $t_field_name
		for ($i=0 ; $i<$field_name_count ; $i++) {
			$t_default_arr[$i] = 1 ;
		}
		$t_default = implode('',$t_default_arr) ;

		# all fields are added by default
		$query = "INSERT
				INTO $g_mantis_user_print_pref_table
				(user_id, print_pref)
				VALUES
				('$c_user_id','$t_default')";

		$result = db_query($query);

		# Rerun select query
	    $query = "SELECT print_pref
	    		FROM $g_mantis_user_print_pref_table
				WHERE user_id='$c_user_id'";
	    $result = db_query($query);
    }

    # putting the query result into an array with the same size as $t_fields_arr
	$row = db_fetch_array($result);
	$t_prefs = $row[0];

?>

<?php # Account Preferences Form BEGIN ?>
<?php $t_index_count=0; ?>
<br />
<div align="center">
<form method="post" action="print_all_bug_options_update.php">
<input type="hidden" name="f_user_id" value="<?php echo $c_user_id ?>" />
<input type="hidden" name="f_redirect_url" value="<?php echo $t_redirect_url ?>" />
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo $s_printing_preferences_title ?>
	</td>
	<td class="right">
	</td>
</tr>


<?php # display the checkboxes
for ($i=0 ; $i <$field_name_count ; $i++) {

	$row_color = $i%2+1;
	PRINT "<tr class=\"row-($row_color)\">";
?>

	<td class="category">
		<?php echo $t_field_name_arr[$i] ?>
	</td>
	<td>
		<?php # @@@ REWORK Code should not span two lines except in extreme cases.  Build this into a variable then print it out.  ?>
		<?php //echo 'f_print_'.strtolower(str_replace(' ','_', $t_field_name_arr[$i])); ?>
		<input type="checkbox" name="<?php echo 'f_print_'.strtolower(str_replace(' ','_', $t_field_name_arr[$i])); ?>"
		<?php if ( isset($t_prefs[$i])&&($t_prefs[$i]==1) ) echo 'checked="checked"' ?> />
	</td>
</tr>

<?php
}
?>
<tr>
	<td>
		&nbsp;
	</td>
	<td>
		<input type="submit" value="<?php echo $s_update_prefs_button ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border-center">
	<form method="post" action="print_all_bug_options_reset.php">
	<input type="hidden" name="f_id" value="<?php echo $c_user_id ?>" />
	<input type="submit" value="<?php echo $s_reset_prefs_button ?>" />
	</form>
</div>

<?php } # end of edit_printing_prefs() ?>
