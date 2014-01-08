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
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'custom_field_api.php' );
	require_once( 'helper_api.php' );
	require_once( 'columns_api.php' );

	auth_reauthenticate();

	html_page_top( lang_get( 'manage_columns_config' ) );

	print_manage_menu( 'adm_permissions_report.php' );
	print_manage_config_menu( 'manage_config_columns_page.php' );

	# Define constant that will be checked by the include page.
	define ( 'MANAGE_COLUMNS', '' );

	include ( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'manage_columns_inc.php' );

	html_page_bottom();
