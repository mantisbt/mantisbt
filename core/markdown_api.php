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

$g_parsedown = null;

function markdown_init() {
	global $g_parsedown;
	if ( $g_parsedown == null ) {
		require_once( dirname( dirname( __FILE__ ) ) . '/library/parsedown/Parsedown.php' );
		$g_parsedown = new Parsedown();
	}
}

function markdown_enabled() {
	return config_get( 'markdown_enabled' ) != OFF;
}

function markdown_trim_para( $p_text ) {
	$t_text = $p_text;
	$t_len = strlen( $t_text );

	if ( $t_len >= 7 && stripos( $t_text, '<p>' ) == 0 && stripos( $t_text, '</p>', $t_len - 4 ) !== false ) {
		$t_text = substr( $t_text, 3, $t_len - 7 );
	}

	return $t_text;	
}

function markdown_text( $p_text ) {
	markdown_init();

	global $g_parsedown;

	$t_text = $g_parsedown->text( $p_text );
	$t_text = markdown_trim_para( $t_text );

	return $t_text;
}

function markdown_line( $p_text ) {
	markdown_init();

	global $g_parsedown;

	$t_text =  $g_parsedown->line( $p_text );
	$t_text = markdown_trim_para( $t_text );

	return $t_text;
}