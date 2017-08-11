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
 * View Column Fields Configuration
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

auth_reauthenticate();

layout_page_header( lang_get( 'manage_columns_config' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( PAGE_CONFIG_DEFAULT );
print_manage_config_menu( 'manage_config_columns_page.php' );


# Define constant that will be checked by the include page.
define( 'MANAGE_COLUMNS', true );

define( 'MANAGE_COLUMNS_INC_ALLOW', true );
include ( dirname( __FILE__ ) . '/manage_columns_inc.php' );

layout_page_end();
