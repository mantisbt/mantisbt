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
 * Get the issue attachment with the specified id.
 *
 * @param string  $p_username            The name of the user trying to access the filters.
 * @param string  $p_password            The password of the user.
 * @param integer $p_issue_attachment_id The id of the attachment to be retrieved.
 * @return string Base64 encoded data that represents the attachment.
 */
function mc_issue_attachment_get( $p_username, $p_password, $p_issue_attachment_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_file = mci_file_get( $p_issue_attachment_id, 'bug', $t_user_id );
	if( SoapObjectsFactory::isSoapFault( $t_file ) ) {
		return $t_file;
	}
	return SoapObjectsFactory::encodeBinary( $t_file );
}

/**
 * Add an attachment to an existing issue.
 *
 * @param string  $p_username  The name of the user trying to add an attachment to an issue.
 * @param string  $p_password  The password of the user.
 * @param integer $p_issue_id  The id of the issue to add the attachment to.
 * @param string  $p_name      The name of the file.
 * @param string  $p_file_type The mime type of the file.
 * @param string  $p_content   The attachment to add (base64 encoded string).
 * @return integer The id of the added attachment.
 */
function mc_issue_attachment_add( $p_username, $p_password, $p_issue_id, $p_name, $p_file_type, $p_content ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	if( !file_allow_bug_upload( $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	if( !access_has_bug_level( config_get( 'upload_bug_file_threshold' ), $p_issue_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	return mci_file_add( $p_issue_id, $p_name, $p_content, $p_file_type, 'bug', '', '', $t_user_id );
}

/**
 * Delete an issue attachment given its id.
 *
 * @param string  $p_username            The name of the user trying to add an attachment to an issue.
 * @param string  $p_password            The password of the user.
 * @param integer $p_issue_attachment_id The id of the attachment to be deleted.
 * @return boolean true: success, false: failure
 */
function mc_issue_attachment_delete( $p_username, $p_password, $p_issue_attachment_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	$t_bug_id = file_get_field( $p_issue_attachment_id, 'bug_id' );

	# Perform access control checks
	$t_attachment_owner = file_get_field( $p_issue_attachment_id, 'user_id' );
	$t_current_user_is_attachment_owner = $t_attachment_owner == $t_user_id;
	# Factor in allow_delete_own_attachments=ON|OFF
	if( !$t_current_user_is_attachment_owner || ( $t_current_user_is_attachment_owner && !config_get( 'allow_delete_own_attachments' ) ) ) {
		# Check access against delete_attachments_threshold
		if( !access_has_bug_level( config_get( 'delete_attachments_threshold' ), $t_bug_id, $t_user_id ) ) {
			return mci_soap_fault_access_denied( $t_user_id );
		}
	}

	return file_delete( $p_issue_attachment_id, 'bug' );
}
