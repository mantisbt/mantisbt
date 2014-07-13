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
 * RSS API
 *
 * @package CoreAPI
 * @subpackage RSSAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses crypto_api.php
 * @uses current_user_api.php
 * @uses helper_api.php
 * @uses user_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'crypto_api.php' );
require_api( 'current_user_api.php' );
require_api( 'helper_api.php' );
require_api( 'user_api.php' );

/**
 * Calculates a key to be used for RSS authentication based on user name,
 * cookie and password. If the user changes their user name or password, this
 * RSS authentication key will become invalidated.
 * @param integer $p_user_id User ID for the user which the key is being calculated for.
 * @return string RSS authentication key (384bit) encoded according to the base64 with URI safe alphabet approach described in RFC4648.
 */
function rss_calculate_key( $p_user_id = null ) {
	if( $p_user_id === null ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}

	$t_username = user_get_field( $t_user_id, 'username' );
	$t_password = user_get_field( $t_user_id, 'password' );
	$t_cookie = user_get_field( $t_user_id, 'cookie_string' );

	$t_key_raw = hash( 'whirlpool', 'rss_key' . config_get_global( 'crypto_master_salt' ) . $t_username . $t_password . $t_cookie, true );
	# Note: We truncate the last 8 bits from the hash output so that base64
	# encoding can be performed without any trailing padding.
	$t_key_base64_encoded = base64_encode( substr( $t_key_raw, 0, 63 ) );
	$t_key = strtr( $t_key_base64_encoded, '+/', '-_' );

	return $t_key;
}

/**
 * Given the user name and the rss key, this method attempts to login the user.  If successful, it
 * return true, otherwise, returns false.
 * @param string $p_username A user name to attempt to login as.
 * @param string $p_key      The RSS key to use for the given user.
 * @return boolean
 */
function rss_login( $p_username, $p_key ) {
	if( ( $p_username === null ) || ( $p_key === null ) ) {
		return false;
	}

	$t_user_id = user_get_id_by_name( $p_username );

	if( false === $t_user_id ) {
		return false;
	}

	$t_correct_key = rss_calculate_key( $t_user_id );
	if( $p_key != $t_correct_key ) {
		return false;
	}

	if( !auth_attempt_script_login( $p_username ) ) {
		return false;
	}

	return true;
}

/**
 * return RSS issues feed URL
 * @param integer $p_project_id The project identifier to retrieve the news feed URL for.
 * @param string  $p_username   The user name accessing the news feed.
 * @param integer $p_filter_id  The filter identifier to generate a URL for.
 * @param boolean $p_relative   Whether to return relative links.
 * @return string
 */
function rss_get_issues_feed_url( $p_project_id = null, $p_username = null, $p_filter_id = null, $p_relative = true ) {
	if( $p_username === null ) {
		$t_username = current_user_get_field( 'username' );
	} else {
		$t_username = $p_username;
	}

	if( $p_project_id === null ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = (integer)$p_project_id;
	}

	$t_user_id = user_get_id_by_name( $t_username );

	if( $p_relative ) {
		$t_url = config_get( 'path' );
	} else {
		$t_url = '';
	}

	if( user_is_anonymous( $t_user_id ) ) {
		$t_url .= 'issues_rss.php?';

		if( $t_project_id == ALL_PROJECTS ) {
			$t_url .= 'project_id=' . $t_project_id;
		}
	} else {
		$t_url .= 'issues_rss.php?username=' . $t_username . '&key=' . rss_calculate_key( $t_user_id );

		if( $t_project_id != ALL_PROJECTS ) {
			$t_url .= '&project_id=' . $t_project_id;
		}
	}

	if( $p_filter_id !== null ) {
		$t_url .= '&filter_id=' . $p_filter_id;
	}

	return $t_url;
}

/**
 * return RSS news feed URL
 * @param integer $p_project_id The project identifier to retrieve the news feed URL for.
 * @param string  $p_username   The user name accessing the news feed.
 * @param boolean $p_relative   Whether to return relative links.
 * @return string
 */
function rss_get_news_feed_url( $p_project_id = null, $p_username = null, $p_relative = true ) {
	if( $p_username === null ) {
		$t_username = current_user_get_field( 'username' );
	} else {
		$t_username = $p_username;
	}

	if( $p_project_id === null ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = (integer)$p_project_id;
	}

	if( $p_relative ) {
		$t_rss_link = '';
	} else {
		$t_rss_link = config_get( 'path' );
	}

	$t_user_id = user_get_id_by_name( $t_username );

	# If we have a logged in user then they can be given a 'proper' feed, complete with auth string.
	if( user_is_anonymous( $t_user_id ) ) {
		$t_rss_link .= 'news_rss.php';

		if( $t_project_id != ALL_PROJECTS ) {
			$t_rss_link .= '?project_id=' . $t_project_id;
		}
	} else {
		$t_rss_link .= 'news_rss.php?username=' . $t_username . '&key=' . rss_calculate_key( $t_user_id );

		if( $t_project_id != ALL_PROJECTS ) {
			$t_rss_link .= '&project_id=' . $t_project_id;
		}
	}

	return $t_rss_link;
}
