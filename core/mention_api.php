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

	static $s_pattern = null;
	if( $s_pattern === null ) {
		$t_quoted_tag = preg_quote( mentions_tag() );
		$s_pattern = '/(?:'
			# Negative lookbehind to ensure we have whitespace or start of
			# string before the tag - ensures we don't match a tag in the
			# middle of a word (e.g. e-mail address)
			. '(?<=^|[^\w])'
			# Negative lookbehind  to ensure we don't match multiple tags
			. '(?<!' . $t_quoted_tag . ')' . $t_quoted_tag
			. ')'
			# any word char or period, but must not end with period
			. '([\w.]*[\w])'
			# Lookforward to ensure next char is not a valid mention char or
			# the end of the string, or the mention tag
			. '(?=[^\w@]|$)'
			. '(?!$t_quoted_tag)'
			. '/';
	}

	preg_match_all( $s_pattern, $p_text, $t_mentions );

	return array_unique( $t_mentions[1] );
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
	$t_mentioned_users = mention_get_users( $p_text );
	if( empty( $t_mentioned_users ) ) {
		return $p_text;
	}

	$t_mentions_tag = mentions_tag();
	$t_formatted_mentions = array();

	foreach( $t_mentioned_users as $t_username => $t_user_id  ) {
		$t_mention = $t_mentions_tag . $t_username;
		$t_mention_formatted = $t_mention;

		if( $p_html ) {
			$t_mention_formatted = string_display_line( $t_mention_formatted );

			$t_mention_formatted = '<a class="user" href="' . string_sanitize_url( 'view_user_page.php?id=' . $t_user_id, true ) . '">' . $t_mention_formatted . '</a>';

			if( !user_is_enabled( $t_user_id ) ) {
				$t_mention_formatted = '<s>' . $t_mention_formatted . '</s>';
			}

			$t_mention_formatted = '<span class="mention">' . $t_mention_formatted . '</span>';
		}

		$t_formatted_mentions[$t_mention] = $t_mention_formatted;
	}

	# Replace the mentions, ignoring existing anchor tags (otherwise
	# previously set mailto links would be processed as mentions,
	# corrupting the output
	$t_text = string_process_exclude_anchors(
		$p_text,
		function( $p_string ) use ( $t_formatted_mentions ) {
			return str_replace(
				array_keys( $t_formatted_mentions ),
				array_values( $t_formatted_mentions ),
				$p_string
			);
		}
	);

	return $t_text;
}

