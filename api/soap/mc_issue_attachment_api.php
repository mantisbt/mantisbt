<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2012  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'mc_core.php' );

/**
 * Get the issue attachment with the specified id.
 *
 * @param string $p_username  The name of the user trying to access the filters.
 * @param string $p_password  The password of the user.
 * @param integer $p_attachment_id  The id of the attachment to be retrieved.
 * @return Base64 encoded data that represents the attachment.
 */
function mc_issue_attachment_get( $p_username, $p_password, $p_issue_attachment_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	
	$t_file = mci_file_get( $p_issue_attachment_id, 'bug', $t_user_id );
	if ( get_class( (object) $t_file ) == 'soap_fault' ) {
		return $t_file;
	}
	return base64_encode( $t_file );
}

/**
 * Add an attachment to an existing issue.
 *
 * @param string $p_username  The name of the user trying to add an attachment to an issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_id  The id of the issue to add the attachment to.
 * @param string $p_name  The name of the file.
 * @param string $p_file_type The mime type of the file.
 * @param base64Binary $p_content  The attachment to add.
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
 * @param string $p_username  The name of the user trying to add an attachment to an issue.
 * @param string $p_password  The password of the user.
 * @param integer $p_issue_attachment_id  The id of the attachment to be deleted.
 * @return true: success, false: failure
 */
function mc_issue_attachment_delete( $p_username, $p_password, $p_issue_attachment_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}
	$t_bug_id = file_get_field( $p_issue_attachment_id, 'bug_id' );
	if( !access_has_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id, $t_user_id ) ) {
		return mci_soap_fault_access_denied( $t_user_id );
	}
	return file_delete( $p_issue_attachment_id, 'bug' );
}
