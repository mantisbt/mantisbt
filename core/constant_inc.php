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
 * MantisBT Constants
 *
 */

/**
 * Mantis Version
 */
define( 'MANTIS_VERSION', '2.22.1' );
define( 'FILTER_VERSION', 'v9' );

# --- constants -------------------
# magic numbers
define( 'ON', 1 );
define( 'OFF', 0 );
define( 'AUTO', 3 );

define( 'BAD', 0 );
define( 'GOOD', 1 );
define( 'WARN', 2 );

# PHP-related constants
define( 'PHP_MIN_VERSION', '5.5.0' );
define( 'PHP_CLI', 0 );
define( 'PHP_CGI', 1 );

# installation
define( 'CONFIGURED_PASSWORD', '______' );
define( 'DB_MIN_VERSION_ADODB', '5.20.2' );  # For mssql, oracle and pgsql
define( 'DB_MIN_VERSION_MSSQL', '11.0.0' );  # MS SQL Server 2012
define( 'DB_MIN_VERSION_MYSQL', '5.5.35' );  # See #20431
define( 'DB_MIN_VERSION_PGSQL', '9.2' );     # Earliest supported version as of Nov 2016
define( 'DB_MIN_VERSION_ORACLE', '11.2' );

# error types
define( 'ERROR', E_USER_ERROR );
define( 'WARNING', E_USER_WARNING );
define( 'NOTICE', E_USER_NOTICE );
define( 'DEPRECATED', E_USER_DEPRECATED );

# access levels
define( 'ANYBODY', 0 );
define( 'VIEWER', 10 );
define( 'REPORTER', 25 );
define( 'UPDATER', 40 );
define( 'DEVELOPER', 55 );
define( 'MANAGER', 70 );
define( 'ADMINISTRATOR', 90 );
define( 'NOBODY', 100 );

define( 'DEFAULT_ACCESS_LEVEL', -1 );

# This is used in add user to project
# status
define( 'NEW_', 10 );

# NEW seems to be a reserved keyword
define( 'FEEDBACK', 20 );
define( 'ACKNOWLEDGED', 30 );
define( 'CONFIRMED', 40 );
define( 'ASSIGNED', 50 );
define( 'RESOLVED', 80 );
define( 'CLOSED', 90 );

# resolution
define( 'OPEN', 10 );
define( 'FIXED', 20 );
define( 'REOPENED', 30 );
define( 'UNABLE_TO_REPRODUCE', 40 );
define( 'NOT_FIXABLE', 50 );
define( 'DUPLICATE', 60 );
define( 'NOT_A_BUG', 70 );
define( 'SUSPENDED', 80 );
define( 'WONT_FIX', 90 );

# priority
define( 'NONE', 10 );
define( 'LOW', 20 );
define( 'NORMAL', 30 );
define( 'HIGH', 40 );
define( 'URGENT', 50 );
define( 'IMMEDIATE', 60 );

# severity
define( 'FEATURE', 10 );
define( 'TRIVIAL', 20 );
define( 'TEXT', 30 );
define( 'TWEAK', 40 );
define( 'MINOR', 50 );
define( 'MAJOR', 60 );
define( 'CRASH', 70 );
define( 'BLOCK', 80 );

# reproducibility
define( 'REPRODUCIBILITY_ALWAYS', 10 );
define( 'REPRODUCIBILITY_SOMETIMES', 30 );
define( 'REPRODUCIBILITY_RANDOM', 50 );
define( 'REPRODUCIBILITY_HAVENOTTRIED', 70 );
define( 'REPRODUCIBILITY_UNABLETODUPLICATE', 90 );
define( 'REPRODUCIBILITY_NOTAPPLICABLE', 100 );

# projection
define( 'PROJECTION_NONE', 10 );
define( 'PROJECTION_TWEAK', 30 );
define( 'PROJECTION_MINOR_FIX', 50 );
define( 'PROJECTION_MAJOR_REWORK', 70 );
define( 'PROJECTION_REDESIGN', 90 );

# ETA
define( 'ETA_NONE', 10 );
define( 'ETA_UNDER_ONE_DAY', 20 );
define( 'ETA_TWO_TO_THREE_DAYS', 30 );
define( 'ETA_UNDER_ONE_WEEK', 40 );
define( 'ETA_UNDER_ONE_MONTH', 50 );
define( 'ETA_OVER_ONE_MONTH', 60 );

# project view_state
define( 'VS_PUBLIC', 10 );
define( 'VS_PRIVATE', 50 );

# direction
define( 'ASCENDING', 101 );
define( 'DESCENDING', 102 );

