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

interface ImportXml_Interface {

	/**
	  * Read stream until current item finishes, processing
	  * the data found
	  *
	  * @param XMLreader $reader
	  */
	public function process( XMLreader $reader );

	/**
	  * Update the old_id => new_id conversion map
	  *
	  * This function works on a Mapper object, storing the
	  * type/old_id/new_id triplet for later use.
	  * Import Classes for items not needing this info can use an
	  * empty implementation
	  *
	  * @param ImportXml_Mapper $mapper
	  */
	public function update_map( ImportXml_Mapper $mapper );
}
