<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_custom_field_remove.php,v 1.15 2004-12-14 20:37:07 marcelloscata Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'custom_field_api.php' );

	$f_field_id = gpc_get_int( 'field_id' );
	$f_project_id = gpc_get_int( 'project_id' );

	# We should check both since we are in the project section and an
	# admin might raise the first threshold and not realize they need
	# to raise the second
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
	access_ensure_project_level( config_get( 'custom_field_link_threshold' ), $f_project_id );

	$t_definition = custom_field_get_definition( $f_field_id );
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;

	# Confirm with the user
	helper_ensure_confirmed( lang_get( 'confirm_custom_field_unlinking' ) .
		'<br/>' . lang_get( 'custom_field' ) . ': ' . string_attribute( $t_definition['name'] ),
		lang_get( 'field_remove_button' ) );

	custom_field_unlink( $f_field_id, $f_project_id );

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
