<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( ADMINISTRATOR );

	function print_setting() {
		global $g_primary_color_dark, $g_primary_color_light, $g_category_title_color;

		$fd = fopen( "config_inc.php", "r" );
		$i = 0;
		while ( !feof( $fd ) ) {
    		$buffer = fgets( $fd, 4096 );
    		$str = array();
    		if (( strpos( $buffer, '$g_' ) > 0 )&&( strpos( $buffer, 'isset' ) == 0 )) {
				$i++;
				eregi( "\\\$g_([^[:space:]]*)", $buffer, $str1 );
				$buffer = eregi_replace( "([[:alnum:]\\\$_ \t]*)=", "", $buffer );
				$buffer = eregi_replace( "(;[[:alnum:] /#]*)", "", $buffer );
    			$status_color = alternate_colors( $i, $g_primary_color_dark, $g_primary_color_light );
				PRINT "<tr bgcolor=\"$status_color\">";
					PRINT "<td width=\"50%\">";
			    		echo $str1[0];
					PRINT "</td>";
					PRINT "<td width=\"50%\">";
			    		#echo $str2[0];
			    		echo htmlspecialchars( $buffer );
					PRINT "</td>";
				PRINT "</tr>";
    		}
    		if ( strpos( $buffer, '### ' ) > 0 ) {
				PRINT "<tr bgcolor=\"$g_category_title_color\">";
					PRINT "<td colspan=2 width=\"50%\">";
			    		echo $buffer;
					PRINT "</td>";
				PRINT "</tr>";
    		}
		}
		fclose( $fd );
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>

<? print_menu( $g_menu_include_file ) ?>

<? print_manage_doc_menu( $g_site_settings_page ) ?>

<p>
<div align="center">
<table width="100%" bgcolor="<? echo $g_primary_border_color ?>" <? echo $g_primary_table_tags ?>>
<tr>
	<td bgcolor="<? echo $g_white_color ?>">
	<table width="100%">
	<tr>
		<td colspan="2" bgcolor="<? echo $g_table_title_color ?>">
			<b><? echo $s_site_settings_title ?></b>
		</td>
	</tr>
	<? print_setting() ?>
	</table>
	</td>
</tr>
</table>
</div>

<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>