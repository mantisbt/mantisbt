<?php

declare(strict_types=1);

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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 */

require_once dirname( __FILE__, 2 ) . '/MantisCoreBase.php';

/**
 * Test for helper_api::helper_get_link_attributes
 *
 * @see helper_get_link_attributes()
 */
class GetLinkAttributesTest extends MantisCoreBase
{
	/**
	 * Link attributes are correctly created accordingly to the
	 * configuration of "$g_html_make_links"
	 *
	 * 1. Helper returns an array on default.
	 * 2. Helper returns the attributes as a string if the
	 *    argument "p_return_array" is set to false.
	 *
	 * @dataProvider provideConfigurations
	 */
	public function testHelperReturnsArrayOrString( int $p_config, string $p_string, array $p_array ): void {
		config_set('html_make_links', $p_config );

		# 1.
		$this->assertSame( $p_array, helper_get_link_attributes() );
		# 2.
		$this->assertSame( $p_string, trim( helper_get_link_attributes( false ) ) );
	}

	public function provideConfigurations(): \Generator {
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
			[
				'rel' => 'noopener'
			]
		];

		yield 'LINKS_NOREFERRER' => [
			LINKS_NOREFERRER,
			'rel="noreferrer"',
			[
				'rel' => 'noreferrer'
			]
		];

		yield 'LINKS_NOOPENER | LINKS_NOREFERRER' => [
			LINKS_NOOPENER | LINKS_NOREFERRER,
			'rel="noreferrer"',
			[
				'rel' => 'noreferrer'
			]
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOOPENER' => [
			LINKS_NEW_WINDOW | LINKS_NOOPENER,
			'target="_blank" rel="noopener"',
			[
				'target' => '_blank',
				'rel' => 'noopener'
			]
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOREFERRER' => [
			LINKS_NEW_WINDOW | LINKS_NOREFERRER,
			'target="_blank" rel="noreferrer"',
			[
				'target' => '_blank',
				'rel' => 'noreferrer'
			]
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOOPENER | LINKS_NOREFERRER' => [
			LINKS_NEW_WINDOW | LINKS_NOOPENER | LINKS_NOREFERRER,
			'target="_blank" rel="noreferrer"',
			[
				'target' => '_blank',
				'rel' => 'noreferrer'
			]
		];
	}
}
