<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2005  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_group_action_api.php,v 1.1 2005-06-12 00:20:47 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	/**
	 * Print the top part for the bug action group page.
	 */
	function bug_group_action_print_top() {
		html_page_top1();
		html_page_top2();
	}

	/**
	 * Print the bottom part for the bug action group page.
	 */
	function bug_group_action_print_bottom() {
		html_page_bottom1( __FILE__ );
	}

	/**
	 * Print the list of selected issues and the legend for the status colors.
	 *
	 * @param $p_bug_ids_array   An array of issue ids.
	 */
	function bug_group_action_print_bug_list( $p_bug_ids_array ) {
		$t_legend_position = config_get( 'status_legend_position' );

		if ( STATUS_LEGEND_POSITION_TOP == $t_legend_position ) {
			html_status_legend();
			echo '<br />';
		}

		echo '<div align="center">';
		echo '<table class="width75" cellspacing="1">';
		echo '<tr class="row-1">';
		echo '<td class="category" colspan="2">';
		echo lang_get( 'actiongroup_bugs' );
		echo '</td>';
		echo '</tr>';

		$t_i = 1;

		foreach( $p_bug_ids_array as $t_bug_id ) {
			$t_class = sprintf( "row-%d", ($t_i++ % 2) + 1 );
			echo sprintf( "<tr bgcolor=\"%s\"> <td>%s</td> <td>%s</td> </tr>\n",
				get_status_color( bug_get_field( $t_bug_id, 'status' ) ),
				string_get_bug_view_link( $t_bug_id ),
				string_attribute( bug_get_field( $t_bug_id, 'summary' ) )
		    );
		}

		echo '</table>';
		echo '</form>';
		echo '</div>';

		if ( STATUS_LEGEND_POSITION_BOTTOM == $t_legend_position ) {
			echo '<br />';
			html_status_legend();
		}
	}

	/**
	 * Print the array of issue ids via hidden fields in the form to be passed on to
	 * the bug action group action page.
	 *
	 * @param $p_bug_ids_array   An array of issue ids.
	 */
	function bug_group_action_print_hidden_fields( $p_bug_ids_array ) {
		foreach( $p_bug_ids_array as $t_bug_id ) {
			echo '<input type="hidden" name="bug_arr[]" value="' . $t_bug_id . '" />' . "\n";
		}
	}
?>