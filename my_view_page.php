<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.1 $
	# $Author: vboctor $
	# $Date: 2004-06-28 10:15:18 $
	#
	# $Id: my_view_page.php,v 1.1 2004-06-28 10:15:18 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path . 'compress_api.php' );
	require_once( $t_core_path . 'filter_api.php' );

	$t_current_user_id = auth_get_current_user_id();

	auth_ensure_user_authenticated();

	compress_enable();

	html_page_top1();

	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		html_meta_redirect( 'my_view_page.php', current_user_get_pref( 'refresh_delay' )*60 );
	}

	html_page_top2();

	$f_page_number		= gpc_get_int( 'page_number', 1 );

	$t_per_page = config_get( 'my_view_bug_count' );
	$t_bug_count = null;
	$t_page_count = null;

	$t_boxes = config_get( 'my_view_boxes' );
	asort ($t_boxes);
	reset ($t_boxes);
	#print_r ($t_boxes);
?>

<div align="center">
<table class="hide" border="0" cellspacing="3" cellpadding="0">

<?php
	if ( STATUS_LEGEND_POSITION_TOP == config_get( 'status_legend_position' ) ) {
		echo '<tr>';
		echo '<td colspan="2">';
		html_status_legend();
		echo '</td>';
		echo '</tr>';
	}
?>

<?php
	$t_number_of_boxes = count ( $t_boxes );
	$t_counter = 0;
	while (list ($t_box_title, $t_box_display) = each ($t_boxes)) {
		if ($t_box_display == 0) {
			$t_number_of_boxes = $t_number_of_boxes - 1;
		} else {
			$t_counter++;
			if ($t_counter%2 == 1) {
				echo '<tr><td valign="top" width="50%">';
				include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'my_view_inc_' . $t_box_title . '.php' );
				echo '</td>';
			} elseif (t_counter%2 == 0) {
				echo '<td valign="top" width="50%">';
				include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'my_view_inc_' . $t_box_title . '.php' );
				echo '</td></tr>';
			}
			if ( ($t_counter == $t_number_of_boxes) && $t_counter%2 == 1) {
				echo '<td valign="top" width="50%"></td></tr>';
			}
		}
	}
?>

<?php
	if ( STATUS_LEGEND_POSITION_BOTTOM == config_get( 'status_legend_position' ) ) {
		echo '<tr>';
		echo '<td colspan="2">';
		html_status_legend();
		echo '</td>';
		echo '</tr>';
	}
?>

</table>
</div>

<?php
	html_page_bottom1( __FILE__ );
?>

