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

	public $definitions = array( 
		'{id_def}' => 'id	I PRIMARY AUTOINCREMENT',
		'{id_def_unsigned}' => 'id I UNSIGNED PRIMARY AUTOINCREMENT',
		'{varchar_def}' => " id		 	I  NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(250) NOTNULL DEFAULT 'foo' ",
		'{varchar_nodef}' => "id		 	I  NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(250) NOTNULL ",
		'{varchar_nulls}' => " id		 	I  NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(250)",
		'{varchar_short}' => "id		 	I  NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(2)",
		'{varchar_multiline}' => "id		 	I  NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(250) NOTNULL DEFAULT 'foo
				bar'",
		'{varchar_long}' => "id		 	I  NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(3999) NOTNULL ",
		'{varchar_toolong}' => "id		 	I  NOTNULL PRIMARY AUTOINCREMENT,
				chars		C(4001) NOTNULL ",
		'{primary_keys}' => " idchar C(64) NOTNULL PRIMARY,
							idint1 I DEFAULT 0 PRIMARY,
							idint2 I DEFAULT 0 PRIMARY,
							nonid I DEFAULT 2",
		'{unsignedint_negative}' => 'id I UNSIGNED DEFAULT -1',
		'{int_negative}' => 'id I DEFAULT -1',
		'{int_large_default}' => 'id I UNSIGNED DEFAULT 2147483647',
		'{int_bad_default}' => 'id I UNSIGNED DEFAULT 2147483648',
		'{int_bad_default2}' => "id I UNSIGNED DEFAULT 'foo'",
		'{int_bad_default3}' => "id I UNSIGNED DEFAULT 10.9",
		'{bool_true}' => "bid L DEFAULT '1'",
		'{bool_false}' => "bid L DEFAULT '0'",
		'{bool_bad_default}' => "id L DEFAULT 10",
		'{bool_bad_default2}' => "id L DEFAULT 'a'",

		'{unsignedtinyint_negative}' => 'id I2 UNSIGNED DEFAULT -1',
		'{tinyint_negative}' => 'id I2 DEFAULT -1',
		'{tinyint_large_default}' => 'id I2 UNSIGNED DEFAULT 32767',
		'{tinyint_bad_default}' => 'id I2 UNSIGNED DEFAULT 32768',
		'{tinyint_bad_default2}' => "id I2 UNSIGNED DEFAULT 'foo'",
		'{tinyint_bad_default3}' => "id I2 UNSIGNED DEFAULT 10.9",

		'{unsignedbigint_negative}' => 'id I8 UNSIGNED DEFAULT -1',
		'{bigint_negative}' => 'id I8 DEFAULT -1',
		'{bigint_large_default}' => 'id I8 UNSIGNED DEFAULT 9223372036854775807',
		'{bigint_bad_default}' => 'id I8 UNSIGNED DEFAULT 9223372036854975818', 
		// note +11 above as floating point so can't trust accuracy tolast digit
		'{bigint_bad_default2}' => "id I8 UNSIGNED DEFAULT 'foo'",
		'{bigint_bad_default3}' => "id I8 UNSIGNED DEFAULT 10.9",

	);
	/**
	 * setUp
	 */
	protected function setUp()
	{
		global $g_db_type, $g_hostname, $g_db_username, $g_db_password, $g_db_table_prefix, $g_db_table_suffix;

		if (array_key_exists('MANTIS_TESTSUITE_DATABASE_NAME', $GLOBALS)) {
			$this->database_name = $GLOBALS['MANTIS_TESTSUITE_DATABASE_NAME'] . rand(1,100);
		} else {
			$this->database_name = 'MantisBTTests';
		}

		$t_db = MantisDatabase::GetInstance($g_db_type);
		$t_result = $t_db->connect( null, $g_hostname, $g_db_username, $g_db_password, null, null );

		$this->dict = MantisDatabaseDict::GetDriverInstance($g_db_type);

		$this->assertEquals( $t_db->DatabaseExists( $this->database_name ), false );

		$t_sqlarray = $this->dict->CreateDatabase( $this->database_name );
		$ret = $this->dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertEquals( $t_db->DatabaseExists( $this->database_name ), true );

		$g_db = MantisDatabase::GetInstance($g_db_type);
		$t_result = $g_db->connect( null, $g_hostname, $g_db_username, $g_db_password, $this->database_name, null );
		$g_db->SetPrefixes( $g_db_table_prefix, $g_db_table_suffix );
	}

	/**
	 * tearDown
	 */
	protected function tearDown() {
		global $g_db_type, $g_hostname, $g_db_username, $g_db_password, $g_database_name, $g_db_table_prefix, $g_db_table_suffix;
		$this->assertEquals( MantisDatabase::GetInstance()->DatabaseExists( $this->database_name ), true );

		$g_db = MantisDatabase::GetInstance($g_db_type);
		$t_result = $g_db->connect( null, $g_hostname, $g_db_username, $g_db_password, null, null );

		$t_sqlarray = $this->dict->DropDatabase( $this->database_name );
		$ret = $this->dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertEquals( $g_db->DatabaseExists( $this->database_name ), false );

		// reset back to where we started
		$g_db = MantisDatabase::GetInstance($g_db_type);
		$t_result = $g_db->connect( null, $g_hostname, $g_db_username, $g_db_password, $g_database_name, null );
		$g_db->SetPrefixes( $g_db_table_prefix, $g_db_table_suffix );
		
		/*$t = MantisDatabase::GetInstance()->GetLastError();
		if ( $t !== null ) {
			var_dump($t);
		}*/
	}

	protected function DropTable( $p_table ) {
		global $g_dict;
		$t_sqlarray = $g_dict->DropTable( $p_table );
		$ret = $g_dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);
		
		$this->assertFalse( MantisDatabase::GetInstance()->TableExists( $p_table ) );
	}
	
	protected function CheckNoTable( $p_table ) {
		$this->assertFalse( MantisDatabase::GetInstance()->TableExists( $p_table ) );
	}

	protected function CheckTable( $p_table ) {
		$this->assertTrue( MantisDatabase::GetInstance()->TableExists( $p_table ) );
	}

	protected function CreateTableDefinition( $p_table, $p_definition ) {
		global $g_dict;
		
		$this->CheckNoTable( $p_table );
		$t_sqlarray = $g_dict->CreateTable( $p_table, $p_definition);
		$ret = $g_dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertTrue( MantisDatabase::GetInstance()->TableExists( $p_table ) );
	}
	
	protected function CreateTable( $p_table ) {
		global $g_dict;
		
		$this->CheckNoTable( $p_table );
		$t_sqlarray = $this->dict->CreateTable( $p_table, $this->definitions[$p_table] );
		$ret = $this->dict->ExecuteSQLarray( $t_sqlarray );
		$this->assertEquals( $ret, DB_QUERY_SUCCESS);

		$this->assertTrue( MantisDatabase::GetInstance()->TableExists( $p_table ) );
	}
}
