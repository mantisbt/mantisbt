<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

html_page_top();

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

html_page_bottom();
