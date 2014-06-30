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
 * This page allows a user to manage the column api data stored in their profile.
 *
 * Users are presented with a list of available columns (fields). They mean then choose which types
 * of field to include on the following types of pages:
 * i) "View Issues Columns" - These are the fields displayed when viewing the list of bugs
 * ii) "Print Issues Columns" - These are the fields that are included when printing out bug
 * details.
 * iii) "Export Issues Columns" - These are the list of fields included when exporting an issue from
 * the bug tracker.
 * Note: These are now shared between different types of exports - for example, the core MantisBT
 * distribution can export to Excel and Word Documents.
 *
 * The settings defined here can be allocated to the current project, or All Projects. In addition,
 * it is possible to copy the column configuration between different projects.
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses current_user_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_once( 'core.php' );
require_api( 'current_user_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

html_page_top( lang_get( 'manage_columns_config' ) );

current_user_ensure_unprotected();

# Define constant that will be checked by the include page.
define( 'ACCOUNT_COLUMNS', true );

define( 'MANAGE_COLUMNS_INC_ALLOW', true );
include ( dirname( __FILE__ ) . '/manage_columns_inc.php' );

html_page_bottom();
