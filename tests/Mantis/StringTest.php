<?php
# MantisBT - a php based bugtracking system

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
 * @package Tests
 * @subpackage String
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once 'PHPUnit/Framework.php';

//require_once 'string_api.php';

class Mantis_StringTest extends PHPUnit_Framework_TestCase {

    /**
      * Tests string_sanitize_url()
      *
      * @dataProvider provider
      */
    public function testStringSanitize( $url )
    {
        $a = htmlspecialchars($url);
        $b = htmlspecialchars( string_do_sanitize_url($url, false) );
        $this->assertEquals( $a, $b );
    }

    public function provider()
    {
        $testStrings = array(
            '',
            'abc.php',
            'abc.php?',
            'abc.php#a',
            'abc.php?abc=def',
            'abc.php?abc=def#a',
            'abc.php?abc=def&z=xyz',
            'abc.php?abc=def&amp;z=xyz',
            'abc.php?abc=def&z=xyz#a',
            'abc.php?abc=def&amp;z=xyz#a',
            'abc.php?abc=def&z=<script>alert("foo")</script>z#a',
            'abc.php?abc=def&z=z#<script>alert("foo")</script>a',
            'plugin.php?page=Source/index',
            'plugin.php?page=Source/list&id=1',
            'plugin.php?page=Source/list&id=1#abc',
/*
   FIXME
            $my_path.'abc.php',
            $my_path.'abc.php?',
            $my_path.'abc.php#a',
            $my_path.'abc.php?abc=def',
            $my_path.'abc.php?abc=def#a',
            $my_path.'abc.php?abc=def&z=xyz',
            $my_path.'abc.php?abc=def&amp;z=xyz',
            $my_path.'abc.php?abc=def&z=xyz#a',
            $my_path.'abc.php?abc=def&amp;z=xyz#a',
            $my_path.'abc.php?abc=def&z=<script>alert("foo")</script>z#a',
            $my_path.'abc.php?abc=def&z=z#<script>alert("foo")</script>a',
            $my_path.'plugin.php?page=Source/index',
            $my_path.'plugin.php?page=Source/list&id=1',
            $my_path.'plugin.php?page=Source/list&id=1#abc',
*/            
            'http://www.test.my.url/'
                );
        foreach( $testStrings as $key=>$value ) {
            $tests[] = array( $value );
        }
        return $tests;
    }


}


// FIXME: hardcoced here to avoid external dependencies, should use code in string_api.php
function string_do_sanitize_url( $p_url, $p_return_absolute = false ) {
	$t_url = strip_tags( urldecode( $p_url ) );

	$t_path = '/';
	$t_short_path = '/';

	$t_pattern = '(?:/*(?P<script>[^\?#]*))(?:\?(?P<query>[^#]*))?(?:#(?P<anchor>[^#]*))?';

	# Break the given URL into pieces for path, script, query, and anchor
	$t_type = 0;
	if ( preg_match( "@^(?P<path>$t_path)$t_pattern\$@", $t_url, $t_matches ) ) {
		$t_type = 1;
	} else if ( preg_match( "@^(?P<path>$t_short_path)$t_pattern\$@", $t_url, $t_matches ) ) {
		$t_type = 2;
	} else if ( preg_match( "@^(?P<path>)$t_pattern\$@", $t_url, $t_matches ) ) {
		$t_type = 3;
	}

	# Check for URL's pointing to other domains
	if ( 0 == $t_type || empty( $t_matches['script'] ) ||
		3 == $t_type && preg_match( '@(?:[^:]*)?://@', $t_url ) > 0 ) {

		return ( $p_return_absolute ? $t_path . '/' : '' ) . 'index.php';
	}

	# Start extracting regex matches
	$t_script = $t_matches['script'];
	$t_script_path = $t_matches['path'];

	# Clean/encode query params
	$t_query = '';
	if ( isset( $t_matches['query'] ) ) {
		$t_pairs = array();
		parse_str( html_entity_decode( $t_matches['query'] ), $t_pairs );

		$t_clean_pairs = array();
		foreach( $t_pairs as $t_key => $t_value ) {
			$t_clean_pairs[] = rawurlencode( $t_key ) . '=' . rawurlencode( $t_value );
		}

		$t_query = '?' . join( '&amp;', $t_clean_pairs );
	}

	# encode link anchor
	$t_anchor = '';
	if ( isset( $t_matches['anchor'] ) ) {
		$t_anchor = '#' . rawurlencode( $t_matches['anchor'] );
	}

	# Return an appropriate re-combined URL string
	if ( $p_return_absolute ) {
		return $t_path . '/' . $t_script . $t_query . $t_anchor;
	} else {
		return ( !empty( $t_script_path ) ? $t_script_path . '/' : '' ) . $t_script . $t_query . $t_anchor;
	}
}

