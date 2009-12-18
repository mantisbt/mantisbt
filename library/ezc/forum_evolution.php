<?php

require 'Base/src/base.php';
function __autoload( $className )
{
        ezcBase::autoload( $className );
}

// Create the graph
$graph = new ezcGraphLineChart();
$graph->palette = new ezcGraphPaletteEzBlue();
$graph->xAxis->majorGrid = '#888888';
$graph->yAxis->majorGrid = '#888888';

// Add the data and hilight norwegian data set
$graph->data['Posts'] = new ezcGraphArrayDataSet( array(
    'May 2006' => 1164,
    'Jun 2006' => 965,
    'Jul 2006' => 1014,
    'Aug 2006' => 1269,
    'Sep 2006' => 1269,
    'Oct 2006' => 771,
) );

$graph->data['per day'] = new ezcGraphArrayDataSet( array(
    'May 2006' => 38,
    'Jun 2006' => 32,
    'Jul 2006' => 33,
    'Aug 2006' => 41,
    'Sep 2006' => 34,
    'Oct 2006' => 25,
) );

// Set graph title
$graph->title = 'Forum posts in last months';

// Use 3d renderer, and beautify it
$graph->renderer = new ezcGraphRenderer3d();

$graph->renderer->options->barChartGleam = .5;
$graph->renderer->options->legendSymbolGleam = .5;

$graph->driver = new ezcGraphSvgDriver();

// Output the graph with std SVG driver
$graph->renderToOutput( 500, 200); //( 500, 200, 'forum_evolution.svg' );

?>
