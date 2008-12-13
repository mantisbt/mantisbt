<?php
# Mantis - a php based bugtracking system

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

/**
 * this file handles preparing of strings like to be printed
 * or stored.  print_api.php will gradually be replaced by
 * think calls to echo the results of functions implemented here.
 * @package CoreAPI
 * @subpackage PrepareAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$t_core_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

# return the mailto: href string link
function prepare_email_link( $p_email, $p_text ) {
	if( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
		return string_display_line( $p_text );
	}

	# If we apply string_url() to the whole mailto: link then the @
	#  gets turned into a %40 and you can't right click in browsers to
	#  do Copy Email Address.
	$t_mailto = string_attribute( "mailto:$p_email" );
	$p_text = string_display_line( $p_text );

	return "<a href=\"$t_mailto\">$p_text</a>";
}

# prepares the name of the user given the id.  also makes it an email link.
function prepare_user_name( $p_user_id ) {

	# Catch a user_id of NO_USER (like when a handler hasn't been assigned)
	if( NO_USER == $p_user_id ) {
		return '';
	}

	$t_username = user_get_name( $p_user_id );
	if( user_exists( $p_user_id ) && user_get_field( $p_user_id, 'enabled' ) ) {
		$t_username = string_display_line( $t_username );
		return '<a href="' . string_sanitize_url( 'view_user_page.php?id=' . $p_user_id, true ) . '">' . $t_username . '</a>';
	} else {
		$t_result = '<font STYLE="text-decoration: line-through">';
		$t_result .= string_display_line( $t_username );
		$t_result .= '</font>';
		return $t_result;
	}
}