# unread status
define( 'READ', 201 );
define( 'UNREAD', 202 );

# login methods
define( 'PLAIN', 0 );
define( 'CRYPT', 1 );
define( 'CRYPT_FULL_SALT', 2 );
define( 'MD5', 3 );
define( 'LDAP', 4 );
define( 'BASIC_AUTH', 5 );
define( 'HTTP_AUTH', 6 );

# file upload methods
define( 'DISK', 1 );
define( 'DATABASE', 2 );

# show variable values
define( 'BOTH', 0 );
define( 'SIMPLE_ONLY', 1 );
define( 'ADVANCED_ONLY', 2 );
define( 'SIMPLE_DEFAULT', 3 );
define( 'ADVANCED_DEFAULT', 4 );

# news values
define( 'BY_LIMIT', 0 );
define( 'BY_DATE', 1 );

# all projects
define( 'ALL_PROJECTS', 0 );

# all users
define( 'ALL_USERS', 0 );

# no user
define( 'NO_USER', 0 );

# history constants
define( 'NORMAL_TYPE', 0 );
define( 'NEW_BUG', 1 );
define( 'BUGNOTE_ADDED', 2 );
define( 'BUGNOTE_UPDATED', 3 );
define( 'BUGNOTE_DELETED', 4 );
define( 'DESCRIPTION_UPDATED', 6 );
define( 'ADDITIONAL_INFO_UPDATED', 7 );
define( 'STEP_TO_REPRODUCE_UPDATED', 8 );
define( 'FILE_ADDED', 9 );
define( 'FILE_DELETED', 10 );
define( 'BUGNOTE_STATE_CHANGED', 11 );
define( 'BUG_MONITOR', 12 );
define( 'BUG_UNMONITOR', 13 );
define( 'BUG_DELETED', 14 );
define( 'BUG_ADD_SPONSORSHIP', 15 );
define( 'BUG_UPDATE_SPONSORSHIP', 16 );
define( 'BUG_DELETE_SPONSORSHIP', 17 );
define( 'BUG_ADD_RELATIONSHIP', 18 );
define( 'BUG_DEL_RELATIONSHIP', 19 );
define( 'BUG_CLONED_TO', 20 );
define( 'BUG_CREATED_FROM', 21 );
define( 'BUG_REPLACE_RELATIONSHIP', 23 );
define( 'BUG_PAID_SPONSORSHIP', 24 );
define( 'TAG_ATTACHED', 25 );
define( 'TAG_DETACHED', 26 );
define( 'TAG_RENAMED', 27 );
define( 'BUG_REVISION_DROPPED', 28 );
define( 'BUGNOTE_REVISION_DROPPED', 29 );
define( 'PLUGIN_HISTORY', 100 );

# bug revisions
define( 'REV_ANY', 0 );
define( 'REV_DESCRIPTION', 1 );
define( 'REV_STEPS_TO_REPRODUCE', 2 );
define( 'REV_ADDITIONAL_INFO', 3 );
define( 'REV_BUGNOTE', 4 );

# bug relationship constants
define( 'BUG_REL_NONE', -2 );
define( 'BUG_REL_ANY', -1 );
define( 'BUG_DUPLICATE', 0 );
define( 'BUG_RELATED', 1 );
define( 'BUG_DEPENDANT', 2 );
define( 'BUG_BLOCKS', 3 );
define( 'BUG_HAS_DUPLICATE', 4 );

# bug update types
define( 'BUG_UPDATE_TYPE_NORMAL', 'update' );
define( 'BUG_UPDATE_TYPE_ASSIGN', 'assign' );
define( 'BUG_UPDATE_TYPE_CLOSE', 'close' );
define( 'BUG_UPDATE_TYPE_REOPEN', 'reopen' );
define( 'BUG_UPDATE_TYPE_CHANGE_STATUS', 'change_status' );

# confirmation message types
define( 'CONFIRMATION_TYPE_SUCCESS', 0 );
define( 'CONFIRMATION_TYPE_WARNING', 1 );
define( 'CONFIRMATION_TYPE_FAILURE', 2 );

