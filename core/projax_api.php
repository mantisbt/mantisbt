<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: projax_api.php,v 1.1.2.1 2007-10-13 22:35:40 giallu Exp $
	# --------------------------------------------------------

	require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'projax' . DIRECTORY_SEPARATOR . 'projax.php' );

	# enables the projax library for this page.
	$g_enable_projax = true;

	$g_projax = new Projax();
	
	# --------------------
	# Outputs an auto-complete field to the HTML form.  The supported attribute keys in the attributes array are:
	# class, size, maxlength, value, and tabindex.
	function projax_autocomplete( $p_entrypoint, $p_field_name, $p_attributes_array = null ) {
		global $g_projax;
		echo $g_projax->text_field_with_auto_complete( $p_field_name, $p_attributes_array, array( 'url' => 'xmlhttprequest.php?entrypoint=' . $p_entrypoint ) );
	}

	# --------------------
	# Filters the provided array of strings and only returns the ones that start with $p_prefix.
	# The comparison is not case sensitive.
	# Returns the array of the filtered strings, or an empty array.  If the input array has non-unique
	# entries, then the output one may contain duplicates.
	function projax_array_filter_by_prefix( $p_array, $p_prefix ) {
		$t_matches = array();

		foreach( $p_array as $t_entry ) {
			if ( strtolower( substr( $t_entry, 0, strlen( $p_prefix ) ) ) == strtolower( $p_prefix ) ) {
				$t_matches[] = $t_entry;
			}
		}
		
		return $t_matches;
	}

	# --------------------
	# Serializes the provided array of strings into the format expected by the auto-complete library.
	function projax_array_serialize_for_autocomplete( $p_array ) {
		$t_matches = '<ul>';

		foreach( $p_array as $t_entry ) {
			$t_matches .= "<li>$t_entry</li>";
		}

		$t_matches .= '</ul>';

		return $t_matches;
	}
?>
