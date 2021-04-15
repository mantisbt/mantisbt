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
 * Tag API
 *
 * @package CoreAPI
 * @subpackage TagAPI
 * @author John Reese
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses antispam_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses form_api.php
 * @uses history_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'antispam_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'history_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

use Mantis\Exceptions\ClientException;

# cache the tag definitions, indexed by tag id
# tag ids that don't exist are stored as 'false', to avoid repeated searches
$g_cache_tags = array();

# cache the bug tags indexed by [bug_id, tag_id]. Items stored are rows (arrays) fetched from table {bug_tags}
# bugs with no tags will be stored as 'false'.
$g_cache_bug_tags = array();

/**
 * Loads into cache a set of tag definitions from tag table
 * Non existent ids are cached as 'false'
 * @global array $g_cache_tags
 * @param array $p_tag_ids	Array of tag ids
 * @return void
 */
function tag_cache_rows( array $p_tag_ids ) {
	global $g_cache_tags;

	$t_ids_to_search = array();
	foreach( $p_tag_ids as $t_id ) {
		if( !isset( $g_cache_tags[(int)$t_id]) ) {
			$t_ids_to_search[(int)$t_id] = (int)$t_id;
		}
	}
	if( empty( $t_ids_to_search ) ) {
		return;
	}

	db_param_push();
	$t_sql_in_params = array();
	$t_params = array();
	foreach( $t_ids_to_search as $t_id ) {
		$t_sql_in_params[] = db_param();
		$t_params[] = $t_id;
	}
	$t_query = 'SELECT * FROM {tag} where id IN (' . implode( ',', $t_sql_in_params ) . ')';
	$t_result = db_query( $t_query, $t_params );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_id = (int)$t_row['id'];
		$g_cache_tags[$c_id] = $t_row;
		unset( $t_ids_to_search[$c_id] );
	}
	# mark the non existent ids
	foreach( $t_ids_to_search as $t_id ) {
		$g_cache_tags[$t_id] = false;
	}
}

/**
 * Loads into cache the tags associated to a set of bug ids
 * A bug id that has no tags will be cached as 'false'
 * @global array $g_cache_bug_tags
 * @param array $p_bug_ids	Array of bug ids
 * @return void
 */
function tag_cache_bug_tag_rows( array $p_bug_ids ) {
	global $g_cache_bug_tags;

	$t_ids_to_search = array();
	foreach( $p_bug_ids as $t_id ) {
		if( !isset( $g_cache_bug_tags[(int)$t_id]) ) {
			$t_ids_to_search[] = (int)$t_id;
		}
	}

	if( empty( $t_ids_to_search ) ) {
		return;
	}

	db_param_push();
	$t_sql_in_params = array();
	$t_params = array();
	foreach( $t_ids_to_search as $t_id ) {
		$t_sql_in_params[] = db_param();
		$t_params[] = $t_id;
	}
	$t_query = 'SELECT B.id AS bug_id, BT.tag_id, BT.user_id, BT.date_attached FROM {bug} B LEFT OUTER JOIN {bug_tag} BT ON B.id=BT.bug_id'
			. ' WHERE B.id IN (' . implode( ',', $t_sql_in_params ) . ')';
	$t_result = db_query( $t_query, $t_params );

	$t_found_tags = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_bug_id = (int)$t_row['bug_id'];
		$t_has_tags = !empty( $t_row['tag_id'] );
		# create a bug index if needed
		if( !isset( $g_cache_bug_tags[$c_bug_id] ) ) {
			$g_cache_bug_tags[$c_bug_id] = $t_has_tags? array() : false;
		}
		if( $t_has_tags ) {
			$c_tag_id = (int)$t_row['tag_id'];
			$g_cache_bug_tags[$c_bug_id][$c_tag_id] = $t_row;
			$t_found_tags[$c_tag_id] = $c_tag_id;
		}
	}
	# also cache the tags founds
	if( !empty( $t_found_tags ) ) {
		tag_cache_rows( $t_found_tags );
	}
}

/**
 * Clear the bug tags cache (or just the given bug id if specified)
 * @global array $g_cache_bug_tags
 * @param integer $p_bug_id	Bug id
 * @return void
 */
