<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: print_all_bug_options_reset.php,v 1.15.18.1 2007-10-13 22:34:13 giallu Exp $
	# --------------------------------------------------------

	# Reset prefs to defaults then redirect to account_prefs_page.php3

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
	require( 'print_all_bug_options_inc.php' );

	# helper_ensure_post();

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
	$t_user_print_pref_table = config_get( 'mantis_user_print_pref_table' );
	$query = "UPDATE $t_user_print_pref_table
			SET print_pref='$t_default'
			WHERE user_id='$t_user_id'";

	$result = db_query( $query );

	$t_redirect_url = 'print_all_bug_options_page.php';

	html_page_top1();
	html_meta_redirect( $t_redirect_url );
	html_page_top2();
	PRINT '<br /><div align="center">';

	if ( $result ) {
		print lang_get( 'operation_successful' );
	} else {
		print error_string( ERROR_GENERIC );
	}

	PRINT '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
	PRINT '<br /></div>';
	html_page_bottom1( __FILE__ );
?>

