<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: documentation_page.php,v 1.18 2003-02-18 02:18:00 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( ADMINISTRATOR );
?>
<?php 
	html_page_top1();

	# get the phpinfo() content
	ob_start();
	phpinfo();
	$content = ob_get_contents();
	ob_end_clean();

	# get the <style> block
	$style = preg_replace( '|^.*(<style.*</style>).*$|si', '\1', $content );
	# add '.phpinfo' before each style definition
	$style = preg_replace( '/(.*\{.*\}.*)/', '.phpinfo \1', $style );
	# output the <style> block
	echo $style;

	html_page_top2();

	print_manage_menu( 'documentation_page.php' );

	print_manage_doc_menu( 'documentation_page.php' );

	echo '<br />';

	# output the contents of the <body> block inside a div with class phpinfo
	echo '<div class="phpinfo">';
	$body = preg_replace( '|^.*<body>(.*)</body>.*$|si', '\1', $content );
	echo $body;
	echo '</div>';
?>
<?php html_page_bottom1( __FILE__ ) ?>
