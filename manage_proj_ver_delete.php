<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_ver_delete.php,v 1.22 2004-12-14 20:37:07 marcelloscata Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'version_api.php' );

	$f_version_id = gpc_get_int( 'version_id' );

	$t_version_info = version_get( $f_version_id );
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $t_version_info->project_id;

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_version_info->project_id );

	# Confirm with the user
	helper_ensure_confirmed( lang_get( 'version_delete_sure' ) .
		'<br/>' . lang_get( 'version' ) . ': ' . $t_version_info->version,
		lang_get( 'delete_version_button' ) );

	version_remove( $f_version_id );

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
