<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: meta_inc.php,v 1.18 2005-05-19 00:25:52 thraxisp Exp $
	# --------------------------------------------------------

    global $g_allow_browser_cache;
    if ( ! isset( $g_allow_browser_cache ) ) {
?>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	<meta http-equiv="Pragma-directive" content="no-cache" />
	<meta http-equiv="Cache-Directive" content="no-cache" />
<?php } # ! isset( $g_allow_browser_cache )  ?>
	<meta http-equiv="Expires" content="<?php echo gmdate( 'D, d M Y H:i:s \G\M\T', time() ) ?>" />
