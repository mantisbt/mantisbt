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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2013  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
/**
 * MantisBT Core API's
 */
require_once( 'core.php' );

$f_type = gpc_get_string( 'type', 'text' );

header( 'Content-Type: application/opensearchdescription+xml' );
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"
                       xmlns:moz="http://www.mozilla.org/2006/browser/search/">
<?php
$t_path = config_get( 'path' );
$t_title = config_get ( 'search_title' );

if ( utf8_strtolower( $f_type ) == 'id' ) {
	echo '<ShortName>', sprintf( lang_get( 'browser_search_id_shortname' ), $t_title ), '</ShortName>';
	echo '<Description>', sprintf( lang_get( 'browser_search_id_description' ), $t_title ), '</Description>';
	echo '<InputEncoding>UTF-8</InputEncoding>';
} else {
	echo '<ShortName>', sprintf( lang_get( 'browser_search_text_shortname' ), $t_title ), '</ShortName>';
	echo '<Description>', sprintf( lang_get( 'browser_search_text_description' ), $t_title ), '</Description>';
	echo '<InputEncoding>UTF-8</InputEncoding>';
}
?>
<Image width="16" height="16">
<?php
$t_favicon = config_get( 'favicon_image' );
$t_favicon_type = config_get( 'favicon_image_type' );
if( !is_blank( $t_favicon ) ) {
    $t_favicon_data = file_get_contents( $t_favicon );
    echo 'data:', $t_favicon_type, ',', rawurlencode($t_favicon_data);
} else {
    echo "data:image/x-icon,%00%00%01%00%01%00%10%10%00%00%01%00%20%00h%04%00%00%16%00%00%00(%00%00%00%10%00%00%00%20%00%00%00%01%00%20%00%00%00%00%00%00%00%00%00%CA%99%00%00%CA%99%00%00%00%00%00%00%00%00%00%007%A4G%D73%A54%ED7%8F%1F%ED8%96%0C%EEY%8F(%BB%5B%8AI%B30%B8%7C%E0%1B%BF%81%ED%8E%B6%96_%00%00%00%00%00%00%00%00%C4%D0%C2)R%A3t%A8v%9Bp%8DR%89%22%BD%3D%8D%01%E0%2C%B2X%F22%97%3C%FF4%A33%FF%3B%95%1D%FDS%94O%D6%2C%BC%82%FB!%C6%86%FF%25%AFQ%FF%7D%A4rv%00%00%00%00%F6%F0%ED%03Z%B0%7B%C4%0F%C4%80%FFF%B0~%D9%89%9Dj%856%8D%00%FF%2C%A0W%ED-%B0W%FF4%A1%40%FBC%91R%E4!%C0%81%FF%22%C0r%FF%2F%A28%FF2%84%14%FF%9E%AF%8BP%00%00%00%00%D2%D4%C7%236%91(%FB%25%C2y%FF!%BF%80%FF%5E%91T%CBT%91!%C9%26%BBx%ED%2B%AFc%FF%2C%A0%5C%FF%22%C1%7F%FF%24%B0P%FFO%989%D4W%96X%BE*%9E%3B%FF%A8%B7%95%3A%00%00%00%00%A3%BC%95h-%81%0D%FF.%A8%3E%FF%20%C7%88%FFE%ACu%E0P%90-%CB%25%C5%88%ED%25%BC~%FF%22%B8g%FF2%A0%3B%F9v%9D%5C%8D%C3%BD%AE%0Di%B6%8C%9C-%A08%FF%BC%C2%AD%16%00%00%00%00k%A5W%B8*%A4N%FCF%8B*%EB(%B7%5D%FF!%BF%81%FFD%8C%3C%D9%22%C1%7D%F3%26%ACL%FFX%9AG%C1%A8%B3%948%00%00%00%00%00%00%00%00M%ACx%CA0%85%26%FEj%7FH%99%A6%AE%94H%3F%A0%3A%F2E%B3v%DB%C1%C4%AE%40%3C%98%2B%F5!%C4%7B%FF9%A0h%DE%3C%9E%40%DF%89%A5sr%C6%C7%BA%03%00%00%00%00%00%00%00%00%A3%B1%94%3A%3F%88%22%EE8w%00%FF3t%00%FF%3C%7B%03%FE(%974%FFk%B7%90%A4%00%00%00%00%9F%B7%87t%26%A27%FF%23%C1%82%EF%B1%BA%A1%1D%00%00%00%00%00%00%00%00%C5%C6%B5%04%85%9EZ%7DD%8A%00%F6%3E%93%00%FF%3D%88%00%FF%3F%90%00%FFA%93%00%FF6%84%00%FF%83%A3l%8A%00%00%00%00%F9%F5%F7%01k%A1Q%BF%20%B2W%FB%00%00%00%00%00%00%00%00%BD%BD%A7%1DW%94_%BA%1F%9DU%FF(%97K%FF8%8D%18%FF%3F%88%00%FF%3C%8B%08%FF%2F%92%3D%FF*%9FR%FF1%8C%2F%F7%9B%ADxs%00%00%00%00%DF%E1%D5%26%3F%983%DF%00%00%00%00%BD%C0%A8!7%99%7D%DB%00%BD%D3%FF%01%A3%C1%FF%00%DC%FF%FF%08%BC%D7%FF7%80%1A%FF%14%AC%A7%FF%00%DC%FF%FF%00%B2%D4%FF%00%B8%D2%FF%15%9Bt%FF%B1%BD%94%5E%00%00%00%00%B1%C4%A2M%00%00%00%00s%9D%7D%8C%00%D4%EB%FF%04bp%FF%0A%26%25%FF%00%EB%FF%FF%09%C4%D6%FF6%8E%26%FF%12%B6%AE%FF%00%F3%FF%FF%08W%5C%FF%0628%FF%00%DA%FC%FF%3F%94b%E0%EC%E9%DE%0C%00%00%00%00%00%00%00%00_%A6%9A%94%04%E4%FF%FFi%8A%8D%FF%05%7C%82%FF%00%F2%FF%FF%1B%AE%90%FF2%AEH%FF%25%A6n%FF%00%E9%FE%FF%02%A1%AC%FF%5Bb%60%FF%23%EB%FF%FF%11%9C%8A%FB%CF%CE%B6%20%00%00%00%00%00%00%00%00%B7%CF%C5%3D%06%C8%E4%FF%0C%DC%FB%FF%00%E7%FF%FF%08%CB%DB%FF'%B1s%FF'%C2%7B%FF*%B3h%FF%10%BC%BC%FF%00%E9%FF%FF%10%D8%F3%FF%02%D4%F5%FFG%9B%80%CF%F2%ED%E5%07%00%00%00%00%00%00%00%00%00%00%00%00~%B0%A3n%0F%B7%C3%FC%0B%BE%C7%FF'%9Df%FF'%BE%7C%FF%25%C3%87%FF%25%C4%84%FF%25%B1%7B%FF%0F%BC%BD%FF%00%C5%D2%FF!%9C%86%F4%C2%CB%B6%3E%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%9D%AD%93U1%8B%2C%FF%2F%87%11%FF%1F%B9x%FF%1E%C8%89%FF%1F%BCz%FF.%932%FF%2B%ABk%FAK%9Ci%BE%AD%BB%A09%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%BB%C4%B1%12d%A1P%9C_%9B%3E%BA%8B%AD%8AjT%AA%7B%ADH%ADz%C4U%A6%7B%AAT%8F7%C0x%A2b%8C%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%F0%FF%FF%00%E0%FF%FF%00%E0%FF%FF%00%E0%FF%FF%04%C0%FF%FF%1CH%FF%FF%7C%0C%FF%FF%F8%0C%FF%FF%E0%0E%FF%FF%C0%07%FF%FF%80%03%FF%FF%80%03%FF%FF%C0%03%FF%FF%E0%07%FF%FF%F0%0F%FF%FF%E4%1F%FF%FF";
}
?>
</Image>
<?php
if ( utf8_strtolower( $f_type ) == 'id' ) {
	echo '<Url type="text/html" method="GET" template="', $t_path, 'view.php?id={searchTerms}"></Url>';
} else {
	echo '<Url type="text/html" method="GET" template="', $t_path, 'view_all_set.php?type=1&amp;temporary=y&amp;handler_id=[all]&amp;search={searchTerms}"></Url>';
}

echo '<moz:SearchForm>', $t_path, 'view_all_bug_page.php</moz:SearchForm>';
?>
</OpenSearchDescription>