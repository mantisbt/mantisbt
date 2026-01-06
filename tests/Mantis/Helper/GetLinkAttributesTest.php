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
 * MantisBT Core Unit Tests
 * @package Tests
 * @subpackage Helper
 * @copyright Copyright 2024  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 */

declare( strict_types = 1 );

namespace Mantis\tests\Mantis\Helper;

use Generator;
use Mantis\tests\Mantis\MantisCoreBase;

/**
 * Tests for Link Attributes (Helper API).
 *
 * @see helper_get_link_attributes()
 * @see helper_is_link_external()
 */
class GetLinkAttributesTest extends MantisCoreBase
{
	const CFG_MAKE_LINKS = 'html_make_links';

	/**
	 * Link attributes are correctly created according to the
	 * configuration of {@see $g_html_make_links}.
	 *
	 * 1. Helper returns an array on default.
	 * 2. Helper returns the attributes as a string if the
	 *    argument "p_return_array" is set to false.
	 *
	 * @param int $p_value                   Value for $g_html_make_links config
	 * @param string $p_string               The expected result as a string
	 * @param array<string, string> $p_array The expected result as an array
	 *
	 * @dataProvider provideConfigurations
	 */
	public function testHelperReturnsArrayOrString( int $p_value, string $p_string, array $p_array ): void {
		$t_old = $this->setConfig( self::CFG_MAKE_LINKS, $p_value );

		# 1.
		$this->assertSame( $p_array, helper_get_link_attributes() );
		# 2.
		$this->assertSame( $p_string, trim( helper_get_link_attributes( false ) ) );

		$this->restoreConfig( self::CFG_MAKE_LINKS, $t_old );
	}

	public static function provideConfigurations(): Generator {
		yield 'LINKS_SAME_WINDOW' => [
			LINKS_SAME_WINDOW,
			'',
			[],
		];

		yield 'LINKS_NEW_WINDOW' => [
			LINKS_NEW_WINDOW,
			'target="_blank"',
			[
				'target' => '_blank'
			]
		];

		yield 'LINKS_NOOPENER' => [
			LINKS_NOOPENER,
			'rel="noopener"',
			['rel' => 'noopener']
		];

		yield 'LINKS_NOREFERRER' => [
			LINKS_NOREFERRER,
			'rel="noreferrer"',
			['rel' => 'noreferrer']
		];

		yield 'LINKS_NOOPENER | LINKS_NOREFERRER' => [
			LINKS_NOOPENER | LINKS_NOREFERRER,
			'rel="noreferrer"',
			['rel' => 'noreferrer']
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOOPENER' => [
			LINKS_NEW_WINDOW | LINKS_NOOPENER,
			'target="_blank" rel="noopener"',
			['target' => '_blank', 'rel' => 'noopener']
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOREFERRER' => [
			LINKS_NEW_WINDOW | LINKS_NOREFERRER,
			'target="_blank" rel="noreferrer"',
			['target' => '_blank', 'rel' => 'noreferrer']
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOOPENER | LINKS_NOREFERRER' => [
			LINKS_NEW_WINDOW | LINKS_NOOPENER | LINKS_NOREFERRER,
			'target="_blank" rel="noreferrer"',
			['target' => '_blank', 'rel' => 'noreferrer']
		];
	}

	/**
	 * Testing {@see helper_is_link_external()}.
	 *
	 * @dataProvider providerLinks
	 */
	public function testLinkIsExternal( string $p_url, bool $p_external ): void
	{
		$this->assertEquals( $p_external, helper_is_link_external( $p_url ), "URL is external" );
	}

	public static function providerLinks(): Generator {
		yield 'External URL' => [
			'https://example.com',
			true,
		];

		$t_path = config_get_global('path' );
		yield 'Mantis URL' => [
			$t_path,
			false,
		];

		yield 'Relative URL' => [
			'/index.html',
			false,
		];
	}

	/**
	 * Tests that links have the "nofollow" attribute set as appropriate.
	 *
	 * @param int   $p_value    Value for html_make_links config.
	 * @param array $p_internal Expected values for internal links ().
	 * @param array $p_external Expected values for external links ().
	 *
	 * @dataProvider providerNoFollow
	 */
	public function testNoFollow( int $p_value, array $p_internal, array $p_external ): void
	{
		$t_old = $this->setConfig( self::CFG_MAKE_LINKS, $p_value );

		# Test internal links
		$this->assertSame( $p_internal, helper_get_link_attributes() );

		# Test internal links
		$this->assertSame( $p_external, helper_get_link_attributes(true, true) );

		$this->restoreConfig( self::CFG_MAKE_LINKS, $t_old );
	}

	public static function providerNoFollow(): Generator {
		yield 'LINKS_NOFOLLOW_EXTERNAL' => [
			LINKS_NOFOLLOW_EXTERNAL,
			[],
			['rel' => 'nofollow'],
		];

		yield 'LINKS_NOOPENER | LINKS_NOFOLLOW_EXTERNAL' => [
			LINKS_NOOPENER | LINKS_NOFOLLOW_EXTERNAL,
			['rel' => 'noopener'],
			['rel' => 'noopener,nofollow'],
		];
	}
}
