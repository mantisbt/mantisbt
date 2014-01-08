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
 * Mobile API
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package CoreAPI
 * @subpackage MobileAPI
 */

/**
 * Detects if it's mobile browser
 * Source: http://www.dannyherran.com/2011/02/detect-mobile-browseruser-agent-with-php-ipad-iphone-blackberry-and-others/
 * @return boolean<p>True if Mobile Browser, False on PC Browser</p>
 */
function mobile_is_mobile_browser() {
	// If api call rather than browser then return false.
	if ( strstr( $_SERVER['SCRIPT_NAME'], '/api/' ) !== false ) {
		return false;
	}

	$_SERVER['ALL_HTTP'] = isset( $_SERVER['ALL_HTTP'] ) ? $_SERVER['ALL_HTTP'] : '';

	$t_mobile_browser = false;

	$t_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );

	if ( preg_match( '/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', $t_agent ) ) {
		$t_mobile_browser = true;
	}

	if ( ( isset( $_SERVER['HTTP_ACCEPT'] ) ) && ( strpos( strtolower( $_SERVER['HTTP_ACCEPT'] ), 'application/vnd.wap.xhtml+xml' ) !== false ) ) {
		$t_mobile_browser = true;
	}

	if ( isset( $_SERVER['HTTP_X_WAP_PROFILE'] ) ) {
		$t_mobile_browser = true;
	}

	if ( isset( $_SERVER['HTTP_PROFILE'] ) ) {
		$t_mobile_browser = true;
	}

	$t_mobile_ua = substr( $t_agent, 0, 4 );
	$t_mobile_agents = array(
		'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
		'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
		'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
		'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
		'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
		'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
		'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
		'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
		'wapr','webc','winw','xda','xda-'
	);

	if ( in_array( $t_mobile_ua, $t_mobile_agents ) ) {
		$t_mobile_browser = true;
	}

	if ( strpos( strtolower( $_SERVER['ALL_HTTP'] ), 'operamini' ) !== false ) {
		$t_mobile_browser = true;
	}

	// Pre-final check to reset everything if the user is on Windows
	if ( strpos( $t_agent, 'windows' ) !== false ) {
		$t_mobile_browser = false;
	}

	// But WP7 is also Windows, with a slightly different characteristic
	if ( strpos( $t_agent, 'windows phone' ) !== false ) {
		$t_mobile_browser = true;
	}

	return $t_mobile_browser;
}
