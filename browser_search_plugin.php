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
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

if ( utf8_strtolower( $f_type ) == 'id' ) {
	echo '<ShortName>MantisBT IssueId</ShortName>';
	echo '<Description>MantisBT Issue Id</Description>';
	echo '<InputEncoding>UTF-8</InputEncoding>';
} else {
	echo '<ShortName>MantisBT Search</ShortName>';
	echo '<Description>MantisBT Text Search</Description>';
	echo '<InputEncoding>UTF-8</InputEncoding>';
}
?>
<Image width="16" height="16">data:image/png,%89PNG%0D%0A%1A%0A%00%00%00%0DIHDR%00%00%00%10%00%00%00%10%08%06%00%00%00%1F%F3%FFa%00%00%00%01sRGB%00%AE%CE%1C%E9%00%00%00%06bKGD%00%FF%00%FF%00%FF%A0%BD%A7%93%00%00%00%09pHYs%00%00%0D%12%00%00%0D%3A%01%E8%DD%99%DE%00%00%00%07tIME%07%D8%0A%1B%035%18L%0A%18d%00%00%02AIDAT8%CB%8D%93%BDOZa%18%C5%7F%7C(%97%8FF%CA%0DU%7B%5BCj%D0Z%E3%AA%12%1C%D4%85A%A2%5D%5C%1C%1C%8C%93%FF%83I%FF%13%07%12'0.%9A%90%88B%24%C6%0Ej4bJ%1A%8C%A2%40%FC%00%8C%20%22%B7p%3Bq%95R%9B%9E%E9%7D%CE%9Bs%F2%BC%E7y%5EM%A9%24%2B%3E_%9CX%2C%87%2C%D7x%0D%82%A0cb%C2A%20%90%40%10t%8C%8C%BC%C7%E3%F9%88%FE%EC%AC%402Y%F8%A7%18%C0%EBu%B0%B6v%06%40%B9%5Cec%E3%02%9DN%83%CEj%FD%FAm%7C%FC%03%FD%FD%22%C9d%81J%A5%D9%A8%BB%BB%8DJ%A5%CA%F9y%B1%81%CF%E7%CBhs%B9'%FC%FE%04%91H%8A%E9%E9n%FA%FA%DE6%19%B8%5C%1D%EC%ED%DD4%F1%B9%DC%13%DAzq%7D%FD%C8%D2%D2%0FDQ%60r%D2%81V%AB%01%C0f3%90%CF%97%A9%D5%14Uh%B7%0B%EAY%FF%A7k4%9AA%92%CC%CC%CD%F5!%CB5%06%06D%0E%0En%D0h%40Q%C0%E9lcv%F63%8B%8B%DF%01%9E%3Bx%89%AB%AB%12%ED%EDF%86%86%DA1%99%F4%B8%DD%9D%B8%DD%9Dj%98%A2(%60%B3%19%5E7%B0Z%0DH%92%A5%813%9B%F5%F4%F6Z%B1%DB%8D%00j%D8%0D%06%92d%06%E0%F6%B6%CC%E6%E6%A5%CA%17%8B2%DB%DB%19%BC%5E%07%F1%F8%1D%0F%0F2%C5%A2%DC%9C%C1%C2%C2%00%3B%3B%19%C2%E1%14~%7F%02E%81%9E%1E%2B%85B%05%87%E3%0DF%A3%1E%8B%A5%85L%A6%F4%F7%10%1F%1F%7F1%3A*1%3C%DC%C1%F1q%96x%FC%8El%B6%CC%FD%7D%85%99%99%1E%02%81%04SS%9F%08%85.TM%C3%13%C2%E1%14fs%0B%E9%F4%03%A2(0%3F%FF%85%C1%C1w%1C%1E%DE%B2%B5%95%E2%E8(K%B5Zcw%F7%EA%EF%1DD%A3%19%BA%BA%2C%B8%5C%1D%AC%AC%9C%B2%BE~%8E%C9%D4B%B5%AA%10%0A%5Db%B7%1B%D9%DF%BFiX%7Bm%7D%1Cu%2C%2F%FF%C4%E7%8B36%26%A1%D5j89%C9%A9w%8A%A2%10%89%A4%D5%DAf3%A0Y%5D%3DU%82%C1d%D3(%5B%5B%B58%9DVb%B1g%83%FA2%D5%E1%F1t%A1)%16%2BJ0xA4%9A%A6%5C%AE%F2%3Fx%F9%9D%7F%03%B4%C6%E9%B0%15%8B%D3U%00%00%00%00IEND%AEB%60%82</Image>
<?php
if ( utf8_strtolower( $f_type ) == 'id' ) {
	echo '<Url type="text/html" method="GET" template="', $t_path, 'view.php?id={searchTerms}"></Url>';
} else {
	echo '<Url type="text/html" method="GET" template="', $t_path, 'view_all_set.php?type=1&amp;temporary=y&amp;handler_id=[all]&amp;search={searchTerms}"></Url>';
}

echo '<moz:SearchForm>', $t_path, 'view_all_bug_page.php</moz:SearchForm>';
?>
</OpenSearchDescription>