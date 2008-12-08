<?php
# Mantis - a php based bugtracking system

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

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	html_page_top1();
	html_page_top2();

	access_ensure_project_level( config_get( 'create_permalink_threshold' ) );

	$f_url = string_sanitize_url( gpc_get_string( 'url' ) );
?>
<div align="center">
	<p>
<?php
	echo lang_get( 'filter_permalink' ), '<br />';
	echo "<a href=\"$f_url\">$f_url</a></p>";

	$t_create_short_url = config_get( 'create_short_url' );

	if ( !is_blank( $t_create_short_url ) ) {
		print_bracket_link( 
			sprintf( $t_create_short_url, $f_url ), 
			lang_get( 'create_short_link' ), 
			/* new window = */ true );
	}
?>
</div>
<?php
	html_page_bottom1( __FILE__ );
?>