# error messages
define( 'ERROR_PHP', -1 );
define( 'ERROR_GENERIC', 0 );
define( 'ERROR_SQL', 1 );
define( 'ERROR_REPORT', 3 );
define( 'ERROR_NO_FILE_SPECIFIED', 4 );
define( 'ERROR_FILE_DISALLOWED', 5 );
define( 'ERROR_NO_DIRECTORY', 6 );
define( 'ERROR_DUPLICATE_PROJECT', 10 );
define( 'ERROR_EMPTY_FIELD', 11 );
define( 'ERROR_PROTECTED_ACCOUNT', 12 );
define( 'ERROR_ACCESS_DENIED', 13 );
define( 'ERROR_UPLOAD_FAILURE', 15 );
define( 'ERROR_HANDLER_ACCESS_TOO_LOW', 17 );
define( 'ERROR_PAGE_REDIRECTION', 18 );
define( 'ERROR_INVALID_REQUEST_METHOD', 19 );
define( 'ERROR_INVALID_SORT_FIELD', 20 );
define( 'ERROR_INVALID_DATE_FORMAT', 21 );
define( 'ERROR_UPDATING_TIMEZONE', 22 );
define( 'ERROR_DEPRECATED_SUPERSEDED', 23 );
define( 'ERROR_INVALID_RESOLUTION', 24 );
define( 'ERROR_DISPLAY_USER_ERROR_INLINE', 25 );
define( 'ERROR_TYPE_MISMATCH', 26 );
define( 'ERROR_SPAM_SUSPECTED', 27 );
define( 'ERROR_FIELD_TOO_LONG', 28 );
define( 'ERROR_INVALID_FIELD_VALUE', 29 );
define( 'ERROR_LOGFILE_NOT_WRITABLE', 30 );

# ERROR_CONFIG_*
define( 'ERROR_CONFIG_OPT_NOT_FOUND', 100 );
define( 'ERROR_CONFIG_OPT_INVALID', 101 );
define( 'ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB', 102 );
define( 'ERROR_CONFIG_OPT_BAD_SYNTAX', 103 );

# ERROR_GPC_*
define( 'ERROR_GPC_VAR_NOT_FOUND', 200 );
define( 'ERROR_GPC_ARRAY_EXPECTED', 201 );
define( 'ERROR_GPC_ARRAY_UNEXPECTED', 202 );
define( 'ERROR_GPC_NOT_NUMBER', 203 );

# ERROR_LANG_*
define( 'ERROR_LANG_STRING_NOT_FOUND', 300 );

# ERROR_DB_*
define( 'ERROR_DB_CONNECT_FAILED', 400 );
define( 'ERROR_DB_QUERY_FAILED', 401 );
define( 'ERROR_DB_SELECT_FAILED', 402 );
define( 'ERROR_DB_FIELD_NOT_FOUND', 403 );
define( 'ERROR_DB_IDENTIFIER_TOO_LONG', 404 );

# ERROR_FILE_*
define( 'ERROR_FILE_TOO_BIG', 500 );
define( 'ERROR_FILE_NOT_ALLOWED', 501 );
define( 'ERROR_FILE_DUPLICATE', 502 );
define( 'ERROR_FILE_INVALID_UPLOAD_PATH', 503 );
define( 'ERROR_FILE_NO_UPLOAD_FAILURE', 504 );
define( 'ERROR_FILE_MOVE_FAILED', 505 );
define( 'ERROR_FILE_NOT_FOUND', 506 );

# ERROR_BUGNOTE_*
define( 'ERROR_BUGNOTE_NOT_FOUND', 600 );

# ERROR_PROJECT_*
define( 'ERROR_PROJECT_NOT_FOUND', 700 );
define( 'ERROR_PROJECT_NAME_NOT_UNIQUE', 701 );
define( 'ERROR_PROJECT_NAME_INVALID', 702 );
define( 'ERROR_PROJECT_RECURSIVE_HIERARCHY', 703 );
define( 'ERROR_PROJECT_SUBPROJECT_DUPLICATE', 704 );
define( 'ERROR_PROJECT_SUBPROJECT_NOT_FOUND', 705 );
define( 'ERROR_PROJECT_HIERARCHY_DISABLED', 706 );

# ERROR_USER_*
define( 'ERROR_USER_NAME_NOT_UNIQUE', 800 );
define( 'ERROR_USER_PREFS_NOT_FOUND', 802 );
define( 'ERROR_USER_CREATE_PASSWORD_MISMATCH', 803 );
define( 'ERROR_USER_PROFILE_NOT_FOUND', 804 );
define( 'ERROR_USER_NAME_INVALID', 805 );
define( 'ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS', 806 );
define( 'ERROR_USER_CHANGE_LAST_ADMIN', 808 );
define( 'ERROR_USER_REAL_NAME_INVALID', 809 );
define( 'ERROR_USER_BY_NAME_NOT_FOUND', 810 );
define( 'ERROR_USER_BY_ID_NOT_FOUND', 811 );
define( 'ERROR_USER_CURRENT_PASSWORD_MISMATCH', 812 );
define( 'ERROR_USER_EMAIL_NOT_UNIQUE', 813 );
define( 'ERROR_USER_BY_EMAIL_NOT_FOUND', 814 );
define( 'ERROR_USER_BY_REALNAME_NOT_FOUND', 815 );

