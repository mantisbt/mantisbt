<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.	If not, see <http://www.gnu.org/licenses/>.

/**
 * Print all bug options include file
 * @package MantisBT
 * @copyright Copyright 2000 - 2002	 Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

if( !defined( 'PRINT_ALL_BUG_OPTIONS_INC_ALLOW' ) ) {
	return;
}

require_api( 'authentication_api.php' );
require_api( 'database_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

/**
 * this function only gets the field names, by appending strings
 * @return array
 */
function get_field_names() {
	return array (
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
				'os_build',
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

/**
 * Edit Printing preferences
 * @param integer $p_user_id            A valid user identifier.
 * @param boolean $p_error_if_protected Error if account protected.
 * @param string  $p_redirect_url       Redirect URL.
 * @return void
 */
function edit_printing_prefs( $p_user_id = null, $p_error_if_protected = true, $p_redirect_url = '' ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	# protected account check
	if( $p_error_if_protected ) {
		user_ensure_unprotected( $p_user_id );
	}

	if( is_blank( $p_redirect_url ) ) {
		$p_redirect_url = 'print_all_bug_page.php';
	}

	# get the fields list
	$t_field_name_arr = get_field_names();
	$t_field_name_count = count( $t_field_name_arr );

	# Grab the data
	$t_query = 'SELECT print_pref FROM {user_print_pref} WHERE user_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_user_id ) );

	$t_row = db_fetch_array( $t_result );

	## OOPS, No entry in the database yet.	Lets make one
	if( !$t_row ) {
		# create a default array, same size than $t_field_name
		for( $i=0; $i<$t_field_name_count; $i++ ) {
			$t_default_arr[$i] = 1 ;
		}
		$t_default = implode( '', $t_default_arr ) ;

		# all fields are added by default
		$t_query = 'INSERT
				INTO {user_print_pref}
				(user_id, print_pref)
				VALUES
				(' . db_param() . ',' . db_param() . ')';
		db_query( $t_query, array( $p_user_id, $t_default ) );

		# Rerun select query
		$t_query = 'SELECT print_pref FROM {user_print_pref} WHERE user_id=' . db_param();
		$t_result = db_query( $t_query, array( $p_user_id ) );

		$t_row = db_fetch_array( $t_result );
	}

	# putting the query result into an array with the same size as $t_fields_arr
	$t_prefs = $t_row['print_pref'];

	# Account Preferences Form BEGIN
?>
<br />
<div>
<form method="post" action="print_all_bug_options_update.php">
<?php echo form_security_field( 'print_all_bug_options_update' ) ?>
<input type="hidden" name="user_id" value="<?php echo $p_user_id ?>" />
<input type="hidden" name="redirect_url" value="<?php echo string_attribute( $p_redirect_url ) ?>" />
<table class="width75" cellspacing="1">
<tr>
	<td class="bold">
		<?php echo lang_get( 'printing_preferences_title' ) ?>
	</td>
	<td class="pull-right">
	</td>
</tr>


<?php # display the checkboxes
for( $i=0; $i <$t_field_name_count; $i++ ) {

	echo '<tr>';
?>

	<th class="category">
		<?php echo lang_get( $t_field_name_arr[$i] ) ?>
	</th>
	<td>
		<input type="checkbox" name="<?php echo 'print_' . $t_field_name_arr[$i]; ?>"
		<?php
			if( isset( $t_prefs[$i] ) && ( $t_prefs[$i]==1 ) ) {
				echo 'checked="checked"';
			}
		?>
		/>
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
