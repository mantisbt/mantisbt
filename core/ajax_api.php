<?php
# MantisBT - a php based bugtracking system

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
 * Ajax API, containing functions to support use of ajax within mantis
 *
 * @package CoreAPI
 * @subpackage AjaxAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @uses lang_api.php
 */

/**
 * @todo function documentation
 * @param string $p_initial_string
 * @param string $p_element_id_prefix
 * @param string $p_query_string
 * @return string returns raw html containing AjaxLoad
 */
function ajax_click_to_edit( $p_initial_string, $p_element_id_prefix, $p_query_string ) {
	$t_element_id_target = $p_element_id_prefix . '_target';
	$t_element_id_edit = $p_element_id_prefix . '_edit';
	$t_edit = lang_get( 'edit_link' );

	$t_return = '<a id="' . $t_element_id_target . '">' . $p_initial_string . '</a> ';
	$t_return .= '<a id="' . $t_element_id_edit . '" onclick="';
	$t_return .= "AjaxLoad('" . $t_element_id_target . "', '" . htmlspecialchars( $p_query_string ) . "', '" . $t_element_id_edit . "' )";
	$t_return .= '"><small>[' . $t_edit . ']</small></a>';

	return $t_return;
}
