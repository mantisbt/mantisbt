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
 * this file handles preparing of strings like to be printed
 * or stored.  print_api.php will gradually be replaced by
 * think calls to echo the results of functions implemented here.
 * @package CoreAPI
 * @subpackage PrepareAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * return the mailto: href string link
 * @param string $p_email
 * @param string $p_text
 * @return string
 */
function prepare_email_link( $p_email, $p_text ) {
	if( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
		return string_display_line( $p_text );
	}

	# If we apply string_url() to the whole mailto: link then the @
	#  gets turned into a %40 and you can't right click in browsers to
	#  do Copy Email Address.
	$t_mailto = string_attribute( 'mailto:' . $p_email );
	$p_text = string_display_line( $p_text );

	return '<a href="' . $t_mailto . '">' . $p_text . '</a>';
}

/**
 * prepares the name of the user given the id.  also makes it an email link.
 * @param int $p_user_id
 * @return string
 */
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

/**
 * A function that prepares the version string for outputting to the user on view / print issue pages.
 * This function would add the version date, if appropriate.
 *
 * @param integer $p_project_id  The project id.
 * @param integer $p_version_id  The version id.  If false then this method will return an empty string.
 * @return The formatted version string.
 */
function prepare_version_string( $p_project_id, $p_version_id ) {
	if ( $p_version_id === false ) {
		return '';
	}

	$t_version_text = version_full_name( $p_version_id, /* showProject */ null, $p_project_id );

	if ( access_has_project_level( config_get( 'show_version_dates_threshold' ), $p_project_id ) ) {
		$t_short_date_format = config_get( 'short_date_format' );

		$t_version = version_get( $p_version_id );
		$t_version_text .= ' (' . date( $t_short_date_format, $t_version->date_order ) . ')';
	}

	return $t_version_text;	
}
