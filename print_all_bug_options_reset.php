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
	 * Reset prefs to defaults then redirect to account_prefs_page.php
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );
	
	require_once( 'current_user_api.php' );
	require( 'print_all_bug_options_inc.php' );

	form_security_validate( 'print_all_bug_options_reset' );

	auth_ensure_user_authenticated();

	# protected account check
	current_user_ensure_unprotected();

	# get user id
	$t_user_id = auth_get_current_user_id();

	# get the fields list
	$t_field_name_arr = get_field_names();
	$field_name_count = count($t_field_name_arr);

	# create a default array, same size than $t_field_name
	for ($i=0 ; $i<$field_name_count ; $i++) {
		$t_default_arr[$i] = 0 ;
	}
	$t_default = implode('',$t_default_arr) ;

	# reset to defaults
	$t_user_print_pref_table = db_get_table( 'mantis_user_print_pref_table' );
	$query = "UPDATE $t_user_print_pref_table
			SET print_pref=" . db_param() . "
			WHERE user_id=" . db_param();

	$result = db_query_bound( $query, Array( $t_default, $t_user_id ) );

	form_security_purge( 'print_all_bug_options_reset' );

	$t_redirect_url = 'print_all_bug_options_page.php';

	html_page_top( null, $t_redirect_url );

	echo '<br /><div align="center">';

	if ( $result ) {
		print lang_get( 'operation_successful' );
	} else {
		print error_string( ERROR_GENERIC );
	}

	echo '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
	echo '<br /></div>';
	html_page_bottom();
