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
 * @package MantisBT
 * @version $Id$
 * @copyright Copyright (C) 2000 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * A class that includes the response information of a version update check.
 */
class VersionUpdateInfo
{
	var $result = '';
	var $name = '';
	var $changelog_url = '';
	var $description = '';
}

/**
 * Retrieve contents from remotely stored file
 */
function get_remote_file( $host, $directory, $filename, &$errstr, &$errno, $port = 80, $timeout = 10 ) {
	if ( $fsock = @fsockopen( $host, $port, $errno, $errstr, $timeout ) ) {
		@fputs( $fsock, "GET $directory/$filename HTTP/1.1\r\n" );
		@fputs( $fsock, "HOST: $host\r\n" );
		@fputs( $fsock, "Connection: close\r\n\r\n" );

		$file_info = '';
		$get_info = false;

		while ( !@feof( $fsock ) ) {
			if ( $get_info ) {
				$file_info .= @fread( $fsock, 1024 );
			} else {
				$line = @fgets( $fsock, 1024 );
				if ( $line == "\r\n" ) {
					$get_info = true;
				} else if ( stripos( $line, '404 not found') !== false ) {
					$errstr = '404';
					return false;
				}
			}
		}

		@fclose($fsock);
	} else {
		if ( $errstr ) {
			return false;
		}

		$errstr = 'fsock open disabled.';
		return false;
	}

	return $file_info;
}

/**
 * Connects to Mantis Bug Tracker to retrieve the latest release that is considered an update to the current release.
 */
function version_check() {
	$t_errstr = '';
	$t_errno = 0;
	$t_version_update = new VersionUpdateInfo();

	$t_context = stream_context_create(array(
	    'http' => array(
	        'timeout' => 1
	        )
	    )
	); 

	#$t_result = @file_get_contents( 'http://127.0.0.1/mantisbt/check_version.php?protocol_version=1&project_id=1&version=1.0.0' );
	$t_result = @file_get_contents( 'http://www.mantisbt.org/bugs/check_version.php?protocol_version=1&project_id=1&version=1.0.0', 0, $t_context );
	#$t_result = get_remote_file( '127.0.0.1', '/mantisbt', 'check_version.php?protocol_version=1&project_id=1&version=1.0.0', $t_errstr, $t_errno );
	#$t_result = get_remote_file( 'mantisbt.org', '/bugs', 'check_version.php?protocol_version=1&project_id=1&version=' . urlencode( MANTIS_VERSION ), $t_errstr, $t_errno, 80, 1 );

	# If unable to read the file, then try another attempt with the sub-domain format.
	if ( $t_result === false || is_blank( $t_result ) || $t_errno != 0 ) {
		$t_version_update->result = 'failure';
		$t_version_update->description = $t_errno . ': ' . $t_errstr;
		return $t_version_update;
	}

	$t_lines = explode( "\n", $t_result, 4 );
	
	switch ( $t_lines[0] ) {
		case VERSION_CHECK_UP_TO_DATE:
			$t_version_update->result = $t_lines[0];
			break;
		case VERSION_CHECK_UPDATE_AVAILABLE:
			$t_version_update->result = $t_lines[0];
			$t_version_update->name = $t_lines[1];
			$t_version_update->changelog_url = $t_lines[2];
			$t_version_update->description = $t_lines[3];
			break;
		case VERSION_CHECK_ACCESS_DENIED:
			$t_version_update->result = $t_lines[0];
			break;
		case VERSION_CHECK_FAILURE:
		default:
			$t_version_update->result = VERSION_CHECK_FAILURE;
			if ( isset( $t_lines[1] ) ) {
				$t_version_update->description = $t_lines[1];
			}
			break;
	}
	
	return $t_version_update;
}
?>