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
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
  * Mapper class
  *
  * it will store the ( type, old, new ) triplet for later retrieval
  */
class ImportXml_Mapper {
	/**
	 * Issues
	 * @var array
	 */
	private $issue = array( );

	/**
	 * add
	 * @param mixed $p_type Type.
	 * @param mixed $p_old  Old.
	 * @param mixed $p_new  New.
	 * @return void
	 */
	public function add( $p_type, $p_old, $p_new ) {
		$this->{$p_type}[$p_old] = $p_new;
	}

	/**
	 * check if entry exists within array
	 * @param mixed $p_type Type.
	 * @param mixed $p_id   ID.
	 * @return boolean
	 */
	public function exists( $p_type, $p_id ) {
		return array_key_exists( $p_id, $this->{$p_type} );
	}

	/**
	 * get new id
	 * @param mixed $p_type Type.
	 * @param mixed $p_old  Old.
	 * @return mixed
	 */
	public function getNewID( $p_type, $p_old ) {
		if( $this->exists( $p_type, $p_old ) ) {
			return $this->{$p_type}[$p_old];
		} else {
			return $p_old;
		}
	}

	/**
	 * get all by type
	 * @param mixed $p_type Type.
	 * @return mixed
	 */
	public function getAll( $p_type ) {
		return $this->{$p_type};
	}
}
