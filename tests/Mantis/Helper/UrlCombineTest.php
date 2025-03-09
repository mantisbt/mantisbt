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
 * MantisBT Core Unit Tests
 *
 * @package    Tests
 * @subpackage Helper
 * @copyright  Copyright 2025 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://www.mantisbt.org
 */

declare(strict_types=1);

namespace Mantis\tests\Mantis\Helper;

use Mantis\tests\Mantis\MantisCoreBase;

/**
 * Tests for combine URL (Helper API).
 *
 * @see helper_url_combine()
 */
final class UrlCombineTest extends MantisCoreBase {

	/**
	 * Tests helper_url_combine()
	 *
	 * @param string       $p_page         The page.
	 * @param string|array $p_query_string The query string or array of query parameters.
	 * @param mixed        $p_expected     Expected result.
	 * @return void
	 *
	 * @dataProvider providerUrlCombine
	 */
	public function testUrlCombine( $p_page, $p_query_string, $p_expected ): void {
		if( is_subclass_of( $p_expected, '\Throwable' ) ) { 
			$this->expectException( $p_expected );
			helper_url_combine( $p_page, $p_query_string );
		} else {
			$this->assertEquals( $p_expected, helper_url_combine( $p_page, $p_query_string ) );
		}
	}

	/**
	 * Data provider for testUrlCombine
	 *
	 * @return array
	 */
	public static function providerUrlCombine(): array {
		return [
			'Error parameter (null)' => [ null, null, \TypeError::class ],
			'Error parameter (array)' => [ [], null, \TypeError::class ],
			'Empty parameter (null)' => [ '', null, '' ],
			'Empty parameter (string)' => [ '', '', '' ],
			'Empty parameter (array)' => [ '', [], '' ],
			'String parameter (page only)' => [ 'page', '', 'page' ],
			'String parameter (query only)' => [ '', 'query', '?query' ],
			'String parameter (page? only)' => [ 'page?', '', 'page?' ],
			'String parameter (page + query)' => [ 'page', 'query', 'page?query' ],
			'String parameter (page? + query)' => [ 'page?', 'query', 'page?query' ],
			'String parameter (full)' => [ 'page?query1', 'query2', 'page?query1&query2' ],
			'Array parameter (empty)' => [ 'page', [], 'page' ],
			'Array parameter (full)' => [ 'page', [ 'query1' => 'value1', 'query2' => 'value2', ], 'page?query1=value1&query2=value2' ],
			'Array parameter (urlencode)' => [ 'page', [ '<query~> &?=' => '<value~> &?=' ], 'page?%3Cquery~%3E%20%26%3F%3D=%3Cvalue~%3E%20%26%3F%3D' ],
		];
	}
}
