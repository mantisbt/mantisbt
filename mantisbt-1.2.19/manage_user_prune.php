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
	require_once( 'core.php' );

	form_security_validate( 'manage_user_prune' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'manage_user_threshold' ) );

	$t_user_table = db_get_table( 'mantis_user_table' );

	# Delete the users who have never logged in and are older than 1 week
	$days_old = (int)7 * SECONDS_PER_DAY;

	$query = "SELECT id, access_level
			FROM $t_user_table
			WHERE ( login_count = 0 ) AND ( date_created = last_visit ) AND " . db_helper_compare_days( 0, "date_created", "> $days_old" );
	$result = db_query_bound($query, Array( db_now() ) );

	if ( !$result ) {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	$count = db_num_rows( $result );

	if ( $count > 0 ) {
		helper_ensure_confirmed( lang_get( 'confirm_account_pruning' ),
								 lang_get( 'prune_accounts_button' ) );
	}

	for ($i=0; $i < $count; $i++) {
		$row = db_fetch_array( $result );
		# Don't prune accounts with a higher global access level than the current user
		if ( access_has_global_level( $row['access_level'] ) ) {
			user_delete($row['id']);
		}
	}

	form_security_purge( 'manage_user_prune' );

	print_header_redirect( 'manage_user_page.php' );
