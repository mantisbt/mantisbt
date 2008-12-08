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
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

access_ensure_global_level( ADMINISTRATOR );

$t_core_path = config_get( 'core_path' );

foreach( $g_file_type_icons as $t_ext => $t_filename ) {
	$t_file_path = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'fileicons' . DIRECTORY_SEPARATOR . $t_filename;

	echo "Testing icon for extension '$t_ext'... $t_file_path ... ";
	flush();

	if( file_exists( $t_file_path ) ) {
		echo 'OK';
	} else {
		echo '<font color="red">NOT FOUND</font>';
	}

	echo '<br />';
}
