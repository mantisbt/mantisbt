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
    $t_redirect_url = 'manage_page.php';

	$t_protected = !user_delete($f_id);
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url, $g_wait_time );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( $t_protected ) {				# PROTECTED
		PRINT $s_account_delete_protected_msg.'<p>';
	} else {						# SUCCESS
		PRINT $s_operation_successful.'<p>';
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
