<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: news_api.php,v 1.2 2002-08-25 08:14:59 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# News API
	###########################################################################

	# function news_add   ( $p_project_id, $p_poster_id, $p_headline, $p_body );
	# function news_delete( $p_id );
	# function news_update( $p_id, $p_headline, $p_body );
	# function news_select( $p_id );

	# --------------------
	function news_add_query( $p_project_id, $p_poster_id, $p_view_state, $p_announcement, $p_headline, $p_body ) {
		global $g_mantis_news_table;

		$c_project_id	= (integer)$p_project_id;
		$c_poster_id	= (integer)$p_poster_id;
		$c_view_state	= (integer)$p_view_state;

		$c_headline		= string_prepare_text( $p_headline );
		$c_body			= string_prepare_textarea( $p_body );

		if ( 'on' == $p_announcement ) {
			$c_announcement = 1;
		} else {
			$c_announcement = 0;
		}

		# Add item
		$query = "INSERT
				INTO $g_mantis_news_table
	    		( id, project_id, poster_id, date_posted, last_modified,
	    		  view_state, announcement, headline, body )
				VALUES
				( null, '$c_project_id', '$c_poster_id', NOW(), NOW(),
				  '$c_view_state', '$c_announcement', '$c_headline', '$c_body' )";
	    return db_query( $query );
	}
	# --------------------
	# Delete the news entry
	function news_delete_query( $p_id ) {
		global $g_mantis_news_table;

		$c_id = (integer)$p_id;

		$query = "DELETE
				FROM $g_mantis_news_table
	    		WHERE id='$c_id'";
	    return db_query( $query );
	}
	# --------------------
	# Update news item
	function news_update_query( $p_id, $p_view_state, $p_announcement, $p_headline, $p_body, $p_project_id ) {
		global $g_mantis_news_table;

		$c_id			= (integer)$p_id;
		$c_project_id	= (integer)$p_project_id;
		$c_view_state	= (integer)$p_view_state;

		$c_headline		= string_prepare_text( $p_headline );
		$c_body			= string_prepare_textarea( $p_body );

		if ( 'on' == $p_announcement ) {
			$c_announcement = 1;
		} else {
			$c_announcement = 0;
		}

		# Update entry
		$query = "UPDATE $g_mantis_news_table
				SET view_state='$c_view_state',
					announcement='$c_announcement',
					headline='$c_headline',
					body='$c_body',
					project_id='$c_project_id',
					last_modified=NOW()
	    		WHERE id='$c_id'";
	    return db_query( $query );
	}
	# --------------------
	# Selects the news item associated with the specified id
	function news_select_query( $p_id ) {
		global $g_mantis_news_table;

		$c_id = (integer)$p_id;

		$query = "SELECT *, UNIX_TIMESTAMP(date_posted) as date_posted
			FROM $g_mantis_news_table
			WHERE id='$c_id'";
	    $result = db_query( $query );
		return db_fetch_array( $result );
	}
	# --------------------
	# get news count (selected project plus sitewide posts)
	function news_count_query( $p_project_id ) {
		global $g_mantis_news_table;

		$c_project_id = (integer)$p_project_id;

		$query = "SELECT COUNT(*)
				FROM $g_mantis_news_table
				WHERE project_id='$c_project_id' OR project_id='0000000'";
		$result = db_query( $query );
	    return db_result( $result, 0, 0 );
	}
	# --------------------
?>