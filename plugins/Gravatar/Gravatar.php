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
 * This is an avatar provider plugin that is based on http://www.gravatar.com.
 * Users will need to register there the same email address used in this
 * MantisBT installation to have their avatar shown.
 * 
 * Please note: upon registration or avatar change, it takes some time for
 * the updated gravatar images to show on sites
 */
class GravatarPlugin extends MantisPlugin {
	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = lang_get( 'description' );
		$this->page = '';

		$this->version = '1.0';
		$this->requires = array(
			'MantisCore' => '1.3.0',
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
    	$t_default_avatar = config_get( 'show_avatar' );

    	# Set default avatar for legacy configuration
    	if( ON === $t_default_avatar || OFF === $t_default_avatar) {
    		$t_default_avatar = 'identicon';
    	}

	    return array(
	        /**
	         * The rating of the avatar to show: 'G', 'PG', 'R', 'X'
	         * @link http://en.gravatar.com/site/implement/images/
	         */
	       'rating' => 'G',

           /**
            * The kind of avatar to use:
            *
            * - One of Gravatar's defaults (mm, identicon, monsterid, wavatar, retro)
            *   @link http://en.gravatar.com/site/implement/images/
            * - An URL to the default image to be used (for example,
            *   "http:/path/to/unknown.jpg" or "%path%images/avatar.png")
	        */
	       'default_avatar' => $t_default_avatar,
	    );
	}

	function hooks() {
		return array(
		    'EVENT_USER_AVATAR' => 'user_get_avatar',
		    'EVENT_LAYOUT_CSP_RECORDS' => 'csp_headers',
		);
	}

    function csp_headers() {
        $t_csp = array();

		# Policy for images: Allow gravatar URL
		if( config_get_global( 'show_avatar' ) !== OFF ) {
			if( http_is_protocol_https() ) {
				$t_avatar_url = 'https://secure.gravatar.com:443';
			} else {
				$t_avatar_url = 'http://www.gravatar.com:80';
			}

			$t_csp[] = "img-src 'self' $t_avatar_url";
		}

		return $t_csp;
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
    	$t_default_avatar = config_get( 'show_avatar' );

    	# Set default avatar for legacy configuration
    	if( ON === $t_default_avatar || OFF === $t_default_avatar ) {
    		$t_default_avatar = 'identicon';
    	}

    	# Default avatar is either one of Gravatar's options, or
    	# assumed to be an URL to a default avatar image
    	$t_default_avatar = urlencode( $t_default_avatar );
    	$t_rating = plugin_config_get( 'rating' );

    	if ( user_exists( $p_user_id ) ) {
    		$t_email_hash = md5( strtolower( trim( user_get_email( $p_user_id ) ) ) );
    	} else {
    		$t_email_hash = md5( 'generic-avatar-since-user-not-found' );
    	}

    	# Build Gravatar URL
    	if( http_is_protocol_https() ) {
    		$t_avatar_url = 'https://secure.gravatar.com/';
    	} else {
    		$t_avatar_url = 'http://www.gravatar.com/';
    	}

    	$t_avatar_url .=
    	    'avatar/' . $t_email_hash . '?d=' . $t_default_avatar .
    	    '&r=' . $t_rating . '&s=' . $p_size;

        $t_avatar = new Avatar();
        $t_avatar->image = $t_avatar_url;

    	return $t_avatar;
    }
}
