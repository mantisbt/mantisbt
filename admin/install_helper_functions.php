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

	function check_database_support($p_db_type) {
		$t_support = false;
		switch ($p_db_type) {
			case 'mysql':
				$t_support = function_exists('mysql_connect');
				break;
			case 'mysqli':
				$t_support = function_exists('mysqli_connect');
				break;
			case 'pgsql':
				$t_support = function_exists('pg_connect');
				break;
			case 'mssql':
				$t_support = function_exists('mssql_connect');
				break;
			case 'oci8':
				$t_support = function_exists('OCILogon');
				break;
			case 'db2':
				$t_support = function_exists( 'db2_connect' );
				break;
			default:
				$t_support = false;
		}
		return $t_support;
	}
	
	function check_php_version( $p_version ) {
		if ($p_version == PHP_MIN_VERSION) {
			return true;
		} else {
			if ( function_exists ( 'version_compare' ) ) {
				if ( version_compare ( phpversion() , PHP_MIN_VERSION, '>=' ) ) {
					return true;
				} else {
					return false;
				}
			} else {
			 	return false;
			}
		}
	}