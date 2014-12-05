<?php
# MantisBT - a php based bugtracking system

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

define( 'MANTIS_VERSION', '1.2.18' );

# --- constants -------------------
# magic numbers
define( 'ON', 1 );
define( 'OFF', 0 );
define( 'AUTO', 3 );

define( 'BAD', 0 );
define( 'GOOD', 1 );
define( 'WARN', 2 );

# PHP-related constants
define( 'PHP_CLI', 0 );
define( 'PHP_CGI', 1 );

# installation
define( 'CONFIGURED_PASSWORD', "______" );

# error types
define( 'ERROR', E_USER_ERROR );
define( 'WARNING', E_USER_WARNING );
define( 'NOTICE', E_USER_NOTICE );

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
define( 'UNABLE_TO_DUPLICATE', 40 );
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
define( 'FTP', 3 );

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
define( 'CHECKIN', 22 );
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

# error messages
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
define( 'ERROR_FTP_CONNECT_ERROR', 16 );
define( 'ERROR_HANDLER_ACCESS_TOO_LOW', 17 );
define( 'ERROR_PAGE_REDIRECTION', 18 );
define( 'ERROR_INVALID_REQUEST_METHOD', 19 );
define( 'ERROR_INVALID_SORT_FIELD', 20 );
define( 'ERROR_INVALID_DATE_FORMAT', 21 );
define( 'ERROR_UPDATING_TIMEZONE', 22 );

# ERROR_CONFIG_*
define( 'ERROR_CONFIG_OPT_NOT_FOUND', 100 );
define( 'ERROR_CONFIG_OPT_INVALID', 101 );
define( 'ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB', 102 );

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

# ERROR_FILE_*
define( 'ERROR_FILE_TOO_BIG', 500 );
define( 'ERROR_FILE_NOT_ALLOWED', 501 );
define( 'ERROR_FILE_DUPLICATE', 502 );
define( 'ERROR_FILE_INVALID_UPLOAD_PATH', 503 );
define( 'ERROR_FILE_NO_UPLOAD_FAILURE', 504 );
define( 'ERROR_FILE_MOVE_FAILED', 505 );

# ERROR_BUGNOTE_*
define( 'ERROR_BUGNOTE_NOT_FOUND', 600 );

# ERROR_PROJECT_*
define( 'ERROR_PROJECT_NOT_FOUND', 700 );
define( 'ERROR_PROJECT_NAME_NOT_UNIQUE', 701 );
define( 'ERROR_PROJECT_NAME_INVALID', 702 );
define( 'ERROR_PROJECT_RECURSIVE_HIERARCHY', 703 );

# ERROR_USER_*
define( 'ERROR_USER_NAME_NOT_UNIQUE', 800 );
define( 'ERROR_USER_NOT_FOUND', 801 );
define( 'ERROR_USER_PREFS_NOT_FOUND', 802 );
define( 'ERROR_USER_CREATE_PASSWORD_MISMATCH', 803 );
define( 'ERROR_USER_PROFILE_NOT_FOUND', 804 );
define( 'ERROR_USER_NAME_INVALID', 805 );
define( 'ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS', 806 );
define( 'ERROR_USER_REAL_MATCH_USER', 807 );
define( 'ERROR_USER_CHANGE_LAST_ADMIN', 808 );
define( 'ERROR_USER_REAL_NAME_INVALID', 809 );
define( 'ERROR_USER_BY_NAME_NOT_FOUND', 810 );
define( 'ERROR_USER_BY_ID_NOT_FOUND', 811 );

# ERROR_AUTH_*
define( 'ERROR_AUTH_INVALID_COOKIE', 900 );

# ERROR_NEWS_*
define( 'ERROR_NEWS_NOT_FOUND', 1000 );

# ERROR_BUG_*
define( 'ERROR_BUG_NOT_FOUND', 1100 );
define( 'ERROR_BUG_DUPLICATE_SELF', 1101 );
define( 'ERROR_BUG_RESOLVED_ACTION_DENIED', 1102 );
define( 'ERROR_BUG_REVISION_NOT_FOUND', 1150 );

// @@@ obsolete, remove after lang files are sync'd
define( 'ERROR_BUG_READ_ONLY_ACTION_DENIED', 1103 );

# ERROR_EMAIL_*
define( 'ERROR_EMAIL_INVALID', 1200 );
define( 'ERROR_EMAIL_DISPOSABLE', 1201 );

# ERROR_CUSTOM_FIELD_*
define( 'ERROR_CUSTOM_FIELD_NOT_FOUND', 1300 );
define( 'ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE', 1301 );
define( 'ERROR_CUSTOM_FIELD_IN_USE', 1302 );
define( 'ERROR_CUSTOM_FIELD_INVALID_VALUE', 1303 );
define( 'ERROR_CUSTOM_FIELD_INVALID_DEFINITION', 1304 );
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

