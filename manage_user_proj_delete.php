<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_user_proj_delete.php,v 1.19 2004-12-14 20:37:07 marcelloscata Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	auth_ensure_user_authenticated();

	$f_project_id = gpc_get_int( 'project_id' );
	$f_user_id = gpc_get_int( 'user_id' );

	access_ensure_project_level( config_get( 'project_user_threshold' ), $f_project_id );

	$t_project_name = project_get_name( $f_project_id );

	# Confirm with the user
	helper_ensure_confirmed( lang_get( 'remove_user_sure_msg' ) .
		'<br/>' . lang_get( 'project_name' ) . ': ' . $t_project_name,
		lang_get( 'remove_user_button' ) );

	$result = project_remove_user( $f_project_id, $f_user_id );

	$t_redirect_url = 'manage_user_edit_page.php?user_id=' .$f_user_id;

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
