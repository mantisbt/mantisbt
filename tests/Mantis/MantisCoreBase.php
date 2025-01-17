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
 * MantisBT Prepare API test cases
 *
 * @package    Tests
 * @subpackage MantisCoreTests
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

# Includes
require_once dirname( __DIR__ ) . '/TestConfig.php';

# MantisBT Core API
require_mantis_core();

abstract class MantisCoreBase extends PHPUnit\Framework\TestCase {

	/**
	 * @var string Username
	 */
	protected static $userName = 'administrator';

	/**
	 * @var string Password
	 */
	protected static $password = 'root';

	/**
	 * MantisCore tests setup
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		if( array_key_exists( 'MANTIS_TESTSUITE_USERNAME', $GLOBALS ) ) {
			self::$userName = $GLOBALS['MANTIS_TESTSUITE_USERNAME'];
		}

		if( array_key_exists( 'MANTIS_TESTSUITE_PASSWORD', $GLOBALS ) ) {
			self::$password = $GLOBALS['MANTIS_TESTSUITE_PASSWORD'];
		}
	}

	/**
	 * Login as defined test suite user, with fall-back to anonymous user.
	 *
	 * Some tests require a logged-in user to function properly.
	 *
	 * @param boolean $p_anonymous true to login anonymously,
	 *                             false (default) to login as test suite user
	 */
	public static function login( $p_anonymous = false ) {
		$t_msg = '';
		if( !$p_anonymous ) {
			$t_logged_in = auth_attempt_script_login( self::$userName, self::$password );
			$t_user = sprintf( "'%s' or ", self::$userName );
		} else {
			$t_user = '';
			$t_logged_in = false;
		}
		if( !$t_logged_in ) {
			# Login failed, try again as anonymous user
			$t_logged_in = auth_attempt_script_login( null );
			$t_msg = sprintf(
				'Login as %s failed - must be logged in to perform test',
				$t_user . 'Anonymous User'
			);
		}
		self::assertTrue( $t_logged_in, $t_msg );
	}

	/**
	 * Utility function to establish DB connection.
	 *
	 * PHPUnit seems to kill the connection after each test case execution;
	 * this allows individual test cases that need the DB to reopen it easily.
	 */
	public static function dbConnect() {
		global $g_hostname, $g_db_username, $g_db_password, $g_database_name,
			   $g_use_persistent_connections;

		db_connect(
			config_get_global( 'dsn', false ),
			$g_hostname,
			$g_db_username,
			$g_db_password,
			$g_database_name,
			$g_use_persistent_connections == ON
		);
	}

	/**
	 * Sets the given configuration and returns its old value.
	 *
	 * @param string $p_config Configuration option name.
	 * @param mixed  $p_value  Configuration option value.
	 *
	 * @return mixed The config's old value, false if it was not set in the database.
	 */
	protected function setConfig( string $p_config, $p_value ) {
		$t_old = config_is_set_in_database( $p_config ) ? config_get( $p_config ) : false;
		config_set( $p_config, $p_value );

		return $t_old;
	}

	/**
	 * Restores a configuration to its initial state.
	 *
	 * @param string $p_config Configuration option name.
	 * @param mixed  $p_value  Configuration option value. If false, the config
	 *                         will be deleted.
	 */
	protected function restoreConfig( string $p_config, $p_value ) {
		if( $p_value === false ) {
			config_delete( $p_config );
		} else {
			config_set( $p_config, $p_value );
		}
	}
}