# ERROR_FILTER_*
define( 'ERROR_FILTER_NOT_FOUND', 2000 );
define( 'ERROR_FILTER_TOO_OLD', 2001 );

# ERROR_TWITTER_*
define( 'ERROR_TWITTER_NO_CURL_EXT', 2100 );

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
define( 'ERROR_PLUGIN_GENERIC', 2599 );

# ERROR_COLUMNS_*
define( 'ERROR_COLUMNS_DUPLICATE', 2600 );
define( 'ERROR_COLUMNS_INVALID', 2601 );

# ERROR_SESSION_*
define( 'ERROR_SESSION_HANDLER_INVALID', 2700 );
define( 'ERROR_SESSION_VAR_NOT_FOUND', 2701 );
define( 'ERROR_SESSION_NOT_VALID', 2702 );

# ERROR_FORM_*
define( 'ERROR_FORM_TOKEN_INVALID', 2800 );

# Generic position constants
define( 'POSITION_NONE', 0 );
define( 'POSITION_TOP', 1 );
define( 'POSITION_BOTTOM', 2 );
define( 'POSITION_BOTH', 3 );

# Status Legend Position
define( 'STATUS_LEGEND_POSITION_TOP', POSITION_TOP );
define( 'STATUS_LEGEND_POSITION_BOTTOM', POSITION_BOTTOM );
define( 'STATUS_LEGEND_POSITION_BOTH', POSITION_BOTH );

# Filter Position
define( 'FILTER_POSITION_NONE', POSITION_NONE );
define( 'FILTER_POSITION_TOP', POSITION_TOP );
define( 'FILTER_POSITION_BOTTOM', POSITION_BOTTOM );
define( 'FILTER_POSITION_BOTH', POSITION_BOTH );

// FILTER_POSITION_TOP | FILTER_POSITION_BOTTOM (bitwise OR)
# Flags for settings E-mail categories
define( 'EMAIL_CATEGORY_PROJECT_CATEGORY', 1 );

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

# Meta filter values
define( 'META_FILTER_MYSELF', -1 );
define( 'META_FILTER_NONE', - 2 );
define( 'META_FILTER_CURRENT', - 3 );
define( 'META_FILTER_ANY', 0 );

# Custom filter types
define( 'FILTER_TYPE_STRING', 0 );
define( 'FILTER_TYPE_INT', 1 );
define( 'FILTER_TYPE_BOOLEAN', 2 );
define( 'FILTER_TYPE_MULTI_STRING', 3 );
define( 'FILTER_TYPE_MULTI_INT', 4 );

# Filter match types
define( 'FILTER_MATCH_ALL', 0);
define( 'FILTER_MATCH_ANY', 1);

# Versions
define( 'VERSION_ALL', null );
define( 'VERSION_FUTURE', 0 );
define( 'VERSION_RELEASED', 1 );

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
define( 'TOKEN_GRAPH', 2 );
define( 'TOKEN_LAST_VISITED', 3 );
define( 'TOKEN_AUTHENTICATED', 4 );
define( 'TOKEN_COLLAPSE', 5 );
define( 'TOKEN_USER', 1000 );

# token expirations
define( 'TOKEN_EXPIRY', 60 * 60 );

# Default expiration of 60 minutes ( 3600 seconds )
define( 'TOKEN_EXPIRY_LAST_VISITED', 24 * 60 * 60 );
define( 'TOKEN_EXPIRY_AUTHENTICATED', 5 * 60 );
define( 'TOKEN_EXPIRY_COLLAPSE', 365 * 24 * 60 * 60 );

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

# system logging
#  logging levels, can be OR'd together
define( 'LOG_NONE',                     0 );  # no logging
define( 'LOG_EMAIL',                    1 );  # all emails sent
define( 'LOG_EMAIL_RECIPIENT',          2 );  # details of email recipient determination
define( 'LOG_FILTERING',                4 );  # logging for filtering.
define( 'LOG_AJAX',                     8 );  # logging for AJAX / XmlHttpRequests
define( 'LOG_LDAP',                     16 );  # logging for ldap
define( 'LOG_DATABASE',                 32 );  # logging for ldap
define( 'LOG_SOAP',                     64 );  # logging for SOAP

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
define( 'PHPMAILER_METHOD_MAIL',		0 );
define( 'PHPMAILER_METHOD_SENDMAIL',	1 );
define( 'PHPMAILER_METHOD_SMTP',		2 );

# Lengths - NOTE: these may represent hard-coded values in db schema and should not be changed.
define( 'DB_FIELD_SIZE_USERNAME', 32);
define( 'DB_FIELD_SIZE_REALNAME', 64);
define( 'DB_FIELD_SIZE_PASSWORD', 32);

# Maximum size for the user's password when storing it as a hash
define( 'PASSWORD_MAX_SIZE_BEFORE_HASH', 1024 );

define( 'SECONDS_PER_DAY', 86400 );

define( 'CAPTCHA_KEY', 'captcha_key' );
