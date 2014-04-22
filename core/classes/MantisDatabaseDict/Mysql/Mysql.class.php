<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Mysql database dictionary class.
 * @package MantisBT
 * @copyright Copyright 2012 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
class MantisDatabaseDict_Mysql extends MantisDatabaseDict {
	/**
	 * {@inheritDoc}
	 */
	protected function GetDropTableSQL() {
		return 'DROP TABLE %s';
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function GetRenameTableSQL() {
		return 'RENAME TABLE %s TO %s';
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function GetDropIndexSQL() {
		return 'DROP INDEX %s ON %s';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetAddColumnSQL() {
		return ' ADD';
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function GetAlterColumnSQL(){
		return ' MODIFY COLUMN';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetDropColumnSQL(){
		return ' DROP COLUMN';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function GetRenameColumnSQL() {
		return 'ALTER TABLE %s CHANGE COLUMN %s %s';	
	}

	/**
	 * {@inheritDoc}
	 * @param string $p_table_name Table Name
	 * @param string $p_old_column column-name to be renamed
	 * @param string $p_new_column new column-name
	 * @return array with SQL strings
	 */	
	function RenameColumn($p_table_name,$p_old_column,$p_new_column)
	{
		$t_table_name = $this->TableName ($p_table_name);
		$t_columns = MantisDatabase::GetInstance()->GetColumns( $p_table_name );
		
		if( !isset( $t_columns[ $p_old_column ] ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'Existing Column Must Exist' );
		}
		if( isset( $t_columns[ $p_new_column ] ) ) {
			throw new MantisBT\Exception\DatabaseDict\Exception( 'New Column Must Not Exist' );
		}
		
		// for mysql we need to build up existing column definition
		$t_columns[ $p_old_column ]->name = $this->GetIdentifierName( $p_new_column );
		$t_definition = $this->BuildFields( $t_columns[ $p_old_column ] );
		
		return array( sprintf( $this->GetRenameColumnSQL(), $t_table_name, $this->GetIdentifierName($p_old_column), $t_definition ) );
	}
	
	private function BuildFields($p_field_object) {
		// TODO - this needs more work I think
		$t_field = new stdClass();
		$t_field->has_default = $p_field_object->has_default;
		$t_field->default_value = $p_field_object->default_value;
		$t_field->notnull = $p_field_object->not_null;
		$t_field->fieldname = $p_field_object->name;
		$t_field->type = $p_field_object->type;
		$t_field->portabletype = null;
		$t_field->primary = $p_field_object->primary;
		$t_field->autoincrement = $p_field_object->auto_increment;
		$t_field->unsigned = $p_field_object->unsigned = true;		
		$t_field->size = null;
		$t_field->precision = null;		

		$t_array = $this->GenerateSQLFields( array( $t_field ) );

		return $t_array[0];
	}
}