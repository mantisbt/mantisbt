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

	/**
	 * Update functions for the installation schema's 'UpdateFunction' option.
	 * All functions must be name install_<function_name> and referenced as just <function_name>.
	 */

	/**
	 * Migrate the legacy category data to the new category_id-based schema.
	 */
	function install_category_migrate() {
		$t_bug_table = db_get_table( 'mantis_bug_table' );
		$t_category_table = db_get_table( 'mantis_category_table' );
		$t_project_category_table = db_get_table( 'mantis_project_category_table' );

		$query = "SELECT project_id, category FROM $t_project_category_table ORDER BY project_id, category";
		$t_category_result = db_query_bound( $query );

		$query = "SELECT project_id, category FROM $t_bug_table ORDER BY project_id, category";
		$t_bug_result = db_query_bound( $query );

		$t_data = Array();

		# Find categories specified by project
		while ( $row = db_fetch_array( $t_category_result ) ) {
			$t_project_id = $row['project_id'];
			$t_name = $row['category'];
			$t_data[$t_project_id][$t_name] = true;
		}

		# Find orphaned categories from bugs
		while ( $row = db_fetch_array( $t_bug_result ) ) {
			$t_project_id = $row['project_id'];
			$t_name = $row['category'];

			$t_data[$t_project_id][$t_name] = true;
		}

		# In every project, go through all the categories found, and create them and update the bug
		foreach ( $t_data as $t_project_id => $t_categories ) {
			foreach ( $t_categories as $t_name => $t_true ) {
				$query = "INSERT INTO $t_category_table ( name, project_id ) VALUES ( " . db_param() . ', ' . db_param() . ' )';
				db_query_bound( $query, array( $t_name, $t_project_id ) );
				$t_category_id = db_insert_id( $t_category_table );

				$query = "UPDATE $t_bug_table SET category_id=" . db_param() . '
							WHERE project_id=' . db_param() . ' AND category=' . db_param();
				db_query_bound( $query, array( $t_category_id, $t_project_id, $t_name ) );
			}
		}

		# return 2 because that's what ADOdb/DataDict does when things happen properly
		return 2;
	}

