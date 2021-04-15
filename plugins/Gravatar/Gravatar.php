<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Mantis Gravatar Plugin
 *
 * This is an avatar provider plugin that is based on https://www.gravatar.com.
 * Users will need to register there the same email address used in this
 * MantisBT installation to have their avatar shown.
 *
 * Please note: upon registration or avatar change, it takes some time for
 * the updated gravatar images to show on sites
 */
class GravatarPlugin extends MantisPlugin {
	const GRAVATAR_URL = 'https://secure.gravatar.com/';

	/**
	 * Default Gravatar image types
	 *
	 * @link https://en.gravatar.com/site/implement/images/
	 */
	const GRAVATAR_DEFAULT_MYSTERYMAN = 'mm';
	const GRAVATAR_DEFAULT_IDENTICON  = 'identicon';
	const GRAVATAR_DEFAULT_MONSTERID  = 'monsterid';
	const GRAVATAR_DEFAULT_WAVATAR    = 'wavatar';
	const GRAVATAR_DEFAULT_RETRO      = 'retro';
	const GRAVATAR_DEFAULT_BLANK      = 'blank';

	/**
	 * Gravatar Ratings
	 *
	 * @link https://en.gravatar.com/site/implement/images/
	 */
	const GRAVATAR_RATING_G  = 'G';
	const GRAVATAR_RATING_PG = 'PG';
	const GRAVATAR_RATING_R  = 'R';
	const GRAVATAR_RATING_X  = 'X';

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->page = '';

		$this->version = MANTIS_VERSION;
		$this->requires = array(
			'MantisCore' => '2.0.0',
		);

		$this->author = 'Victor Boctor';
		$this->contact = 'vboctor@mantisbt.org';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Default plugin configuration.
	 * @return array
	 */
	function config() {
		return array(
			/**
			 * The rating of the avatar to show: 'G', 'PG', 'R', 'X'
			 * @link https://en.gravatar.com/site/implement/images/
			 */
			'rating' => self::GRAVATAR_RATING_G,

			/**
			 * The kind of avatar to use:
			 *
			 * - One of Gravatar's defaults (mm, identicon, monsterid, wavatar, retro)
			 *   @link https://en.gravatar.com/site/implement/images/
			 * - An URL to the default image to be used (for example,
			 *   "http:/path/to/unknown.jpg" or "%path%images/avatar.png")
			 */
			'default_avatar' => self::GRAVATAR_DEFAULT_IDENTICON
		);
	}

	/**
	 * Register event hooks for plugin.
	 */
	function hooks() {
		return array(
			'EVENT_USER_AVATAR' => 'user_get_avatar',
			'EVENT_CORE_HEADERS' => 'csp_headers',
		);
	}

	/**
	 * Register gravatar url as an img-src for CSP header
	 */
	function csp_headers() {
		if( config_get( 'show_avatar' ) !== OFF ) {
			http_csp_add( 'img-src', self::GRAVATAR_URL );
		}
	}

	/**
	 * Return the user avatar image URL
	 * in this first implementation, only gravatar.com avatars are supported
	 *
	 * This function returns an array( URL, width, height ) or an empty array when the given user has no avatar.
	 *
	 * @param string  $p_event   The name for the event.
	 * @param integer $p_user_id A valid user identifier.
	 * @param integer $p_size    The required number of pixel in the image to retrieve the link for.
	 *
	 * @return object An instance of class Avatar or null.
	 */
	function user_get_avatar( $p_event, $p_user_id, $p_size = 80 ) {
		$t_default_avatar = plugin_config_get( 'default_avatar' );

		# Default avatar is either one of Gravatar's options, or
		# assumed to be an URL to a default avatar image
		$t_default_avatar = urlencode( $t_default_avatar );
		$t_rating = plugin_config_get( 'rating' );

		if( user_exists( $p_user_id ) ) {
			$t_email_hash = md5( strtolower( trim( user_get_email( $p_user_id ) ) ) );
		} else {
			$t_email_hash = md5( 'generic-avatar-since-user-not-found' );
		}

		# Build Gravatar URL
		$t_avatar_url = self::GRAVATAR_URL .
			'avatar/' . $t_email_hash . '?' .
			http_build_query(
				array(
					'd' => $t_default_avatar,
					'r' => $t_rating,
					's' => $p_size,
				)
			);

		$t_avatar = new Avatar();
		$t_avatar->image = $t_avatar_url;

		return $t_avatar;
	}
}