function tag_clear_cache_bug_tags( $p_bug_id = null ) {
	global $g_cache_bug_tags;

	if( null === $p_bug_id ) {
		$g_cache_bug_tags = array();
	} else {
		if( isset( $g_cache_bug_tags[(int)$p_bug_id] ) ) {
			unset( $g_cache_bug_tags[(int)$p_bug_id] );
		}
	}
}

/**
 * Determine if a tag exists with the given ID.
 * @param integer $p_tag_id A tag ID to check.
 * @return boolean True if tag exists
 */
function tag_exists( $p_tag_id ) {
	return ( tag_get( $p_tag_id ) !== false );
}

/**
 * Ensure a tag exists with the given ID.
 * @param integer $p_tag_id A tag ID to check.
 * @return void
 */
function tag_ensure_exists( $p_tag_id ) {
	if( !tag_exists( $p_tag_id ) ) {
		throw new ClientException(
			sprintf( "Tag '%d' does not exist", $p_tag_id ),
			ERROR_TAG_NOT_FOUND,
			array( $p_tag_id ) );
	}
}

/**
 * Determine if a given name is unique (not already used).
 * Uses a case-insensitive search of the database for existing tags with the same name.
 * @param string $p_name The tag name to check.
 * @return boolean True if name is unique
 */
function tag_is_unique( $p_name ) {
	$c_name = trim( $p_name );

	$t_query = 'SELECT id FROM {tag} WHERE ' . db_helper_like( 'name' );
	$t_result = db_query( $t_query, array( $c_name ) );

	if( db_result( $t_result ) ) {
		return false;
	}
	return true;
}

/**
 * Ensure that a name is unique.
 * @param string $p_name The tag name to check.
 * @return void
 */
function tag_ensure_unique( $p_name ) {
	if( !tag_is_unique( $p_name ) ) {
		error_parameters( $p_name );
		trigger_error( ERROR_TAG_DUPLICATE, ERROR );
	}
}

/**
 * Determine if a given name is valid.
 *
 * Name must not begin with '+' and '-' characters (they are used for
 * filters) and must not contain the configured tag separator.
 * The matches parameter allows to also receive an array of regex matches,
 * which by default only includes the valid tag name itself.
 * The prefix parameter is optional, but allows you to prefix the regex
 * check, which is useful for filters, etc.
 * @param string $p_name     The tag name to check.
 * @param array  &$p_matches Array reference for regex matches.
 * @param string $p_prefix   The regex pattern to use as a prefix.
 * @return boolean True if the name is valid.
 */
function tag_name_is_valid( $p_name, array &$p_matches, $p_prefix = '' ) {
	$t_separator = config_get( 'tag_separator' );
	$t_pattern = '/^' . $p_prefix . '([^\+\-' . $t_separator . '][^' . $t_separator . ']*)$/';
	return preg_match( $t_pattern, $p_name, $p_matches );
}

/**
 * Ensure a tag name is valid.
 * @param string $p_name The tag name to check.
 * @return void
 */
function tag_ensure_name_is_valid( $p_name ) {
	$t_matches = array();
	if( !tag_name_is_valid( $p_name, $t_matches ) ) {
		error_parameters( $p_name );
		trigger_error( ERROR_TAG_NAME_INVALID, ERROR );
	}
}

/**
 * Compare two tag rows based on tag name.
 * @param array $p_tag1 The first tag row to compare.
 * @param array $p_tag2 The second tag row to compare.
 * @return int -1 when Tag 1 < Tag 2, 1 when Tag 1 > Tag 2, 0 otherwise
 */
function tag_cmp_name( array $p_tag1, array $p_tag2 ) {
	return strcasecmp( $p_tag1['name'], $p_tag2['name'] );
}

/**
 * Parse a form input string to extract existing and new tags.
 * When given a string, parses for tag names separated by configured separator,
 * then returns an array of tag rows for each tag.  Existing tags get the full
 * row of information returned.  If the tag does not exist, a row is returned with
 * id = -1 and the tag name, and if the name is invalid, a row is returned with
 * id = -2 and the tag name.  The resulting array is then sorted by tag name.
 * @param string $p_string Input string to parse.
 * @return array Rows of tags parsed from input string
 */
