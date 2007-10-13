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
	# $Id: test_langs.php,v 1.1.18.1 2007-10-13 22:34:57 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	$t_core_path = config_get( 'core_path' );

	foreach( $g_language_choices_arr as $t_lang ) {
		if ( $t_lang == 'auto' ) {
			continue;
		}

		echo "Testing language '$t_lang'...<br />";
		flush();

		require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'strings_' . $t_lang . '.txt' );
	}
?>