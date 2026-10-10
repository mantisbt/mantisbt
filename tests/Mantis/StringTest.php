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
 * Test cases for String API within mantis
 *
 * @package    Tests
 * @subpackage String API
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

/**
 * PHPUnit tests for String API
 */
final class StringTest extends MantisCoreBase {

	private static string $path;
	private static string $short_path;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$path = config_get_global( 'path' );
		self::$short_path = config_get_global( 'short_path' );
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();

		config_set_global( 'path', self::$path );
		config_set_global( 'short_path', self::$short_path );
	}

	/**
	 * Tests string_sanitize_url()
	 *
	 * @dataProvider provider
	 * @param string  $p_in              Input.
	 * @param string  $p_out             Expected output.
	 * @param boolean $p_return_absolute Whether to use the absolute URL.
	 * @return void
	 */
	public function testStringSanitize( $p_in, $p_out, $p_return_absolute = false ) {
		config_set_global( 'short_path', $t_short_path = '/foo/bar/' );
		config_set_global( 'path', $t_path = 'https://foo.bar' . $t_short_path );
		$t_a = string_sanitize_url( str_replace( '{path}', $t_path, str_replace( '{short_path}', $t_short_path, $p_in ) ), $p_return_absolute );
		$this->assertEquals( str_replace( '{path}', $t_path, str_replace( '{short_path}', $t_short_path, $p_out ) ), $t_a );
	}

	/**
	 * Tests string_sanitize_url() with defaults
	 *
	 * Since unit tests are run from command-line, with a default MantisBT
	 * config $g_short_path will be that of the phpunit binary. We also
	 * need to cover the case of Mantis being installed at the server's
	 * root (i.e. $g_short_path = '/')
	 *
	 * @dataProvider provider
	 * @param string  $p_in              Input.
	 * @param string  $p_out             Expected output.
	 * @param boolean $p_return_absolute Whether to use the absolute URL.
	 * @return void
	 */
	public function testStringSanitizeDefault( $p_in, $p_out, $p_return_absolute = false ) {
		config_set_global( 'short_path', $t_short_path = '/' );
		config_set_global( 'path', $t_path = 'http://localhost' . $t_short_path );
		$t_a = string_sanitize_url( str_replace( '{path}', $t_path, str_replace( '{short_path}', $t_short_path, $p_in ) ), $p_return_absolute );
		$this->assertEquals( str_replace( '{path}', $t_path, str_replace( '{short_path}', $t_short_path, $p_out ) ), $t_a );
	}

	/**
	 * Data provider for string sanitize test
	 * @return array
	 */
	public static function provider() {
		return [
			# No path
			['', 'index.php'],
			['abc.php', 'abc.php'],
			['abc.php?', 'abc.php'],
			['abc.php#a', 'abc.php#a'],
			['abc.php?abc=def', 'abc.php?abc=def'],
			['abc.php?abc=def#a', 'abc.php?abc=def#a'],
			['abc.php?abc=def&z=xyz', 'abc.php?abc=def&z=xyz'],
			['abc.php?abc=def&amp;z=xyz', 'abc.php?abc=def&z=xyz'],
			['abc.php?abc=def&z=xyz#a', 'abc.php?abc=def&z=xyz#a'],
			['abc.php?abc=def&amp;z=xyz#a', 'abc.php?abc=def&z=xyz#a'],
			['abc.php?abc=def&z=<script>alert("foo")</script>z#a', 'abc.php?abc=def&z=alert%28%22foo%22%29z#a'],
			['abc.php?abc=def&z=z#<script>alert("foo")</script>a', 'abc.php?abc=def&z=z#alert%28%22foo%22%29a'],
			['plugin.php?page=Source/index', 'plugin.php?page=Source%2Findex'],
			['plugin.php?page=Source/list&id=1', 'plugin.php?page=Source%2Flist&id=1'],
			['plugin.php?page=Source/list&id=1#abc', 'plugin.php?page=Source%2Flist&id=1#abc'],
			['login_page.php?return=http://google.com/', 'index.php'],
			['javascript:alert(1);', 'index.php'],
			['\/csrf-22702', '%5C/csrf-22702'],
			['/abc.php', '/abc.php'],
			['/a/b/c/abc.php', '/a/b/c/abc.php'],
			['/', 'index.php'],

			# Short path
			['{short_path}abc.php', '{short_path}abc.php'],
			['{short_path}abc.php?', '{short_path}abc.php'],
			['{short_path}abc.php#a', '{short_path}abc.php#a'],
			['{short_path}abc.php?abc=def', '{short_path}abc.php?abc=def'],
			['{short_path}abc.php?abc=def#a', '{short_path}abc.php?abc=def#a'],
			['{short_path}abc.php?abc=def&z=xyz', '{short_path}abc.php?abc=def&z=xyz'],
			['{short_path}abc.php?abc=def&amp;z=xyz', '{short_path}abc.php?abc=def&z=xyz'],
			['{short_path}abc.php?abc=def&z=xyz#a', '{short_path}abc.php?abc=def&z=xyz#a'],
			['{short_path}abc.php?abc=def&amp;z=xyz#a', '{short_path}abc.php?abc=def&z=xyz#a'],
			['{short_path}abc.php?abc=def&z=<script>alert("foo")</script>z#a', '{short_path}abc.php?abc=def&z=alert%28%22foo%22%29z#a'],
			['{short_path}abc.php?abc=def&z=z#<script>alert("foo")</script>a', '{short_path}abc.php?abc=def&z=z#alert%28%22foo%22%29a'],
			['{short_path}plugin.php?page=Source/index', '{short_path}plugin.php?page=Source%2Findex'],
			['{short_path}plugin.php?page=Source/list&id=1', '{short_path}plugin.php?page=Source%2Flist&id=1'],
			['{short_path}plugin.php?page=Source/list&id=1#abc', '{short_path}plugin.php?page=Source%2Flist&id=1#abc'],
			['{short_path}login_page.php?return=http://google.com/', 'index.php'],
			['{short_path}javascript:alert(1);', 'index.php'],
			['{short_path}\/csrf-22702', '{short_path}%5C/csrf-22702'],

			['/{short_path}abc.php', '{short_path}abc.php'],
			['{short_path}/abc.php', '{short_path}abc.php'],

			['http://www.test.my.url/', 'index.php'],

			# Absolute output
			['abc.php', '{path}abc.php', true],
			['/abc.php', '{path}abc.php', true],
			['//abc.php', '{path}abc.php', true],
			['/a/b/c/abc.php', '{path}a/b/c/abc.php', true],
			['{short_path}abc.php', '{path}abc.php', true],
			['{short_path}/abc.php', '{path}abc.php', true],
			['/{short_path}abc.php', '{path}abc.php', true],
			['{short_path}a/b/c/abc.php', '{path}a/b/c/abc.php', true],
			['{path}abc.php', '{path}abc.php', true],
			['{path}/abc.php', '{path}abc.php', true],

			['http://www.test.my.url/', '{path}index.php', true],
		];
	}

}
