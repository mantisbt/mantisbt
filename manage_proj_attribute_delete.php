<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );
	$f_attribute = urldecode( $f_attribute );
	$result = attribute_delete( $f_parameter, $f_project_id, $f_attribute );
   // $t_redirect_url = 'manage_proj_edit_page.php?f_project_id='.$f_project_id;
   $t_redirect_url = 'manage_proj_menu_page.php';
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php	
	if ( $result ) {
		print_bracket_link( $t_redirect_url, $s_proceed );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
</div>
<?php print_page_bot1( __FILE__ ) ?>
