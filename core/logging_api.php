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
 * Logging api
 * This is used to log system events other than bug related history
 * @package CoreAPI
 * @subpackage LoggingAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$g_log_levels = array(
	LOG_EMAIL => 'mail',
	LOG_EMAIL_RECIPIENT => 'mail_recipient',
	LOG_FILTERING => 'filtering',
	LOG_AJAX => 'ajax',
	LOG_LDAP => 'ldap',
	LOG_DATABASE => 'database',
	LOG_SOAP => 'soap'
);

/**
 * Log an event
 * @param int $p_level
 * @param string $p_msg
 * @return null
 */
function log_event( $p_level, $p_msg ) {
	global $g_log_levels;

	# check to see if logging is enabled
	$t_sys_log = config_get_global( 'log_level' );

	if ( 0 == ( $t_sys_log & $p_level ) ) {
		return;
	}

	$t_now = date( config_get_global( 'complete_date_format' ) );
	$t_level = $g_log_levels[$p_level];

	$t_plugin_event = '[' . $t_level . '] ' . $p_msg;

	if( function_exists( 'event_signal' ) )
		event_signal( 'EVENT_LOG', array( $t_plugin_event ) );

	$t_php_event = $t_now . ' ' . $t_level . ' ' . $p_msg;

	$t_log_destination = config_get_global( 'log_destination' );

	if ( is_blank( $t_log_destination ) ) {
		$t_destination = '';
	} else {
		list( $t_destination, $t_modifiers ) = explode( ':', $t_log_destination, 2 );
	}

	switch( $t_destination ) {
		case 'file':
				error_log( $t_php_event . PHP_EOL, 3, $t_modifiers );
			break;
		case 'firebug':
				if( !class_exists( 'FirePHP' ) ) {
					if( file_exists( BASE_PATH . DIRECTORY_SEPARATOR  . 'library' . DIRECTORY_SEPARATOR . 'FirePHPCore' . DIRECTORY_SEPARATOR . 'FirePHP.class.php' ) ) {
						require_once( BASE_PATH . DIRECTORY_SEPARATOR  . 'library' . DIRECTORY_SEPARATOR . 'FirePHPCore' . DIRECTORY_SEPARATOR . 'FirePHP.class.php' );
					}
				}
				if( class_exists( 'FirePHP' ) ) {
					static $firephp;
					if( $firephp === null ) {
						$firephp = FirePHP::getInstance(true);
					}
					$firephp->log( $t_php_event );
					return;
				}
				// if firebug is not available, fall through
		default:
			# use default PHP error log settings
				error_log( $t_php_event . PHP_EOL );
			break;
	}
}