function tag_parse_string( $p_string ) {
	$t_tags = array();

	$t_strings = explode( config_get( 'tag_separator' ), $p_string );
	foreach( $t_strings as $t_name ) {
		$t_name = trim( $t_name );
		if( is_blank( $t_name ) ) {
			continue;
		}

		$t_matches = array();
		$t_tag_row = tag_get_by_name( $t_name );
		if( $t_tag_row !== false ) {
			$t_tags[] = $t_tag_row;
		} else {
			if( tag_name_is_valid( $t_name, $t_matches ) ) {
				$t_id = -1;
			} else {
				$t_id = -2;
			}
			$t_tags[] = array(
				'id' => $t_id,
				'name' => $t_name,
			);
		}
	}
	usort( $t_tags, 'tag_cmp_name' );
	return $t_tags;
}

/**
 * Attaches a bunch of tags to the specified issue.
 *
 * @param int    $p_bug_id     The bug id.
 * @param string $p_tag_string String of tags separated by configured separator.
 * @param int    $p_tag_id     Tag id to add or 0 to skip.
 * @return array|bool true for success, otherwise array of failures.  The array elements follow the tag_parse_string()
 *                    format.
 */
function tag_attach_many( $p_bug_id, $p_tag_string, $p_tag_id = 0 ) {
	# If no work, then there is no need to do access check.
	if( $p_tag_id === 0 && is_blank( $p_tag_string ) ) {
		return true;
	}

	access_ensure_bug_level( config_get( 'tag_attach_threshold' ), $p_bug_id );

	$t_tags = tag_parse_string( $p_tag_string );
	$t_can_create = tag_can_create();

	$t_tags_create = array();
	$t_tags_attach = array();
	$t_tags_failed = array();

	foreach ( $t_tags as $t_tag_row ) {
		if( -1 == $t_tag_row['id'] ) {
			if( $t_can_create ) {
				$t_tags_create[] = $t_tag_row;
			} else {
				$t_tags_failed[] = $t_tag_row;
			}
		} else if( -2 == $t_tag_row['id'] ) {
			$t_tags_failed[] = $t_tag_row;
		} else {
			$t_tags_attach[] = $t_tag_row;
		}
	}

	if( 0 < $p_tag_id && tag_exists( $p_tag_id ) ) {
		$t_tags_attach[] = tag_get( $p_tag_id );
	}

	# failed to attach at least one tag
	if( count( $t_tags_failed ) > 0 ) {
		return $t_tags_failed;
	}

	foreach( $t_tags_create as $t_tag_row ) {
		$t_tag_row['id'] = tag_create( $t_tag_row['name'] );
		$t_tags_attach[] = $t_tag_row;
	}

	foreach( $t_tags_attach as $t_tag_row ) {
		if( !tag_bug_is_attached( $t_tag_row['id'], $p_bug_id ) ) {
			tag_bug_attach( $t_tag_row['id'], $p_bug_id );
		}
	}

	event_signal( 'EVENT_TAG_ATTACHED', array( $p_bug_id, $t_tags_attach ) );
	return true;
}

/**
 * Parse a filter string to extract existing and new tags.
 * When given a string, parses for tag names separated by configured separator,
 * then returns an array of tag rows for each tag.  Existing tags get the full
 * row of information returned.  If the tag does not exist, a row is returned with
 * id = -1 and the tag name, and if the name is invalid, a row is returned with
 * id = -2 and the tag name.  The resulting array is then sorted by tag name.
 * @param string $p_string Filter string to parse.
 * @return array Rows of tags parsed from filter string
 */
function tag_parse_filters( $p_string ) {
	$t_tags = array();
	$t_prefix = '[+-]{0,1}';

	$t_strings = explode( config_get( 'tag_separator' ), $p_string );
	foreach( $t_strings as $t_name ) {
		$t_name = trim( $t_name );
		$t_matches = array();

		if( !is_blank( $t_name ) && tag_name_is_valid( $t_name, $t_matches, $t_prefix ) ) {
			$t_tag_row = tag_get_by_name( $t_matches[1] );
			if( $t_tag_row !== false ) {
				$t_filter = mb_substr( $t_name, 0, 1 );

				if( '+' == $t_filter ) {
					$t_tag_row['filter'] = 1;
				} else if( '-' == $t_filter ) {
					$t_tag_row['filter'] = -1;
				} else {
					$t_tag_row['filter'] = 0;
				}

				$t_tags[] = $t_tag_row;
			}
		} else {
			continue;
		}
	}
	usort( $t_tags, 'tag_cmp_name' );
	return $t_tags;
}

