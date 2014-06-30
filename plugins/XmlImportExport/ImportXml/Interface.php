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
 * Import XML interface
 */
interface ImportXml_Interface {
	/**
	  * Read stream until current item finishes, processing the data found
	  *
	  * @param XMLreader $p_reader XML Reader.
	  * @return void
	  */
	public function process( XMLreader $p_reader );

	/**
	  * Update the old_id => new_id conversion map
	  *
	  * This function works on a Mapper object, storing the
	  * type/old_id/new_id triplet for later use.
	  * Import Classes for items not needing this info can use an empty implementation
	  *
	  * @param ImportXml_Mapper $p_mapper XML Mapper.
	  * @return void
	  */
	public function update_map( ImportXml_Mapper $p_mapper );
}
