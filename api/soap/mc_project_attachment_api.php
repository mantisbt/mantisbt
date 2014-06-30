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
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( __FILE__ ) . '/mc_core.php' );

/**
 * Get the project attachment with the specified id.
 *
 * @param string  $p_username              The name of the user trying to access the filters.
 * @param string  $p_password              The password of the user.
 * @param integer $p_project_attachment_id The id of the attachment to be retrieved.
 * @return string Base64 encoded data that represents the attachment.
 */
function mc_project_attachment_get( $p_username, $p_password, $p_project_attachment_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_file = mci_file_get( $p_project_attachment_id, 'doc', $t_user_id );
	if( SoapObjectsFactory::isSoapFault( $t_file ) ) {
		return $t_file;
	}
	return SoapObjectsFactory::encodeBinary( $t_file );
}

/**
 * Add an attachment to an existing project.
 *
 * @param string  $p_username    The name of the user trying to add an attachment to an issue.
 * @param string  $p_password    The password of the user.
 * @param integer $p_project_id  The id of the project to add the attachment to.
 * @param string  $p_name        The name of the file.
 * @param string  $p_title       The title for the attachment.
 * @param string  $p_description The description for the attachment.
 * @param string  $p_file_type   The mime type of the file.
 * @param string  $p_content     The attachment to add (base64Binary encoded).
 * @return integer The id of the added attachment.
 */
function mc_project_attachment_add( $p_username, $p_password, $p_project_id, $p_name, $p_title, $p_description, $p_file_type, $p_content ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	# Check if project documentation feature is enabled.
	if( OFF == config_get( 'enable_project_documentation' ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	if( !access_has_project_level( config_get( 'upload_project_file_threshold' ), $p_project_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	if( is_blank( $p_title ) ) {
		return SoapObjectsFactory::newSoapFault( 'Client', 'Title must not be empty.' );
	}
	return mci_file_add( $p_project_id, $p_name, $p_content, $p_file_type, 'project', $p_title, $p_description, $t_user_id );
}

/**
 * Delete a project attachment given its id.
 *
 * @param string  $p_username              The name of the user trying to add an attachment to an issue.
 * @param string  $p_password              The password of the user.
 * @param integer $p_project_attachment_id The id of the attachment to be deleted.
 * @return boolean true: success, false: failure
 */
function mc_project_attachment_delete( $p_username, $p_password, $p_project_attachment_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	$t_project_id = file_get_field( $p_project_attachment_id, 'project_id', 'project' );
	if( !access_has_project_level( config_get( 'upload_project_file_threshold' ), $t_project_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	return file_delete( $p_project_attachment_id, 'project' );
}
