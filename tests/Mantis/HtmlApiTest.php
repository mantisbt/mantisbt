<?php declare(strict_types=1);
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
 * Test cases for HTML API within mantis
 *
 * @package    Tests
 * @subpackage HtmlAPI
 * @copyright  Copyright 2025  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link       https://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

/**
 * PHPUnit tests for HTML API
 */
final class HtmlApiTest extends MantisCoreBase {

	private static string $short_path;

	public static function setUpBeforeClass(): void {
		self::$short_path = config_get_global( 'short_path' );
		config_set_global( 'short_path', '/test_dir/' );
	}

	public static function tearDownAfterClass(): void {
		config_set_global( 'short_path', self::$short_path );
	}
	
	/**
	 * Tests print_menu()
	 *
	 * @dataProvider providerPrintMenu
	 * @param mixed  $p_menu_items   List of menu items.
	 * @param string $p_current_page Current page's file name to highlight active tab.
	 * @param mixed  $p_expected     Expected result.
	 * @return void
	 */
	public function testPrintMenu( $p_menu_items, $p_current_page, $p_expected ): void {
		if( is_subclass_of( $p_expected, '\Throwable' ) ) { 
			$this->expectException( $p_expected );
		} else {
			$this->expectOutputString( $p_expected );
		}
		print_menu( $p_menu_items, $p_current_page, null );
	}

	/**
	 * Data provider for testPrintMenu
	 *
	 * @return array
	 */
	public static function providerPrintMenu(): array {
		$t_begin = '<ul class="nav nav-tabs padding-18">' . "\n";
		$t_end = '</ul>' . "\n";
		$t_no_label= '<i class="fa fa-info-circle blue ace-icon" ></i>';

		return [
			# Error parameter tests
			[
				null,
				'',
				\TypeError::class
			],
			# Empty tests
			[
				[],
				'',
				$t_begin . $t_end
			],
			[
				[ '' => [ 'url' => '', 'label' => '' ]  ],
				'',
				$t_begin . '<li class=""><a href="">' . $t_no_label . '</a></li>' . "\n" . $t_end
			],
			# 'Absolute' parameter tests
			[
				[ '' => [ 'url' => 'test.php', 'label' => '', 'absolute' ]  ],
				'',
				$t_begin . '<li class=""><a href="/test_dir/test.php">' . $t_no_label . '</a></li>' . "\n" . $t_end
			],
			[
				[ '' => [ 'url' => 'test.php', 'label' => '', 'absolute' => false ]  ],
				'',
				$t_begin . '<li class=""><a href="/test_dir/test.php">' . $t_no_label . '</a></li>' . "\n" . $t_end
			],
			[
				[ '' => [ 'url' => 'test.php', 'label' => '', 'absolute' => true ]  ],
				'',
				$t_begin . '<li class=""><a href="test.php">' . $t_no_label . '</a></li>' . "\n" . $t_end
			],
			# Current page test
			[
				[ '' => [ 'url' => 'test.php', 'label' => '' ]  ],
				'test.php',
				$t_begin . '<li class="active"><a href="/test_dir/test.php">' . $t_no_label . '</a></li>' . "\n" . $t_end
			],
			# 'Label' test
			[
				[ '' => [ 'url' => '', 'label' => 'add_file' ] ],
				'',
				$t_begin . '<li class=""><a href="">' . lang_get_defaulted( 'add_file' ) . '</a></li>' . "\n" . $t_end
			],
		];
	}
}
