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

	require_once( 'category_api.php' );

	form_security_validate( 'manage_proj_cat_delete' );

	auth_reauthenticate();

	$f_category_id = gpc_get_int( 'id' );
	$f_project_id = gpc_get_int( 'project_id' );

	$t_row = category_get_row( $f_category_id );
	$t_name = category_full_name( $f_category_id );
	$t_project_id = $t_row['project_id'];

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_project_id );

	# Protect the 'default category for moves' from deletion
	$t_default_cat = 'default_category_for_moves';
	$t_config_table = db_get_table( 'mantis_config_table' );
	$t_query = "SELECT count(config_id) FROM $t_config_table "
		. "WHERE config_id = " . db_param() . " AND value = " . db_param();
	$t_default_cat_count = db_result( db_query_bound( $t_query, array( $t_default_cat, $f_category_id ) ) );
	if( $t_default_cat_count > 0 || $f_category_id == config_get_global( $t_default_cat ) ) {
		trigger_error( ERROR_CATEGORY_CANNOT_DELETE_DEFAULT, ERROR );
	}

	# Get a bug count
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_query = "SELECT COUNT(id) FROM $t_bug_table WHERE category_id=" . db_param();
	$t_bug_count = db_result( db_query_bound( $t_query, array( $f_category_id ) ) );

	# Confirm with the user
	helper_ensure_confirmed( sprintf( lang_get( 'category_delete_sure_msg' ), string_display_line( $t_name ), $t_bug_count ),
		lang_get( 'delete_category_button' ) );

	category_remove( $f_category_id );

	form_security_purge( 'manage_proj_cat_delete' );

	if ( $f_project_id == ALL_PROJECTS ) {
		$t_redirect_url = 'manage_proj_page.php';
	} else {
		$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
	}

	html_page_top( null, $t_redirect_url );
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
