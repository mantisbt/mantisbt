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
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Unit Test
	  */
	 /**
	  * test string_sanitize_url 
	  *
	  * run from command line using: php -q test_string_sanitize_url.php 
	  *  inspect results manually
	  */
require_once( 'test.php' );

$my_path = config_get('path');
echo "my path is ".$my_path."\n<br />";
$t_test = array(
    '',
    'abc.php',
    'abc.php?',
    'abc.php#a',
    'abc.php?abc=def',
    'abc.php?abc=def#a',
    'abc.php?abc=def&z=xyz',
	'abc.php?abc=def&amp;z=xyz',
    'abc.php?abc=def&z=xyz#a',
    'abc.php?abc=def&amp;z=xyz#a',
    'abc.php?abc=def&z=<script>alert("foo")</script>z#a',
    'abc.php?abc=def&z=z#<script>alert("foo")</script>a',
	'plugin.php?page=Source/index',
	'plugin.php?page=Source/list&id=1',
	'plugin.php?page=Source/list&id=1#abc',
    $my_path.'abc.php',
    $my_path.'abc.php?',
    $my_path.'abc.php#a',
    $my_path.'abc.php?abc=def',
    $my_path.'abc.php?abc=def#a',
    $my_path.'abc.php?abc=def&z=xyz',
    $my_path.'abc.php?abc=def&amp;z=xyz',
    $my_path.'abc.php?abc=def&z=xyz#a',
    $my_path.'abc.php?abc=def&amp;z=xyz#a',
    $my_path.'abc.php?abc=def&z=<script>alert("foo")</script>z#a',
    $my_path.'abc.php?abc=def&z=z#<script>alert("foo")</script>a',
	$my_path.'plugin.php?page=Source/index',
	$my_path.'plugin.php?page=Source/list&id=1',
	$my_path.'plugin.php?page=Source/list&id=1#abc',
    'http://www.test.my.url/'
    );

echo '<h2>Normal</h2><pre>';

foreach($t_test as $t_url) {
    echo "\n: ", htmlspecialchars($t_url), "\n: ", htmlspecialchars(string_sanitize_url($t_url, false)), "\n";
}

echo '</pre><h2>Absolute</h2><pre>';

foreach($t_test as $t_url) {
    echo "\n: ", htmlspecialchars($t_url), "\n: ", htmlspecialchars(string_sanitize_url($t_url, true)), "\n";
}

echo '</pre>';

