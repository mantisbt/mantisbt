<?php
# MantisBT - A PHP based bugtracking system

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
 * News API
 *
 * @package CoreAPI
 * @subpackage NewsAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'utility_api.php' );

/**
 * Add a news item
 *
 * @param int $p_project_id project id
 * @param int $p_poster_id user id of poster
 * @param int $p_view_state View state
 * @param bool $p_announcement annoucement
 * @param string $p_headline News Headline
 * @param string $p_body News Body
 * @return int news article id
 */
function news_create( $p_project_id, $p_poster_id, $p_view_state, $p_announcement, $p_headline, $p_body ) {
	if( is_blank( $p_headline ) ) {
		error_parameters( lang_get( 'headline' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if( is_blank( $p_body ) ) {
		error_parameters( lang_get( 'body' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_news_table = db_get_table( 'news' );

	$t_query = "INSERT
				INTO $t_news_table
	    		  ( project_id, poster_id, date_posted, last_modified,
	    		    view_state, announcement, headline, body )
				VALUES
				    ( " . db_param() . ",
				      " . db_param() . ",
				      " . db_param() . ",
				      " . db_param() . ",
				      " . db_param() . ",
				      " . db_param() . ",
				      " . db_param() . ",
				      " . db_param() . "
					)";
	db_query_bound( $t_query, array( (int)$p_project_id, (int)$p_poster_id, db_now(), db_now(), (int)$p_view_state, $p_announcement, $p_headline, $p_body ) );

	$t_news_id = db_insert_id( $t_news_table );

	return $t_news_id;
}

/**
 * Delete the news entry
 *
 * @param int $p_news_id news id
 * @return bool always true
 */
function news_delete( $p_news_id ) {
	$t_news_table = db_get_table( 'news' );
	$t_query = "DELETE FROM $t_news_table WHERE id=" . db_param();
	db_query_bound( $t_query, array( $p_news_id ) );

	# db_query_bound() errors on failure so:
	return true;
}

/**
 * Delete the news entry
 *
 * @param int $p_project_id project id
 * @return bool always true
 */
function news_delete_all( $p_project_id ) {
	$t_news_table = db_get_table( 'news' );
	$t_query = "DELETE FROM $t_news_table WHERE project_id=" . db_param();
	db_query_bound( $t_query, array( (int)$p_project_id ) );

	# db_query_bound() errors on failure so:
	return true;
}

/**
 * Update news item
 *
 * @param int $p_news_id news id
 * @param int $p_project_id project id
 * @param int $p_view_state view state
 * @param bool $p_announcement announcement
 * @param string $p_headline news headline
 * @param string $p_body news body
 * @return bool always true
 */
function news_update( $p_news_id, $p_project_id, $p_view_state, $p_announcement, $p_headline, $p_body ) {
	if( is_blank( $p_headline ) ) {
		error_parameters( lang_get( 'headline' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if( is_blank( $p_body ) ) {
		error_parameters( lang_get( 'body' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_news_table = db_get_table( 'news' );

	# Update entry
	$t_query = "UPDATE $t_news_table
				  SET view_state=" . db_param() . ",
					announcement=" . db_param() . ",
					headline=" . db_param() . ",
					body=" . db_param() . ",
					project_id=" . db_param() . ",
					last_modified= " . db_param() . "
				  WHERE id=" . db_param();

	db_query_bound( $t_query, array( $p_view_state, $p_announcement, $p_headline, $p_body, $p_project_id, db_now(), $p_news_id ) );

	# db_query_bound() errors on failure so:
	return true;
}

/**
 * Selects the news item associated with the specified id
 *
 * @param int $p_news_id news id
 * @return array news article
 */
function news_get_row( $p_news_id ) {
	$t_news_table = db_get_table( 'news' );
	$t_query = "SELECT * FROM $t_news_table WHERE id=" . db_param();
	$result = db_query_bound( $t_query, array( $p_news_id ) );

	$row = db_fetch_array( $result );

	if( !$row ) {
		trigger_error( ERROR_NEWS_NOT_FOUND, ERROR );
	} else {
		return $row;
	}
}

/**
 * get news count (selected project plus site wide posts)
 *
 * @param int $p_project_id project id
 * @param bool $p_global site wide news i.e. ALL_PROJECTS
 * @return int news count
 */
function news_get_count( $p_project_id, $p_global = true ) {
	$t_news_table = db_get_table( 'news' );
	$t_project_where = helper_project_specific_where( $p_project_id );

	$t_query = "SELECT COUNT(*) FROM $t_news_table WHERE $t_project_where";

	if( $p_global ) {
		$t_query .= ' OR project_id=' . ALL_PROJECTS;
	}

	$result = db_query_bound( $t_query );

	return db_result( $result, 0 );
}

/**
 * get news items (selected project plus site wide posts)
 *
 * @param int $p_project_id project id
 * @param bool $p_global site wide news i.e. ALL_PROJECTS
 * @return array Array of news articles
 */
function news_get_rows( $p_project_id, $p_global = true ) {
	$t_projects = current_user_get_all_accessible_subprojects( $p_project_id );
	$t_projects[] = (int)$p_project_id;

	if( $p_global && ALL_PROJECTS != $p_project_id ) {
		$t_projects[] = ALL_PROJECTS;
	}

	$t_news_table = db_get_table( 'news' );
	$t_query = "SELECT * FROM $t_news_table";

	if( 1 == count( $t_projects ) ) {
		$c_project_id = $t_projects[0];
		$t_query .= " WHERE project_id='$c_project_id'";
	} else {
		$t_query .= ' WHERE project_id IN (' . join( $t_projects, ',' ) . ')';
	}

	$t_query .= " ORDER BY date_posted DESC";

	$result = db_query_bound( $t_query, array() );

	$t_rows = array();

	while( $t_row = db_fetch_array( $result ) ) {
		array_push( $t_rows, $t_row );
	}

	return $t_rows;
}

/**
 * Get field from news item
 *
 * @param int $p_news_id news id
 * @param string $p_field_name field name
 * @return
 */
function news_get_field( $p_news_id, $p_field_name ) {
	$t_row = news_get_row( $p_news_id );
	return( $t_row[$p_field_name] );
}

/**
 * Check if the specified news item is private
 *
 * @param int $p_news_id news id
 * @return bool
 */
function news_is_private( $p_news_id ) {
	return( news_get_field( $p_news_id, 'view_state' ) == VS_PRIVATE );
}

/**
 * Gets a limited set of news rows to be viewed on one page based on the criteria
 * defined in the configuration file.
 *
 * @param int $p_offset offset
 * @param int $p_project_id project id
 * @return array
 */
function news_get_limited_rows( $p_offset, $p_project_id = null ) {
	if( $p_project_id === null ) {
		$p_project_id = helper_get_current_project();
	}

	$c_offset = (int)$p_offset;

	$t_projects = current_user_get_all_accessible_subprojects( $p_project_id );
	$t_projects[] = (int)$p_project_id;
	if( ALL_PROJECTS != $p_project_id ) {
		$t_projects[] = ALL_PROJECTS;
	}

	$t_news_table = db_get_table( 'news' );
	$t_news_view_limit = config_get( 'news_view_limit' );
	$t_news_view_limit_days = config_get( 'news_view_limit_days' ) * SECONDS_PER_DAY;

	switch( config_get( 'news_limit_method' ) ) {
		case 0:

			# BY_LIMIT - Select the news posts
			$query = "SELECT * FROM $t_news_table";

			if( 1 == count( $t_projects ) ) {
				$c_project_id = $t_projects[0];
				$query .= " WHERE project_id=" . db_param();
				$t_params = array( $c_project_id );
			} else {
				$query .= ' WHERE project_id IN (' . join( $t_projects, ',' ) . ')';
				$t_params = null;
			}

			$query .= ' ORDER BY announcement DESC, id DESC';
			$result = db_query_bound( $query, $t_params, $t_news_view_limit, $c_offset );
			break;
		case 1:

			# BY_DATE - Select the news posts
			$query = "SELECT *
						FROM $t_news_table WHERE
						( " . db_helper_compare_days( 0, 'date_posted', "< $t_news_view_limit_days" ) . "
						 OR announcement = " . db_param() . " ) ";
			$t_params = array(
				db_now(),
				1,
			);
			if( 1 == count( $t_projects ) ) {
				$c_project_id = $t_projects[0];
				$query .= " AND project_id=" . db_param();
				$t_params[] = $c_project_id;
			} else {
				$query .= ' AND project_id IN (' . join( $t_projects, ',' ) . ')';
			}
			$query .= " ORDER BY announcement DESC, id DESC";
			$result = db_query_bound( $query, $t_params, $t_news_view_limit, $c_offset );
			break;
	}

	$t_rows = array();
	while( $t_row = db_fetch_array( $result ) ) {
		array_push( $t_rows, $t_row );
	}

	return $t_rows;
}

/** 
 * Checks if the news feature is enabled or not.
 * true: enabled, otherwise false.
 */
function news_is_enabled() {
	return config_get( 'news_enabled' ) == ON;
}

/**
 * Ensures that the news feature is enabled, otherwise generates an access denied error.
 */
function news_ensure_enabled() {
	if ( !news_is_enabled() ) {
		access_denied();
	}
}