/**
 * Returns all available tags
 *
 * @param integer $p_name_filter A string to match the beginning of the tag name.
 * @param integer $p_count       The number of tags to return.
 * @param integer $p_offset      The offset of the result.
 *
 * @return ADORecordSet|boolean Tags sorted by name, or false if the query failed.
 */
function tag_get_all( $p_name_filter, $p_count, $p_offset ) {
	$t_where = '';
	$t_where_params = array();

	if( !is_blank( $p_name_filter ) ) {
		$t_where = 'WHERE ' . db_helper_like( 'name' );
		$t_where_params[] = $p_name_filter . '%';
	}

	$t_query = 'SELECT * FROM {tag} ' . $t_where . ' ORDER BY name';

	return db_query( $t_query, $t_where_params, $p_count, $p_offset );
}

/**
 * Counts all available tags
 * @param integer $p_name_filter A string to match the beginning of the tag name.
 * @return integer
 */
function tag_count( $p_name_filter ) {
	$t_where = '';
	$t_where_params = array();

	if( $p_name_filter ) {
		$t_where = ' WHERE ' . db_helper_like( 'name' );
		$t_where_params[] = $p_name_filter . '%';
	}

	$t_query = 'SELECT count(*) FROM {tag}' . $t_where;

	$t_result = db_query( $t_query, $t_where_params );
	$t_row = db_fetch_array( $t_result );
	return (int)db_result( $t_result );

}

/**
 * Return a tag row for the given ID.
 * @param integer $p_tag_id The tag ID to retrieve from the database.
 * @return boolean|array Tag row, or false if not found
 */
function tag_get( $p_tag_id ) {
	global $g_cache_tags;

	$c_tag_id = (int)$p_tag_id;
	if( !isset( $g_cache_tags[$c_tag_id] ) ) {
		tag_cache_rows( array( $c_tag_id ) );
	}

	$t_tag = $g_cache_tags[$c_tag_id];
	if( null === $t_tag ) {
		return false;
	} else {
		return $t_tag;
	}
}

/**
 * Get tag name by id.
 * @param integer $p_tag_id The tag ID to retrieve from the database.
 * @return string tag name or empty string if not found.
 */
function tag_get_name( $p_tag_id ) {
	$t_tag_row = tag_get( $p_tag_id );
	if( $t_tag_row === false ) {
		return '';
	}

	return $t_tag_row['name'];
}

/**
 * Return a tag row for the given name.
 * @param string $p_name The tag name to retrieve from the database.
 * @return array|false Tag row
 */
