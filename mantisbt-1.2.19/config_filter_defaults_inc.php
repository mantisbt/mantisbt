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

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

    ###########################################################################
    # Filter Property Names
    ###########################################################################

    define( 'FILTER_PROPERTY_FREE_TEXT', 'search' );
    define( 'FILTER_PROPERTY_CATEGORY', 'show_category' );
    define( 'FILTER_PROPERTY_SEVERITY_ID', 'show_severity' );
    define( 'FILTER_PROPERTY_STATUS_ID', 'show_status' );
    define( 'FILTER_PROPERTY_PRIORITY_ID', 'show_priority' );
    define( 'FILTER_PROPERTY_HIGHLIGHT_CHANGED', 'highlight_changed' );
    define( 'FILTER_PROPERTY_REPORTER_ID', 'reporter_id' );
    define( 'FILTER_PROPERTY_HANDLER_ID', 'handler_id' );
    define( 'FILTER_PROPERTY_PROJECT_ID', 'project_id' );
    define( 'FILTER_PROPERTY_NOTE_USER_ID', 'note_user_id' );
    define( 'FILTER_PROPERTY_RESOLUTION_ID', 'show_resolution' );
    define( 'FILTER_PROPERTY_PRODUCT_BUILD', 'show_build' );
    define( 'FILTER_PROPERTY_PRODUCT_VERSION', 'show_version' );

    define( 'FILTER_PROPERTY_MONITOR_USER_ID', 'user_monitor' );
    define( 'FILTER_PROPERTY_HIDE_STATUS_ID', 'hide_status' );
    define( 'FILTER_PROPERTY_SORT_FIELD_NAME', 'sort' );
    define( 'FILTER_PROPERTY_SORT_DIRECTION', 'dir' );
    define( 'FILTER_PROPERTY_SHOW_STICKY_ISSUES', 'sticky_issues' );
    define( 'FILTER_PROPERTY_VIEW_STATE_ID', 'view_state' );
    define( 'FILTER_PROPERTY_FIXED_IN_VERSION', 'fixed_in_version' );
    define( 'FILTER_PROPERTY_TARGET_VERSION', 'target_version' );
    define( 'FILTER_PROPERTY_ISSUES_PER_PAGE', 'per_page' );
    define( 'FILTER_PROPERTY_PROFILE', 'profile_id' );
    define( 'FILTER_PROPERTY_PLATFORM', 'platform' );
    define( 'FILTER_PROPERTY_OS', 'os' );
    define( 'FILTER_PROPERTY_OS_BUILD', 'os_build' );
    define( 'FILTER_PROPERTY_START_DAY', 'start_day' );
    define( 'FILTER_PROPERTY_START_MONTH', 'start_month' );
    define( 'FILTER_PROPERTY_START_YEAR', 'start_year' );
    define( 'FILTER_PROPERTY_END_DAY', 'end_day' );
    define( 'FILTER_PROPERTY_END_MONTH', 'end_month' );
    define( 'FILTER_PROPERTY_END_YEAR', 'end_year' );
    define( 'FILTER_PROPERTY_NOT_ASSIGNED', 'and_not_assigned' );
    define( 'FILTER_PROPERTY_FILTER_BY_DATE', 'do_filter_by_date' );
    define( 'FILTER_PROPERTY_RELATIONSHIP_TYPE', 'relationship_type' );
    define( 'FILTER_PROPERTY_RELATIONSHIP_BUG', 'relationship_bug' );
    define( 'FILTER_PROPERTY_TAG_STRING', 'tag_string' );
    define( 'FILTER_PROPERTY_TAG_SELECT', 'tag_select' );
    define( 'FILTER_PROPERTY_MATCH_TYPE', 'match_type');

    ###########################################################################
    # Filter Query Parameter Names
    ###########################################################################

    define( 'FILTER_SEARCH_FREE_TEXT', 'search' );
    define( 'FILTER_SEARCH_CATEGORY', 'category' );
    define( 'FILTER_SEARCH_SEVERITY_ID', 'severity_id');
    define( 'FILTER_SEARCH_STATUS_ID', 'status_id' );
    define( 'FILTER_SEARCH_REPORTER_ID', 'reporter_id' );
    define( 'FILTER_SEARCH_HANDLER_ID', 'handler_id' );
    define( 'FILTER_SEARCH_PROJECT_ID', 'project_id' );
    define( 'FILTER_SEARCH_RESOLUTION_ID', 'resolution_id' );
    define( 'FILTER_SEARCH_FIXED_IN_VERSION', 'fixed_in_version' );
    define( 'FILTER_SEARCH_NOTE_USER_ID', 'note_user_id' );
    define( 'FILTER_SEARCH_TARGET_VERSION', 'target_version' );
    define( 'FILTER_SEARCH_START_DAY', 'start_day' );
    define( 'FILTER_SEARCH_START_MONTH', 'start_month' );
    define( 'FILTER_SEARCH_START_YEAR', 'start_year' );
    define( 'FILTER_SEARCH_END_DAY', 'end_day' );
    define( 'FILTER_SEARCH_END_MONTH', 'end_month' );
    define( 'FILTER_SEARCH_END_YEAR', 'end_year' );
    define( 'FILTER_SEARCH_PRIORITY_ID', 'priority_id' );
    define( 'FILTER_SEARCH_PROFILE', 'profile_id' );
    define( 'FILTER_SEARCH_PLATFORM', 'platform' );
    define( 'FILTER_SEARCH_OS', 'os' );
    define( 'FILTER_SEARCH_OS_BUILD', 'os_build' );
    define( 'FILTER_SEARCH_MONITOR_USER_ID', 'monitor_user_id' );
    define( 'FILTER_SEARCH_PRODUCT_BUILD', 'product_build' );
    define( 'FILTER_SEARCH_PRODUCT_VERSION', 'product_version' );
    define( 'FILTER_SEARCH_VIEW_STATE_ID', 'view_state_id' );
    define( 'FILTER_SEARCH_SHOW_STICKY_ISSUES', 'sticky_issues' );
    define( 'FILTER_SEARCH_SORT_FIELD_NAME', 'sortby' );
    define( 'FILTER_SEARCH_SORT_DIRECTION', 'dir' );
    define( 'FILTER_SEARCH_ISSUES_PER_PAGE', 'per_page' );
    define( 'FILTER_SEARCH_HIGHLIGHT_CHANGED', 'highlight_changed' );
    define( 'FILTER_SEARCH_HIDE_STATUS_ID', 'hide_status_id' );
    define( 'FILTER_SEARCH_NOT_ASSIGNED', 'not_assigned' );
    define( 'FILTER_SEARCH_FILTER_BY_DATE', 'filter_by_date' );
    define( 'FILTER_SEARCH_RELATIONSHIP_TYPE', 'relationship_type' );
    define( 'FILTER_SEARCH_RELATIONSHIP_BUG', 'relationship_bug' );
    define( 'FILTER_SEARCH_TAG_STRING', 'tag_string' );
    define( 'FILTER_SEARCH_TAG_SELECT', 'tag_select' );
    define( 'FILTER_SEARCH_MATCH_TYPE', 'match_type');

