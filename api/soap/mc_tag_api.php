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

use Mantis\Exceptions\ClientException;

/**
 * Retrieves all tags, unless the users
 *
 * @param string  $p_username    The user's username.
 * @param string  $p_password    The user's password.
 * @param integer $p_page_number The page number to return data for.
 * @param string  $p_per_page    The number of issues to return per page.
 * @return array The tag data
 */
function mc_tag_get_all( $p_username, $p_password, $p_page_number, $p_per_page ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !access_has_global_level( config_get( 'tag_view_threshold' ) ) ) {
		return mci_fault_access_denied( $t_user_id, 'No rights to view tags' );
	}

	if( $p_per_page == 0 ) {
		$p_per_page = 1;
	}

	$t_results = array();
	$t_total_results = tag_count( '' );
	$t_tags = tag_get_all( '', $p_per_page, $p_per_page *  ( $p_page_number - 1 ) );

	while( $t_tag = db_fetch_array( $t_tags ) ) {
		$t_tag['user_id'] = mci_account_get_array_by_id( $t_tag['user_id'] );
		$t_tag['date_created'] = ApiObjectFactory::datetime( $t_tag['date_created'] );
		$t_tag['date_updated'] = ApiObjectFactory::datetime( $t_tag['date_updated'] );
		$t_results[] = $t_tag;
	}

	log_event( LOG_WEBSERVICE,
		'retrieved ' . count( $t_results ) .
		'/' . $t_total_results . ' tags (page #' . $p_page_number . ')'
	);

	return array(
		'results' => $t_results,
		'total_results' => $t_total_results
	);
}

/**
 * Creates a tag
 *
 * @param string   $p_username The user's username.
 * @param string   $p_password The user's password.
 * @param stdClass $p_tag      The tag to create.
 * @return soap_fault|integer
 */
function mc_tag_add( $p_username, $p_password, stdClass $p_tag ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !access_has_global_level( config_get( 'tag_create_threshold' ) ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	$t_valid_matches = array();

	$p_tag = ApiObjectFactory::objectToArray( $p_tag );

	$t_tag_name = $p_tag['name'];
	$t_tag_description = array_key_exists( 'description', $p_tag ) ? $p_tag['description'] : '';

	if( !tag_name_is_valid( $t_tag_name, $t_valid_matches ) ) {
		return ApiObjectFactory::faultBadRequest( 'Invalid tag name : "' . $t_tag_name . '"' );
	}

	$t_matching_by_name = tag_get_by_name( $t_tag_name );
	if( $t_matching_by_name != false ) {
		return ApiObjectFactory::faultConflict( 'A tag with the same name already exists , id: ' . $t_matching_by_name['id'] );
	}

	log_event( LOG_WEBSERVICE, 'creating tag \'' . $t_tag_name . '\' for user \'' . $t_user_id . '\'' );
	return tag_create( $t_tag_name, $t_user_id, $t_tag_description );
}

/**
 *
 * Deletes a tag
 *
 * @param string  $p_username The user's username.
 * @param string  $p_password The user's password.
 * @param integer $p_tag_id   The id of the tag.
 * @return soap_fault|boolean
 */
function mc_tag_delete( $p_username, $p_password, $p_tag_id ) {
	$t_user_id = mci_check_login( $p_username, $p_password );

	if( $t_user_id === false ) {
		return mci_fault_login_failed();
	}

	if( !access_has_global_level( config_get( 'tag_edit_threshold' ) ) ) {
		return mci_fault_access_denied( $t_user_id );
	}

	if( !tag_exists( $p_tag_id ) ) {
		return ApiObjectFactory::faultNotFound( 'No tag with id ' . $p_tag_id );
	}

	log_event( LOG_WEBSERVICE, 'deleting tag id \'' . $p_tag_id . '\'' );
	return tag_delete( $p_tag_id );
}

/**
 * Set tag(s) for a given issue id
 * @param integer $p_issue_id Issue id.
 * @param array   $p_tags     Array of tags.
 * @param integer $p_user_id  User id.
 * @return void|RestFault|SoapFault
 */
function mci_tag_set_for_issue ( $p_issue_id, array $p_tags, $p_user_id ) {
	$t_tag_ids_to_attach = array();
	$t_tag_ids_to_detach = array();

	$t_submitted_tag_ids = array();
	$t_attached_tags = tag_bug_get_attached( $p_issue_id );
	$t_attached_tag_ids = array();
	foreach( $t_attached_tags as $t_attached_tag ) {
		$t_attached_tag_ids[] = $t_attached_tag['id'];
	}

	foreach( $p_tags as $t_tag ) {
		$t_tag = ApiObjectFactory::objectToArray( $t_tag );

		if( isset( $t_tag['id'] ) ) {
			$t_tag_id = $t_tag['id'];
			if( !tag_exists( $t_tag_id ) ) {
				throw new ClientException(
					"Tag with id $t_tag_id not found.",
					ERROR_TAG_NOT_FOUND
				);
			}
		} else if( isset( $t_tag['name'] ) ) {
			$t_get_tag = tag_get_by_name( $t_tag['name'] );
			if( $t_get_tag === false ) {
				throw new ClientException(
					"Tag '{$t_tag['name']}' not found.",
					ERROR_TAG_NOT_FOUND
				);
			}

			$t_tag_id = $t_get_tag['id'];
		} else {
			throw new ClientException(
				'Tag without id or name.',
				ERROR_TAG_NAME_INVALID
			);
		}

		$t_submitted_tag_ids[] = $t_tag_id;

		if( in_array( $t_tag_id, $t_attached_tag_ids ) ) {
			continue;
		}

		$t_tag_ids_to_attach[] = $t_tag_id;
	}

	foreach( $t_attached_tag_ids as $t_attached_tag_id ) {
		if( in_array( $t_attached_tag_id, $t_submitted_tag_ids ) ) {
			continue;
		}

		$t_tag_ids_to_detach[] = $t_attached_tag_id;
	}

	foreach( $t_tag_ids_to_detach as $t_tag_id ) {
		if( access_has_bug_level( config_get( 'tag_detach_threshold' ), $p_issue_id, $p_user_id ) ) {
			log_event( LOG_WEBSERVICE, 'detaching tag id \'' . $t_tag_id . '\' from issue \'' . $p_issue_id . '\'' );
			tag_bug_detach( $t_tag_id, $p_issue_id );
		}
	}

	foreach ( $t_tag_ids_to_attach as $t_tag_id ) {
		if( access_has_bug_level( config_get( 'tag_attach_threshold' ), $p_issue_id, $p_user_id ) ) {
			log_event( LOG_WEBSERVICE, 'attaching tag id \'' . $t_tag_id . '\' to issue \'' . $p_issue_id . '\'' );
			tag_bug_attach( $t_tag_id, $p_issue_id );
		}
	}
}
