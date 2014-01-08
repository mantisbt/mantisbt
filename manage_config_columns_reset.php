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

	require_once( 'config_api.php' );

	form_security_validate( 'manage_config_columns_reset' );

	$t_user_id = auth_get_current_user_id();

	config_delete_for_user( 'view_issues_page_columns', $t_user_id );
	config_delete_for_user( 'print_issues_page_columns', $t_user_id );
	config_delete_for_user( 'csv_columns', $t_user_id );
	config_delete_for_user( 'excel_columns', $t_user_id );

	form_security_purge( 'manage_config_columns_reset' );

	echo '<br />';
	echo '<div align="center">';

	$t_redirect_url = 'account_manage_columns_page.php';
	html_page_top( lang_get( 'manage_email_config' ), $t_redirect_url );
	echo '<br />';
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
	echo '</div>';

	html_page_bottom();
