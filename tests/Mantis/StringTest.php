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

namespace Mantis\tests\Mantis;

/**
 * Mantis string handling test cases
 * @package    Tests
 * @subpackage String
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class StringTest extends MantisCoreBase {

	/**
	 * Tests string_sanitize_url()
	 *
	 * @dataProvider providerSanitize
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
	public static function providerSanitize() {
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


  /**
   * @dataProvider providerPrLink
   */
  public function testProcessPrLink(
    string $p_tag,
    string $p_url,
    string $p_in,
    bool $p_with_anchor,
    string $p_out
  ): void {
    config_set( 'pr_link_tag', $p_tag );
    config_set( 'pr_link_url', $p_url );
    self::assertSame( $p_out, string_process_pr_link( $p_in, $p_with_anchor ) );
  }

	/**
   * Data provider for PR link processing test.
   *
	 * @return array
	 */
  public function providerPrLink(): array {
    $t_valid_tag = 'pr:';
    $t_valid_url = 'https://gitrepository.com/user/repo/pull/{id}/commits';
    $t_invalid_url_1 = 'ssh://gitrepository.com/user/repo/pull/{id}/commits';
    $t_invalid_url_2 = 'https://gitrepository.com/user/repo/pull/-id-/commits';
    $t_input_no_tag = 'See rp:1234 for more info';
    $t_input_tag = 'See pr:1234 for more info';
    $t_input_tag_start = 'pr:1234 for more info';
    $t_processed_url = 'https://gitrepository.com/user/repo/pull/1234/commits';
    $t_output_no_anchor = 'See ' . $t_processed_url . ' for more info';
    $t_output_anchor = 'See <a href="' . $t_processed_url . '">pr:1234</a> for more info';
    $t_output_start = $t_processed_url . ' for more info';
    return [
      'Empty tag' => ['', $t_valid_url, $t_input_tag, true, $t_input_tag],
      'Empty URL' => [$t_valid_tag, '', $t_input_tag, true, $t_input_tag],
      'Empty input' => [$t_valid_tag, $t_valid_url, '', true, ''],
      'Invalid URL (wrong protocol)' => [$t_valid_tag, $t_invalid_url_1, $t_input_tag, true, $t_input_tag],
      'Invalid URL (wrong placeholder)' => [$t_valid_tag, $t_invalid_url_2, $t_input_tag, true, $t_input_tag],
      'Input without tag' => [$t_valid_tag, $t_valid_url, $t_input_no_tag, true, $t_input_no_tag],
      'Input with tag (no anchor)' => [$t_valid_tag, $t_valid_url, $t_input_tag, false, $t_output_no_anchor],
      'Input with tag (anchor)' => [$t_valid_tag, $t_valid_url, $t_input_tag, true, $t_output_anchor],
      'Input with tag at start' => [$t_valid_tag, $t_valid_url, $t_input_tag_start, false, $t_output_start]
    ];
  }

}
