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
 * Prepare API
 *
 * Handles preparation of strings prior to be printed or stored.
 *
 * @package CoreAPI
 * @subpackage PrepareAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses version_api.php
 */

require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'version_api.php' );

/**
 * Return a ready-to-use mailto: URL.
 *
 * No validation is performed on the e-mail address, it is the caller's
 * responsibility to ensure it is valid and not empty.
 *
 * @param string $p_email   Target e-mail address
 * @param string $p_subject Optional e-mail subject
 *
 * @return string
 */
function prepare_mailto_url ( $p_email, $p_subject = '' ) {
	# If we apply string_url() to the whole mailto: link then the @ gets
	# turned into a %40 and you can't right click in browsers to use the
	# Copy Email Address functionality.
	if( $p_subject ) {
		# URL-encoding the subject is required otherwise special characters
		# (ampersand for example) will truncate the text
		$p_subject = '?subject=' . string_url( $p_subject );
	}
	$t_mailto = 'mailto:' . $p_email . $p_subject;

	return string_attribute( $t_mailto );
}

/**
 * return an HTML link with mailto: href.
 *
 * If user does not have access level required to see email addresses, the
 * function will only return the display text (with tooltip if provided).
 *
 * @param string $p_email           Email address to prepare.
 * @param string $p_text            Display text for the hyperlink.
 * @param string $p_subject         Optional e-mail subject
 * @param string  $p_tooltip        Optional tooltip to show.
 * @param boolean $p_show_as_button If true, show link as button with envelope
 *                                  icon, otherwise display a plain-text link.
 *
 * @return string
 */
function prepare_email_link( $p_email, $p_text, $p_subject = '', $p_tooltip ='', $p_show_as_button = false ) {
	$t_text = string_display_line( $p_text );
	if( !is_blank( $p_tooltip ) && $p_tooltip != $p_text ) {
		$t_tooltip = ' title="' . string_display_line( $p_tooltip ) . '"';
	} else {
		$t_tooltip = '';
	}

	if( !access_has_project_level( config_get( 'show_user_email_threshold' ) ) ) {
		return $t_tooltip ? '<a' . $t_tooltip . '>' . $t_text . '</a>' : $t_text;
	}

	$t_mailto = prepare_mailto_url( $p_email, $p_subject );

	if( $p_show_as_button ) {
		$t_class = ' class="noprint blue zoom-130"';
		$t_text = icon_get( 'fa-envelope-o', 'bigger-115' )
			. ( $t_text ? "&nbsp;$t_text" : '' );
	} else {
		$t_class = '';
	}

	return sprintf( '<a href="%s"%s%s>%s</a>',
		$t_mailto,
		$t_tooltip,
		$t_class,
		$t_text
	);
}

/**
 * Prepares the name of the user given the id.
 * Also can make it a link to user info page.
 * @param integer $p_user_id  A valid user identifier.
 * @param boolean $p_link     Whether to include an html link
 * @return string
 */
function prepare_user_name( $p_user_id, $p_link = true ) {
	# Catch a user_id of NO_USER (like when a handler hasn't been assigned)
	if( NO_USER == $p_user_id ) {
		return '';
	}

	$t_username = user_get_username( $p_user_id );
	$t_name = user_get_name( $p_user_id );
	if( $t_username != $t_name ) {
		$t_tooltip = ' title="' . string_attribute( $t_username ) . '"';
	} else {
		$t_tooltip = '';
	}

	$t_name = string_display_line( $t_name );

	if( user_exists( $p_user_id ) && user_get_field( $p_user_id, 'enabled' ) ) {
		if( $p_link ) {
			return '<a' . $t_tooltip . ' href="' . string_sanitize_url( 'view_user_page.php?id=' . $p_user_id, true ) . '">' . $t_name . '</a>';
		} else {
			return '<span ' . $t_tooltip . '>' . $t_name . '</span>';
		}
	}

	return '<del ' . $t_tooltip . '>' . $t_name . '</del>';
}

/**
 * A function that prepares the version string for outputting to the user on view / print issue pages.
 * This function would add the version date, if appropriate.
 *
 * @param integer $p_project_id         The project id to use as context.
 * @param integer $p_version_id         The version id. If false then this method will return an empty string.
 * @param boolean|null $p_show_project  Whether to include the project name or not,
 *                                      null means include the project if different from current context.
 * @return string The formatted version string.
 */
function prepare_version_string( $p_project_id, $p_version_id, $p_show_project = null ) {
	if( $p_version_id === false ) {
		return '';
	}

	$t_version_text = version_full_name( $p_version_id, $p_show_project, $p_project_id );

	if( access_has_project_level( config_get( 'show_version_dates_threshold' ), $p_project_id ) ) {
		$t_short_date_format = config_get( 'short_date_format' );

		$t_version = version_cache_row( $p_version_id );
		if( 1 == $t_version['released'] ) {
			$t_version_text .= ' (' . date( $t_short_date_format, $t_version['date_order'] ) . ')';
		}
	}

	return $t_version_text;
}

/**
 * Prepares avatar for raw outputting (only avatar image).
 *
 * @param Avatar $p_avatar          An instance of class Avatar.
 * @param string $p_class_prefix    CSS class prefix to add to the avatar's surrounding div and to the img.
 *   The CSS classes to implement will be named [$p_class_prefix]-avatar_container-[$p_size] and 
 *   [$p_class_prefix]-avatar-[$p_size].
 * @param integer $p_size           Image maximum size.
 * @return string the HTML string of the avatar.
 */
function prepare_raw_avatar( $p_avatar, $p_class_prefix, $p_size) {
	if( $p_avatar === null ) {
		return '';
	}

	$t_image = htmlspecialchars( $p_avatar->image );
	$t_text = htmlspecialchars( $p_avatar->text );

	$t_avatar_class = $p_class_prefix . '-avatar' . '-' . $p_size;
	return '<img class="' . $t_avatar_class . '" src="' . $t_image . '" alt="' .
			$t_text . '" />';
}

/**
 * Prepares avatar for outputting.
 *
 * @param Avatar $p_avatar          An instance of class Avatar.
 * @param string $p_class_prefix    CSS class prefix to add to the avatar's surrounding div and to the img.
 *   The CSS classes to implement will be named [$p_class_prefix]-avatar-container-[$p_size] and
 *   [$p_class_prefix]-avatar-[$p_size].
 * @param integer $p_size           Image maximum size.
 * @return string the HTML string of the avatar.
 */
function prepare_avatar( $p_avatar, $p_class_prefix, $p_size ) {
	if( $p_avatar === null ) {
		return '';
	}

	$t_link = htmlspecialchars( $p_avatar->link );

	$t_container_class = $p_class_prefix . '-avatar-container' . '-' . $p_size;
	return '<div class="' . $t_container_class . '">' . 
			'<a rel="nofollow" href="' . $t_link . '">' .
			prepare_raw_avatar( $p_avatar, $p_class_prefix, $p_size ) . 
			'</a></div>';
}

