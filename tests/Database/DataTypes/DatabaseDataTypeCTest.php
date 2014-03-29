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

require_once 'Database/DatabaseBase.php';

/**
 *
 *
 * @group DATABASE
 */
class DatabaseDataTypeCTest extends DatabaseBase
{	
	private $table_name = 'TestTable';

	protected function setUp() {
		parent::setUp();

		$this->CreateVarCharTables();
	}

	protected function tearDown() {
		global $g_db, $g_dict, $g_db_type, $g_hostname, $g_db_username, $g_db_password, $g_prefix, $g_suffix;

		$this->DropVarCharTables();
		
		parent::tearDown();

	}
	
	private function DropTable( $p_table ) {
		global $g_db, $g_dict;
		$t_sqlarray = $g_dict->DropTable( $p_table );
		$ret = $g_dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);
		
		$this->assertFalse( $g_db->TableExists( $p_table ) );
	}
	
	private function CheckNoTable( $p_table ) {
		global $g_db, $g_dict;
		$this->assertFalse( $g_db->TableExists( $p_table ) );
	}

	private function CreateTable( $p_table, $p_definition ) {
		global $g_db, $g_dict;
		
		$this->CheckNoTable( $p_table );
		$t_sqlarray = $g_dict->CreateTable( $p_table, $p_definition);
		$ret = $g_dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertTrue( $g_db->TableExists( $p_table ) );
	}
	
	private function CreateVarCharTables() {
		$this->CreateTable( '{varchar_def}', "
				id		 	I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(250) NOTNULL DEFAULT 'foo'
				");

		$this->CreateTable( '{varchar_nodef}', "
				id		 	I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(250) NOTNULL 
				");

		$this->CreateTable( '{varchar_nulls}', "
				id		 	I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(250)
				");
				
		$this->CreateTable( '{varchar_short}', "
				id		 	I  UNSIGNED NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(2)
				");
		
	}
	
	private function DropVarCharTables() {
		$this->DropTable( '{varchar_def}' );
		$this->DropTable( '{varchar_nodef}' );
		$this->DropTable( '{varchar_nulls}' );
		$this->DropTable( '{varchar_short}' );
	}

	/**
	 * Test Handling of VarChars - With Default Value
	 * a) Check we can insert a value
	 * b) check that we can insert no-value and default is used
	 */
	public function testDBVarCharHandlingDefault() {
		global $g_db, $g_dict;
				
		// Basic Tests to Insert/Read Data
		$t_result = $g_db->Execute( 'INSERT INTO {varchar_def} (chars) VALUES ( %s )', array( 'moo' ) );
		$t_result = $g_db->Execute( 'INSERT INTO {varchar_def} () VALUES ( )', array( ) );
		
		$t_result = $g_db->Execute( 'SELECT chars FROM {varchar_def} WHERE id=%d', array( 1 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'moo');

		$t_result = $g_db->Execute( 'SELECT chars FROM {varchar_def} WHERE id=%d', array( 2 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'foo');
	}
	
	/**
	 * Test Handling of VarChars - Without Default Value
	 * a) check we can insert a value
	 * b) check that db query fails if we attempt to insert a null value
	 */
	public function testDBVarCharHandlingNoDefault() {
		global $g_db, $g_dict;
				
		// Basic Tests to Insert/Read Data
		$t_result = $g_db->Execute( 'INSERT INTO {varchar_nodef} (chars) VALUES ( %s )', array( 'moo' ) );

		$t_result = $g_db->Execute( 'SELECT chars FROM {varchar_nodef} WHERE id=%d', array( 1 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'moo');
		
		$this->setExpectedException(
          'MantisBT\Exception\Database\QueryFailed', ''
        );
		$t_result = $g_db->Execute( 'INSERT INTO {varchar_nodef} () VALUES ( )', array( ) );
	}
	
	/**
	 * Test Handling of VarChars - Without Default Value
	 * a) check we can insert a value
	 * b) check that db query accepts a null value
	 */
	public function testDBVarCharHandlingNulls() {
		global $g_db, $g_dict;
				
		// Basic Tests to Insert/Read Data
		$t_result = $g_db->Execute( 'INSERT INTO {varchar_nulls} (chars) VALUES ( %s )', array( 'moo' ) );

		$t_result = $g_db->Execute( 'SELECT chars FROM {varchar_nulls} WHERE id=%d', array( 1 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'moo');
		
		$t_result = $g_db->Execute( 'INSERT INTO {varchar_nulls} () VALUES ( )', array( ) );

		$t_result = $g_db->Execute( 'SELECT chars FROM {varchar_nulls} WHERE id=%d', array( 2 ) );
		$this->assertNull( $t_result->fetch()['chars']);
	}
	
	/**
	 * Test Handling of VarChars - Short Length
	 * a) check we can insert a value
	 * b) check that db query accepts a rejects a long value
	 */
	public function testDBVarCharHandlingShort() {
		global $g_db, $g_dict;
				
		// Basic Tests to Insert/Read Data
		$t_result = $g_db->Execute( 'INSERT INTO {varchar_short} (chars) VALUES ( %s )', array( 'mo' ) );

		$t_result = $g_db->Execute( 'SELECT chars FROM {varchar_short} WHERE id=%d', array( 1 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'mo');

		$this->setExpectedException(
          'MantisBT\Exception\Database\QueryFailed', ''
        );		
		$t_result = $g_db->Execute( 'INSERT INTO {varchar_short} () VALUES ( %s )', array( 'moo' ) );

		$t_result = $g_db->Execute( 'SELECT chars FROM {varchar_short} WHERE id=%d', array( 2 ) );
		$this->assertNull( $t_result->fetch()['chars']);
	}
}