<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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
  * Mapper class
  *
  * it will store the ( type, old, new ) triplet for later retrieval
  */
class ImportXml_Mapper {
	private $issue = array( );

	public function add( $type, $old, $new ) {
		$this->{$type}[ $old ] = $new;
	}

	public function exists( $type, $id ) {
		return array_key_exists( $id, $this->{$type} );
	}

	public function getNewID( $type, $old ) {
		if( $this->exists( $type, $old ) ) {
			return $this->{$type}[ $old ];
		} else {
			return $old;
		}
	}

	public function getAll( $type ) {
		return $this->{$type};
	}
}
