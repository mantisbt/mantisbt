<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: csv_api.php,v 1.4 2004-07-16 23:03:09 vboctor Exp $
	# --------------------------------------------------------

	### CSV API ###

	# --------------------
	# get the csv file new line, can be moved to config in the future
	function csv_get_newline() {
		return "\r\n";
	}

	# --------------------
	# get the csv file separator, can be moved to config in the future
	function csv_get_separator() {
		return ',';
	}

	# --------------------
	# if all projects selected, default to <username>.csv, otherwise default to
	# <projectname>.csv.
	function csv_get_default_filename() {
		$t_current_project_id = helper_get_current_project();

		if ( ALL_PROJECTS == $t_current_project_id ) {
			$t_filename = user_get_name( auth_get_current_user_id() );
		} else {
			$t_filename = project_get_field( $t_current_project_id, 'name' );
		}

		return $t_filename . '.csv';
	}

	# --------------------
	# escape a string before writing it to csv file.
	function csv_escape_string( $p_str ) {
		if ( strpos( $p_str, csv_get_separator() ) !== false ) {
			$p_str = '"' . str_replace( '"', '""', $p_str ) . '"';
		}

		return $p_str;
	}

	# --------------------
	# Identified which fields to include, in which order, and the string to
	# pass to lang_get() to retrieve the title.
	# array ( 'column_internal_name' => 'lang str for column title', ... )
	function csv_get_columns() {
		# @@@ Support configuration in the future
	        $t_columns = array(	'id' => 'id',
								'project_id' => 'email_project',
								'reporter_id' => 'reporter',
								'handler_id' => 'assigned_to',
								'priority' => 'priority',
								'severity' => 'severity',
								'reproducibility' => 'reproducibility',
								'version' => 'version',
								'projection' => 'projection',
								'category' => 'category',
								'date_submitted' => 'date_submitted',
								'eta' => 'eta',
								'os' => 'os',
								'os_build' => 'os_version',
								'platform' => 'platform',
								'view_state' => 'view_status',
								'last_updated' => 'last_update',
								'summary' => 'summary',
								'status' => 'status',
								'resolution' => 'resolution',
								'fixed_in_version' => 'fixed_in_version' );

		if ( OFF == config_get( 'enable_relationship' ) ) {
			$t_columns['duplicate_id'] = 'duplicate_id';
		} # MASC RELATIONSHIP

		return $t_columns;
	}

	#
	# Formatting Functions
	#
	# Names for formatting functions are csv_format_*, where * corresponds to the
	# field name as return get csv_get_columns() and by the filter api.
	#

	# --------------------
	# format bug id
	function csv_format_id( $p_bug_id ) {
		return bug_format_id( $p_bug_id );
	}

	# --------------------
	# returns the project name corresponding to the supplied project id.
	function csv_format_project_id( $p_project_id ) {
		return csv_escape_string( project_get_name( $p_project_id ) );
	}

	# --------------------
	# returns the reporter name corresponding to the supplied id.
	function csv_format_reporter_id( $p_reporter_id ) {
		return csv_escape_string( user_get_name( $p_reporter_id ) );
	}

	# --------------------
	# returns the handler name corresponding to the supplied id
	function csv_format_handler_id( $p_handler_id ) {
		return csv_escape_string( user_get_name( $p_handler_id ) );
	}

	# --------------------
	# return the priority string
	function csv_format_priority( $p_priority ) {
		return csv_escape_string( get_enum_element( 'priority', $p_priority ) );
	}

	# --------------------
	# return the severity string
	function csv_format_severity( $p_severity ) {
		return csv_escape_string( get_enum_element( 'severity', $p_severity ) );
	}

	# --------------------
	# return the reproducability string
	function csv_format_reproducibility( $p_reproducibility ) {
		return csv_escape_string( get_enum_element( 'reproducibility', $p_reproducibility ) );
	}

	# --------------------
	# return the version
	function csv_format_version( $p_version ) {
		return csv_escape_string( $p_version );
	}

	# --------------------
	# return the fixed_in_version
	function csv_format_fixed_in_version( $p_fixed_in_version ) {
		return csv_escape_string( $p_fixed_in_version );
	}

	# --------------------
	# return the projection
	function csv_format_projection( $p_projection ) {
		return csv_escape_string( get_enum_element( 'projection', $p_projection ) );
	}

	# --------------------
	# return the category
	function csv_format_category( $p_category ) {
		return csv_escape_string( $p_category );
	}

	# --------------------
	# return the date submitted
	function csv_format_date_submitted( $p_date_submitted ) {
		return date( config_get( 'short_date_format' ), $p_date_submitted );
	}

	# --------------------
	# return the eta
	function csv_format_eta( $p_eta ) {
		return csv_escape_string( get_enum_element( 'eta', $p_eta ) );
	}

	# --------------------
	# return the operating system
	function csv_format_os( $p_os ) {
		return csv_escape_string( $p_os );
	}

	# --------------------
	# return the os build (os version)
	function csv_format_os_build( $p_os_build ) {
		return csv_escape_string( $p_os_build );
	}

	# --------------------
	# return the platform
	function csv_format_platform( $p_platform ) {
		return csv_escape_string( $p_platform );
	}

	# --------------------
	# return the view state (eg: private / public)
	function csv_format_view_state( $p_view_state ) {
		return csv_escape_string( get_enum_element( 'view_state', $p_view_state ) );
	}

	# --------------------
	# return the last updated date
	function csv_format_last_updated( $p_last_updated ) {
		return date( config_get( 'short_date_format' ), $p_last_updated );
	}

	# --------------------
	# return the summary
	function csv_format_summary( $p_summary ) {
		return csv_escape_string( $p_summary );
	}

	# --------------------
	# return the status string
	function csv_format_status( $p_status ) {
		return csv_escape_string( get_enum_element( 'status', $p_status ) );
	}

	# --------------------
	# return the resolution string
	function csv_format_resolution( $p_resolution ) {
		return csv_escape_string( get_enum_element( 'resolution', $p_resolution ) );
	}

	# --------------------
	# return the duplicate bug id
	function csv_format_duplicate_id( $p_duplicate_id ) {
		return bug_format_id( $p_duplicate_id );
	}
?>
