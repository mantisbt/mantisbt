<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_subproj_delete.php,v 1.1 2005-02-13 21:36:17 jlatour Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	$f_project_id    = gpc_get_int( 'project_id' );
	$f_subproject_id = gpc_get_int( 'subproject_id' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	project_hierarchy_remove( $f_subproject_id, $f_project_id );

	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;

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
