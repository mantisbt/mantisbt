<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: permalink_page.php,v 1.4.2.1 2007-10-13 22:34:11 giallu Exp $
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
