<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: constant_inc.php,v 1.9 2004-02-03 13:06:14 vboctor Exp $
	# --------------------------------------------------------

	###########################################################################
	# CONFIGURATION VARIABLES
	###########################################################################


	########################
	# Mantis Constants
	########################

	# --- constants -------------------

	# magic numbers
	define( 'ON',		1 );
	define( 'OFF',		0 );

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

	# file upload methods
	define( 'DISK',			1 );
	define( 'DATABASE',		2 );
	define( 'FTP',			3 );

	# show variable values
	define( 'BOTH',				0 );
	define( 'SIMPLE_ONLY',		1 );
	define( 'ADVANCED_ONLY',	2 );

	# news values
	define( 'BY_LIMIT',		0 );
	define( 'BY_DATE',		1 );

	# all projects
	define( 'ALL_PROJECTS',	0 );

	# no user
	define( 'NO_USER',		0 );

	# history constants
	define( 'NORMAL_TYPE',					0 );
	define( 'NEW_BUG',						1 );
	define( 'BUGNOTE_ADDED',				2 );
	define( 'BUGNOTE_UPDATED',				3 );
	define( 'BUGNOTE_DELETED',				4 );
	define( 'SUMMARY_UPDATED',				5 );
	define( 'DESCRIPTION_UPDATED',			6 );
	define( 'ADDITIONAL_INFO_UPDATED',		7 );
	define( 'STEP_TO_REPRODUCE_UPDATED',	8 );
	define( 'FILE_ADDED',					9 );
	define( 'FILE_DELETED',					10 );
	define( 'BUGNOTE_STATE_CHANGED',		11 );
	define( 'BUG_MONITOR',					12 );
	define( 'BUG_UNMONITOR',				13 );
	define( 'BUG_DELETED',					14 );

	# bug relationship constants
	define( 'BUG_DUPLICATE',	0 );
	define( 'BUG_RELATED',		1 );
	define( 'BUG_DEPENDANT',	2 );

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

	# ERROR_CONFIG_*
	define( 'ERROR_CONFIG_OPT_NOT_FOUND',			100 );

	# ERROR_GPC_*
	define( 'ERROR_GPC_VAR_NOT_FOUND',				200 );
	define( 'ERROR_GPC_ARRAY_EXPECTED',				201 );
	define( 'ERROR_GPC_ARRAY_UNEXPECTED',			202 );

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
	define( 'ERROR_FILE_INVALID_UPLOAD_PATH',			503 );

	# ERROR_BUGNOTE_*
	define( 'ERROR_BUGNOTE_NOT_FOUND',				600 );

	# ERROR_PROJECT_*
	define( 'ERROR_PROJECT_NOT_FOUND',				700 );
	define( 'ERROR_PROJECT_NAME_NOT_UNIQUE',		701 );
	define( 'ERROR_PROJECT_NAME_INVALID',			702 );

	# ERROR_USER_*
	define( 'ERROR_USER_NAME_NOT_UNIQUE',			800 );
	define( 'ERROR_USER_NOT_FOUND',					801 );
	define( 'ERROR_USER_PREFS_NOT_FOUND',			802 );
	define( 'ERROR_USER_CREATE_PASSWORD_MISMATCH',	803 );
	define( 'ERROR_USER_PROFILE_NOT_FOUND',			804 );
	define( 'ERROR_USER_NAME_INVALID',				805 );

	# ERROR_AUTH_*
	define( 'ERROR_AUTH_INVALID_COOKIE',			900 );

	# ERROR_NEWS_*
	define( 'ERROR_NEWS_NOT_FOUND',					1000 );

	# ERROR_BUG_*
	define( 'ERROR_BUG_NOT_FOUND',					1100 );
	define( 'ERROR_BUG_DUPLICATE_SELF',				1101 );
	define( 'ERROR_BUG_RESOLVED_ACTION_DENIED',		1101 );

	# ERROR_EMAIL_*
	define( 'ERROR_EMAIL_INVALID',					1200 );

	# ERROR_CUSTOM_FIELD_*
	define( 'ERROR_CUSTOM_FIELD_NOT_FOUND',			1300 );
	define( 'ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE',   1301 );
	define( 'ERROR_CUSTOM_FIELD_IN_USE',			1302 );
	define( 'ERROR_CUSTOM_FIELD_INVALID_VALUE',		1303 );
	define( 'ERROR_CUSTOM_FIELD_INVALID_DEFINITION',	1304 );

	# ERROR_LDAP_*
	define( 'ERROR_LDAP_AUTH_FAILED',				1400 );
	define( 'ERROR_LDAP_SERVER_CONNECT_FAILED',		1401 );
	define( 'ERROR_LDAP_UPDATE_FAILED',				1402 );
	define( 'ERROR_LDAP_USER_NOT_FOUND',			1402 );
	
	# ERROR_CATEGORY_*
	define( 'ERROR_CATEGORY_DUPLICATE',				1500 );
	define( 'ERROR_CATEGORY_NO_ACTION',				1501 );
	define( 'ERROR_CATEGORY_NOT_FOUND',				1502 );

	# ERROR_VERSION_*
	define( 'ERROR_VERSION_DUPLICATE',				1600 );
	define( 'ERROR_VERSION_NOT_FOUND',				1601 );

	# Status Legend Position
	define( 'STATUS_LEGEND_POSITION_TOP',		1);
	define( 'STATUS_LEGEND_POSITION_BOTTOM',	2);

	# Flags for settings E-mail categories
	define( 'EMAIL_CATEGORY_PROJECT_CATEGORY',	1);

	# Custom Field types
	define( 'CUSTOM_FIELD_TYPE_STRING',   0 );
	define( 'CUSTOM_FIELD_TYPE_NUMERIC',  1 );
	define( 'CUSTOM_FIELD_TYPE_FLOAT',    2 );
	define( 'CUSTOM_FIELD_TYPE_ENUM',     3 );
	define( 'CUSTOM_FIELD_TYPE_EMAIL',    4 );
?>
