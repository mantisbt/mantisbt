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
	protected function setUp() {
		parent::setUp();

		$this->CreateVarCharTables();
	}

	protected function tearDown() {
		$this->DropVarCharTables();
		
		parent::tearDown();
	}
	
	private function CreateVarCharTables() {
		$this->CreateTable( '{varchar_def}' );
		$this->CreateTable( '{varchar_nodef}' );
		$this->CreateTable( '{varchar_nulls}' );			
		$this->CreateTable( '{varchar_short}' );
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
		global $g_dict;
				
		// Basic Tests to Insert/Read Data
		$t_result = MantisDatabase::GetInstance()->Execute( 'INSERT INTO {varchar_def} (chars) VALUES ( %s )', array( 'moo' ) );
		$t_result = MantisDatabase::GetInstance()->Execute( 'INSERT INTO {varchar_def} () VALUES ( )', array( ) );
		
		$t_result = MantisDatabase::GetInstance()->Execute( 'SELECT chars FROM {varchar_def} WHERE id=%d', array( 1 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'moo');

		$t_result = MantisDatabase::GetInstance()->Execute( 'SELECT chars FROM {varchar_def} WHERE id=%d', array( 2 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'foo');
	}
	
	/**
	 * Test Handling of VarChars - Without Default Value
	 * a) check we can insert a value
	 * b) check that db query fails if we attempt to insert a null value
	 */
	public function testDBVarCharHandlingNoDefault() {
		global $g_dict;
				
		// Basic Tests to Insert/Read Data
		$t_result = MantisDatabase::GetInstance()->Execute( 'INSERT INTO {varchar_nodef} (chars) VALUES ( %s )', array( 'moo' ) );

		$t_result = MantisDatabase::GetInstance()->Execute( 'SELECT chars FROM {varchar_nodef} WHERE id=%d', array( 1 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'moo');
		
		$this->setExpectedException(
          'MantisBT\Exception\Database\QueryFailed', ''
        );
		$t_result = MantisDatabase::GetInstance()->Execute( 'INSERT INTO {varchar_nodef} () VALUES ( )', array( ) );
	}
	
	/**
	 * Test Handling of VarChars - Without Default Value
	 * a) check we can insert a value
	 * b) check that db query accepts a null value
	 */
	public function testDBVarCharHandlingNulls() {
		global $g_dict;
				
		// Basic Tests to Insert/Read Data
		$t_result = MantisDatabase::GetInstance()->Execute( 'INSERT INTO {varchar_nulls} (chars) VALUES ( %s )', array( 'moo' ) );

		$t_result = MantisDatabase::GetInstance()->Execute( 'SELECT chars FROM {varchar_nulls} WHERE id=%d', array( 1 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'moo');
		
		$t_result = MantisDatabase::GetInstance()->Execute( 'INSERT INTO {varchar_nulls} () VALUES ( )', array( ) );

		$t_result = MantisDatabase::GetInstance()->Execute( 'SELECT chars FROM {varchar_nulls} WHERE id=%d', array( 2 ) );
		$this->assertNull( $t_result->fetch()['chars']);
	}
	
	/**
	 * Test Handling of VarChars - Short Length
	 * a) check we can insert a value
	 * b) check that db query accepts a rejects a long value
	 */
	public function testDBVarCharHandlingShort() {
		global $g_dict;
				
		// Basic Tests to Insert/Read Data
		$t_result = MantisDatabase::GetInstance()->Execute( 'INSERT INTO {varchar_short} (chars) VALUES ( %s )', array( 'mo' ) );

		$t_result = MantisDatabase::GetInstance()->Execute( 'SELECT chars FROM {varchar_short} WHERE id=%d', array( 1 ) );
		$this->assertEquals( $t_result->fetch()['chars'], 'mo');

		$this->setExpectedException(
          'MantisBT\Exception\Database\QueryFailed', ''
        );		
		$t_result = MantisDatabase::GetInstance()->Execute( 'INSERT INTO {varchar_short} () VALUES ( %s )', array( 'moo' ) );

		$t_result = MantisDatabase::GetInstance()->Execute( 'SELECT chars FROM {varchar_short} WHERE id=%d', array( 2 ) );
		$this->assertNull( $t_result->fetch()['chars']);
	}
}