<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php
	if ( auth_is_user_authenticated() ) {
		print_header_redirect( 'main_page.php' );
	} else {
		print_header_redirect( 'login_page.php' );
	}
?>
<?php print_page_top1() ?>
<?php print_meta_redirect( $g_login_page, $g_wait_time ) ?>
<?php print_page_top2() ?>

<br />
<div align="center">
	<a href="login_page.php"><?php echo $s_click_to_login ?></a>
</div>

<?php print_bottom_page( $g_bottom_include_page ) ?>
<?php print_footer(__FILE__) ?>
<?php print_body_bottom() ?>
<?php print_html_bottom() ?>