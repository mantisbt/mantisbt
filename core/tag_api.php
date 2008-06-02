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
	# $Id: tag_api.php,v 1.4.2.2 2007-10-13 22:35:44 giallu Exp $
	# --------------------------------------------------------

	/**
	 * Tag API
	 *
	 * @package TagAPI
	 * @author John Reese
	 */

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'history_api.php' );
	
	/**
	 * Determine if a tag exists with the given ID.
	 * @param integer Tag ID
	 * @return boolean True if tag exists
	 */
	function tag_exists( $p_tag_id ) {
		$c_tag_id = db_prepare_int( $p_tag_id );
		$t_tag_table = config_get( 'mantis_tag_table' );

		$query = "SELECT * FROM $t_tag_table WHERE id='$c_tag_id'";
		$result = db_query( $query ) ;

		return db_num_rows( $result ) > 0;
	}

	/**
	 * Ensure a tag exists with the given ID.
	 * @param integer Tag ID
	 */
	function tag_ensure_exists( $p_tag_id ) {
		if ( !tag_exists( $p_tag_id ) ) {
			error_parameters( $p_tag_id );
			trigger_error( ERROR_TAG_NOT_FOUND, ERROR );
		}
	}

	/**
	 * Determine if a given name is unique (not already used).
	 * Uses a case-insensitive search of the database for existing tags with the same name.
	 * @param string Tag name
	 * @return boolean True if name is unique
	 */
	function tag_is_unique( $p_name ) {
		$c_name = trim( db_prepare_string( $p_name ) );
		$t_tag_table = config_get( 'mantis_tag_table' );

		$query = "SELECT id FROM $t_tag_table WHERE ".db_helper_like( 'name', $c_name );
		$result = db_query( $query ) ;

		return db_num_rows( $result ) == 0;
	}

	/**
	 * Ensure that a name is unique.
	 * @param string Tag name
	 */
	function tag_ensure_unique( $p_name ) {
		if ( !tag_is_unique( $p_name ) ) {
			trigger_error( ERROR_TAG_DUPLICATE, ERROR );
		}
	}

	/**
	 * Determine if a given name is valid.
	 * Name must start with letter/number and consist of letters, numbers, 
	 * hyphens, underscores, periods, or spaces.  The matches parameter allows 
	 * you to also receive an array of regex matches, which by default only 
	 * includes the valid tag name itself.  The prefix parameter is optional,
	 * but allows you to prefix the regex check, which is useful for filters, etc.
	 * @param string Tag name
	 * @param array Array reference for regex matches
	 * @param string Prefix regex pattern
	 * @return boolean True if the name is valid
	 */
	function tag_name_is_valid( $p_name, &$p_matches, $p_prefix="" ) {
		$t_pattern = "/^$p_prefix([a-zA-Z0-9][a-zA-Z0-9-_. ]*)$/";
		return preg_match( $t_pattern, $p_name, $p_matches );
	}

	/**
	 * Ensure a tag name is valid.
	 * @param string Tag name
	 */
	function tag_ensure_name_is_valid( $p_name ) {
		$t_matches = array();
		if ( !tag_name_is_valid( $p_name, $t_matches ) ) {
			trigger_error( ERROR_TAG_NAME_INVALID, ERROR );
		}
	}

	/**
	 * Compare two tag rows based on tag name.
	 * @param array Tag row 1
	 * @param array Tag row 2
	 * @return integer -1 when Tag 1 < Tag 2, 1 when Tag 1 > Tag 2, 0 otherwise
	 */
	function tag_cmp_name( $p_tag1, $p_tag2 ) {
		return strcasecmp( $p_tag1['name'], $p_tag2['name'] );
	}

	/**
	 * Parse a form input string to extract existing and new tags.
	 * When given a string, parses for tag names separated by configured separator,
	 * then returns an array of tag rows for each tag.  Existing tags get the full
	 * row of information returned.  If the tag does not exist, a row is returned with
	 * id = -1 and the tag name, and if the name is invalid, a row is returned with
	 * id = -2 and the tag name.  The resulting array is then sorted by tag name.
	 * @param string Input string to parse
	 * @return array Rows of tags parsed from input string
	 */
	function tag_parse_string( $p_string ) {
		$t_tags = array();

		$t_strings = explode( config_get( 'tag_separator' ), $p_string );
		foreach( $t_strings as $t_name ) {
			$t_name = trim( $t_name );
			if ( is_blank( $t_name ) ) { continue; }
			
			$t_matches = array();
			$t_tag_row = tag_get_by_name( $t_name );
			if ( $t_tag_row !== false ) {
				$t_tags[] = $t_tag_row;
			} else {
				if ( tag_name_is_valid( $t_name, $t_matches ) ) {
					$t_id = -1;
				} else {
					$t_id = -2;
				}
				$t_tags[] = array( 'id' => $t_id, 'name' => $t_name );
			}
		}
		usort( $t_tags, "tag_cmp_name" );
		return $t_tags;
	}

	/**
	 * Parse a filter string to extract existing and new tags.
	 * When given a string, parses for tag names separated by configured separator,
	 * then returns an array of tag rows for each tag.  Existing tags get the full
	 * row of information returned.  If the tag does not exist, a row is returned with
	 * id = -1 and the tag name, and if the name is invalid, a row is returned with
	 * id = -2 and the tag name.  The resulting array is then sorted by tag name.
	 * @param string Filter string to parse
	 * @return array Rows of tags parsed from filter string
	 */
	function tag_parse_filters( $p_string ) {
		$t_tags = array();
		$t_prefix = "[+-]{0,1}";

		$t_strings = explode( config_get( 'tag_separator' ), $p_string );
		foreach( $t_strings as $t_name ) {
			$t_name = trim( $t_name );
			$t_matches = array();

			if ( !is_blank( $t_name ) && tag_name_is_valid( $t_name, $t_matches, $t_prefix ) ) { 
				$t_tag_row = tag_get_by_name( $t_matches[1] );
				if ( $t_tag_row !== false ) {
					$t_filter = substr( $t_name, 0, 1 );

					if ( "+" == $t_filter ) {
						$t_tag_row['filter'] = 1;
					} elseif ( "-" == $t_filter ) {
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
		usort( $t_tags, "tag_cmp_name" );
		return $t_tags;
	}

	# CRUD

	/**
	 * Return a tag row for the given ID.
	 * @param integer Tag ID
	 * @return array Tag row
	 */
	function tag_get( $p_tag_id ) {
		tag_ensure_exists( $p_tag_id );

		$c_tag_id		= db_prepare_int( $p_tag_id );
		
		$t_tag_table	= config_get( 'mantis_tag_table' );

		$query = "SELECT * FROM $t_tag_table
					WHERE id='$c_tag_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			return false;
		}
		$row = db_fetch_array( $result );

		return $row;
	}

	/**
	 * Return a tag row for the given name.
	 * @param string Tag name
	 * @return Tag row
	 */
	function tag_get_by_name( $p_name ) {
		$c_name 		= db_prepare_string( $p_name );

		$t_tag_table	= config_get( 'mantis_tag_table' );

		$query = "SELECT * FROM $t_tag_table
					WHERE ".db_helper_like( 'name', $c_name );
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			return false;
		}
		$row = db_fetch_array( $result );

		return $row;
	}

	/**
	 * Return a single field from a tag row for the given ID.
	 * @param integer Tag ID
	 * @param string Field name
	 * @return mixed Field value
	 */
	function tag_get_field( $p_tag_id, $p_field_name ) {
		$row = tag_get( $p_tag_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			error_parameters( $p_field_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}

	/**
	 * Create a tag with the given name, creator, and description.
	 * Defaults to the currently logged in user, and a blank description.
	 * @param string Tag name
	 * @param integer User ID
	 * @param string Description
	 * @return integer Tag ID
	 */
	function tag_create( $p_name, $p_user_id=null, $p_description='' ) {
		access_ensure_global_level( config_get( 'tag_create_threshold' ) );

		tag_ensure_name_is_valid( $p_name );
		tag_ensure_unique( $p_name );

		if ( null == $p_user_id ) {
			$p_used_id = auth_get_current_user_id();
		} else {
			user_ensure_exists( $p_user_id );
		}

		$c_name			= trim( db_prepare_string( $p_name ) );
		$c_description	= db_prepare_string( $p_description );
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_date_created	= db_now();
		
		$t_tag_table	= config_get( 'mantis_tag_table' );

		$query = "INSERT INTO $t_tag_table
				( user_id, 	
				  name, 
				  description, 
				  date_created, 
				  date_updated 
				)
				VALUES
				( '$c_user_id', 
				  '$c_name', 
				  '$c_description', 
				  ".$c_date_created.", 
				  ".$c_date_created."
				)";

		db_query( $query );
		return db_insert_id( $t_tag_table );
	}

	/**
	 * Update a tag with given name, creator, and description.
	 * @param integer Tag ID
	 * @param string Tag name
	 * @param integer User ID
	 * @param string Description
	 */
	function tag_update( $p_tag_id, $p_name, $p_user_id, $p_description ) {
		user_ensure_exists( $p_user_id );
		
		if ( auth_get_current_user_id() == tag_get_field( $p_tag_id, 'user_id' ) ) {
			$t_update_level = config_get( 'tag_edit_own_threshold' );
		} else {
			$t_update_level = config_get( 'tag_edit_threshold' );
		}

		access_ensure_global_level( $t_update_level );
		
		tag_ensure_name_is_valid( $p_name );

		$t_tag_name = tag_get_field( $p_tag_id, 'name' );

		$t_rename = false;
		if ( strtolower($p_name) != strtolower($t_tag_name) ) {
			tag_ensure_unique( $p_name );
			$t_rename = true;
		}
		
		$c_tag_id		= trim( db_prepare_int( $p_tag_id ) );
		$c_user_id		= db_prepare_string( $p_user_id );
		$c_name			= db_prepare_string( $p_name );
		$c_description	= db_prepare_string( $p_description );
		$c_date_updated	= db_now();

		$t_tag_table	= config_get( 'mantis_tag_table' );

		$query = "UPDATE $t_tag_table
					SET user_id='$c_user_id',
						name='$c_name',
						description='$c_description',
						date_updated=".$c_date_updated."
					WHERE id='$c_tag_id'";
		db_query( $query );

		if ( $t_rename ) {
			$t_bugs = tag_get_bugs_attached( $p_tag_id );

			foreach ( $t_bugs as $t_bug_id ) {
				history_log_event_special( $t_bug_id, TAG_RENAMED, $t_tag_name, $c_name );
			}
		}

		return true;
	}

	/**
	 * Delete a tag with the given ID.
	 * @param integer Tag ID
	 */
	function tag_delete( $p_tag_id ) {
		tag_ensure_exists( $p_tag_id );
		
		access_ensure_global_level( config_get( 'tag_edit_threshold' ) );
		
		$t_bugs = tag_get_bugs_attached( $p_tag_id );
		foreach ( $t_bugs as $t_bug_id ) {
			tag_bug_detach( $p_tag_id, $t_bug_id );
		}
		
		$c_tag_id			= db_prepare_int( $p_tag_id );

		$t_tag_table		= config_get( 'mantis_tag_table' );
		$t_bug_tag_table	= config_get( 'mantis_bug_tag_table' );

		$query = "DELETE FROM $t_tag_table
					WHERE id='$c_tag_id'";
		db_query( $query );

		return true;
	}
	
	# Associative

	/**
	 * Determine if a tag is attached to a bug.
	 * @param integer Tag ID
	 * @param integer Bug ID
	 * @return boolean True if the tag is attached
	 */
	function tag_bug_is_attached( $p_tag_id, $p_bug_id ) {
		$c_tag_id 		= db_prepare_int( $p_tag_id );
		$c_bug_id 		= db_prepare_int( $p_bug_id );

		$t_bug_tag_table= config_get( 'mantis_bug_tag_table' );

		$query = "SELECT * FROM $t_bug_tag_table
					WHERE tag_id='$c_tag_id' AND bug_id='$c_bug_id'";
		$result = db_query( $query );
		return ( db_num_rows( $result ) > 0 );
	}

	/**
	 * Return the tag attachment row.
	 * @param integer Tag ID
	 * @param integer Bug ID
	 * @return array Tag attachment row
	 */
	function tag_bug_get_row( $p_tag_id, $p_bug_id ) {
		$c_tag_id 		= db_prepare_int( $p_tag_id );
		$c_bug_id 		= db_prepare_int( $p_bug_id );

		$t_bug_tag_table= config_get( 'mantis_bug_tag_table' );

		$query = "SELECT * FROM $t_bug_tag_table
					WHERE tag_id='$c_tag_id' AND bug_id='$c_bug_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) == 0 ) {
			trigger_error( TAG_NOT_ATTACHED, ERROR );
		}
		return db_fetch_array( $result );
	}

	/**
	 * Return an array of tags attached to a given bug sorted by tag name.
	 * @param Bug ID
	 * @return array Array of tag rows with attachement information
	 */
	function tag_bug_get_attached( $p_bug_id ) {
		$c_bug_id 		= db_prepare_int( $p_bug_id );

		$t_tag_table	= config_get( 'mantis_tag_table' );
		$t_bug_tag_table= config_get( 'mantis_bug_tag_table' );

		$query = "SELECT t.*, b.user_id as user_attached, b.date_attached
					FROM $t_tag_table as t
					LEFT JOIN $t_bug_tag_table as b
						on t.id=b.tag_id
					WHERE b.bug_id='$c_bug_id'";
		$result = db_query( $query );

		$rows = array();
		while ( $row = db_fetch_array( $result ) ) {
			$rows[] = $row;
		}
		
		usort( $rows, "tag_cmp_name" );
		return $rows;
	}

	/**
	 * Return an array of bugs that a tag is attached to.
	 * @param integer Tag ID
	 * @return array Array of bug ID's.
	 */
	function tag_get_bugs_attached( $p_tag_id ) {
		$c_tag_id 		= db_prepare_int( $p_tag_id );

		$t_bug_tag_table= config_get( 'mantis_bug_tag_table' );

		$query = "SELECT bug_id FROM $t_bug_tag_table
					WHERE tag_id='$c_tag_id'";
		$result = db_query( $query );

		$bugs = array();
		while ( $row = db_fetch_array( $result ) ) {
			$bugs[] = $row['bug_id'];
		}

		return $bugs;
	}

	/**
	 * Attach a tag to a bug.
	 * @param integer Tag ID
	 * @param integer Bug ID
	 * @param integer User ID
	 */
	function tag_bug_attach( $p_tag_id, $p_bug_id, $p_user_id=null ) {
		access_ensure_bug_level( config_get( 'tag_attach_threshold' ), $p_bug_id, $p_user_id );

		tag_ensure_exists( $p_tag_id );

		if ( tag_bug_is_attached( $p_tag_id, $p_bug_id ) ) {
			trigger_error( TAG_ALREADY_ATTACHED, ERROR );
		}

		if ( null == $p_user_id ) {
			$p_used_id = auth_get_current_user_id();
		} else {
			user_ensure_exists( $p_user_id );
		}

		$c_tag_id 		= db_prepare_int( $p_tag_id );
		$c_bug_id 		= db_prepare_int( $p_bug_id );
		$c_user_id	 	= db_prepare_int( $p_user_id );
		$c_date_attached= db_now();

		$t_bug_tag_table= config_get( 'mantis_bug_tag_table' );

		$query = "INSERT INTO $t_bug_tag_table
					( tag_id,
					  bug_id,
					  user_id,
					  date_attached
					)
					VALUES
					( '$c_tag_id',
					  '$c_bug_id',
					  '$c_user_id',
					  ".$c_date_attached."
					)";
		db_query( $query );

		$t_tag_name = tag_get_field( $p_tag_id, 'name' );
		history_log_event_special( $p_bug_id, TAG_ATTACHED, $t_tag_name );

		return true;
	}

	/**
	 * Detach a tag from a bug.
	 * @param integer Tag ID
	 * @param integer Bug ID
	 * @param boolean Add history entries to bug
	 * @param integer User Id (or null for current logged in user)	 
	 */
	function tag_bug_detach( $p_tag_id, $p_bug_id, $p_add_history=true, $p_user_id = null ) {
		if ( $p_user_id === null ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}

		if ( $t_user_id == tag_get_field( $p_tag_id, 'user_id' ) ) {
			$t_detach_level = config_get( 'tag_detach_own_threshold' );
		} else {
			$t_detach_level = config_get( 'tag_detach_threshold' );
		}

		access_ensure_bug_level( config_get( 'tag_detach_threshold' ), $p_bug_id, $t_user_id );

		if ( !tag_bug_is_attached( $p_tag_id, $p_bug_id ) ) {
			trigger_error( TAG_NOT_ATTACHED, ERROR );
		}
		
		$c_tag_id 		= db_prepare_int( $p_tag_id );
		$c_bug_id 		= db_prepare_int( $p_bug_id );

		$t_bug_tag_table= config_get( 'mantis_bug_tag_table' );

		$query = "DELETE FROM $t_bug_tag_table 
					WHERE tag_id='$c_tag_id' AND bug_id='$c_bug_id'";
		db_query( $query );

		if ( $p_add_history ) {
			$t_tag_name = tag_get_field( $p_tag_id, 'name' );
			history_log_event_special( $p_bug_id, TAG_DETACHED, $t_tag_name );
		}

		return true;
	}

	/**
	 * Detach all tags from a given bug.
	 * @param integer Bug ID
	 * @param boolean Add history entries to bug
	 * @param integer User Id (or null for current logged in user)	 
	 */
	function tag_bug_detach_all( $p_bug_id, $p_add_history=true, $p_user_id = null ) {
		$t_tags = tag_bug_get_attached( $p_bug_id );
		foreach ( $t_tags as $t_tag_row ) {
			tag_bug_detach( $t_tag_row['id'], $p_bug_id, $p_add_history, $p_user_id );
		}
	}

	# Display

	/**
	 * Display a tag hyperlink.
	 * If a bug ID is passed, the tag link will include a detach link if the 
	 * user has appropriate privileges.
	 * @param array Tag row
	 * @param integer Bug ID
	 */
	function tag_display_link( $p_tag_row, $p_bug_id=0 ) {
		static $t_security_token = null;
		if ( is_null( $t_security_token ) ) {
			$t_security_token = form_security_param( 'tag_detach' );
		}

		if ( auth_get_current_user_id() == $p_tag_row['user_attached'] ) {
			$t_detach = config_get( 'tag_detach_own_threshold' );
		} else {
			$t_detach = config_get( 'tag_detach_threshold' );
		}
		
		$t_name = string_display_line( $p_tag_row['name'] );
		$t_description = string_display_line( $p_tag_row['description'] );
		
		echo "<a href='tag_view_page.php?tag_id=$p_tag_row[id]' title='$t_description'>$t_name</a>";
		
		if ( access_has_global_level($t_detach) ) {
			$t_tooltip = sprintf( lang_get( 'tag_detach' ), $t_name );
			echo " <a href='tag_detach.php?bug_id=$p_bug_id&tag_id=$p_tag_row[id]$t_security_token'><img src='images/delete.png' class='delete-icon' title=\"$t_tooltip\"/></a>";
		}
		
		return true;
	}

	/**
	 * Display a list of attached tag hyperlinks separated by the configured hyperlinks.
	 * @param Bug ID
	 */
	function tag_display_attached( $p_bug_id ) {
		$t_tag_rows = tag_bug_get_attached( $p_bug_id );

		if ( count( $t_tag_rows ) == 0 ) {
			echo lang_get( 'tag_none_attached' );
		} else {
			$i = 0;
			foreach ( $t_tag_rows as $t_tag ) {
				echo ( $i > 0 ? config_get('tag_separator')." " : "" );
				tag_display_link( $t_tag, $p_bug_id );
				$i++;
			}
		}

		return true;
	}

	# Statistics

	/**
	 * Get the number of bugs a given tag is attached to.
	 * @param integer Tag ID
	 * @return integer Number of attached bugs
	 */
	function tag_stats_attached( $p_tag_id ) {
		$c_tag_id = db_prepare_int( $p_tag_id );
		$t_bug_tag_table = config_get( 'mantis_bug_tag_table' );

		$query = "SELECT COUNT(*) FROM $t_bug_tag_table
					WHERE tag_id='$c_tag_id'";
		$result = db_query( $query );

		return db_result( $result );
	}

	/**
	 * Get a list of related tags.
	 * Returns a list of tags that are the most related to the given tag,
	 * based on the number of times they have been attached to the same bugs.
	 * Defaults to a list of five tags.
	 * @param integer Tag ID
	 * @param integer List size
	 * @return array Array of tag rows, with share count added
	 */
	function tag_stats_related( $p_tag_id, $p_limit=5 ) {
		$t_bug_table = config_get( 'mantis_bug_table' );
		$t_tag_table = config_get( 'mantis_tag_table' );
		$t_bug_tag_table = config_get( 'mantis_bug_tag_table' );
		$t_project_user_list_table = config_get( 'mantis_project_user_list_table' );
		$t_user_table = config_get( 'mantis_user_table' );

		$c_tag_id = db_prepare_int( $p_tag_id );
		$c_user_id = auth_get_current_user_id();

		$subquery = "SELECT b.id FROM $t_bug_table AS b
					LEFT JOIN $t_project_user_list_table AS p
						ON p.project_id=b.project_id AND p.user_id=$c_user_id
					JOIN $t_user_table AS u
						ON u.id=$c_user_id
					JOIN $t_bug_tag_table AS t
						ON t.bug_id=b.id
					WHERE ( p.access_level>b.view_state OR u.access_level>b.view_state )
						AND t.tag_id=$c_tag_id";
					
		$query = "SELECT * FROM $t_bug_tag_table
					WHERE tag_id != $c_tag_id
						AND bug_id IN ( $subquery ) ";

		$result = db_query( $query );

		$t_tag_counts = array();
		while ( $row = db_fetch_array( $result ) ) {
			if ( !isset( $t_tag_counts[$row['tag_id']] ) ) {
				$t_tag_counts[$row['tag_id']] = 1;
			} else {
				$t_tag_counts[$row['tag_id']]++;
			}
		}

		arsort( $t_tag_counts );

		$t_tags = array();
		$i = 1;
		foreach ( $t_tag_counts as $t_tag_id => $t_count ) {
			$t_tag_row = tag_get($t_tag_id);
			$t_tag_row['count'] = $t_count;
			$t_tags[] = $t_tag_row;
			$i++;
			if ( $i > $p_limit ) { break; }
		}

		return $t_tags;
	}
