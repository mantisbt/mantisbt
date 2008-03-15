<?php
# Mantis - a php based bugtracking system
# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net
# This program is distributed under the terms and conditions of the GPL
# See the README and LICENSE files for details

# export the currently filtered list of bugs as a freemind mindmap
# see https://freemind.sourceforge.net
# it works with freemind version 0.8.0
# Copyright (C) 2006-2007 Peter Tandler
#    http://www.digital-moderation.com
#
# Note: to link it directly to the Juan Pedro's 
# freemind flash browser, use the parameter
#     type_page=flash
#
# see also: 
#   freemind.html
#   http://www.efectokiwano.net/mm/
#   http://freemind.sourceforge.net/wiki/index.php/Flash_browser

# --------------------------------------------------------
# $Id$
# --------------------------------------------------------

require_once( 'core.php' );

$t_core_path = config_get( 'core_path' );

require_once( $t_core_path . 'access_api.php' );

if ( config_get( 'mindmap_enabled' ) == OFF ) {
	access_denied();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo string_display_line( config_get( 'window_title' ) ) ?></title>
<script type="text/javascript" src="freemind_browser/flashobject.js"></script>
<style type="text/css">

	/* hide from ie on mac \*/
	html {
		height: 100%;
		overflow: hidden;
	}

	#flashcontent {
		height: 100%;
	}
	/* end hide */

	body {
		height: 100%;
		margin: 0;
		padding: 0;
		background-color: #9999ff;
	}

</style>
</head>
<body>
	<div id="flashcontent">
		 Flash plugin or Javascript are turned off.
		 Activate both  and reload to view the mindmap
	</div>

	<script type="text/javascript">
		// <![CDATA[
		var fo = new FlashObject("freemind_browser/freemind.swf", "visorFreeMind", "100%", "100%", 6, "#9999ff");
		fo.addParam("quality", "high");
		fo.addParam("bgcolor", "#ffffff");
		fo.addVariable("openUrl", "_blank");
		fo.addVariable("initLoadFile", "freemind_export.php?type_page=flash");
		fo.addVariable("startCollapsedToLevel","5");
		fo.write("flashcontent");
		// ]]>
	</script>
</body>
</html>