# ERROR_AUTH_*
define( 'ERROR_AUTH_INVALID_COOKIE', 900 );

# ERROR_NEWS_*
define( 'ERROR_NEWS_NOT_FOUND', 1000 );

# ERROR_BUG_*
define( 'ERROR_BUG_NOT_FOUND', 1100 );
define( 'ERROR_BUG_DUPLICATE_SELF', 1101 );
define( 'ERROR_BUG_READ_ONLY_ACTION_DENIED', 1103 );
define( 'ERROR_BUG_RESOLVE_DEPENDANTS_BLOCKING', 1104 );
define( 'ERROR_BUG_CONFLICTING_EDIT', 1105 );
define( 'ERROR_BUG_REVISION_NOT_FOUND', 1150 );

# ERROR_EMAIL_*
define( 'ERROR_EMAIL_INVALID', 1200 );
define( 'ERROR_EMAIL_DISPOSABLE', 1201 );

# ERROR_CUSTOM_FIELD_*
define( 'ERROR_CUSTOM_FIELD_NOT_FOUND', 1300 );
define( 'ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE', 1301 );
define( 'ERROR_CUSTOM_FIELD_IN_USE', 1302 );
define( 'ERROR_CUSTOM_FIELD_INVALID_VALUE', 1303 );
define( 'ERROR_CUSTOM_FIELD_INVALID_DEFINITION', 1304 );
define( 'ERROR_CUSTOM_FIELD_NOT_LINKED_TO_PROJECT', 1305 );
define( 'ERROR_CUSTOM_FIELD_INVALID_PROPERTY', 1306 );

# ERROR_LDAP_*
define( 'ERROR_LDAP_AUTH_FAILED', 1400 );
define( 'ERROR_LDAP_SERVER_CONNECT_FAILED', 1401 );
define( 'ERROR_LDAP_UPDATE_FAILED', 1402 );
define( 'ERROR_LDAP_USER_NOT_FOUND', 1403 );
define( 'ERROR_LDAP_EXTENSION_NOT_LOADED', 1404 );

# ERROR_CATEGORY_*
define( 'ERROR_CATEGORY_DUPLICATE', 1500 );
define( 'ERROR_CATEGORY_NO_ACTION', 1501 );
define( 'ERROR_CATEGORY_NOT_FOUND', 1502 );
define( 'ERROR_CATEGORY_NOT_FOUND_FOR_PROJECT', 1503 );
define( 'ERROR_CATEGORY_CANNOT_DELETE_DEFAULT', 1504 );
define( 'ERROR_CATEGORY_CANNOT_DELETE_HAS_ISSUES', 1505 );

# ERROR_VERSION_*
define( 'ERROR_VERSION_DUPLICATE', 1600 );
define( 'ERROR_VERSION_NOT_FOUND', 1601 );

# ERROR_SPONSORSHIP_*
define( 'ERROR_SPONSORSHIP_NOT_ENABLED', 1700 );
define( 'ERROR_SPONSORSHIP_NOT_FOUND', 1701 );
define( 'ERROR_SPONSORSHIP_AMOUNT_TOO_LOW', 1702 );
define( 'ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW', 1703 );
define( 'ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW', 1704 );
define( 'ERROR_SPONSORSHIP_SPONSOR_NO_EMAIL', 1705 );

# ERROR RELATIONSHIP
define( 'ERROR_RELATIONSHIP_ALREADY_EXISTS', 1800 );  # TODO: remove once no longer used in lang files
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

# ERROR_FILTER_*
define( 'ERROR_FILTER_NOT_FOUND', 2000 );
define( 'ERROR_FILTER_TOO_OLD', 2001 );

# ERROR_TAG_*
define( 'ERROR_TAG_NOT_FOUND', 2200 );
define( 'ERROR_TAG_DUPLICATE', 2201 );
define( 'ERROR_TAG_NAME_INVALID', 2202 );
define( 'ERROR_TAG_NOT_ATTACHED', 2203 );
define( 'ERROR_TAG_ALREADY_ATTACHED', 2204 );

# ERROR_TOKEN_*
define( 'ERROR_TOKEN_NOT_FOUND', 2300 );

# ERROR_EVENT_*
define( 'ERROR_EVENT_UNDECLARED', 2400 );

