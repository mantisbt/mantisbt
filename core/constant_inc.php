<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2005  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: constant_inc.php,v 1.52.4.1.6.1 2006-05-07 05:56:22 vboctor Exp $
	# --------------------------------------------------------

	### CONSTANTS  ###

	# --- constants -------------------

	# magic numbers
	define( 'ON',	1 );
	define( 'OFF',	0 );
	define( 'AUTO', 3 );


	# error types
	define( 'ERROR',	E_USER_ERROR );
	define( 'WARNING',	E_USER_WARNING );
	define( 'NOTICE',	E_USER_NOTICE );

	# access levels
	define( 'ANYBODY',			0 );
	define( 'VIEWER',			10 );
	define( 'REPORTER',			25 );
	define( 'UPDATER',			40 );
	define( 'DEVELOPER',		55 );
	define( 'MANAGER',			70 );
	define( 'ADMINISTRATOR',	90 );
	define( 'NOBODY',			100 );

	define( 'DEFAULT_ACCESS_LEVEL', -1); # This is used in add user to project


	# status
	define( 'NEW_',				10 );   # NEW seems to be a reserved keyword
	define( 'FEEDBACK',			20 );
	define( 'ACKNOWLEDGED',		30 );
	define( 'CONFIRMED',		40 );
	define( 'ASSIGNED',			50 );
	define( 'RESOLVED',			80 );
	define( 'CLOSED',			90 );

	# resolution
	define( 'OPEN',					10 );
	define( 'FIXED',				20 );
	define( 'REOPENED',				30 );
	define( 'UNABLE_TO_DUPLICATE',	40 );
	define( 'NOT_FIXABLE',			50 );
	define( 'DUPLICATE',			60 );
	define( 'NOT_A_BUG',			70 );
	define( 'SUSPENDED',			80 );
	define( 'WONT_FIX',				90 );

	# priority
	define( 'NONE',			10 );
	define( 'LOW',			20 );
	define( 'NORMAL',		30 );
	define( 'HIGH',			40 );
	define( 'URGENT',		50 );
	define( 'IMMEDIATE',	60 );

	# severity
	define( 'FEATURE',	10 );
	define( 'TRIVIAL',	20 );
	define( 'TEXT',		30 );
	define( 'TWEAK',	40 );
	define( 'MINOR',	50 );
	define( 'MAJOR',	60 );
	define( 'CRASH',	70 );
	define( 'BLOCK',	80 );

	# project view_state
	define( 'VS_PUBLIC',	10 );
	define( 'VS_PRIVATE',	50 );

	# direction
	define( 'ASC',	101 );
	define( 'DESC',	102 );

	# unread status
	define( 'READ',		201 );
	define( 'UNREAD',	202 );

	# login methods
	define( 'PLAIN',			0 );
	define( 'CRYPT',			1 );
	define( 'CRYPT_FULL_SALT',	2 );
	define( 'MD5',				3 );
	define( 'LDAP',				4 );
	define( 'BASIC_AUTH',		5 );
	define( 'HTTP_AUTH',		6 );

	# file upload methods
	define( 'DISK',			1 );
	define( 'DATABASE',		2 );
	define( 'FTP',			3 );

	# show variable values
	define( 'BOTH',				0 );
	define( 'SIMPLE_ONLY',		1 );
	define( 'ADVANCED_ONLY',	2 );
	define( 'SIMPLE_DEFAULT',	3 );
	define( 'ADVANCED_DEFAULT',	4 );

	# news values
	define( 'BY_LIMIT',		0 );
	define( 'BY_DATE',		1 );

	# all projects
	define( 'ALL_PROJECTS',	0 );

	# all users
	define( 'ALL_USERS',    0 );

	# no user
	define( 'NO_USER',		0 );

	# history constants
	define( 'NORMAL_TYPE',					0 );
	define( 'NEW_BUG',						1 );
	define( 'BUGNOTE_ADDED',				2 );
	define( 'BUGNOTE_UPDATED',				3 );
	define( 'BUGNOTE_DELETED',				4 );
	define( 'DESCRIPTION_UPDATED',			6 );
	define( 'ADDITIONAL_INFO_UPDATED',		7 );
	define( 'STEP_TO_REPRODUCE_UPDATED',	8 );
	define( 'FILE_ADDED',					9 );
	define( 'FILE_DELETED',					10 );
	define( 'BUGNOTE_STATE_CHANGED',		11 );
	define( 'BUG_MONITOR',					12 );
	define( 'BUG_UNMONITOR',				13 );
	define( 'BUG_DELETED',					14 );
	define( 'BUG_ADD_SPONSORSHIP',				15 );
	define( 'BUG_UPDATE_SPONSORSHIP',			16 );
	define( 'BUG_DELETE_SPONSORSHIP',			17 );
	define( 'BUG_ADD_RELATIONSHIP', 		18 );
	define( 'BUG_DEL_RELATIONSHIP', 		19 );
	define( 'BUG_CLONED_TO', 				20 );
	define( 'BUG_CREATED_FROM', 			21 );
	define( 'CHECKIN',				22 );
	define( 'BUG_REPLACE_RELATIONSHIP', 		23 );
	define( 'BUG_PAID_SPONSORSHIP', 		24 );

	# bug relationship constants
	define( 'BUG_DUPLICATE',	0 );
	define( 'BUG_RELATED',		1 );
	define( 'BUG_DEPENDANT',	2 );
	define( 'BUG_BLOCKS', 3 );
	define( 'BUG_HAS_DUPLICATE', 4 );

	# error messages
	define( 'ERROR_GENERIC',						0 );
	define( 'ERROR_SQL',							1 );
	define( 'ERROR_REPORT',							3 );
	define( 'ERROR_NO_FILE_SPECIFIED',				4 );
	define( 'ERROR_FILE_DISALLOWED',				5 );
	define( 'ERROR_NO_DIRECTORY',					6 );
	define( 'ERROR_DUPLICATE_FILE',					9 );
	define( 'ERROR_DUPLICATE_PROJECT',				10 );
	define( 'ERROR_EMPTY_FIELD',					11 );
	define( 'ERROR_PROTECTED_ACCOUNT',				12 );
	define( 'ERROR_ACCESS_DENIED',					13 );
	define( 'ERROR_UPLOAD_FAILURE',					15 );
	define( 'ERROR_FTP_CONNECT_ERROR',				16 );
	define( 'ERROR_HANDLER_ACCESS_TOO_LOW',				17 );
	define( 'ERROR_PAGE_REDIRECTION',				18 );

	# ERROR_CONFIG_*
	define( 'ERROR_CONFIG_OPT_NOT_FOUND',			100 );
	define( 'ERROR_CONFIG_OPT_INVALID',			101 );

	# ERROR_GPC_*
	define( 'ERROR_GPC_VAR_NOT_FOUND',				200 );
	define( 'ERROR_GPC_ARRAY_EXPECTED',				201 );
	define( 'ERROR_GPC_ARRAY_UNEXPECTED',			202 );
	define( 'ERROR_GPC_NOT_NUMBER',			203 );

	# ERROR_LANG_*
	define( 'ERROR_LANG_STRING_NOT_FOUND',			300 );

	# ERROR_DB_*
	define( 'ERROR_DB_CONNECT_FAILED',				400 );
	define( 'ERROR_DB_QUERY_FAILED',				401 );
	define( 'ERROR_DB_SELECT_FAILED',				402 );
	define( 'ERROR_DB_FIELD_NOT_FOUND',				403 );

	# ERROR_FILE_*
	define( 'ERROR_FILE_TOO_BIG',					500 );
	define( 'ERROR_FILE_NOT_ALLOWED',				501 );
	define( 'ERROR_FILE_DUPLICATE',					502 );
	define( 'ERROR_FILE_INVALID_UPLOAD_PATH',		503 );
	define( 'ERROR_FILE_NO_UPLOAD_FAILURE',		    504 );
	define( 'ERROR_FILE_MOVE_FAILED',		    505 );

	# ERROR_BUGNOTE_*
	define( 'ERROR_BUGNOTE_NOT_FOUND',				600 );

	# ERROR_PROJECT_*
	define( 'ERROR_PROJECT_NOT_FOUND',				700 );
	define( 'ERROR_PROJECT_NAME_NOT_UNIQUE',		701 );
	define( 'ERROR_PROJECT_NAME_INVALID',			702 );
	define( 'ERROR_PROJECT_RECURSIVE_HIERARCHY',	703 );

	# ERROR_USER_*
	define( 'ERROR_USER_NAME_NOT_UNIQUE',			800 );
	define( 'ERROR_USER_NOT_FOUND',					801 );
	define( 'ERROR_USER_PREFS_NOT_FOUND',			802 );
	define( 'ERROR_USER_CREATE_PASSWORD_MISMATCH',	803 );
	define( 'ERROR_USER_PROFILE_NOT_FOUND',			804 );
	define( 'ERROR_USER_NAME_INVALID',				805 );
	define( 'ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS',		806 );
	define( 'ERROR_USER_REAL_MATCH_USER',		807 );
	define( 'ERROR_USER_CHANGE_LAST_ADMIN',		808 );

	# ERROR_AUTH_*
	define( 'ERROR_AUTH_INVALID_COOKIE',			900 );

	# ERROR_NEWS_*
	define( 'ERROR_NEWS_NOT_FOUND',					1000 );

	# ERROR_BUG_*
	define( 'ERROR_BUG_NOT_FOUND',					1100 );
	define( 'ERROR_BUG_DUPLICATE_SELF',				1101 );
	define( 'ERROR_BUG_RESOLVED_ACTION_DENIED',			1102 );	// @@@ obsolete, remove after lang files are sync'd
	define( 'ERROR_BUG_READ_ONLY_ACTION_DENIED',			1103 );

	# ERROR_EMAIL_*
	define( 'ERROR_EMAIL_INVALID',					1200 );

	# ERROR_CUSTOM_FIELD_*
	define( 'ERROR_CUSTOM_FIELD_NOT_FOUND',			1300 );
	define( 'ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE',   1301 );
	define( 'ERROR_CUSTOM_FIELD_IN_USE',			1302 );
	define( 'ERROR_CUSTOM_FIELD_INVALID_VALUE',		1303 );
	define( 'ERROR_CUSTOM_FIELD_INVALID_DEFINITION',1304 );

	# ERROR_LDAP_*
	define( 'ERROR_LDAP_AUTH_FAILED',				1400 );
	define( 'ERROR_LDAP_SERVER_CONNECT_FAILED',		1401 );
	define( 'ERROR_LDAP_UPDATE_FAILED',				1402 );
	define( 'ERROR_LDAP_USER_NOT_FOUND',			1403 );

	# ERROR_CATEGORY_*
	define( 'ERROR_CATEGORY_DUPLICATE',				1500 );
	define( 'ERROR_CATEGORY_NO_ACTION',				1501 );
	define( 'ERROR_CATEGORY_NOT_FOUND',				1502 );

	# ERROR_VERSION_*
	define( 'ERROR_VERSION_DUPLICATE',				1600 );
	define( 'ERROR_VERSION_NOT_FOUND',				1601 );

	# ERROR_SPONSORSHIP_*
	define( 'ERROR_SPONSORSHIP_NOT_ENABLED',			1700 );
	define( 'ERROR_SPONSORSHIP_NOT_FOUND',				1701 );
	define( 'ERROR_SPONSORSHIP_AMOUNT_TOO_LOW',			1702 );
	define( 'ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW',	1703 );
	define( 'ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW',	1704 );
	define( 'ERROR_SPONSORSHIP_SPONSOR_NO_EMAIL',	1705 );

	# ERROR RELATIONSHIP
	define( 'ERROR_RELATIONSHIP_ALREADY_EXISTS', 1800 );
	define( 'ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW', 1801 );
	define( 'ERROR_RELATIONSHIP_NOT_FOUND', 1802 );
	define( 'ERROR_RELATIONSHIP_SAME_BUG', 1803 );

	# ERROR_LOST_PASSWORD_*
	define( 'ERROR_LOST_PASSWORD_NOT_ENABLED', 1900 );
	define( 'ERROR_LOST_PASSWORD_CONFIRM_HASH_INVALID', 1901 );
	define( 'ERROR_LOST_PASSWORD_NO_EMAIL_SPECIFIED', 1902 );
	define( 'ERROR_LOST_PASSWORD_NOT_MATCHING_DATA', 1903 );
	define( 'ERROR_SIGNUP_NOT_MATCHING_CAPTCHA', 1904 );
	define( 'ERROR_LOST_PASSWORD_MAX_IN_PROGRESS_ATTEMPTS_REACHED', 1905 );

	# ERROR_FILTER_NOT_FOUND
	define( 'ERROR_FILTER_NOT_FOUND', 2000 );
	define( 'ERROR_FILTER_TOO_OLD', 2001 );

	# Status Legend Position
	define( 'STATUS_LEGEND_POSITION_TOP',		1);
	define( 'STATUS_LEGEND_POSITION_BOTTOM',	2);

	# Filter Position
	define( 'FILTER_POSITION_NONE',				0 );
	define( 'FILTER_POSITION_TOP',				1 );
	define( 'FILTER_POSITION_BOTTOM',			2 );
	define( 'FILTER_POSITION_BOTH',				3 );  // FILTER_POSITION_TOP | FILTER_POSITION_BOTTOM (bitwise OR)

	# Flags for settings E-mail categories
	define( 'EMAIL_CATEGORY_PROJECT_CATEGORY',	1);

	# Custom Field types
	define( 'CUSTOM_FIELD_TYPE_STRING',		0 );
	define( 'CUSTOM_FIELD_TYPE_NUMERIC',	1 );
	define( 'CUSTOM_FIELD_TYPE_FLOAT',		2 );
	define( 'CUSTOM_FIELD_TYPE_ENUM',		3 );
	define( 'CUSTOM_FIELD_TYPE_EMAIL',		4 );
	define( 'CUSTOM_FIELD_TYPE_CHECKBOX',	5 );
	define( 'CUSTOM_FIELD_TYPE_LIST',		6 );
	define( 'CUSTOM_FIELD_TYPE_MULTILIST',	7 );
	define( 'CUSTOM_FIELD_TYPE_DATE',		8 );

	# Meta filter values
	define( 'META_FILTER_MYSELF',	-1 );
	define( 'META_FILTER_NONE',     -2  );
	define( 'META_FILTER_ANY',      0   );

	# Versions
	define( 'VERSION_ALL',		null );
	define( 'VERSION_FUTURE',	0 );
	define( 'VERSION_RELEASED',	1 );

	# Contexts for bug summary
	define( 'SUMMARY_CAPTION', 1 );
	define( 'SUMMARY_FIELD', 2 );
	define( 'SUMMARY_EMAIL', 3 );

	# bugnote types
	define( 'BUGNOTE', 0 );
	define( 'REMINDER', 1 );

	# token types
	define( 'TOKEN_UNKNOWN',	0 );
	define( 'TOKEN_FILTER',		1 );
	define( 'TOKEN_GRAPH',		2 );

	# config types
	define( 'CONFIG_TYPE_INT', 1 );
	define( 'CONFIG_TYPE_STRING', 2 );
	define( 'CONFIG_TYPE_COMPLEX', 3 );

	# Control types for date custom fields.
	define( 'CUSTOM_FIELD_DATE_ANY',		0 ) ;
	define( 'CUSTOM_FIELD_DATE_NONE',		1 ) ;
	define( 'CUSTOM_FIELD_DATE_BETWEEN',	2 ) ;
	define( 'CUSTOM_FIELD_DATE_ONORBEFORE', 3 ) ;
	define( 'CUSTOM_FIELD_DATE_BEFORE',		4 ) ;
	define( 'CUSTOM_FIELD_DATE_ON',			5 ) ;
	define( 'CUSTOM_FIELD_DATE_AFTER',		6 ) ;
	define( 'CUSTOM_FIELD_DATE_ONORAFTER',	7 ) ;

	# system logging
	#  logging levels, can be OR'd together
	define( 'LOG_EMAIL',                    1 );  # all emails sent
	define( 'LOG_EMAIL_RECIPIENT',          2 );  # details of email recipient determination

	# COLUMNS_TARGET_*
	define( 'COLUMNS_TARGET_VIEW_PAGE',   1 );
	define( 'COLUMNS_TARGET_PRINT_PAGE',  2 );
	define( 'COLUMNS_TARGET_CSV_PAGE',    3 );
	
	# sponsorship "paid" values
	define( 'SPONSORSHIP_UNPAID',         0 );
	define( 'SPONSORSHIP_REQUESTED',      1 );
	define( 'SPONSORSHIP_PAID',           2 );
	
?>
