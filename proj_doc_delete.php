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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	form_security_validate( 'proj_doc_delete' );

	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ) {
		access_denied();
	}

	$f_file_id = gpc_get_int( 'file_id' );

	$t_project_id = file_get_field( $f_file_id, 'project_id', 'project' );

	access_ensure_project_level( config_get( 'upload_project_file_threshold' ), $t_project_id );

	$t_project_file_table = db_get_table( 'mantis_project_file_table' );
	$query = "SELECT title FROM $t_project_file_table
				WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $f_file_id ) );
	$t_title = db_result( $result );

	# Confirm with the user
	helper_ensure_confirmed( lang_get( 'confirm_file_delete_msg' ) .
		'<br />' . lang_get( 'filename' ) . ': ' . string_display( $t_title ),
		lang_get( 'file_delete_button' ) );

	file_delete( $f_file_id, 'project' );

	form_security_purge( 'proj_doc_delete' );

	$t_redirect_url = 'proj_doc_page.php';

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
