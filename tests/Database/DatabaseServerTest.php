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

/**
 * Database Level Tests
 * a) Create/Drop a database
 * b) Server Version
 *
 * @group DATABASE
 */
class DatabaseServerTest extends PHPUnit_Framework_TestCase
{
	protected function setUp() {
		if (array_key_exists('MANTIS_TESTSUITE_DATABASE_NAME', $GLOBALS)) {
			$this->database_name = $GLOBALS['MANTIS_TESTSUITE_DATABASE_NAME'] . 'a';
		} else {
			$this->database_name = 'MantisBTTests';
		}

		global $g_dict, $g_db_type, $g_hostname, $g_db_username, $g_db_password, $g_db_table_prefix, $g_db_table_suffix;

		$g_db = MantisDatabase::GetInstance($g_db_type);
 		$t_result = $g_db->connect( null, $g_hostname, $g_db_username, $g_db_password, null, null );
		$g_db->SetPrefixes( $g_db_table_prefix, $g_db_table_suffix );
		
		$g_dict = MantisDatabaseDict::GetDriverInstance($g_db_type);
	}

	/**
	 * @expectedException MantisBT\Exception\Database\InvalidDriver
	 */
    public function testExceptionIsRaisedForInvalidConstructorArguments()
    {
        MantisDatabase::GetInstance('Wibble');
    }
	
	public function testCreateDropDatabase() {
		global $g_dict;
		
		$this->assertEquals( MantisDatabase::GetInstance()->DatabaseExists( $this->database_name ), false );
				
		$t_sqlarray = $g_dict->CreateDatabase( $this->database_name );
		$ret = $g_dict->ExecuteSQLarray( $t_sqlarray );	
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertEquals( MantisDatabase::GetInstance()->DatabaseExists( $this->database_name ), true );

		$t_sqlarray = $g_dict->CreateDatabase( $this->database_name );
		$t_failed = false;
		try {
			$ret = $g_dict->ExecuteSQLarray( $t_sqlarray );
		} catch (MantisBT\Exception\Database\QueryFailed $ex) {
			$t_failed = true;
		}

		$this->assertTrue( $t_failed );

		$this->assertEquals( MantisDatabase::GetInstance()->DatabaseExists( $this->database_name ), true );

		$t_sqlarray = $g_dict->DropDatabase( $this->database_name );
		$ret = $g_dict->ExecuteSQLarray( $t_sqlarray );	
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertEquals( MantisDatabase::GetInstance()->DatabaseExists( $this->database_name ), false );
	}
	
	public function testGetVersion() {
		global $g_dict;
		
		$t_result = MantisDatabase::GetInstance()->GetServerInfo();
		
		$this->assertNotNull( $t_result['version'] );
		$this->assertNotNull( $t_result['description'] );
		$this->assertNotNull( $t_result['information'] );
		
		$this->assertTrue( version_compare( $t_result['version'], '1.0' ) >= 0 ); 
	}
}