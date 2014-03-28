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
 * Collapse API
 *
 * @package CoreAPI
 * @subpackage CollapseAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses gpc_api.php
 * @uses tokens_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'gpc_api.php' );
require_api( 'tokens_api.php' );
require_api( 'utility_api.php' );

/**
 *
 * @global string $g_current_collapse_section
 */
$g_current_collapse_section = null;

/**
 *
 * @global bool $g_open_collapse_section
 */
$g_open_collapse_section = false;

/**
 *
 * @global string $g_collapse_cache_token
 */
$g_collapse_cache_token = null;

/**
 * Marks the beginning of a collapse block's open phase.
 * This will be visible if the block is expanded, or if
 * javascript is disabled.
 * @param string $p_name Collapse block name
 * @param string $p_section Collapse block section
 * @param string $p_css_class CSS class to apply to the div (defaults to none)
 */
function collapse_open( $p_name, $p_section = '', $p_css_class = '' ) {
	global $g_current_collapse_section, $g_open_collapse_section;

	$t_block = ( is_blank( $p_section ) ? $p_name : $p_section . '_' . $p_name );
	$t_display = collapse_display( $t_block );

	# make sure no other collapse section is started
	if( $g_current_collapse_section !== null ) {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	if( $t_display ) {
		$p_css_class .= ' collapse-open';
	} else {
		$p_css_class .= ' collapse-closed';
	}

	$g_open_collapse_section = true;
	$g_current_collapse_section = $t_block;

	$t_div_id = $t_block . '_open';
	echo "\n<div id=\"$t_div_id\" class=\"$p_css_class\">";
}

/**
 * Marks the end of a collapse block's open phase and the beginning
 * of the block's closed phase.  Thi will only be visible if the
 * block have been collapsed and javascript is enabled.
 * @param string $p_name Collapse block name
 * @param string $p_section Collapse block section
 */
function collapse_closed( $p_name, $p_section = '' ) {
	global $g_current_collapse_section, $g_open_collapse_section;

	$t_block = ( is_blank( $p_section ) ? $p_name : $p_section . '_' . $p_name );
	$t_display = !collapse_display( $t_block );

	# Make sure a section is opened, and it is the same section.
	if( $t_block !== $g_current_collapse_section ) {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	echo '</div>';

	$g_open_collapse_section = false;

	ob_start();

	$t_div_id = $t_block . '_closed';
	echo "\n<div id=\"", $t_div_id, '"', ( $t_display ? ' class="collapse-open"' : ' class="collapse-closed"' ), '>';
}

/**
 * Marks the location where a +/- icon is placed in output
 * for the user to toggle the collapse block status.
 * This should appear in both the open and closed phase of a block.
 * @param string $p_name Collapse block name
 * @param string $p_section Collapse block section
 */
function collapse_icon( $p_name, $p_section = '' ) {
	if( OFF == config_get( 'use_javascript' ) ) {
		return;
	}

	global $g_open_collapse_section;

	if( $g_open_collapse_section === true ) {
		$t_icon = 'minus.png';
		$t_alt = '-';
		$t_id = $p_name . '_open_link';
	} else {
		$t_icon = 'plus.png';
		$t_alt = '+';
		$t_id = $p_name. '_closed_link';
	}

	echo '<a id="', $t_id, '" href="" class="collapse-link"><img src="images/', $t_icon, '" alt="', $t_alt, '" /></a>&#160;';
}

/**
 * Marks the end of a collapse block's closed phase.
 * Closed phase output is discarded if javascript is disabled.
 * @param string $p_name Collapse block name
 * @param string $p_section Collapse block section
 */
function collapse_end( $p_name, $p_section = '' ) {
	global $g_current_collapse_section, $g_open_collapse_section;

	$t_block = ( is_blank( $p_section ) ? $p_name : $p_section . '_' . $p_name );
	collapse_display( $t_block );

	# Make sure a section is opened, and it is the same section.
	if( $t_block !== $g_current_collapse_section ) {
		ob_end_clean();
		trigger_error( ERROR_GENERIC, ERROR );
	}

	echo '</div>';

	$g_open_collapse_section = false;

	if( ON == config_get( 'use_javascript' ) ) {
		ob_end_flush();
	} else {
		ob_end_clean();
	}

	$g_current_collapse_section = null;
}

/**
 * Determine if a block should be displayed open by default.
 * @param string $p_block Collapse block
 * @return bool
 */
function collapse_display( $p_block ) {
	global $g_collapse_cache_token;

	if( !isset( $g_collapse_cache_token[$p_block] ) || OFF == config_get( 'use_javascript' ) ) {
		return true;
	}

	return( true == $g_collapse_cache_token[$p_block] );
}

/**
 * Cache collapse API data from the database for the current user.
 * If the collapse cookie has been set, grab the changes and resave
 * the token, or touch it otherwise.
 */
function collapse_cache_token() {
	global $g_collapse_cache_token;

	if( !auth_is_user_authenticated() || current_user_is_anonymous() ) {
		$g_collapse_cache_token = array();
		return;
	}

	if( isset( $g_collapse_cache_token ) ) {
		return;
	}

	$t_token = token_get_value( TOKEN_COLLAPSE );

	if( !is_null( $t_token ) ) {
		$t_data = unserialize( $t_token );
	} else {
		$t_data = array();
	}

	$g_collapse_cache_token = $t_data;

	$t_cookie = gpc_get_cookie( 'MANTIS_collapse_settings', '' );

	if( false !== $t_cookie && !is_blank( $t_cookie ) ) {
		$t_update = false;
		$t_data = explode( '|', $t_cookie );

		foreach( $t_data as $t_pair ) {
			$t_pair = explode( ',', $t_pair );

			if( false !== $t_pair && count( $t_pair ) == 2 ) {
				$g_collapse_cache_token[$t_pair[0]] = ( true == $t_pair[1] );
				$t_update = true;
			}
		}

		if( $t_update ) {
			$t_token = serialize( $g_collapse_cache_token );
			token_set( TOKEN_COLLAPSE, $t_token, TOKEN_EXPIRY_COLLAPSE );
		} else {
			token_touch( TOKEN_COLLAPSE );
		}

		gpc_clear_cookie( 'MANTIS_collapse_settings' );
	}
}
