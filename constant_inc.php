<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# CONFIGURATION VARIABLES
	###########################################################################

	########################
	# PHP Constants
	########################

	# Directory separator was introduced in PHP 4.0.6
	if ( !defined( 'DIRECTORY_SEPARATOR' ) ) {
		if (substr(php_uname(), 0, 7) == 'Windows') {
			define('DIRECTORY_SEPARATOR', '\\');
		} else {
			define('DIRECTORY_SEPARATOR', '/');
		}
	}

	########################
	# Mantis Constants
	########################

	# --- constants -------------------

	# magic numbers
	define( 'ON',       1 );
	define( 'OFF',      0 );

	#define( 'SUCCESS', 1 );
	#define( 'NOERROR', 1 );
	#define( 'ERROR',   0 );

	# access levels
	define( 'VIEWER',         10 );
	define( 'REPORTER',       25 );
	define( 'UPDATER',        40 );
	define( 'DEVELOPER',      55 );
	define( 'MANAGER',        70 );
	define( 'ADMINISTRATOR',  90 );

	# status
	define( 'NEW_',          10 );   # NEW seems to be a reserved keyword
	define( 'FEEDBACK',      20 );
	define( 'ACKNOWLEDGED',  30 );
	define( 'CONFIRMED',     40 );
	define( 'ASSIGNED',      50 );
	define( 'RESOLVED',      80 );
	define( 'CLOSED',        90 );

	# resolution
	define( 'OPEN',                 10 );
	define( 'FIXED',                20 );
	define( 'REOPENED',             30 );
	define( 'UNABLE_TO_DUPLICATE',  40 );
	define( 'NOT_FIXABLE',          50 );
	define( 'DUPLICATE',            60 );
	define( 'NOT_A_BUG',            70 );
	define( 'SUSPENDED',            80 );
	define( 'WONT_FIX',             90 );

	# priority
	define( 'NONE',       10 );
	define( 'LOW',        20 );
	define( 'NORMAL',     30 );
	define( 'HIGH',       40 );
	define( 'URGENT',     50 );
	define( 'IMMEDIATE',  60 );

	# severity
	define( 'FEATURE',  10 );
	define( 'TRIVIAL',  20 );
	define( 'TEXT',     30 );
	define( 'TWEAK',    40 );
	define( 'MINOR',    50 );
	define( 'MAJOR',    60 );
	define( 'CRASH',    70 );
	define( 'BLOCK',    80 );

	# project view_state
	define( 'PUBLIC',     10 );
	define( 'PRIVATE',    50 );

	# direction
	define( 'ASC',    101 );
	define( 'DESC',   102 );

	# unread status
	define( 'READ',     201 );
	define( 'UNREAD',   202 );

	# login methods
	define( 'PLAIN',	       0 );
	define( 'CRYPT',	       1 );
	define( 'CRYPT_FULL_SALT', 2 );
	define( 'MD5',		       3 );
	define( 'LDAP',		       4 );
	define( 'BASIC_AUTH',      5 );

	# file upload methods
	define( 'DISK',       1 );
	define( 'DATABASE',   2 );
	define( 'FTP',        3 );

	# show variable values
	define( 'BOTH',             0 );
	define( 'SIMPLE_ONLY',      1 );
	define( 'ADVANCED_ONLY',    2 );

	# news values
	define( 'BY_LIMIT',     0 );
	define( 'BY_DATE',      1 );

	# show email limits
	#define( 'NONE',         0 );
	define( 'ALL',          1 );
	define( 'NO_ANONYMOUS', 2 );
	define( 'ADMIN_ONLY',   3 );

	# history constants
	define( 'NORMAL_TYPE',               0 );
	define( 'NEW_BUG',                   1 );
	define( 'BUGNOTE_ADDED',             2 );
	define( 'BUGNOTE_UPDATED',           3 );
	define( 'BUGNOTE_DELETED',           4 );
	define( 'SUMMARY_UPDATED',           5 );
	define( 'DESCRIPTION_UPDATED',       6 );
	define( 'ADDITIONAL_INFO_UPDATED',   7 );
	define( 'STEP_TO_REPRODUCE_UPDATED', 8 );
	define( 'FILE_ADDED',                9 );
	define( 'FILE_DELETED',             10 );
	define( 'BUGNOTE_STATE_CHANGED',    11 );
	define( 'BUG_MONITOR',              12 );
	define( 'BUG_UNMONITOR',            13 );

	# bug relationship constants
	define( 'BUG_DUPLICATE', 0 );
	define( 'BUG_RELATED',   1 );
	define( 'BUG_DEPENDANT', 2 );

	# error messages
	define( 'ERROR_GENERIC',              0 );
	define( 'ERROR_SQL',                  1 );
	define( 'ERROR_LOGIN',                2 );
	define( 'ERROR_REPORT',               3 );
	define( 'ERROR_NO_FILE_SPECIFIED',    4 );
	define( 'ERROR_FILE_DISALLOWED',      5 );
	define( 'ERROR_NO_DIRECTORY',         6 );
	define( 'ERROR_DUPLICATE_CATEGORY',   7 );
	define( 'ERROR_DUPLICATE_VERSION',    8 );
	define( 'ERROR_DUPLICATE_FILE',       9 );
	define( 'ERROR_DUPLICATE_PROJECT',   10 );
	define( 'ERROR_EMPTY_FIELD',         11 );
	define( 'ERROR_PROTECTED_ACCOUNT',   12 );
	define( 'ERROR_ACCESS_DENIED',       13 );
	define( 'ERROR_COOKIES_DISABLED',    14 );
	define( 'ERROR_UPLOAD_FAILURE',      15 );
	define( 'ERROR_FILE_TOO_BIG',        16 );

	# Status Legend Position
	define( 'STATUS_LEGEND_POSITION_TOP',    1);
	define( 'STATUS_LEGEND_POSITION_BOTTOM', 2);
?>
