<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Date API
	###########################################################################
	### --------------------
	# converts the mysql TIMESTAMP field to a unix timestamp
	# it may be possible to replace this with a UNIX_TIMESTAMP() call in the SQL query
	function sql_to_unix_time( $p_timeString ) {
		return mktime( substr( $p_timeString, 8, 2 ),
					   substr( $p_timeString, 10, 2 ),
					   substr( $p_timeString, 12, 2 ),
					   substr( $p_timeString, 4, 2 ),
					   substr( $p_timeString, 6, 2 ),
					   substr( $p_timeString, 0, 4 ) );
	}
	### --------------------
	# prints the date given the formating string
	function print_date( $p_format, $p_date ) {
		echo date( $p_format, $p_date );
	}
	### --------------------
	###########################################################################
	### END                                                                 ###
	###########################################################################
?>