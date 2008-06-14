<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: proj_doc_add.php,v 1.51.16.1 2007-10-13 22:34:20 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'file_api.php' );

	# helper_ensure_post();

	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ) {
		access_denied();
	}

	access_ensure_project_level( config_get( 'upload_project_file_threshold' ) );

	$f_title = gpc_get_string( 'title' );
	$f_description = gpc_get_string( 'description' );
	$f_file = gpc_get_file( 'file' );

	if ( is_blank( $f_title ) ) {
		error_parameters( lang_get( 'title' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

    $f_file_error =  ( isset( $f_file['error'] ) ) ? $f_file['error'] : 0;
	file_add( 0, $f_file['tmp_name'], $f_file['name'], $f_file['type'], 'project', $f_file_error, $f_title, $f_description );

	$t_redirect_url = 'proj_doc_page.php';

	html_page_top1();
	html_meta_redirect( $t_redirect_url );
	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
