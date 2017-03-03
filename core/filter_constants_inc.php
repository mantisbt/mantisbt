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
 * Constants used for Filters
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

###########################################################################
# Filter Property Names
###########################################################################

# filter meta fields
define( 'FILTER_PROPERTY_SEARCH', 'search' );
define( 'FILTER_PROPERTY_HIGHLIGHT_CHANGED', 'highlight_changed' );
define( 'FILTER_PROPERTY_HIDE_STATUS', 'hide_status' ); # hide_status
define( 'FILTER_PROPERTY_SORT_FIELD_NAME', 'sort' );
define( 'FILTER_PROPERTY_SORT_DIRECTION', 'dir' );
define( 'FILTER_PROPERTY_ISSUES_PER_PAGE', 'per_page' ); # ?

define( 'FILTER_PROPERTY_DATE_SUBMITTED_START_DAY', 'start_day' );
define( 'FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH', 'start_month' );
define( 'FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR', 'start_year' );
define( 'FILTER_PROPERTY_DATE_SUBMITTED_END_DAY', 'end_day' );
define( 'FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH', 'end_month' );
define( 'FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR', 'end_year' );
define( 'FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED', 'filter_by_date' ); #do_filter_by_date

define( 'FILTER_PROPERTY_LAST_UPDATED_START_DAY', 'last_updated_start_day' );
define( 'FILTER_PROPERTY_LAST_UPDATED_START_MONTH', 'last_updated_start_month' );
define( 'FILTER_PROPERTY_LAST_UPDATED_START_YEAR', 'last_updated_start_year' );
define( 'FILTER_PROPERTY_LAST_UPDATED_END_DAY', 'last_updated_end_day' );
define( 'FILTER_PROPERTY_LAST_UPDATED_END_MONTH', 'last_updated_end_month' );
define( 'FILTER_PROPERTY_LAST_UPDATED_END_YEAR', 'last_updated_end_year' );
define( 'FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE', 'filter_by_last_updated_date' ); #do_filter_by_last_updated_date

define( 'FILTER_PROPERTY_RELATIONSHIP_TYPE', 'relationship_type' );
define( 'FILTER_PROPERTY_RELATIONSHIP_BUG', 'relationship_bug' );
define( 'FILTER_PROPERTY_TAG_STRING', 'tag_string' );
define( 'FILTER_PROPERTY_TAG_SELECT', 'tag_select' );
define( 'FILTER_PROPERTY_MATCH_TYPE', 'match_type' );

define( 'FILTER_PROPERTY_NOTE_USER_ID', 'note_user_id' );
define( 'FILTER_PROPERTY_MONITOR_USER_ID', 'monitor_user_id' ); # user_monitor

# bug fields
define( 'FILTER_PROPERTY_CATEGORY_ID', 'category_id' ); #show_category
define( 'FILTER_PROPERTY_SEVERITY', 'severity' ); #show_severity
define( 'FILTER_PROPERTY_STATUS', 'status' ); #show_status
define( 'FILTER_PROPERTY_PRIORITY', 'priority' ); #show_priority
define( 'FILTER_PROPERTY_REPORTER_ID', 'reporter_id' );
define( 'FILTER_PROPERTY_HANDLER_ID', 'handler_id' );
define( 'FILTER_PROPERTY_PROJECT_ID', 'project_id' );
define( 'FILTER_PROPERTY_RESOLUTION', 'resolution' ); #show_resolution
define( 'FILTER_PROPERTY_BUILD', 'build' ); #show_build
define( 'FILTER_PROPERTY_VERSION', 'version' ); #show_version
define( 'FILTER_PROPERTY_STICKY', 'sticky' );
define( 'FILTER_PROPERTY_VIEW_STATE', 'view_state' ); # view_state
define( 'FILTER_PROPERTY_FIXED_IN_VERSION', 'fixed_in_version' );
define( 'FILTER_PROPERTY_TARGET_VERSION', 'target_version' );
define( 'FILTER_PROPERTY_PROFILE_ID', 'profile_id' ); # show_profile
define( 'FILTER_PROPERTY_PLATFORM', 'platform' );
define( 'FILTER_PROPERTY_OS', 'os' );
define( 'FILTER_PROPERTY_OS_BUILD', 'os_build' );

# These are old date fields names, keeping them for backward compatibility, if used externally
define( 'FILTER_PROPERTY_START_DAY', FILTER_PROPERTY_DATE_SUBMITTED_START_DAY );
define( 'FILTER_PROPERTY_START_MONTH', FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH );
define( 'FILTER_PROPERTY_START_YEAR', FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR );
define( 'FILTER_PROPERTY_END_DAY', FILTER_PROPERTY_DATE_SUBMITTED_END_DAY );
define( 'FILTER_PROPERTY_END_MONTH', FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH );
define( 'FILTER_PROPERTY_END_YEAR', FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR );
define( 'FILTER_PROPERTY_FILTER_BY_DATE', FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED );

