<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### Check to make sure that the access is legal
	### NOTE: enabling this can be a rather bad idea except for debugging
	if ( $g_show_source==1 ) {
		### not an admin
		if ( !access_level_check_greater_or_equal( "administrator" ) ) {
			### need to replace with access error page
			header( "Location: $g_logout_page" );
			exit;
		}
	}
	### not supposed to be viewing
	else if ( $g_show_source==0 ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=left>
<?
	PRINT "$s_show_source_for: $f_url<p>";
	### Print source
	$t_ver = phpversion();
	if ( floor( $t_ver )>=4 ) {
		show_source( $f_url );
	}
	else {
		PRINT "$s_not_supported_part1 ($t_ver) $s_not_supported_part2 show_source()";
	}
?>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>