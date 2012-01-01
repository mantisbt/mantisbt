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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Unit Test
	  */
	 /**
	  * test config_get and config_set 
	  *
	  * run from command line using: php -q test_config_get_set.php 
	  *  inspect results manually
	  * Notes:
	  *  data set from the test may need to be removed from the database manually
	  */
require_once( 'test.php' );

$t_config = 'main_menu_custom_options';
$t_test = config_get( $t_config );
print_r( $t_config);
print_r( $t_test );
$t_test[0][1] = 20;
config_set( $t_config, $t_test );
$t_test = config_get( $t_config );
print_r( $t_test );

$t_config = 'default_home_page';
$t_test = config_get( $t_config );
print_r( $t_config);
print_r( $t_test );
$t_test .= '?test';
config_set( $t_config, $t_test );
$t_test = config_get( $t_config );
print_r( $t_test );

$g_test_config = array();
$t_config = 'test_config';
$t_test = config_get( $t_config );
print_r( $t_config);
print_r( $t_test );
echo " ".(isset($t_test[0])?"set":"not set")." ".count($t_test)." ";
$t_test[0] = 20;
config_set( $t_config, $t_test );
$t_test = config_get( $t_config );
print_r( $t_test );
echo " ".(isset($t_test[0])?"set":"not set")." ".count($t_test)." ";
