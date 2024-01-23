<?php
# MantisBT - A PHP based bugtracking system

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
 * Test cases for string handling within mantis
 *
 * @package    Tests
 * @subpackage String
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Includes
require_once 'MantisCoreBase.php';

/**
 * Mantis string handling test cases
 * @package    Tests
 * @subpackage String
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class MantisStringTest extends MantisCoreBase {

	/**
	 * Tests string_sanitize_url()
	 *
	 * @dataProvider provider
	 * @param string $p_in  Input.
	 * @param string $p_out Expected output.
	 * @return void
	 */
	public function testStringSanitize( $p_in, $p_out ) {
		$t_a = string_sanitize_url( $p_in, false );
		$this->assertEquals( $p_out, $t_a );

		# Since unit tests are run from command-line, with a default MantisBT
		# config $g_short_path will be that of the phpunit binary. We also
		# need to cover the case of Mantis being installed at the server's
		# root (i.e. $g_short_path = '/')
		config_set_global('short_path', '/');
		$t_a = string_sanitize_url($p_in, false);
		$this->assertEquals( $p_out, $t_a );
	}

	/**
	 * Data provider for string sanitize test
	 * @return array
	 */
	public function provider() {
		$t_test_strings = array(
			array( '', 'index.php' ),
			array( 'abc.php', 'abc.php' ),
			array( 'abc.php?', 'abc.php'),
			array( 'abc.php#a', 'abc.php#a'),
			array( 'abc.php?abc=def', 'abc.php?abc=def'),
			array( 'abc.php?abc=def#a', 'abc.php?abc=def#a'),
			array( 'abc.php?abc=def&z=xyz', 'abc.php?abc=def&z=xyz'),
			array( 'abc.php?abc=def&amp;z=xyz', 'abc.php?abc=def&z=xyz'),
			array( 'abc.php?abc=def&z=xyz#a', 'abc.php?abc=def&z=xyz#a'),
			array( 'abc.php?abc=def&amp;z=xyz#a', 'abc.php?abc=def&z=xyz#a'),
# @FIXME	array( 'abc.php?abc=def&z=<script>alert("foo")</script>z#a', 'abc.php?abc=def&z=alert%28%22foo%29%22%3cz#a'),
# @FIXME	array( 'abc.php?abc=def&z=z#<script>alert("foo")</script>a', 'abc.php?abc=def&z=z#alert%28%22foo%22%3ca'),
			array( 'plugin.php?page=Source/index', 'plugin.php?page=Source%2Findex'),
			array( 'plugin.php?page=Source/list&id=1', 'plugin.php?page=Source%2Flist&id=1'),
			array( 'plugin.php?page=Source/list&id=1#abc', 'plugin.php?page=Source%2Flist&id=1#abc'),
			array( 'login_page.php?return=http://google.com/', 'index.php'),
			array( 'javascript:alert(1);', 'index.php'),
			array( '\/csrf-22702', '%5C/csrf-22702' ),
		);

		# @FIXME
		#	array( $my_path.'abc.php',
		#	array( $my_path.'abc.php?',
		#	array( $my_path.'abc.php#a',
		#	array( $my_path.'abc.php?abc=def',
		#	array( $my_path.'abc.php?abc=def#a',
		#	array( $my_path.'abc.php?abc=def&z=xyz',
		#	array( $my_path.'abc.php?abc=def&amp;z=xyz',
		#	array( $my_path.'abc.php?abc=def&z=xyz#a',
		#	array( $my_path.'abc.php?abc=def&amp;z=xyz#a',
		#	array( $my_path.'abc.php?abc=def&z=<script>alert("foo")</script>z#a',
		#	array( $my_path.'abc.php?abc=def&z=z#<script>alert("foo")</script>a',
		#	array( $my_path.'plugin.php?page=Source/index',
		#	array( $my_path.'plugin.php?page=Source/list&id=1',
		#	array( $my_path.'plugin.php?page=Source/list&id=1#abc',
		#	array( 'http://www.test.my.url/'),

		return $t_test_strings;
	}

}
