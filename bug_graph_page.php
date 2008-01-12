<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: bug_graph_page.php,v 1.2.2.1 2007-10-13 22:32:42 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'class.period.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$f_interval = gpc_get_int( 'interval', 0 );
    $t_today = date( 'Y-m-d' );
	$f_type = gpc_get_int( 'graph_type', config_get( 'default_graph_type' ) );
	$f_show_as_table = gpc_get_bool( 'show_table', FALSE );

    html_page_top1( lang_get( 'graph_page' ) );
	$t_path = config_get( 'path' );
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $t_path . "javascript/jscalendar/calendar-blue.css\" /> \n";
	echo "<script type=\"text/javascript\" src=\"" . $t_path . "javascript/jscalendar/calendar.js\"></script> \n";
	echo "<script type=\"text/javascript\" src=\"" . $t_path . "javascript/jscalendar/lang/calendar-en.js\"></script> \n";
	echo "<script type=\"text/javascript\" src=\"" . $t_path . "javascript/jscalendar/calendar-setup.js\"></script> \n";
    html_page_top2();

    $t_period = new Period();
	$t_period->set_period_from_selector( 'interval' );
    $t_types = array(
        0 => lang_get( 'select' ),
        2 => lang_get( 'select_bystatus'),
        3 => lang_get( 'select_summbystatus'),
        4 => lang_get( 'select_bycat'),
        6 => lang_get( 'select_both')
    );
    
    $t_show = array(
        0 => lang_get( 'show_as_graph' ),
        1 => lang_get( 'show_as_table' ),
    );
    
?>
    <form name="graph_form" method="post" action="bug_graph_page.php">
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
    	    <input type="submit" name="show" value="<?php echo lang_get( 'show_graph' ); ?>"/>
    	</td>
    </tr>
    </table>
    </form>
<?php
    // build the graphs if both an interval and graph type are selected
    if ( ( 0 != $f_type ) && ( $f_interval > 0 ) && ( gpc_get( 'show', '' ) != '') ) { 
        $t_width = config_get( 'graph_window_width' );
        $t_summary = ( $f_type % 2 ) != 0;
        $t_body = (int)( $f_type / 2 );
        $f_start = $t_period->get_start_formatted();
        $f_end = $t_period->get_end_formatted();
        if ( ($t_body == 1 ) || ($t_body == 3) ) {
            if ( $f_show_as_table ) {
                echo '<br /><IFRAME SRC="bug_graph_bystatus.php?width='.$t_width.'&interval=' . $f_interval . 
                    '&start_date=' . $f_start . '&end_date=' . $f_end .
                    '&summary=' . $t_summary . '&show_table=1" width="100%" height="80%" frameborder="0"' .
                    ' marginwidth="0" marginheight="0"></IFRAME>';
            } else {
                echo '<br /><img src="bug_graph_bystatus.php?width=600&interval=' . $f_interval . 
                    '&start_date=' . $f_start . '&end_date=' . $f_end .
                    '&summary=' . $t_summary . '&show_table=0" alt="Bug Graph"';
            }
        }       
        if ( ($t_body == 2 ) || ($t_body == 3) ) {
            if ( $f_show_as_table ) {
                echo '<br /><IFRAME SRC="bug_graph_bycategory.php?width='.$t_width.'&interval=' . $f_interval . 
                    '&start_date=' . $f_start . '&end_date=' . $f_end .
                    '&summary=' . $t_summary . '&show_table=1" width="100%" height="80%" frameborder="0"' .
                    ' marginwidth="0" marginheight="0"></IFRAME>';
            } else {
                echo '<br /><img src="bug_graph_bycategory.php?width=600&interval=' . $f_interval . 
                    '&start_date=' . $f_start . '&end_date=' . $f_end .
                    '&summary=' . $t_summary . '&show_table=0" alt="Bug Graph"';
            }
        }
    }

    html_page_bottom1( __FILE__ );
?>