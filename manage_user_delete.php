<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );
	
	$f_user_id	= gpc_get_int( 'f_user_id' );

    $t_redirect_url = 'manage_page.php';

	user_delete( $f_user_id );

	print_page_top1();

	print_meta_redirect( $t_redirect_url, $g_wait_time );

	print_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
