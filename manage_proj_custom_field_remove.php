<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	# We should check both since we are in the project section and an
	#  admin might raise the first threshold and not realize they need
	#  to raise the second
	check_access( config_get( 'manage_project_threshold' ) );
	check_access( config_get( 'custom_field_link_threshold' ) );

	$f_field_id		= gpc_get_int( 'field_id' );
	$f_project_id	= gpc_get_int( 'project_id' );

	custom_field_unlink( $f_field_id, $f_project_id );

	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
?>
<?php print_page_top1() ?>
<?php
	print_meta_redirect( $t_redirect_url );
?>
<?php print_page_top2() ?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>
