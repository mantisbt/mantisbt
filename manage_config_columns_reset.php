<?php
# Mantis - a php based bugtracking system

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'config_api.php' );

	# helper_ensure_post();

	auth_ensure_user_authenticated();
	auth_reauthenticate();

	$t_user_id = auth_get_current_user_id();

	config_delete_for_user( 'view_issues_page_columns', $t_user_id );
	config_delete_for_user( 'print_issues_page_columns', $t_user_id );
	config_delete_for_user( 'csv_columns', $t_user_id );
	config_delete_for_user( 'excel_columns', $t_user_id );

	echo '<br />';
	echo '<div align="center">';

	$t_redirect_url = 'account_manage_columns_page.php';
	html_page_top1( lang_get( 'manage_email_config' ) );
	html_meta_redirect( $t_redirect_url );
	html_page_top2();
	echo '<br />';
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
	echo '</div>';

	html_page_bottom1( __FILE__ );
?>