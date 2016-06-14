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
 * This page allows the user to set the fields of the bugs he wants to print
 * Update is POSTed to acount_prefs_update.php
 * Reset is POSTed to acount_prefs_reset.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses html_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'html_api.php' );

define( 'PRINT_ALL_BUG_OPTIONS_INC_ALLOW', true );
include( dirname( __FILE__ ) . '/print_all_bug_options_inc.php' );

auth_ensure_user_authenticated();

layout_page_header();
layout_page_begin();
edit_printing_prefs();
layout_page_end();
