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

	require_once( 'string_api.php' );

	$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );

	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) || !file_is_uploading_enabled() ) {
		access_denied();
	}

	# Override the current page to make sure we get the appropriate project-specific configuration
	$g_project_override = $f_project_id;

	$t_user_id = auth_get_current_user_id();
	$t_project_file_table = db_get_table( 'mantis_project_file_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );
	$t_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
	$t_user_table = db_get_table( 'mantis_user_table' );
	$t_pub = VS_PUBLIC;
	$t_priv = VS_PRIVATE;
	$t_admin = config_get_global( 'admin_site_threshold' );

	if ( $f_project_id == ALL_PROJECTS ) {
		# Select all the projects that the user has access to
		$t_projects = user_get_accessible_projects( $t_user_id );
	} else {
		# Select the specific project
		$t_projects = array( $f_project_id );
	}

	$t_projects[] = ALL_PROJECTS; # add "ALL_PROJECTS to the list of projects to fetch

	$t_reqd_access = config_get( 'view_proj_doc_threshold' );
	if ( is_array( $t_reqd_access ) ) {
		if ( 1 == count( $t_reqd_access ) ) {
			$t_access_clause = "= " . array_shift( $t_reqd_access ) . " ";
		} else {
			$t_access_clause = "IN (" . implode( ',', $t_reqd_access ) . ")";
		}
	} else {
		$t_access_clause = ">= $t_reqd_access ";
	}

	$query = "SELECT pft.id, pft.project_id, pft.filename, pft.filesize, pft.title, pft.description, pft.date_added
				FROM $t_project_file_table pft
					LEFT JOIN $t_project_table pt ON pft.project_id = pt.id
					LEFT JOIN $t_project_user_list_table pult
						ON pft.project_id = pult.project_id AND pult.user_id = " . db_param() . "
					LEFT JOIN $t_user_table ut ON ut.id = " . db_param() . "
				WHERE pft.project_id in (" . implode( ',', $t_projects ) . ") AND
					( ( ( pt.view_state = " . db_param() . " OR pt.view_state is null ) AND pult.user_id is null AND ut.access_level $t_access_clause ) OR
						( ( pult.user_id = " . db_param() . " ) AND ( pult.access_level $t_access_clause ) ) OR
						( ut.access_level >= " . db_param() . " ) )
				ORDER BY pt.name ASC, pft.title ASC";
	$result = db_query_bound( $query, array( $t_user_id, $t_user_id, $t_pub, $t_user_id, $t_admin ) );
	$num_files = db_num_rows( $result );

	html_page_top( lang_get( 'docs_link' ) );
?>
<br />
<div align="center">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'project_documentation_title' ) ?>
	</td>
	<td class="right">
		<?php print_doc_menu( 'proj_doc_page.php' ) ?>
	</td>
</tr>
<?php
	for ($i=0;$i<$num_files;$i++) {
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v' );
		$v_filesize = number_format( $v_filesize );
		$v_title = string_display( $v_title );
		$v_description = string_display_links( $v_description );
		$v_date_added = date( config_get( 'normal_date_format' ), $v_date_added );

?>
<tr valign="top" <?php echo helper_alternate_class( $i ) ?>>
	<td>
<?php
		$t_href = '<a href="file_download.php?file_id='.$v_id.'&amp;type=doc">';
		echo $t_href;
		print_file_icon( $v_filename );
		echo '</a>&#160;' . $t_href . $v_title . '</a> (' . $v_filesize . lang_get( 'word_separator' ) . lang_get( 'bytes' ) . ')';
?>
	<br />
	<span class="small">
<?php
		if( $v_project_id == ALL_PROJECTS ) {
			echo lang_get( 'all_projects' ) . '<br />';
		}
		else if( $v_project_id != $f_project_id ) {
			$t_project_name = project_get_name( $v_project_id );
			echo $t_project_name . '<br />';
		}
		echo '(' . $v_date_added . ')';
		if ( access_has_project_level( config_get( 'upload_project_file_threshold', null, null, $v_project_id ), $v_project_id ) ) {
			echo '&#160;';
			print_button( 'proj_doc_edit_page.php?file_id='.$v_id, lang_get( 'edit_link' ) );
			echo '&#160;';
			print_button( 'proj_doc_delete.php?file_id=' . $v_id, lang_get( 'delete_link' ) );
		}
?>
	</span>
	</td>
	<td>
		<?php echo $v_description ?>
	</td>
</tr>
<?php
	} # end for loop
?>
</table>
</div>

<?php
	html_page_bottom();
