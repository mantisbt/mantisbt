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
 * Mention API
 *
 * @package CoreAPI
 * @subpackage LanguageAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses bug_api.php
 * @uses email_api.php
 */

require_api( 'bug_api.php' );
require_api( 'email_api.php' );

/**
 * Check if @ mentions feature is enabled or not.
 *
 * @return bool true enabled, false otherwise.
 */
function mention_enabled() {
	return config_get( 'mentions_enabled' ) != OFF;
}

/**
 * Get the tag to use for mentions.
 * @return string The mentions tag.
 */
function mentions_tag() {
	return config_get( 'mentions_tag', null, ALL_USERS, ALL_PROJECTS );
}

/**
 * A method that takes in a text argument and extracts all candidate @ mentions
 * from it.  The return list will not include the @ sign and will not include
 * duplicates.  This method is mainly for testability and it doesn't take into
 * consideration whether the @ mentions features is enabled or not.
 *
 * @param string $p_text The text to process.
 * @return array of @ mentions without the @ sign.
 * @private
 */
function mention_get_candidates( $p_text ) {
	if( is_blank( $p_text ) ) {
		return array();
	}

	$t_mentions_tag = mentions_tag();
	$t_mentions_tag_length = strlen( $t_mentions_tag );
	$t_separators = "\n\r\t,;:/\\| ";
	$t_token = strtok( $p_text, $t_separators );

	$t_mentions = array();

	while( $t_token !== false ) {
		$t_mention = $t_token;
		$t_token = strtok( $t_separators );

		# make sure token has @
		if( stripos( $t_mention, $t_mentions_tag ) !== 0 ) {
			continue;
		}

		$t_mention = substr( $t_mention, $t_mentions_tag_length );
		if( is_blank( $t_mention ) ) {
			continue;
		}

		# Filter out the @@vboctor case.
		if( stripos( $t_mention, $t_mentions_tag ) === 0 ) {
			continue;
		}

		$t_valid = true;
		for( $i = 0; $i < strlen( $t_mention ); $i++ ) {
			$t_char = $t_mention[$i];
			if( !ctype_alnum( $t_char ) && $t_char != '.' && $t_char != '_' ) {
				$t_valid = false;
				break;
			}
		}

		if( !$t_valid ) {
			continue;
		}

		# "victor.boctor" is valid, "vboctor." should be "vboctor"
		$t_mention = trim( $t_mention, '.' );
		$t_mentions[$t_mention] = true;
	}

	$t_mentions = array_keys( $t_mentions );

	return $t_mentions;
}

/**
 * Given a string find the @ mentioned users.  The return list is a valid
 * list of valid mentioned users.  The list will be empty if the mentions
 * feature is disabled.
 *
 * @param string $p_text The text to process.
 * @return array with valid usernames as keys and their ids as values.
 */
function mention_get_users( $p_text ) {
	if ( !mention_enabled() ) {
		return array();
	}

	$t_matches = mention_get_candidates( $p_text );
	if( empty( $t_matches )) {
		return array();
	}

	$t_mentioned_users = array();

	foreach( $t_matches as $t_candidate ) {
		if( $t_user_id = user_get_id_by_name( $t_candidate ) ) {
			if( false === $t_user_id ) {
				continue;
			}

			$t_mentioned_users[$t_candidate] = $t_user_id;
		}
	}

	return $t_mentioned_users;
}

/**
 * Process users that are mentioned on the specified bug.
 *
 * @param int $p_bug_id The bug id.
 * @param array $p_mentioned_user_ids An array of user ids
 * @param string $p_message The message containing the mentions.
 * @param array $p_removed_mentions_user_ids The list of ids removed due to lack of access to issue or note.
 * @return void
 */
function mention_process_user_mentions( $p_bug_id, $p_mentioned_user_ids, $p_message, $p_removed_mentions_user_ids ) {
	email_user_mention( $p_bug_id, $p_mentioned_user_ids, $p_message, $p_removed_mentions_user_ids );
}

/**
 * Format and hyperlink mentions
 *
 * @param string $p_text The text to process.
 * @param bool $p_html true for html, false otherwise.
 * @return string The processed text.
 */
function mention_format_text( $p_text, $p_html = true ) {
	if ( !mention_enabled() ) {
		return $p_text;
	}

	$t_mentioned_users = mention_get_users( $p_text );
	if( empty( $t_mentioned_users ) ) {
		return $p_text;
	}

	$t_mentions_tag = mentions_tag();
	$t_formatted_mentions = array();

	foreach( $t_mentioned_users as $t_username => $t_user_id  ) {
		$t_mention = $t_mentions_tag . $t_username;
		
		# Uncomment the line below to use realname / username based on settings
		# The reason we always use username is to avoid confusing users by showing
		# @ mentions using realname but only supporting it using usernames.
		# We could support realnames if we assume they contain no spaces, but that
		# is unlikely to be the case.
		# $t_username = user_get_name( $t_user_id );

		if( $p_html ) {
			$t_username = string_display_line( $t_username );

			if( user_exists( $t_user_id ) && user_get_field( $t_user_id, 'enabled' ) ) {
				$t_user_url = '<a class="user" href="' . string_sanitize_url( 'view_user_page.php?id=' . $t_user_id, true ) . '">' . $t_mentions_tag . $t_username . '</a>';
			} else {
				$t_user_url = '<del class="user">' . $t_mentions_tag . $t_username . '</del>';
			}

			$t_formatted_mentions[$t_mention] = '<span class="mention">' . $t_user_url . '</span>';
		} else {
			$t_formatted_mentions[$t_mention] = $t_mentions_tag . $t_username;
		}
	}

	$t_text = str_replace(
		array_keys( $t_formatted_mentions ),
		array_values( $t_formatted_mentions ),
		$p_text
	);

	return $t_text;
}

