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
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'Period.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$f_interval = gpc_get_int( 'interval', 0 );
	$t_today = date( 'Y-m-d' );
	$f_type = gpc_get_int( 'graph_type', 0 );
	$f_show_as_table = gpc_get_bool( 'show_table', FALSE );

	html_page_top1( plugin_lang_get( 'graph_page' ) );
	$t_path = config_get( 'path' );
	// follows the convention in html_api::html_javascript_link
	if( config_get_global( 'minimal_jscss' ) ) {
		echo '<link rel="stylesheet" type="text/css" href="', helper_mantis_url( 'javascript/min/jscalendar/calendar-blue.css' ), '">' . "\n";
	} else {
		echo '<link rel="stylesheet" type="text/css" href="', helper_mantis_url( 'javascript/dev/jscalendar/calendar-blue.css' ), '">' . "\n";
	}

	html_javascript_link( 'jscalendar/calendar.js');
	html_javascript_link( 'jscalendar/lang/calendar-en.js');
	html_javascript_link( 'jscalendar/calendar-setup.js');
	html_page_top2();

	$t_period = new Period();
	$t_period->set_period_from_selector( 'interval' );
	$t_types = array(
		0 => plugin_lang_get( 'select' ),
		2 => plugin_lang_get( 'select_bystatus'),
		3 => plugin_lang_get( 'select_summbystatus'),
		4 => plugin_lang_get( 'select_bycat'),
		6 => plugin_lang_get( 'select_both')
	);

	$t_show = array(
		0 => plugin_lang_get( 'show_as_graph' ),
		1 => plugin_lang_get( 'show_as_table' ),
	);

?>
	<form name="graph_form" method="post" action="<?php echo plugin_page( 'bug_graph_page.php' ); ?>">
	<table class="width100" cellspacing="1">

	<tr>
		<td>
			<?php echo get_dropdown( $t_types, 'graph_type', $f_type ); ?>
		</td>
		<td>
			<?php echo $t_period->period_selector( 'interval' ); ?>
		</td>
		<td>
			<?php echo get_dropdown( $t_show, 'show_table', $f_show_as_table ? 1 : 0 ); ?>
		</td>
		<td>
			<input type="submit" class="button" name="show" value="<?php echo plugin_lang_get( 'show_graph' ); ?>"/>
		</td>
	</tr>
	</table>
	</form>
<?php
	// build the graphs if both an interval and graph type are selected
	if ( ( 0 != $f_type ) && ( $f_interval > 0 ) && ( gpc_get( 'show', '' ) != '') ) {
		$t_width = plugin_config_get( 'window_width' );
		$t_summary = ( $f_type % 2 ) != 0;
		$t_body = (int)( $f_type / 2 );
		$f_start = $t_period->get_start_formatted();
		$f_end = $t_period->get_end_formatted();
		if ( ($t_body == 1 ) || ($t_body == 3) ) {
			if ( $f_show_as_table ) {
				echo '<br />';
				include(  config_get_global('plugin_path' ). plugin_get_current() .  DIRECTORY_SEPARATOR .
					 'pages' .  DIRECTORY_SEPARATOR . 'bug_graph_bystatus.php' );
			} else {
				echo '<br /><img src="' . plugin_page( 'bug_graph_bystatus.php' ) . '&amp;width=600&amp;interval=' . $f_interval .
					'&amp;start_date=' . $f_start . '&amp;end_date=' . $f_end .
					'&amp;summary=' . $t_summary . '&amp;show_table=0" alt="Bug Graph" />';
			}
		}
		if ( ($t_body == 2 ) || ($t_body == 3) ) {
			if ( $f_show_as_table ) {
				echo '<br />';
				include(  config_get_global('plugin_path' ). plugin_get_current() .  DIRECTORY_SEPARATOR .
					 'pages' .  DIRECTORY_SEPARATOR . 'bug_graph_bycategory.php' );
			} else {
				echo '<br /><img src="' . plugin_page( 'bug_graph_bycategory.php' ) . '&amp;width=600&amp;interval=' . $f_interval .
					'&amp;start_date=' . $f_start . '&amp;end_date=' . $f_end .
					'&amp;summary=' . $t_summary . '&amp;show_table=0" alt="Bug Graph" />';
			}
		}
	}

	html_page_bottom();
