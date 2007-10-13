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
	# $Id: last_visited_api.php,v 1.4.2.1 2007-10-13 22:35:33 giallu Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'tokens_api.php' );

	#---------------------------------
	# Determine if last visited feature is enabled
	function last_visited_enabled() {
		return !( OFF == config_get( 'recently_visited' ) || current_user_is_anonymous() );
	}

	#---------------------------------
	# This method should be called from view, update, print pages for issues, mantisconnect.
	function last_visited_issue( $p_issue_id, $p_user_id = null ) {
		if ( !last_visited_enabled() ) {
			return;
		}

		$c_issue_id = db_prepare_int( $p_issue_id );

		$t_value = token_get_value( TOKEN_LAST_VISITED, $p_user_id );
		if ( is_null( $t_value ) ) {
			$t_value = $c_issue_id;
		} else {
			$t_ids = explode( ',', $p_issue_id . ',' . $t_value );
			$t_ids = array_unique( $t_ids );
			$t_ids = array_slice( $t_ids, 0, config_get( 'recently_visited_count' ) );
			$t_value = implode( ',', $t_ids );
		}
		
		token_set( TOKEN_LAST_VISITED, $t_value, TOKEN_EXPIRY_LAST_VISITED, $p_user_id );
	}
	
	#---------------------------------
	# Get an array of the last visited bug ids.  We intentionally don't check if the ids still exists to avoid performance
	# degradation.
	function last_visited_get_array( $p_user_id = null ) {
		$t_value = token_get_value( TOKEN_LAST_VISITED, $p_user_id );

		if ( is_null( $t_value ) ) {
			return array();
		}

		# we don't slice the array here to optimise for performance.  If the user reduces the number of recently
		# visited to track, then he/she will get the extra entries until visiting an issue.
		$t_ids = explode( ',', $t_value );

		return $t_ids;
	}
?>
