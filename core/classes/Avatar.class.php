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
 * Avatar class.
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Avatar class
 *
 * @package MantisBT
 * @subpackage classes
 */

require_api( 'access_api.php' );
require_api( 'user_api.php' );

/**
 * A class that represents information about a user's avatar.
 */
class Avatar
{
    public $image = null;
    public $link = null;
    public $text = null;

    /**
     * Gets the avatar information for the user.  The avatars are provided by
     * plugins that can integrate with a variety of services like gravatar.com,
     * LDAP, Social Identities, etc.
     *
     * If logged in user doesn't have access to view avatars or no avatar is found,
     * then a default avatar will be used.
     *
     * Note that the provided user id may no longer has a corresponding user in the
     * system, if the user was deleted.
     *
     * @param integer $p_user_id  The user id.
     * @param integer $p_size     The desired width/height of the avatar.
     *
     * @return array The array with avatar information.
     */
    public static function get( $p_user_id, $p_size = 80 ) {
        $t_enabled = config_get( 'show_avatar' ) !== OFF;
        $t_avatar = null;

        if ( $t_enabled ) {
			$t_user_exists = user_exists( $p_user_id );
            if ( $t_user_exists &&
                 access_has_project_level( config_get( 'show_avatar_threshold' ), null, $p_user_id ) ) {
                $t_avatar = event_signal(
                    'EVENT_USER_AVATAR',
                    array( $p_user_id, $p_size ) );
            }

            if( $t_avatar === null ) {
                $t_avatar = new Avatar();
            }

            $t_avatar->normalize( $p_user_id, $t_user_exists );
        }

        return $t_avatar;
    }

    /**
     * A method that is called on the Avatar object after it is populated by
     * the plugins to make sure that all fields are validated correctly,
     * missing values are defaulted, and match the expectations of the MantisBT
     * core.
     *
     * @param integer $p_user_id  The user id.
	 * @param bool    $p_user_exists Whether the user exists.
     *
     * @return void
     */
    private function normalize( $p_user_id, $p_user_exists ) {
        if( $this->image === null) {
            $this->image = config_get_global( 'path' ) . 'images/avatar.png';
        }

        if( $this->link === null ) {
            if ( $p_user_exists ) {
                $this->link = config_get_global( 'path' ) .
                    'view_user_page.php?id=' . $p_user_id;
            } else {
                $this->link = '';
            }
        }

        if( $this->text === null ) {
            $this->text = $p_user_exists ? user_get_name( $p_user_id ) : '';
        }
    }
}

