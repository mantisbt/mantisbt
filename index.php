<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php
	if ( BASIC_AUTH == $g_login_method ) {
		print_header_redirect( $g_login );
	} else {
		# Only place this function is called
		# Other pages use login_cookie_check()
		index_login_cookie_check( $g_main_page );
	}
?>
<?php print_page_top1() ?>
<?php print_meta_redirect( $g_login_page, $g_wait_time ) ?>
<?php print_page_top2() ?>

<p>
<div align="center">
	<a href="<?php echo $g_login_page ?>"><?php echo $s_click_to_login ?></a>
</div>

<?php print_bottom_page( $g_bottom_include_page ) ?>
<?php print_footer(__FILE__) ?>
<?php print_body_bottom() ?>
<?php print_html_bottom() ?>