# ERROR_PLUGIN *
define( 'ERROR_PLUGIN_NOT_REGISTERED', 2500 );
define( 'ERROR_PLUGIN_ALREADY_INSTALLED', 2501 );
define( 'ERROR_PLUGIN_PAGE_NOT_FOUND', 2502 );
define( 'ERROR_PLUGIN_UPGRADE_FAILED', 2503 );
define( 'ERROR_PLUGIN_INSTALL_FAILED', 2504 );
define( 'ERROR_PLUGIN_UPGRADE_NEEDED', 2505 );
define( 'ERROR_PLUGIN_NOT_LOADED', 2506 );
define( 'ERROR_PLUGIN_INVALID_PAGE', 2507 );
define( 'ERROR_PLUGIN_INVALID_FILE', 2508 );
define( 'ERROR_PLUGIN_FILE_NOT_FOUND', 2509 );
define( 'ERROR_PLUGIN_CLASS_NOT_FOUND', 2510 );
define( 'ERROR_PLUGIN_GENERIC', 2599 );

# ERROR_COLUMNS_*
define( 'ERROR_COLUMNS_DUPLICATE', 2600 );
define( 'ERROR_COLUMNS_INVALID', 2601 );

# ERROR_SESSION_*
define( 'ERROR_SESSION_HANDLER_INVALID', 2700 );
# ERROR_SESSION_HANDLER_INVALID is no longer used in code
# but can't be removed as long as it's used in one of the language files
# for $MANTIS_ERROR[ERROR_SESSION_HANDLER_INVALID]
define( 'ERROR_SESSION_VAR_NOT_FOUND', 2701 );
define( 'ERROR_SESSION_NOT_VALID', 2702 );

# ERROR_FORM_*
define( 'ERROR_FORM_TOKEN_INVALID', 2800 );

# ERROR_CRYPTO_*
define( 'ERROR_CRYPTO_MASTER_SALT_INVALID', 2900 );
define( 'ERROR_CRYPTO_CAN_NOT_GENERATE_STRONG_RANDOMNESS', 2901 );

# ERROR_API_TOKEN_*
define( 'ERROR_API_TOKEN_NAME_NOT_UNIQUE', 3000 );

# Generic position constants
define( 'POSITION_NONE', 0 );
define( 'POSITION_TOP', 1 );
define( 'POSITION_BOTTOM', 2 );
define( 'POSITION_BOTH', 3 ); # POSITION_TOP | POSITION_BOTTOM (bitwise OR)

# Filter Position
define( 'FILTER_POSITION_NONE', POSITION_NONE );
define( 'FILTER_POSITION_TOP', POSITION_TOP );
define( 'FILTER_POSITION_BOTTOM', POSITION_BOTTOM );
define( 'FILTER_POSITION_BOTH', POSITION_BOTH );

# Custom Field types
define( 'CUSTOM_FIELD_TYPE_STRING', 0 );
define( 'CUSTOM_FIELD_TYPE_NUMERIC', 1 );
define( 'CUSTOM_FIELD_TYPE_FLOAT', 2 );
define( 'CUSTOM_FIELD_TYPE_ENUM', 3 );
define( 'CUSTOM_FIELD_TYPE_EMAIL', 4 );
define( 'CUSTOM_FIELD_TYPE_CHECKBOX', 5 );
define( 'CUSTOM_FIELD_TYPE_LIST', 6 );
define( 'CUSTOM_FIELD_TYPE_MULTILIST', 7 );
define( 'CUSTOM_FIELD_TYPE_DATE', 8 );
define( 'CUSTOM_FIELD_TYPE_RADIO', 9 );
define( 'CUSTOM_FIELD_TYPE_TEXTAREA', 10 );

# Meta filter values
define( 'META_FILTER_MYSELF', -1 );
define( 'META_FILTER_NONE', - 2 );
define( 'META_FILTER_CURRENT', - 3 );
define( 'META_FILTER_ANY', 0 );

# Filter view types
define( 'FILTER_VIEW_TYPE_SIMPLE', 'simple' );
define( 'FILTER_VIEW_TYPE_ADVANCED', 'advanced' );

# Custom filter types
define( 'FILTER_TYPE_STRING', 0 );
define( 'FILTER_TYPE_INT', 1 );
define( 'FILTER_TYPE_BOOLEAN', 2 );
define( 'FILTER_TYPE_MULTI_STRING', 3 );
define( 'FILTER_TYPE_MULTI_INT', 4 );

# Filter match types
define( 'FILTER_MATCH_ALL', 0 );
define( 'FILTER_MATCH_ANY', 1 );

# Standard Filters
define( 'FILTER_STANDARD_ANY', 'any' );
define( 'FILTER_STANDARD_ASSIGNED', 'assigned' );
define( 'FILTER_STANDARD_UNASSIGNED', 'unassigned' );
define( 'FILTER_STANDARD_REPORTED', 'reported' );
define( 'FILTER_STANDARD_MONITORED', 'monitored' );

