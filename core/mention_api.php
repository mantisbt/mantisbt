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
	preg_match_all( "/(?<!@)@[A-Za-z0-9_\.]+/", $p_text, $t_matches );
	
	$t_mentions = array();

	foreach( $t_matches[0] as $t_mention ) {
		$t_mention = substr( $t_mention, 1 );

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
 * @return Array with valid usernames as keys and their ids as values.
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
 * @return void
 */
function mention_process_user_mentions( $p_bug_id, $p_mentioned_user_ids, $p_message = '' ) {
	email_user_mention( $p_bug_id, $p_mentioned_user_ids, $p_message );
}

/**
 * Replace the @{U123} with the username or realname based on configuration.
 * If user is deleted, use user123.
 *
 * @param string $p_text The text to process.
 * @param bool $p_html true for html, false otherwise.
 * @return string The processed text.
 */
function mention_format_text( $p_text, $p_html = true ) {
	if ( !mention_enabled() ) {
		return $p_text;
	}

	$t_text = $p_text;

	$t_mentioned_users = mention_get_users( $p_text );
	if( empty( $t_mentioned_users ) ) {
		return $p_text;
	}

	$t_formatted_mentions = array();

	foreach( $t_mentioned_users as $t_user_name => $t_user_id  ) {
		if( $t_username = user_get_name( $t_user_id ) ) {
			$t_mention = '@' . user_get_field( $t_user_id, 'username' );

			if( $p_html ) {
				$t_username = string_display_line( $t_username );

				if( user_exists( $t_user_id ) && user_get_field( $t_user_id, 'enabled' ) ) {
					$t_user_url = '<a class="user" href="' . string_sanitize_url( 'view_user_page.php?id=' . $t_user_id, true ) . '">@' . $t_username . '</a>';
				} else {
					$t_user_url = '<del class="user">@' . $t_username . '</del>';
				}

				$t_formatted_mentions[$t_mention] = '<span class="mention">' . $t_user_url . '</span>';
			} else {
				$t_formatted_mentions[$t_mention] = '@' . $t_username;
			}
		}
	}

	$t_text = str_replace(
		array_keys( $t_formatted_mentions ),
		array_values( $t_formatted_mentions ),
		$p_text
	);

	return $t_text;
}

/**
 * Given a block of text find the @ mentioned users and replace them
 * with markup that @ mentions them using their user id.  This way the
 * mentions don't break if username is changed.  Also this allows to show
 * username or realname at display time based on configuration.
 *
 * For example, '@vboctor' will be replaced with '@{U10}' assuming user
 * 'vboctor' has id 10.
 *
 * @param string $p_text The text to process.
 * @param array $p_mentioned_users This is used for testing (key: username, value: id).
 * @return string The processed text.
 */
function mention_format_text_save( $p_text, $p_mentioned_users = null ) {
	if ( $p_mentioned_users !== null ) {
		$t_mentioned_users = $p_mentioned_users;	
	} else {
		$t_mentioned_users = mention_get_users( $p_text );
	}

	if( empty( $t_mentioned_users ) ) {
		return $p_text;
	}

	$t_formatted_mentions = array();

	foreach( $t_mentioned_users as $t_user_name => $t_user_id  ) {
		$t_formatted_mentions[$t_user_name] = "{U" . $t_user_id . "}";
	}

	$t_text = str_replace(
		array_keys( $t_formatted_mentions ),
		array_values( $t_formatted_mentions ),
		$p_text
	);

	return $t_text;
}

/**
 * Given a block of text, replace placeholders with usernames.
 *
 * For example, '@{U10}' will be replaced with '@vboctor' assuming user
 * 'vboctor' has id 10.
 *
 * @param string $p_text The text to process.
 * @param array $p_user_lookup key: user id, value: username - used for testing.
 * @return string The processed text.
 */
function mention_format_text_load( $p_text, $p_user_lookup = null ) {
	if ( !mention_enabled() ) {
		return $p_text;
	}
	
	$t_text = $p_text;

	preg_match_all( "/(?<!@)@{U[0-9]+}/", $p_text, $t_matches );

	if( !empty( $t_matches[0] ) ) {
		$t_matched_mentions = $t_matches[0];
		$t_matched_mentions = array_unique( $t_matched_mentions );

		$t_formatted_mentions = array();

		foreach( $t_matched_mentions as $t_mention ) {
			$t_user_id = substr( $t_mention, 3, strlen( $t_mention ) - 4 );
			
			if( $p_user_lookup !== null ) {
				if( isset( $p_user_lookup[$t_user_id] ) ) {
					$t_username = $p_user_lookup[$t_user_id];
				} else {
					$t_username = false;
				}
			} else {
				$t_username = user_get_name( $t_user_id );
			}

			if( $t_username ) {
				$t_formatted_mentions[$t_mention] = '@' . $t_username;
			} else {
				$t_formatted_mentions[$t_mention] = '@user' . $t_user_id;
			}
		}

		$t_text = str_replace(
			array_keys( $t_formatted_mentions ),
			array_values( $t_formatted_mentions ),
			$t_text
		);
	}

	return $t_text;
}
