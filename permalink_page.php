<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: permalink_page.php,v 1.4 2007-07-18 21:32:22 prichards Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	html_page_top1();
	html_page_top2();

	$f_url = string_sanitize_url( gpc_get_string( 'url' ) );
?>
<div align="center">
	<p>
	<?php
		echo lang_get( 'filter_permalink' ), '<br />';
		echo "<a href=\"$f_url\">$f_url</a>"; ?></p><br />
<?php

	print_bracket_link( 
		sprintf( config_get( 'create_short_url' ), $f_url ), 
		lang_get( 'create_short_link' ), 
		/* new window = */ true );

?>
</div>
<?php
	html_page_bottom1( __FILE__ );
?>
