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
 * Mantis Database Tests
 *
 * @package Tests
 * @subpackage UnitTests
 * @copyright Copyright 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$t_root_path = dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR;

/**
 * MantisBT constants
 */
require_once ( $t_root_path . DIRECTORY_SEPARATOR . 'core/constant_inc.php' );

/**
 * Test cases for Database classes.
 */
class DatabaseBase extends PHPUnit_Framework_TestCase {
	/**
	 * Database Name
	 */
	protected $database_name;

	protected $db;

	protected $dict;

	/**
	 * setUp
	 */
	protected function setUp()
	{
		global $g_db, $g_db_type, $g_hostname, $g_db_username, $g_db_password, $g_prefix, $g_suffix;

		if (array_key_exists('MANTIS_TESTSUITE_DATABASE_NAME', $GLOBALS)) {
			$this->database_name = $GLOBALS['MANTIS_TESTSUITE_DATABASE_NAME'];
		} else {
			$this->database_name = 'MantisBTTests';
		}

		$g_db = MantisDatabase::GetDriverInstance($g_db_type);
		$t_result = $g_db->connect( null, $g_hostname, $g_db_username, $g_db_password, null, null );

		$this->dict = MantisDatabaseDict::GetDriverInstance($g_db_type);

		$this->assertEquals( $g_db->DatabaseExists( $this->database_name ), false );

		$t_sqlarray = $this->dict->CreateDatabase( $this->database_name );
		$ret = $this->dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertEquals( $g_db->DatabaseExists( $this->database_name ), true );

		$g_db = MantisDatabase::GetDriverInstance($g_db_type);
		$t_result = $g_db->connect( null, $g_hostname, $g_db_username, $g_db_password, $this->database_name, null );
		$g_db->SetPrefixes( $g_prefix, $g_suffix );

		$this->db = $g_db;
	}

	/**
	 * tearDown
	 */
	protected function tearDown() {
		global $g_db, $g_db_type, $g_hostname, $g_db_username, $g_db_password, $g_prefix, $g_suffix;
		$this->assertEquals( $g_db->DatabaseExists( $this->database_name ), true );

		$g_db = MantisDatabase::GetDriverInstance($g_db_type);
		$t_result = $g_db->connect( null, $g_hostname, $g_db_username, $g_db_password, null, null );

		$t_sqlarray = $this->dict->DropDatabase( $this->database_name );
		$ret = $this->dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertEquals( $g_db->DatabaseExists( $this->database_name ), false );

		/*$t = $this->db->GetLastError();
		if ( $t !== null ) {
			var_dump($t);
		}*/
	}
}
