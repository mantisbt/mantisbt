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
	# $Id: db_stats.php,v 1.5 2007-10-28 01:06:36 prichards Exp $
	# --------------------------------------------------------

	require_once ( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	# --------------------
	function helper_table_row_count( $p_table ) {
		$t_table = $p_table;

		$query = "SELECT COUNT(*) FROM $t_table";
		$result = db_query_bound( $query );

		$t_users = db_result( $result );

		return $t_users;
	}


	# --------------------
	function print_table_stats( $p_table_name ) {
		$t_count = helper_table_row_count( $p_table_name );
		# echo "<tr><td>$p_table_name</td><td>$t_count</td></tr>";
		echo "$p_table_name = $t_count records<br />";
	}

	echo '<html><head><title>Mantis Database Statistics</title></head><body>';

	echo '<h1>Mantis Database Statistics</h1>';
	# echo '<table border="1" width="50%" cellpadding="3" cellspacing="0">';

	print_table_stats( config_get_global( 'mantis_bug_file_table' ) );
	print_table_stats( config_get_global( 'mantis_bug_history_table' ) );
	print_table_stats( config_get_global( 'mantis_bug_monitor_table' ) );
	print_table_stats( config_get_global( 'mantis_bug_relationship_table' ) );
	print_table_stats( config_get_global( 'mantis_bug_table' ) );
	print_table_stats( config_get_global( 'mantis_bug_text_table' ) );
	print_table_stats( config_get_global( 'mantis_bugnote_table' ) );
	print_table_stats( config_get_global( 'mantis_bugnote_text_table' ) );
	print_table_stats( config_get_global( 'mantis_config_table' ) );
	print_table_stats( config_get_global( 'mantis_custom_field_project_table' ) );
	print_table_stats( config_get_global( 'mantis_custom_field_string_table' ) );
	print_table_stats( config_get_global( 'mantis_custom_field_table' ) );
	print_table_stats( config_get_global( 'mantis_filters_table' ) );
	print_table_stats( config_get_global( 'mantis_news_table' ) );
	print_table_stats( config_get_global( 'mantis_project_category_table' ) );
	print_table_stats( config_get_global( 'mantis_project_file_table' ) );
	print_table_stats( config_get_global( 'mantis_project_hierarchy_table' ) );
	print_table_stats( config_get_global( 'mantis_project_table' ) );
	print_table_stats( config_get_global( 'mantis_project_user_list_table' ) );
	print_table_stats( config_get_global( 'mantis_project_version_table' ) );
	print_table_stats( config_get_global( 'mantis_sponsorship_table' ) );
	print_table_stats( config_get_global( 'mantis_tokens_table' ) );
	print_table_stats( config_get_global( 'mantis_user_pref_table' ) );
	print_table_stats( config_get_global( 'mantis_user_print_pref_table' ) );
	print_table_stats( config_get_global( 'mantis_user_profile_table' ) );
	print_table_stats( config_get_global( 'mantis_user_table' ) );

	# echo '</table>';
	echo '</body></html>';
?>