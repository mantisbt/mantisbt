<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_graph_page.php,v 1.1 2007-08-14 01:46:32 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );
	require_once( $t_core_path.'class.period.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$f_interval = gpc_get_int( 'interval', 0 );
    $t_today = date( 'Y-m-d' );
	$f_type = gpc_get_int( 'graph_type', 0 );
	$f_show_as_table = gpc_get_bool( 'show_table', FALSE );
	$f_summary = gpc_get_bool( 'summary', FALSE );

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
        1 => lang_get( 'select_bystatus'),
        2 => lang_get( 'select_summbystatus'),
        3 => lang_get( 'select_bycat')
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
    if ( ( 0 != $f_type ) && ( $f_interval > 0 ) && ( gpc_get( 'show', '' ) != '') ) {   // show selected info
        $t_width = config_get( 'graph_window_width' );
        $t_summary = ( $f_type % 2 ) == 0;
        $f_start = $t_period->get_start_formatted();
        $f_end = $t_period->get_end_formatted();
        switch ( $f_type ) {
            case 1:
            case 2:
                if ( $f_show_as_table ) {
                    echo '<IFRAME SRC="bug_graph_bystatus.php?width='.$t_width.'&interval=' . $f_interval . 
                        '&start_date=' . $f_start . '&end_date=' . $f_end .
                        '&summary=' . $t_summary . '&show_table=1" width="100%" height="80%" frameborder="0"' .
                        ' marginwidth="0" marginheight="0"></IFRAME>';
                } else {
                    echo '<img src="bug_graph_bystatus.php?width=600&interval=' . $f_interval . 
                        '&start_date=' . $f_start . '&end_date=' . $f_end .
                        '&summary=' . $t_summary . '&show_table=0" alt="Bug Graph"';
                }
                break;
            case 3:
                if ( $f_show_as_table ) {
                    echo '<IFRAME SRC="bug_graph_bycategory.php?width='.$t_width.'&interval=' . $f_interval . 
                        '&start_date=' . $f_start . '&end_date=' . $f_end .
                        '&summary=' . $t_summary . '&show_table=1" width="100%" height="80%" frameborder="0"' .
                        ' marginwidth="0" marginheight="0"></IFRAME>';
                } else {
                    echo '<img src="bug_graph_bycategory.php?width=600&interval=' . $f_interval . 
                        '&start_date=' . $f_start . '&end_date=' . $f_end .
                        '&summary=' . $t_summary . '&show_table=0" alt="Bug Graph"';
                }
                break;
        }
    }

    html_page_bottom1( __FILE__ );
?>