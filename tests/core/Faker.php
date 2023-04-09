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
 * Mantis Webservice Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * A class that supplies fake data.
 */
class Faker {
	/**
	 * Create a fake username
	 *
	 * @return string A random username
	 */
	public static function username() {
		return 'testuser_' . self::randStr( 10 );
	}

	/**
	 * Create a fake password
	 *
	 * @return string A random password
	 */
	public static function password() {
		return self::randStr( 10 ) . '!@#$%^&*()';
	}

	/**
	 * Create a fake email address
	 *
	 * @return string A random email address
	 */
	public static function email() {
		return self::randStr( 10 ) . '@somedomain.com';
	}

	/**
	 * Create a fake real name
	 *
	 * @return string A random real name
	 */
	public static function realname() {
		return self::randStr( 10 ) . ' ' . self::randStr( 10 );
	}

	/**
	 * Create a random string of the specified length.
	 *
	 * @param int $p_length The length of the string to generate
	 * @return string the random string
	 */
	public static function randStr( $p_length = 10 ) {
		$t_characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$t_characters_length = strlen( $t_characters );
		$t_randomString = '';

		for ( $i = 0; $i < $p_length; $i++ ) {
			$t_randomString .= $t_characters[random_int(0, $t_characters_length - 1)];
		}

		return $t_randomString;
	}
}