# Filter actions, refactored from view_all_set.php
define( 'FILTER_ACTION_RESET', 0 );
define( 'FILTER_ACTION_PARSE_NEW', 1 );
define( 'FILTER_ACTION_PARSE_ADD', 2 );
define( 'FILTER_ACTION_LOAD', 3 );
define( 'FILTER_ACTION_GENERALIZE', 4 );

# Versions
define( 'VERSION_ALL', null );
define( 'VERSION_FUTURE', false );
define( 'VERSION_RELEASED', true );

# Contexts for bug summary
define( 'SUMMARY_CAPTION', 1 );
define( 'SUMMARY_FIELD', 2 );
define( 'SUMMARY_EMAIL', 3 );

# bugnote types
define( 'BUGNOTE', 0 );
define( 'REMINDER', 1 );
define( 'TIME_TRACKING', 2 );

# token types
define( 'TOKEN_UNKNOWN', 0 );
define( 'TOKEN_FILTER', 1 );
define( 'TOKEN_LAST_VISITED', 3 );
define( 'TOKEN_AUTHENTICATED', 4 );
define( 'TOKEN_COLLAPSE', 5 );
define( 'TOKEN_ACCOUNT_VERIFY', 6 );
define( 'TOKEN_ACCOUNT_ACTIVATION', 7 );
define( 'TOKEN_USER', 1000 );

# Token expiry durations (in seconds)
define( 'TOKEN_EXPIRY', 60 * 60 );
define( 'TOKEN_EXPIRY_LAST_VISITED', 24 * 60 * 60 );
define( 'TOKEN_EXPIRY_AUTHENTICATED', 5 * 60 );
define( 'TOKEN_EXPIRY_COLLAPSE', 365 * 24 * 60 * 60 );
define( 'TOKEN_EXPIRY_ACCOUNT_ACTIVATION', 7 * 24 * 60 * 60 );

# config types
define( 'CONFIG_TYPE_DEFAULT', 0 );
define( 'CONFIG_TYPE_INT', 1 );
define( 'CONFIG_TYPE_STRING', 2 );
define( 'CONFIG_TYPE_COMPLEX', 3 );
define( 'CONFIG_TYPE_FLOAT', 4 );

# Control types for date custom fields.
define( 'CUSTOM_FIELD_DATE_ANY', 0 );
define( 'CUSTOM_FIELD_DATE_NONE', 1 );
define( 'CUSTOM_FIELD_DATE_BETWEEN', 2 );
define( 'CUSTOM_FIELD_DATE_ONORBEFORE', 3 );
define( 'CUSTOM_FIELD_DATE_BEFORE', 4 );
define( 'CUSTOM_FIELD_DATE_ON', 5 );
define( 'CUSTOM_FIELD_DATE_AFTER', 6 );
define( 'CUSTOM_FIELD_DATE_ONORAFTER', 7 );

# custom field types
define( 'CUSTOM_FIELD_TYPE_BUG', 0 );
define( 'CUSTOM_FIELD_TYPE_USER', 1 );
define( 'CUSTOM_FIELD_TYPE_BUGNOTE', 2 );
define( 'CUSTOM_FIELD_TYPE_PROJECT', 3 );
define( 'CUSTOM_FIELD_TYPE_FILE', 4 );

# display types for $g_display_errors
define( 'DISPLAY_ERROR_HALT', 'halt' );
define( 'DISPLAY_ERROR_INLINE', 'inline' );
define( 'DISPLAY_ERROR_NONE', 'none' );

# system logging
# The logging levels can be combined using bitwise operators
define( 'LOG_ALL', ~0 );            # All possible log levels
define( 'LOG_NONE', 0 );            # no logging
define( 'LOG_EMAIL', 1 );           # all emails sent
define( 'LOG_EMAIL_RECIPIENT', 2 ); # details of email recipient determination
define( 'LOG_FILTERING', 4 );       # logging for filtering.
define( 'LOG_AJAX', 8 );            # logging for AJAX
define( 'LOG_LDAP', 16 );           # logging for LDAP
define( 'LOG_DATABASE', 32 );       # logging for Database
define( 'LOG_WEBSERVICE', 64 );     # logging for Web Service Requests
define( 'LOG_EMAIL_VERBOSE', 128 ); # logging for verbose email internals
define( 'LOG_PLUGIN', 256 );        # logging for plugins

