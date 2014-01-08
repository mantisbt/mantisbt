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

	require_once( 'current_user_api.php' );

# this function only gets the field names, by appending strings
function get_field_names()
{
	#currently 27 fields
	return $t_arr = array (
	                       	'id',
	                       	'category',
	                       	'severity',
	                       	'reproducibility',
	                       	'date_submitted',
	                       	'last_update',
	                       	'reporter',
	                       	'assigned_to',
	                      	'priority',
	                       	'status',
	                       	'build',
	                       	'projection',
	                       	'eta',
	                       	'platform',
	                       	'os',
	                       	'os_version',
	                       	'product_version',
	                       	'resolution',
	                       	'duplicate_id',
	                       	'summary',
	                       	'description',
	                       	'steps_to_reproduce',
	                       	'additional_information',
	                       	'attached_files',
	                       	'bugnote_title',
	                       	'bugnote_date',
	                       	'bugnote_description',
				'time_tracking' );
}


function edit_printing_prefs( $p_user_id = null, $p_error_if_protected = true, $p_redirect_url = '' )
{
	if ( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$c_user_id = db_prepare_int( $p_user_id );

	# protected account check
	if ( $p_error_if_protected ) {
		user_ensure_unprotected( $p_user_id );
	}

	$t_user_print_pref_table = db_get_table( 'mantis_user_print_pref_table' );

	if ( is_blank( $p_redirect_url ) ) {
		$p_redirect_url = 'print_all_bug_page.php';
	}

	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count( $t_field_name_arr );

	# Grab the data
	$query = "SELECT print_pref
			FROM $t_user_print_pref_table
			WHERE user_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_user_id ) );

	## OOPS, No entry in the database yet.  Lets make one
	if ( 0 == db_num_rows( $result ) ) {

		# create a default array, same size than $t_field_name
		for ($i=0 ; $i<$field_name_count ; $i++) {
			$t_default_arr[$i] = 1 ;
		}
		$t_default = implode( '', $t_default_arr ) ;

		# all fields are added by default
		$query = "INSERT
				INTO $t_user_print_pref_table
				(user_id, print_pref)
				VALUES
				(" . db_param() . "," . db_param() . ")";

		$result = db_query_bound( $query, Array( $c_user_id, $t_default ) );

		# Rerun select query
		$query = "SELECT print_pref
				FROM $t_user_print_pref_table
				WHERE user_id=" . db_param();
		$result = db_query_bound( $query, Array( $c_user_id ) );
	}

	# putting the query result into an array with the same size as $t_fields_arr
	$row = db_fetch_array( $result );
	$t_prefs = $row['print_pref'];

	# Account Preferences Form BEGIN
	$t_index_count=0; 
?>
<br />
<div align="center">
<form method="post" action="print_all_bug_options_update.php">
<?php echo form_security_field( 'print_all_bug_options_update' ) ?>
<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
<input type="hidden" name="redirect_url" value="<?php echo string_attribute( $p_redirect_url ) ?>" />
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'printing_preferences_title' ) ?>
	</td>
	<td class="right">
	</td>
</tr>


<?php # display the checkboxes
for ($i=0 ; $i <$field_name_count ; $i++) {

	printf ( '<tr %s>', helper_alternate_class( $i ) );
?>

	<td class="category">
		<?php echo lang_get( $t_field_name_arr[$i] ) ?>
	</td>
	<td>
		<input type="checkbox" name="<?php echo 'print_' . $t_field_name_arr[$i]; ?>"
		<?php if ( isset( $t_prefs[$i] ) && ( $t_prefs[$i]==1 ) ) echo 'checked="checked"' ?> />
	</td>
</tr>

<?php
}
?>
<tr>
	<td>&#160;</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_prefs_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border center">
	<form method="post" action="print_all_bug_options_reset.php">
	<?php echo form_security_field( 'print_all_bug_options_reset' ) ?>
	<input type="submit" class="button" value="<?php echo lang_get( 'reset_prefs_button' ) ?>" />
	</form>
</div>

<?php
}