function tag_get_by_name( $p_name ) {
	db_param_push();
	$t_query = 'SELECT * FROM {tag} WHERE ' . db_helper_like( 'name' );
	$t_result = db_query( $t_query, array( $p_name ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		return false;
	}

	return $t_row;
}

/**
 * Return a single field from a tag row for the given ID.
 * @param integer $p_tag_id     The tag id to lookup.
 * @param string  $p_field_name The field name to retrieve from the tag.
 * @return array Field value
 */
function tag_get_field( $p_tag_id, $p_field_name ) {
	$t_row = tag_get( $p_tag_id );

	if( isset( $t_row[$p_field_name] ) ) {
		return $t_row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * Can the specified user create a tag?
 *
 * @param integer $p_user_id The id of the user to check access rights for.
 * @return bool true: can create, false: otherwise.
 */
function tag_can_create( $p_user_id = null ) {
	return access_has_global_level( config_get( 'tag_create_threshold' ), $p_user_id );
}

/**
 * Ensure specified user can create tags.
 *
 * @param integer $p_user_id The id of the user to check access rights for.
 * @return void
 */
function tag_ensure_can_create( $p_user_id = null ) {
	access_ensure_global_level( config_get( 'tag_create_threshold' ), $p_user_id );
}

/**
 * Create a tag with the given name, creator, and description.
 * Defaults to the currently logged in user, and a blank description.
 * @param string  $p_name        The tag name to create.
 * @param integer $p_user_id     The user ID to link the new tag to.
 * @param string  $p_description A Description for the tag.
 * @return int Tag ID
 */
function tag_create( $p_name, $p_user_id = null, $p_description = '' ) {
	tag_ensure_can_create( $p_user_id );

	tag_ensure_name_is_valid( $p_name );
	tag_ensure_unique( $p_name );

	if( null == $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	} else {
		user_ensure_exists( $p_user_id );
	}

	$c_date_created = db_now();

	db_param_push();
	$t_query = 'INSERT INTO {tag}
				( user_id, name, description, date_created, date_updated )
				VALUES
				( ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ')';
	db_query( $t_query, array( $p_user_id, trim( $p_name ), trim( $p_description ), $c_date_created, $c_date_created ) );

	return db_insert_id( db_get_table( 'tag' ) );
}

/**
 * Update a tag with given name, creator, and description.
 * @param integer $p_tag_id      The tag ID which is being updated.
 * @param string  $p_name        The name of the tag.
 * @param integer $p_user_id     The user ID to set when updating the tag.
 *                               Note: This replaces the existing user id.
 * @param string  $p_description An updated description for the tag.
 * @return boolean
 * @throws ClientException
 */
function tag_update( $p_tag_id, $p_name, $p_user_id, $p_description ) {
	$t_tag_row = tag_get( $p_tag_id );
	$t_tag_name = $t_tag_row['name'];

	if( $t_tag_name == $p_name &&
		 $t_tag_row['description'] == $p_description &&
		 $t_tag_row['user_id'] == $p_user_id ) {
		# nothing has changed
		return true;
	}

	user_ensure_exists( $p_user_id );

	if( auth_get_current_user_id() == $t_tag_row['user_id'] ) {
		$t_update_level = config_get( 'tag_edit_own_threshold' );
	} else {
		$t_update_level = config_get( 'tag_edit_threshold' );
	}
	access_ensure_global_level( $t_update_level );

	tag_ensure_name_is_valid( $p_name );

	# Do not allow assigning a tag to a user who is not allowed to create one
	if( !access_has_global_level( config_get( 'tag_create_threshold' ), $p_user_id ) ) {
		trigger_error( ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS, ERROR );
	}

	$t_rename = false;
	if( mb_strtolower( $p_name ) != mb_strtolower( $t_tag_name ) ) {
		tag_ensure_unique( $p_name );
		$t_rename = true;
	}

	$c_date_updated = db_now();

	db_param_push();
	$t_query = 'UPDATE {tag}
					SET user_id=' . db_param() . ',
						name=' . db_param() . ',
						description=' . db_param() . ',
						date_updated=' . db_param() . '
					WHERE id=' . db_param();
	db_query( $t_query, array( (int)$p_user_id, $p_name, $p_description, $c_date_updated, $p_tag_id ) );

	if( $t_rename ) {
		$t_bugs = tag_get_bugs_attached( $p_tag_id );

		foreach( $t_bugs as $t_bug_id ) {
			history_log_event_special( $t_bug_id, TAG_RENAMED, $t_tag_name, $p_name );
		}
	}

	return true;
}

/**
 * Delete a tag with the given ID.
 * @param integer $p_tag_id The tag ID to delete.
 * @return boolean
 */
function tag_delete( $p_tag_id ) {
	tag_ensure_exists( $p_tag_id );

	access_ensure_global_level( config_get( 'tag_edit_threshold' ) );

	$t_bugs = tag_get_bugs_attached( $p_tag_id );
	foreach( $t_bugs as $t_bug_id ) {
		tag_bug_detach( $p_tag_id, $t_bug_id );
	}

	db_param_push();
	$t_query = 'DELETE FROM {tag} WHERE id=' . db_param();
	db_query( $t_query, array( $p_tag_id ) );

	return true;
}

/**
 * Gets the tags that are not associated with the specified bug.
 *
 * @param integer $p_bug_id The bug id, if 0 returns all available tags.
 *
 * @return array List of tag rows, each with id, name, and description.
 */
function tag_get_candidates_for_bug( $p_bug_id ) {
	db_param_push();
	$t_query = 'SELECT id, name, description FROM {tag}';
	$t_params = array();

	if( 0 != $p_bug_id ) {
		$t_assoc_tags_query = 'SELECT tag_id FROM {bug_tag} WHERE bug_id = ' . db_param();
		$t_params[] = $p_bug_id;

		# Define specific where clause to exclude tags already attached to the bug
		# Special handling for odbc_mssql which does not support bound subqueries (#14774)
		if( config_get_global( 'db_type' ) == 'odbc_mssql' ) {
			db_param_push();
			$t_result = db_query( $t_assoc_tags_query, $t_params );

			$t_subquery_results = array();
			while( $t_row = db_fetch_array( $t_result ) ) {
				$t_subquery_results[] = (int)$t_row['tag_id'];
			}
			if( $t_subquery_results ) {
				$t_where = ' WHERE id NOT IN (' . implode( ', ', $t_subquery_results ) . ')';
			} else {
				$t_where = '';
			}
			$t_params = null;
		} else {
			$t_where = " WHERE id NOT IN ($t_assoc_tags_query)";
		}
		$t_query .= $t_where;
	}

	$t_query .= ' ORDER BY name ASC ';
	$t_result = db_query( $t_query, $t_params );

	$t_results_to_return = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_results_to_return[] = $t_row;
	}

	return $t_results_to_return;
}

/**
 * Determine if a tag is attached to a bug.
 * @param integer $p_tag_id The tag ID to check.
 * @param integer $p_bug_id The bug ID to check.
 * @return boolean True if the tag is attached
 */
function tag_bug_is_attached( $p_tag_id, $p_bug_id ) {
	db_param_push();
	$t_query = 'SELECT bug_id FROM {bug_tag} WHERE tag_id=' . db_param() . ' AND bug_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_tag_id, $p_bug_id ) );
	return( db_result( $t_result ) !== false );
}

/**
 * Return the tag attachment row.
 * @param integer $p_tag_id The tag ID to check.
 * @param integer $p_bug_id The bug ID to check.
 * @return array Tag attachment row
 */
function tag_bug_get_row( $p_tag_id, $p_bug_id ) {
	global $g_cache_bug_tags;

	$c_bug_id = (int)$p_bug_id;
	if( !isset( $g_cache_bug_tags[$c_bug_id] ) ) {
		tag_cache_bug_tag_rows( array( $c_bug_id ) );
	}

	$t_bug_tags = $g_cache_bug_tags[$c_bug_id];
	if( !$t_bug_tags || !isset( $t_bug_tags[$p_tag_id] ) ) {
		trigger_error( TAG_NOT_ATTACHED, ERROR );
	}
	return $t_bug_tags[$p_tag_id];
}

/**
 * Return an array of tags attached to a given bug sorted by tag name.
 * @param integer $p_bug_id The bug ID to check.
 * @return array Array of tag rows with attachment information
 */
function tag_bug_get_attached( $p_bug_id ) {
	global $g_cache_bug_tags;

	$c_bug_id = (int)$p_bug_id;
	if( !isset( $g_cache_bug_tags[$c_bug_id] ) ) {
		tag_cache_bug_tag_rows( array( $c_bug_id ) );
	}

	$t_bug_tags = $g_cache_bug_tags[$c_bug_id];
	if( !$t_bug_tags ) {
		return array();
	}

	$t_tag_info_rows = array();
	foreach( $t_bug_tags as $t_row ) {
		$t_tag_data = tag_get($t_row['tag_id']);
		$t_tag_data['user_attached'] = $t_row['user_id'];
		$t_tag_data['date_attached'] = $t_row['date_attached'];
		$t_tag_info_rows[] = $t_tag_data;
	}
	usort( $t_tag_info_rows, 'tag_cmp_name' );
	return $t_tag_info_rows;
}

/**
 * Return an array of bugs that a tag is attached to.
 * @param integer $p_tag_id The tag ID to check.
 * @return array Array of bug ID's.
 */
function tag_get_bugs_attached( $p_tag_id ) {
	db_param_push();
	$t_query = 'SELECT bug_id FROM {bug_tag} WHERE tag_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_tag_id ) );

	$t_bugs = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_bugs[] = $t_row['bug_id'];
	}

	return $t_bugs;
}

/**
 * Attach a tag to a bug.
 * @param integer $p_tag_id  The tag ID to attach.
 * @param integer $p_bug_id  The bug ID to attach.
 * @param integer $p_user_id The user ID to attach.
 * @return boolean
 */
function tag_bug_attach( $p_tag_id, $p_bug_id, $p_user_id = null ) {
	antispam_check();

	access_ensure_bug_level( config_get( 'tag_attach_threshold' ), $p_bug_id, $p_user_id );

	tag_ensure_exists( $p_tag_id );

	if( tag_bug_is_attached( $p_tag_id, $p_bug_id ) ) {
		trigger_error( TAG_ALREADY_ATTACHED, ERROR );
	}

	if( null == $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	} else {
		user_ensure_exists( $p_user_id );
	}

	db_param_push();
	$t_query = 'INSERT INTO {bug_tag}
					( tag_id, bug_id, user_id, date_attached )
					VALUES
					( ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ')';
	db_query( $t_query, array( $p_tag_id, $p_bug_id, $p_user_id, db_now() ) );

	tag_clear_cache_bug_tags( $p_bug_id );

	$t_tag_name = tag_get_field( $p_tag_id, 'name' );
	history_log_event_special( $p_bug_id, TAG_ATTACHED, $t_tag_name );

	# updated the last_updated date
	bug_update_date( $p_bug_id );

	return true;
}

/**
 * Detach a tag from a bug.
 * @param integer $p_tag_id      The tag ID to detach.
 * @param integer $p_bug_id      The bug ID to detach.
 * @param boolean $p_add_history Add history entries to bug.
 * @param integer $p_user_id     User Id (or null for current logged in user).
 * @return boolean
 */
function tag_bug_detach( $p_tag_id, $p_bug_id, $p_add_history = true, $p_user_id = null ) {
	if( $p_user_id === null ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}

	if( !tag_bug_is_attached( $p_tag_id, $p_bug_id ) ) {
		trigger_error( TAG_NOT_ATTACHED, ERROR );
	}

	$t_tag_row = tag_bug_get_row( $p_tag_id, $p_bug_id );
	if( $t_user_id == tag_get_field( $p_tag_id, 'user_id' ) || $t_user_id == $t_tag_row['user_id'] ) {
		$t_detach_level = config_get( 'tag_detach_own_threshold' );
	} else {
		$t_detach_level = config_get( 'tag_detach_threshold' );
	}

	if( !access_has_bug_level( $t_detach_level, $p_bug_id, $t_user_id ) ) {
		throw new ClientException(
			sprintf( "Access denied to detach '%s'", $t_tag_row['name'] ),
			ERROR_ACCESS_DENIED
		);
	}

	db_param_push();
	$t_query = 'DELETE FROM {bug_tag} WHERE tag_id=' . db_param() . ' AND bug_id=' . db_param();
	db_query( $t_query, array( $p_tag_id, $p_bug_id ) );

	tag_clear_cache_bug_tags( $p_bug_id );

	if( $p_add_history ) {
		$t_tag_name = tag_get_field( $p_tag_id, 'name' );
		history_log_event_special( $p_bug_id, TAG_DETACHED, $t_tag_name );
	}

	# updated the last_updated date
	bug_update_date( $p_bug_id );

	return true;
}

/**
 * Detach all tags from a given bug.
 * @param integer $p_bug_id      The bug ID to detach.
 * @param boolean $p_add_history Add history entries to bug.
 * @param integer $p_user_id     User Id (or null for current logged in user).
 * @return void
 */
function tag_bug_detach_all( $p_bug_id, $p_add_history = true, $p_user_id = null ) {
	$t_tags = tag_bug_get_attached( $p_bug_id );
	foreach( $t_tags as $t_tag_row ) {
		tag_bug_detach( $t_tag_row['id'], $p_bug_id, $p_add_history, $p_user_id );
	}
}

/**
 * Builds a hyperlink to the Tag Detail page
 * @param array $p_tag_row Tag row.
 * @return string
 */
function tag_get_link( array $p_tag_row ) {
	return sprintf(
		'<a class="btn btn-xs btn-primary btn-white btn-round" href="tag_view_page.php?tag_id=%s" title="%s">%s</a>',
		$p_tag_row['id'],
		string_display_line( $p_tag_row['description'] ),
		string_display_line( $p_tag_row['name'] )
	);
}

/**
 * Display a tag hyperlink.
 * If a bug ID is passed, the tag link will include a detach link if the
 * user has appropriate privileges.
 * @param array   $p_tag_row Tag row.
 * @param integer $p_bug_id  The bug ID to display.
 * @return boolean
 */
function tag_display_link( array $p_tag_row, $p_bug_id = 0 ) {
	static $s_security_token = null;
	if( is_null( $s_security_token ) ) {
		$s_security_token = htmlspecialchars( form_security_param( 'tag_detach' ) );
	}

	echo tag_get_link( $p_tag_row );

	if( isset( $p_tag_row['user_attached'] ) && auth_get_current_user_id() == $p_tag_row['user_attached']
	 || auth_get_current_user_id() == $p_tag_row['user_id']
	) {
		$t_detach = config_get( 'tag_detach_own_threshold' );
	} else {
		$t_detach = config_get( 'tag_detach_threshold' );
	}

	if( $p_bug_id > 0 && access_has_bug_level( $t_detach, $p_bug_id ) ) {
		$t_tooltip = string_html_specialchars( sprintf( lang_get( 'tag_detach' ), string_display_line( $p_tag_row['name'] ) ) );
		$t_href = 'tag_detach.php?bug_id=' . $p_bug_id . '&amp;tag_id=' . $p_tag_row['id'] . $s_security_token;
		echo ' <a class="btn btn-xs btn-primary btn-white btn-round" title="' . $t_tooltip . '" href="' . $t_href . '">';
		print_icon( 'fa-times' );
		echo '</a>';
	}

	return true;
}

/**
 * Display a list of attached tag hyperlinks separated by the configured hyperlinks.
 * @param integer $p_bug_id The bug ID to display.
 * @return boolean
 */
function tag_display_attached( $p_bug_id ) {
	$t_tag_rows = tag_bug_get_attached( $p_bug_id );

	if( count( $t_tag_rows ) == 0 ) {
		echo lang_get( 'tag_none_attached' );
	} else {
		$i = 0;
		foreach( $t_tag_rows as $t_tag ) {
			echo( $i > 0 ? config_get( 'tag_separator' ) . ' ' : '' );
			tag_display_link( $t_tag, $p_bug_id );
			$i++;
		}
	}

	return true;
}

/**
 * Get all attached tags separated by the Tag Separator.
 * @param integer $p_bug_id The bug ID to display.
 * @return string tags separated by the configured Tag Separator
 */
function tag_bug_get_all( $p_bug_id ) {
	$t_tag_rows = tag_bug_get_attached( $p_bug_id );
	$t_value = '';

	$i = 0;
	foreach( $t_tag_rows as $t_tag ) {
		$t_value .= ( $i > 0 ? config_get( 'tag_separator' ) . ' ' : '' );
		$t_value .= $t_tag['name'];
		$i++;
	}

	return $t_value;
}

/**
 * Get the number of bugs a given tag is attached to.
 * @param integer $p_tag_id The tag ID to retrieve statistics on.
 * @return int Number of attached bugs
 */
function tag_stats_attached( $p_tag_id ) {
	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM {bug_tag} WHERE tag_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_tag_id ) );

	return db_result( $t_result );
}

/**
 * Get a list of related tags.
 * Returns a list of tags that are the most related to the given tag,
 * based on the number of times they have been attached to the same bugs.
 * Defaults to a list of five tags.
 * @param integer $p_tag_id The tag ID to retrieve statistics on.
 * @param integer $p_limit  List size.
 * @return array Array of tag rows, with share count added
 */
function tag_stats_related( $p_tag_id, $p_limit = 5 ) {

	# Use a filter to get all visible issues for this tag id
	$t_filter = array(
		FILTER_PROPERTY_HIDE_STATUS => array( META_FILTER_NONE ),
		FILTER_PROPERTY_TAG_SELECT => $p_tag_id,
		FILTER_PROPERTY_PROJECT_ID => array( ALL_PROJECTS ),
		'_view_type' => FILTER_VIEW_TYPE_ADVANCED,
	);
	$t_filter = filter_ensure_valid_filter( $t_filter );

	$t_filter_subquery = new BugFilterQuery( $t_filter, BugFilterQuery::QUERY_TYPE_IDS );

	$t_sql = 'SELECT tag_id, COUNT(1) AS tag_count FROM {bug_tag}'
			. ' WHERE bug_id IN :filter AND tag_id <> :tagid'
			. ' GROUP BY tag_id ORDER BY tag_count DESC';
	$t_query = new DbQuery( $t_sql );
	$t_query->bind( 'filter', $t_filter_subquery );
	$t_query->bind( 'tagid', (int)$p_tag_id );
	$t_query->execute();

	$t_tags = array();
	while( $t_row = $t_query->fetch() ) {
		$t_tag_row = tag_get( $t_row['tag_id'] );
		$t_tag_row['count'] = (int)$t_row['tag_count'];
		$t_tags[] = $t_tag_row;
	}

	return $t_tags;
}

