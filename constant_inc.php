<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# CONFIGURATION VARIABLES
	###########################################################################

	########################
	# Mantis Constants
	########################

	# --- constants -------------------

	# magic numbers
	define( "ON",       1 );
	define( "OFF",      0 );

	#define( "SUCCESS", 1 );
	#define( "NOERROR", 1 );
	#define( "ERROR",   0 );

	# access levels
	define( "VIEWER",         10 );
	define( "REPORTER",       25 );
	define( "UPDATER",        40 );
	define( "DEVELOPER",      55 );
	define( "MANAGER",        70 );
	define( "ADMINISTRATOR",  90 );

	# status
	define( "NEW_",          10 );   # NEW seems to be a reserved keyword
	define( "FEEDBACK",      20 );
	define( "ACKNOWLEDGED",  30 );
	define( "CONFIRMED",     40 );
	define( "ASSIGNED",      50 );
	define( "RESOLVED",      80 );
	define( "CLOSED",        90 );

	# resolution
	define( "OPEN",                 10 );
	define( "FIXED",                20 );
	define( "REOPENED",             30 );
	define( "UNABLE_TO_DUPLICATE",  40 );
	define( "NOT_FIXABLE",          50 );
	define( "DUPLICATE",            60 );
	define( "NOT_A_BUG",            70 );
	define( "SUSPENDED",            80 );
	define( "WONT_FIX",             90 );

	# priority
	define( "NONE",       10 );
	define( "LOW",        20 );
	define( "NORMAL",     30 );
	define( "HIGH",       40 );
	define( "URGENT",     50 );
	define( "IMMEDIATE",  60 );

	# severity
	define( "FEATURE",  10 );
	define( "TRIVIAL",  20 );
	define( "TEXT",     30 );
	define( "TWEAK",    40 );
	define( "MINOR",    50 );
	define( "MAJOR",    60 );
	define( "CRASH",    70 );
	define( "BLOCK",    80 );

	# project view_state
	define( "PUBLIC",     10 );
	define( "PRIVATE",    50 );

	# direction
	define( "ASC",    101 );
	define( "DESC",   102 );

	# unread status
	define( "READ",     201 );
	define( "UNREAD",   202 );

	# login methods
	define( "PLAIN",	  0 );
	define( "CRYPT",	  1 );
	define( "MD5",		  2 );
	define( "LDAP",		  3 );
	define( "BASIC_AUTH", 4 );

	# file upload methods
	define( "DISK",       1 );
	define( "DATABASE",   2 );

	# show variable values
	define( "BOTH",             0 );
	define( "SIMPLE_ONLY",      1 );
	define( "ADVANCED_ONLY",    2 );

	# news values
	define( "BY_LIMIT",     0 );
	define( "BY_DATE",      1 );

	# error messages
	define( "ERROR_GENERIC",              0 );
	define( "ERROR_SQL",                  1 );
	define( "ERROR_LOGIN",                2 );
	define( "ERROR_REPORT",               3 );
	define( "ERROR_NO_FILE_SPECIFIED",    4 );
	define( "ERROR_NO_DIRECTORY",         5 );
	define( "ERROR_DUPLICATE_CATEGORY",   6 );
	define( "ERROR_DUPLICATE_VERSION",    7 );
	define( "ERROR_DUPLICATE_FILE",       8 );
	define( "ERROR_EMPTY_FIELD",          9 );
	define( "ERROR_PROTECTED_ACCOUNT",   10 );
?>