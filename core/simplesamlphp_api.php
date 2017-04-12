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
 * simpleSAMLphp API
 *
 * Api spouse that you have working instance of simpleSAMLPHP with good configuration.
 * More information is available on follow URL : 
 * http://simplesamlphp.org/docs/1.7/
 * http://simplesamlphp.org/docs/1.7/simplesamlphp-install
 * http://simplesamlphp.org/docs/1.7/simplesamlphp-sp
 *
 * Also conntact your Federation provider for extra configuration parameters and 
 * connection instruction
 *
 * This API was tested with simpleSAMLPHP v 1.7 against Croatian AAI@eduHr Federation
 *
 * @package CoreAPI
 * @subpackage simpleSAMLAPI
 * @copyright Copyright (C) 2011  Dubravko Penezic Dubravko.Penezic@srce.hr
 * @copyright Copyright (C) 2002 - 2011  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_inc.php
 * @uses config_api.php
 * @uses php_api.php
 * @uses utility_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'php_api.php' );
require_api( 'utility_api.php' );

require_once($GLOBALS['g_simplesamlphp_autoloader']);

/**
 * Init simpleSAMLphp instance
 * Basicaly set communication parameters for SP to IdP
 *
 * @access public
 */

function ssphp_init() {
  global $g_simplesamlphp_instance, $g_simplesamlphp_SP_name;

  $g_simplesamlphp_instance = new SimpleSAML_Auth_Simple($g_simplesamlphp_SP_name);
}

/**
 * Check if user (browser) is authenticated, if so return username, or FALSE if
 * user (browser) isnt authenticated
 *
 * @access public
 */

function ssphp_is_user_authenticated() {
  global $g_simplesamlphp_instance;

  return $g_simplesamlphp_instance->isAuthenticated();
}

/**
 * Get username of authenticated user, or FALSE if user isnt authenticated, 
 * or username isnt available (check configuration parameters)
 * Also return FALSE if value for email isnt available
 * 
 * Also set global variable $g_simplesamlphp_attributes with list of available 
 * atributes and values
 *
 * @access public
 */

function ssphp_get_username(){
  global $g_simplesamlphp_instance, $g_simplesamlphp_auth_attributes, $g_simplesamlphp_autz_attributes, $g_simplesamlphp_attributes;

  $g_simplesamlphp_attributes = $g_simplesamlphp_instance->getAttributes();

  if(isset($g_simplesamlphp_attributes[$g_simplesamlphp_auth_attributes['username']][0]))
    return $g_simplesamlphp_attributes[$g_simplesamlphp_auth_attributes['username']][0];
  else
    return FALSE;
  if(! isset($g_simplesamlphp_attributes[$g_simplesamlphp_auth_attributes['email']][0]))
    return FALSE;

# Autorization TO-DO
/*
  foreach($g_simplesamlphp_autz_attributes as $k => $v)
    if(! preg_match($v,$k)) return false;
*/
 
}

/**
 * Start authentication proces, and return username of authenticate user or FALSE
 * 
 * Be aware that once started SSO will not return to previus page before user is actualy
 * authenticated
 *
 * @access public
 */

function ssphp_authenticate_user(){
  global $g_simplesamlphp_instance;

  $g_simplesamlphp_instance->requireAuth();

  return ssphp_get_username();
}