# COLUMNS_TARGET_*
define( 'COLUMNS_TARGET_VIEW_PAGE', 1 );
define( 'COLUMNS_TARGET_PRINT_PAGE', 2 );
define( 'COLUMNS_TARGET_CSV_PAGE', 3 );
define( 'COLUMNS_TARGET_EXCEL_PAGE', 4 );

# sponsorship "paid" values
define( 'SPONSORSHIP_UNPAID', 0 );
define( 'SPONSORSHIP_REQUESTED', 1 );
define( 'SPONSORSHIP_PAID', 2 );

# Plugin events
define( 'EVENT_TYPE_DEFAULT', 0 );
define( 'EVENT_TYPE_EXECUTE', 1 );
define( 'EVENT_TYPE_OUTPUT', 2 );
define( 'EVENT_TYPE_CHAIN', 3 );
define( 'EVENT_TYPE_FIRST', 4 );

# Timeline types
define( 'TIMELINE_TARGETTED', 1 );
define( 'TIMELINE_FIXED', 2 );

# PHPMailer Methods
define( 'PHPMAILER_METHOD_MAIL', 0 );
define( 'PHPMAILER_METHOD_SENDMAIL', 1 );
define( 'PHPMAILER_METHOD_SMTP', 2 );

# Binary flag values for $g_email_shutdown_processing
define( 'EMAIL_SHUTDOWN_SKIP', 0 );
define( 'EMAIL_SHUTDOWN_GENERATED', 1 );
define( 'EMAIL_SHUTDOWN_FORCE', 2 );

# Lengths - NOTE: these may represent hard-coded values in db schema and should not be changed.
# Note: 191 comes from a MySQL/InnoDB limitation on Index key size (767 bytes)
# divided by 4 (bytes per unicode char), see #20431:52209 for details.
define( 'DB_FIELD_SIZE_USERNAME', 191 );
define( 'DB_FIELD_SIZE_REALNAME', 255 );
define( 'DB_FIELD_SIZE_PASSWORD', 64 );
define( 'DB_FIELD_SIZE_API_TOKEN_NAME', 128 );
define( 'DB_FIELD_SIZE_HISTORY_VALUE', 255 );

# Maximum size for the user's password when storing it as a hash
define( 'PASSWORD_MAX_SIZE_BEFORE_HASH', 1024 );

define( 'SECONDS_PER_DAY', 86400 );

# Auto-generated link targets
define( 'LINKS_SAME_WINDOW', 1 );
define( 'LINKS_NEW_WINDOW', 2 );

# Auth Related Constants
define( 'API_TOKEN_LENGTH', 32 );

# Obsolete / deprecated constants
# Defined below for backwards-compatibility purposes -- Do not use them
#        Constant                                   # Replaced by
define( 'UNABLE_TO_DUPLICATE', 40 );                # UNABLE_TO_REPRODUCE
define( 'ERROR_BUG_RESOLVED_ACTION_DENIED', 1102 ); # N/A
define( 'LOG_SOAP', 64 );                           # LOG_WEBSERVICE
define( 'FTP', 1 );                                 # DISK
define( 'ERROR_FTP_CONNECT_ERROR', 16 );            # N/A
define( 'ERROR_USER_NOT_FOUND', 801 );
define( 'ERROR_USER_REAL_MATCH_USER', 807 );

# JQuery
# hashes acquired with command 'cat file.js | openssl dgst -sha256 -binary | openssl enc -base64 -A'
define( 'JQUERY_VERSION', '2.2.4' );
define( 'JQUERY_HASH', 'sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=' );

# Bootstrap & FontAwesome
define( 'BOOTSTRAP_VERSION', '3.4.1' );
define( 'BOOTSTRAP_HASH_JS', 'sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd' );
define( 'BOOTSTRAP_HASH_CSS', 'sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu' );
define( 'FONT_AWESOME_VERSION', '4.6.3' );
define( 'FONT_AWESOME_HASH', 'sha384-T8Gy5hrqNKT+hzMclPo118YTQO6cYprQmhrYwIiQ/3axmI1hQomh7Ud2hPOy8SP1' );

# Moment & DateTimePicker
define( 'MOMENT_VERSION', '2.15.2' );
define( 'MOMENT_HASH', 'sha256-K+AZsAFjiBd4piqBmFzaxDsiQiHfREubm1ExNGW1JIA=' );
define( 'DATETIME_PICKER_VERSION', '4.17.47' );
define( 'DATETIME_PICKER_HASH_JS', 'sha256-5YmaxAwMjIpMrVlK84Y/+NjCpKnFYa8bWWBbUHSBGfU=' );
define( 'DATETIME_PICKER_HASH_CSS', 'sha256-yMjaV542P+q1RnH6XByCPDfUFhmOafWbeLPmqKh11zo=' );

