<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_user_delete.php,v 1.29 2004-08-21 00:41:32 prichards Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( config_get( 'manage_user_threshold' ) );
	
	$f_user_id	= gpc_get_int( 'user_id' );

	$t_user = user_get_row( $f_user_id );

	helper_ensure_confirmed( lang_get( 'delete_account_sure_msg' ) . '<br/>' . lang_get( 'username' ) . ': ' . $t_user['username'],
							 lang_get( 'delete_account_button' ) );

	user_delete( $f_user_id );

	$t_redirect_url = 'manage_user_page.php';

	html_page_top1();

	html_meta_redirect( $t_redirect_url );

	html_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
