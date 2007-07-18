<?php
	# MantisConnect - A webservice interface to Mantis Bug Tracker
	# Copyright (C) 2004-2007  Victor Boctor - vboctor@users.sourceforge.net
	# This program is distributed under dual licensing.  These include
	# GPL and a commercial licenses.  Victor Boctor reserves the right to
	# change the license of future releases.
	# See docs/ folder for more details

	# --------------------------------------------------------
	# $Id: mc_compat_api.php,v 1.1 2007-07-18 06:52:49 vboctor Exp $
	# --------------------------------------------------------


	############################
	# Added in Mantis 1.0.0
	############################

	# --------------------
	# bugnote_add, didn't allow specifying the author name.  It also checked the project access level rather than
	# the bug access level.
	function mci_bugnote_add( $p_bug_id, $p_bugnote_text, $p_private = false, $p_type = 0, $p_attr = '', $p_user_id = null ) {
		$c_bug_id         = db_prepare_int( $p_bug_id );
		$c_bugnote_text   = db_prepare_string( $p_bugnote_text );
		$c_private        = db_prepare_bool( $p_private );
		$c_type           = db_prepare_int( $p_type );
		$c_attr      	  = db_prepare_string( $p_attr );

		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_bugnote_table     	= config_get( 'mantis_bugnote_table' );

		# insert bugnote text
		$query = "INSERT INTO $t_bugnote_text_table
		          		( note )
		          	 VALUES
		          		( '$c_bugnote_text' )";
		db_query( $query );

		# retrieve bugnote text id number
		$t_bugnote_text_id = db_insert_id( $t_bugnote_text_table );

		# get user information
		if ( $p_user_id === null ) {
			$c_user_id = auth_get_current_user_id();
		} else {
			$c_user_id = db_prepare_int( $p_user_id );
		}

		# Check for private bugnotes.
		# @@@ VB: Should we allow users to report private bugnotes, and possibly see only their own private ones
		if ( $p_private && access_has_bug_level( config_get( 'private_bugnote_threshold' ), $p_bug_id, $c_user_id ) ) {
			$t_view_state = VS_PRIVATE;
		} else {
			$t_view_state = VS_PUBLIC;
		}

		# insert bugnote info
		$query = "INSERT INTO $t_bugnote_table
		          		(bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified, note_type, note_attr )
		          	 VALUES
		          		('$c_bug_id', '$c_user_id','$t_bugnote_text_id', '$t_view_state', " . db_now() . "," . db_now() . ", '$c_type', '$c_attr')";
		db_query( $query );

		# get bugnote id
		$t_bugnote_id = db_insert_id( $t_bugnote_table );

		# update bug last updated
		bug_update_date( $p_bug_id );

		# log new bug
		history_log_event_special( $p_bug_id, BUGNOTE_ADDED, bugnote_format_id( $t_bugnote_id ) );

		return $t_bugnote_id;
	}

	############################
	# Added in Mantis 1.0.0a3
	############################

	# This function is implemented in Mantis 1.0.0a3.
	if ( !function_exists( 'bug_get_attachments' ) ) {
		/**
		 * Get array of attachments associated with the specified bug id.  The array will be
		 * sorted in terms of date added (ASC).  The array will include the following fields:
		 * id, title, diskfile, filename, filesize, file_type, date_added.
		 */
		function bug_get_attachments( $p_issue_id ) {
			if ( !file_can_view_bug_attachments( $p_issue_id ) ) {
				return;
			}

			$c_bug_id = db_prepare_int( $p_issue_id );

			$t_bug_file_table = config_get( 'mantis_bug_file_table' );

			$query = "SELECT id, title, diskfile, filename, filesize, file_type, date_added
							FROM $t_bug_file_table
							WHERE bug_id='$c_bug_id'
							ORDER BY date_added";
			$db_result = db_query( $query );
			$num_notes = db_num_rows( $db_result );

			$t_result = array();

			for ( $i = 0; $i < $num_notes; $i++ ) {
				$t_result[] = db_fetch_array( $db_result );
			}

			return $t_result;
		}
	}


	############################
	# Added in Mantis 1.0.0a1
	############################


	# This class was defined in 1.0.0a1
	if ( !class_exists( 'BugnoteData' ) ) {
        class BugnoteData {
                var $id;
                var $bug_id;
                var $reporter_id;
                var $note;
                var $view_state;
                var $date_submitted;
                var $last_modified;
                var $note_type;
                var $note_attr;
        }
	}


	# This function was added in 1.0.0a1
	if ( !function_exists( 'bugnote_get_all_visible_bugnotes' ) ) {
        function bugnote_get_all_visible_bugnotes( $p_bug_id, $p_user_access_level, $p_user_bugnote_order, $p_user_bugnote_limit ) {
                $t_all_bugnotes                 = bugnote_get_all_bugnotes( $p_bug_id, $p_user_bugnote_order, $p_user_bugnote_limit );
                $t_private_bugnote_threshold    = config_get( 'private_bugnote_threshold' );

                if ( $p_user_access_level >= $t_private_bugnote_threshold ) {
                        $t_private_bugnote_visible = true;
                } else {
                        $t_private_bugnote_visible = false ;
                }

                $t_bugnotes = array();
                foreach ( $t_all_bugnotes as $t_note_index => $t_bugnote ) {
                        if ( $t_private_bugnote_visible || ( VS_PUBLIC == $t_bugnote->view_state ) ) {
                                $t_bugnotes[$t_note_index] = $t_bugnote;
                        }
                }

                return $t_bugnotes;
        }
    }


	# This function was implemented in 1.0.0a1
	if ( !function_exists( 'bugnote_get_all_bugnotes' ) ) {
        function bugnote_get_all_bugnotes( $p_bug_id, $p_user_bugnote_order, $p_user_bugnote_limit ) {
            $c_bug_id               = db_prepare_int( $p_bug_id );
            $t_bugnote_table        = config_get( 'mantis_bugnote_table' );
            $t_bugnote_text_table   = config_get( 'mantis_bugnote_text_table' );
            if ( 0 == $p_user_bugnote_limit ) {
                    ## Show all bugnotes
                    $t_bugnote_limit = -1;
                    $t_bugnote_offset = -1;
            } else {
                    ## Use offset only if order is ASC to get the last bugnotes
                    if ( 'ASC' == $p_user_bugnote_order ) {
                            $result = db_query( "SELECT COUNT(*) AS row_count FROM $t_bugnote_table WHERE bug_id = '$c_bug_id'" );
                            $row    = db_fetch_array( $result );

                            $t_bugnote_offset = $row['row_count'] - $p_user_bugnote_limit;
                    } else {
                            $t_bugnote_offset = -1;
                    }

                    $t_bugnote_limit = $p_user_bugnote_limit;
            }

            $query = "SELECT b.*, t.note
                            FROM      $t_bugnote_table AS b
                            LEFT JOIN $t_bugnote_text_table AS t ON b.bugnote_text_id = t.id
                            WHERE b.bug_id = '$c_bug_id'
                            ORDER BY b.date_submitted $p_user_bugnote_order";
            $t_bugnotes = array();

            # BUILD bugnotes array
            $result = db_query( $query, $t_bugnote_limit, $t_bugnote_offset );
            $count  = db_num_rows( $result );
            for ( $i=0; $i < $count; $i++ ) {
                    $row = db_fetch_array( $result );

                    $t_bugnote = new BugnoteData;

                    $t_bugnote->id            = $row['id'];
                    $t_bugnote->bug_id        = $row['bug_id'];
                    $t_bugnote->note          = $row['note'];
                    $t_bugnote->view_state    = $row['view_state'];
                    $t_bugnote->reporter_id   = $row['reporter_id'];
                    $t_bugnote->date_submitted = db_unixtimestamp( $row['date_submitted'] );
                    $t_bugnote->last_modified = db_unixtimestamp( $row['last_modified'] );
                    $t_bugnote->note_type     = $row['note_type'];
                    $t_bugnote->note_attr     = $row['note_attr'];

                    $t_bugnotes[] = $t_bugnote;
            }

            return $t_bugnotes;
        }
    }


		/**
		 * Enhanced version of version_get_all_rows from Mantis'
		 * core/version_api.php. Should be removed once it gets into Mantis.
		 *
		 * FIXED IN Mantis 1.0.0a1.
		 */
        function mci_version_get_all_rows( $p_project_id, $p_released = null ) {
                $c_project_id = db_prepare_int( $p_project_id );

                if ( $p_released === null ) {
                        $t_released_where = '';
                } else {
                        $c_released = db_prepare_int( $p_released );
                        $t_released_where = "AND ( released = $c_released )";
                }

                $t_project_version_table = config_get( 'mantis_project_version_table' );

                $query = "SELECT *
                                  FROM $t_project_version_table
                                  WHERE project_id='$c_project_id' $t_released_where
                                  ORDER BY date_order DESC";
                $result = db_query( $query );
                $count = db_num_rows( $result );
                $rows = array();
                for ( $i = 0 ; $i < $count ; $i++ ) {
                        $row = db_fetch_array( $result );
                        $rows[] = $row;
                }
                return $rows;
        }


	############################
	# Added in Mantis 0.19.1
	############################


	if ( !function_exists( 'project_get_id_by_name' ) ) {
		# --------------------
		# Get the id of the project with the specified name.
		# "project_get_id" was added to Mantis 0.19.1-CVS.
		function project_get_id_by_name( $p_project_name ) {
			$c_project_name = db_prepare_string( $p_project_name );

			$t_project_table = config_get( 'mantis_project_table' );

			$query = "SELECT id FROM $t_project_table WHERE name = '$c_project_name'";
			$t_result = db_query( $query, 1 );

			if ( db_num_rows( $t_result ) == 0 ) {
				return 0;
			} else {
				return db_result( $t_result );
			}
		}
	}

	# --------------------
	# In 0.19.0 this api has no parameters in Mantis, in 0.19.1-cvs, I changed it to the current implementation.
	function mci_filter_db_get_available_queries( $p_project_id = null, $p_user_id = null ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$t_overall_query_arr = array();

		if ( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = $p_project_id;
		}

		if ( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}

		# If the user doesn't have access rights to stored queries, just return
		if ( !access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) {
			return $t_overall_query_arr;
		}

		# Get the list of available queries. By sorting such that public queries are
		# first, we can override any query that has the same name as a private query
		# with that private one
		$query = "SELECT * FROM $t_filters_table
					WHERE (project_id='$t_project_id'
					OR project_id='0')
					AND name!=''
					AND filter_string!=''
					ORDER BY is_public DESC, name ASC";
		$result = db_query( $query );
		$query_count = db_num_rows( $result );

		for ( $i = 0; $i < $query_count; $i++ ) {
			$row = db_fetch_array( $result );
			if ( ( $row['user_id'] == $t_user_id ) || db_prepare_bool( $row['is_public'] ) ) {
				$t_overall_query_arr[$row['name']] = $row;
			}
		}

		return array_values( $t_overall_query_arr );
	}
?>