# Tyeahead JS
define( 'TYPEAHEAD_VERSION', '1.1.1' );
define( 'TYPEAHEAD_HASH', 'sha256-qZIhMVBV4/crmcmYXNq5ZE5gPRiiPPMKVYbapf5HDBs=' );

# List JS
define( 'LISTJS_VERSION', '1.5.0' );
define( 'LISTJS_HASH', 'sha384-JDmRxRiXkNkskRM5AD4qHRGk9ItwZ9flbqOpsRYs8SOrIRwcMtTGKP2Scnjptzgm' );

# Dropzone JS
define( 'DROPZONE_VERSION', '5.5.0' );
define( 'DROPZONE_HASH', 'sha384-TBYvJK9bRa7Ii3OZh+eaXb5r98Ad36+kotsxTD3tIEa9vgJOQ0lRMfZtWAJxPq4P' );

# Byte Order Markers
define( 'UTF8_BOM', "\xEF\xBB\xBF" );

# Maximum number of bugs that are treated simultaneously in export procedures,
# to keep memory usage under control. Do not exceed 1000 if using Oracle DB.
define( 'EXPORT_BLOCK_SIZE', 500 );

# Maximum "safe" value to be used for integer fields in database.
# Note: mantis ids are defined in schema as "I UNSIGNED", which Adodb maps to
# the closest integer (4 bytes) type available. As some DBs don't support unsigned
# types, 2^31 is a safe limit to be used for all.
define( 'DB_MAX_INT', 2147483647 );

# HTTP Status Codes
define( 'HTTP_STATUS_SUCCESS', 200 );
define( 'HTTP_STATUS_CREATED', 201 );
define( 'HTTP_STATUS_NO_CONTENT', 204 );
define( 'HTTP_STATUS_NOT_MODIFIED', 304 );
define( 'HTTP_STATUS_BAD_REQUEST', 400 );
define( 'HTTP_STATUS_UNAUTHORIZED', 401 );
define( 'HTTP_STATUS_FORBIDDEN', 403 );
define( 'HTTP_STATUS_NOT_FOUND', 404 );
define( 'HTTP_STATUS_CONFLICT', 409 );
define( 'HTTP_STATUS_PRECONDITION_FAILED', 412 );
define( 'HTTP_STATUS_TOO_MANY_REQUESTS', 429 );
define( 'HTTP_STATUS_INTERNAL_SERVER_ERROR', 500 );
define( 'HTTP_STATUS_UNAVAILABLE', 503 );

# HTTP HEADERS
define( 'HEADER_AUTHORIZATION', 'Authorization' );
define( 'HEADER_LOGIN_METHOD', 'X-Mantis-LoginMethod' );
define( 'HEADER_USERNAME', 'X-Mantis-Username' );
define( 'HEADER_VERSION', 'X-Mantis-Version' );
define( 'HEADER_IF_MATCH', 'If-Match' );
define( 'HEADER_IF_NONE_MATCH', 'If-None-Match' );
define( 'HEADER_ETAG', 'ETag' );

# LOGIN METHODS
define( 'LOGIN_METHOD_COOKIE', 'cookie' );
define( 'LOGIN_METHOD_API_TOKEN', 'api-token' );
define( 'LOGIN_METHOD_ANONYMOUS', 'anonymous' );

# AUTH PAGES
define( 'AUTH_PAGE_USERNAME', 'login_page.php' );
define( 'AUTH_PAGE_CREDENTIAL', 'login_password_page.php' );

# SLIM FRAMEWORK ATTRIBUTES
define( 'ATTRIBUTE_FORCE_API_ENABLED', 'force_enable_api' );

# Default parent pages for sub-menu items
define( 'PAGE_CONFIG_DEFAULT', 'adm_permissions_report.php' );

# Configuration management actions (adm_config_report.php)
define( 'MANAGE_CONFIG_ACTION_CREATE', 'create' );
define( 'MANAGE_CONFIG_ACTION_CLONE', 'clone' );
define( 'MANAGE_CONFIG_ACTION_EDIT', 'edit' );
define( 'MANAGE_CONFIG_ACTION_VIEW', 'view' );

# Database functional type identifiers.
define( 'DB_TYPE_UNDEFINED', 0 );
define( 'DB_TYPE_MYSQL', 1 );
define( 'DB_TYPE_PGSQL', 2 );
define( 'DB_TYPE_MSSQL', 3 );
define( 'DB_TYPE_ORACLE', 4 );

# Database special capabilities identifiers
define( 'DB_CAPABILITY_WINDOW_FUNCTIONS', 1 );
