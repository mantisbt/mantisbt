<?php
/**
 * File containing the ezcGraphAxisRotatedLabelRenderer class
 *
 * @package Graph
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Can render axis labels rotated, so that more axis labels fit on one axis.
 * Produces best results if the axis space was increased, so that more spcae is
 * available below the axis.
 *
 * <code>
 *   $chart->xAxis->axisLabelRenderer = new ezcGraphAxisRotatedLabelRenderer();
 *
 *   // Define angle manually in degree
 *   $chart->xAxis->axisLabelRenderer->angle = 45;
 *
 *   // Increase axis space
 *   $chart->xAxis->axisSpace = .2;
 * </code>
 *
 * @property float $angle
 *           Angle of labels on axis in degrees.
 *
 * @version //autogentag//
 * @package Graph
 * @mainclass
 */
class ezcGraphAxisRotatedLabelRenderer extends ezcGraphAxisLabelRenderer
{
    /**
     * Store step array for later coordinate modifications
     * 
     * @var array(ezcGraphStep)
     */
    protected $steps;

    /**
     * Store direction for later coordinate modifications
     * 
     * @var ezcGraphVector
     */
    protected $direction;

    /**
     * Store coordinate width modifier for later coordinate modifications
     * 
     * @var float
     */
    protected $widthModifier;
    
    /**
     * Store coordinate offset for later coordinate modifications
     * 
     * @var float
     */
    protected $offset;
    
    /**
     * Constructor
     * 
     * @param array $options Default option array
     * @return void
     * @ignore
     */
    public function __construct( array $options = array() )
    {
        parent::__construct( $options );
        $this->properties['angle']  = null;
        $this->properties['labelOffset'] = true;
    }

    /**
     * __set 
     * 
     * @param mixed $propertyName 
     * @param mixed $propertyValue 
     * @throws ezcBaseValueException
     *          If a submitted parameter was out of range or type.
     * @throws ezcBasePropertyNotFoundException
     *          If a the value for the property options is not an instance of
     * @return void
     * @ignore
     */
    public function __set( $propertyName, $propertyValue )
    {
        switch ( $propertyName )
        {
            case 'angle':
                if ( !is_numeric( $propertyValue ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, '0 <= float < 360' );
                }

                $reducement = (int) ( $propertyValue - $propertyValue % 360 );
                $this->properties['angle'] = (float) $propertyValue - $reducement;
                break;

            case 'labelOffset':
                if ( !is_bool( $propertyValue ) )
                {
                    throw new ezcBaseValueException( $propertyName, $propertyValue, 'bool' );
                }

                $this->properties[$propertyName] = (bool) $propertyValue;
                break;

            default:
                return parent::__set( $propertyName, $propertyValue );
        }
    }

    /**
     * Render Axis labels
     *
     * Render labels for an axis.
     *
     * @param ezcGraphRenderer $renderer Renderer used to draw the chart
     * @param ezcGraphBoundings $boundings Boundings of the axis
     * @param ezcGraphCoordinate $start Axis starting point
     * @param ezcGraphCoordinate $end Axis ending point
     * @param ezcGraphChartElementAxis $axis Axis instance
     * @return void
     */
    public function renderLabels(
        ezcGraphRenderer $renderer,
        ezcGraphBoundings $boundings,
        ezcGraphCoordinate $start,
        ezcGraphCoordinate $end,
        ezcGraphChartElementAxis $axis,
        ezcGraphBoundings $innerBoundings = null )
    {
        // receive rendering parameters from axis
        $steps = $axis->getSteps();
        $this->steps = $steps;

        $axisBoundings = new ezcGraphBoundings(
            $start->x, $start->y,
            $end->x, $end->y
        );

        // Determine normalized axis direction
        $this->direction = new ezcGraphVector(
            $end->x - $start->x,
            $end->y - $start->y
        );
        $this->direction->unify();
        $axisAngle = -$this->direction->angle( new ezcGraphVector( 1, 0 ) );

        // Get axis space
        $gridBoundings = null;
        list( $xSpace, $ySpace ) = $this->getAxisSpace( $renderer, $boundings, $axis, $innerBoundings, $gridBoundings );

        // Determine optimal angle if none specified
        if ( $this->angle === null )
        {
            $minimumStepWidth = null;
            foreach ( $steps as $nr => $step )
            {
                if ( ( $minimumStepWidth === null ) || 
                     ( $step->width < $minimumStepWidth ) )
                {
                    $minimumStepWidth = $step->width;
                }
            }

            $width = abs(
                $axisBoundings->width * $minimumStepWidth * $this->direction->x +
                $axisBoundings->height * $minimumStepWidth * $this->direction->y
            );
            $height = abs(
                $ySpace * $this->direction->x +
                $xSpace * $this->direction->y
            );

            $length = sqrt( pow( $width, 2 ) + pow( $height, 2 ) );
            $this->angle = rad2deg( acos( $height / $length ) );
        }

        // Determine additional required axis space by boxes
        $firstStep = reset( $steps );
        $lastStep = end( $steps );

        $textAngle = $axisAngle + 
            deg2rad( $this->angle ) + 
            ( $axis->position & ( ezcGraph::TOP | ezcGraph::BOTTOM ) ? deg2rad( 270 ) : deg2rad( 90 ) );

        // Ensure angle between 0 and 360 degrees
        $degTextAngle = rad2deg( $textAngle );
        while ( $degTextAngle < 0 )
        {
            $degTextAngle += 360.;
        }

        if ( $this->properties['labelOffset'] )
        {
            $this->offset =
                ( $this->angle < 0 ? -1 : 1 ) *
                ( $axis->position & ( ezcGraph::TOP | ezcGraph::LEFT ) ? 1 : -1 ) *
                ( 1 - cos( deg2rad( $this->angle * 2 ) ) );
        }
        else
        {
            $this->offset = 0;
        }

        $axisSpaceFactor = abs(
            ( $this->direction->x == 0 ? 0 :
                $this->direction->x * $ySpace / $axisBoundings->width ) +
            ( $this->direction->y == 0 ? 0 :
                $this->direction->y * $xSpace / $axisBoundings->height )
        );

        $start = new ezcGraphCoordinate(
            $start->x + max( 0., $axisSpaceFactor * $this->offset ) * ( $end->x - $start->x ),
            $start->y + max( 0., $axisSpaceFactor * $this->offset ) * ( $end->y - $start->y )
        );
        $end = new ezcGraphCoordinate(
            $end->x + min( 0., $axisSpaceFactor * $this->offset ) * ( $end->x - $start->x ),
            $end->y + min( 0., $axisSpaceFactor * $this->offset ) * ( $end->y - $start->y )
        );

        $labelLength = sqrt(
            pow(
                $xSpace * $this->direction->y +
                $axisSpaceFactor * $this->offset * ( $end->x - $start->x ),
                2 ) +
            pow(
                $ySpace * $this->direction->x +
                $axisSpaceFactor * $this->offset * ( $end->y - $start->y ),
                2 )
        );

        $this->offset *= $axisSpaceFactor;

        // Draw steps and grid
        foreach ( $steps as $nr => $step )
        {
            $position = new ezcGraphCoordinate(
                $start->x + ( $end->x - $start->x ) * $step->position * abs( $this->direction->x ),
                $start->y + ( $end->y - $start->y ) * $step->position * abs( $this->direction->y )
            );
    
            $stepSize = new ezcGraphCoordinate(
                ( $end->x - $start->x ) * $step->width,
                ( $end->y - $start->y ) * $step->width
            );

            // Calculate label boundings
            switch ( true )
            {
                case ( $nr === 0 ):
                    $labelSize = min(
                        abs( 
                            $xSpace * 2 * $this->direction->y +
                            $ySpace * 2 * $this->direction->x ),
                        abs( 
                            $step->width * $axisBoundings->width * $this->direction->x +
                            $step->width * $axisBoundings->height * $this->direction->y )
                    );
                    break;
                case ( $step->isLast ):
                    $labelSize = min(
                        abs( 
                            $xSpace * 2 * $this->direction->y +
                            $ySpace * 2 * $this->direction->x ),
                        abs( 
                            $steps[$nr - 1]->width * $axisBoundings->width * $this->direction->x +
                            $steps[$nr - 1]->width * $axisBoundings->height * $this->direction->y )
                    );
                    break;
                default:
                    $labelSize = abs( 
                        $step->width * $axisBoundings->width * $this->direction->x +
                        $step->width * $axisBoundings->height * $this->direction->y
                    );
                    break;
            }

            $labelSize = $labelSize * cos( deg2rad( $this->angle ) );
            $lengthReducement = min(
                abs( tan( deg2rad( $this->angle ) ) * ( $labelSize / 2 ) ),
                abs( $labelLength / 2 )
            );

            switch ( true )
            {
                case ( ( ( $degTextAngle >= 0 ) && 
                         ( $degTextAngle < 90 ) &&
                         ( ( $axis->position === ezcGraph::LEFT ) ||
                           ( $axis->position === ezcGraph::RIGHT )
                         )
                       ) ||
                       ( ( $degTextAngle >= 270 ) && 
                         ( $degTextAngle < 360 ) &&
                         ( ( $axis->position === ezcGraph::TOP ) ||
                           ( $axis->position === ezcGraph::BOTTOM )
                         )
                       )
                     ):
                    $labelBoundings = new ezcGraphBoundings(
                        $position->x,
                        $position->y,
                        $position->x + abs( $labelLength ) - $lengthReducement,
                        $position->y + $labelSize
                    );
                    $labelAlignement = ezcGraph::LEFT | ezcGraph::TOP;
                    $labelRotation = $degTextAngle;
                    break;
                case ( ( ( $degTextAngle >= 90 ) && 
                         ( $degTextAngle < 180 ) &&
                         ( ( $axis->position === ezcGraph::LEFT ) ||
                           ( $axis->position === ezcGraph::RIGHT )
                         )
                       ) ||
                       ( ( $degTextAngle >= 180 ) && 
                         ( $degTextAngle < 270 ) &&
                         ( ( $axis->position === ezcGraph::TOP ) ||
                           ( $axis->position === ezcGraph::BOTTOM )
                         )
                       )
                     ):
                    $labelBoundings = new ezcGraphBoundings(
                        $position->x - abs( $labelLength ) + $lengthReducement,
                        $position->y,
                        $position->x,
                        $position->y + $labelSize
                    );
                    $labelAlignement = ezcGraph::RIGHT | ezcGraph::TOP;
                    $labelRotation = $degTextAngle - 180;
                    break;
                case ( ( ( $degTextAngle >= 180 ) && 
                         ( $degTextAngle < 270 ) &&
                         ( ( $axis->position === ezcGraph::LEFT ) ||
                           ( $axis->position === ezcGraph::RIGHT )
                         )
                       ) ||
                       ( ( $degTextAngle >= 90 ) && 
                         ( $degTextAngle < 180 ) &&
                         ( ( $axis->position === ezcGraph::TOP ) ||
                           ( $axis->position === ezcGraph::BOTTOM )
                         )
                       )
                     ):
                    $labelBoundings = new ezcGraphBoundings(
                        $position->x - abs( $labelLength ) + $lengthReducement,
                        $position->y - $labelSize,
                        $position->x,
                        $position->y
                    );
                    $labelAlignement = ezcGraph::RIGHT | ezcGraph::BOTTOM;
                    $labelRotation = $degTextAngle - 180;
                    break;
                case ( ( ( $degTextAngle >= 270 ) && 
                         ( $degTextAngle < 360 ) &&
                         ( ( $axis->position === ezcGraph::LEFT ) ||
                           ( $axis->position === ezcGraph::RIGHT )
                         )
                       ) ||
                       ( ( $degTextAngle >= 0 ) && 
                         ( $degTextAngle < 90 ) &&
                         ( ( $axis->position === ezcGraph::TOP ) ||
                           ( $axis->position === ezcGraph::BOTTOM )
                         )
                       )
                     ):
                    $labelBoundings = new ezcGraphBoundings(
                        $position->x,
                        $position->y + $labelSize,
                        $position->x + abs( $labelLength ) - $lengthReducement,
                        $position->y
                    );
                    $labelAlignement = ezcGraph::LEFT | ezcGraph::BOTTOM;
                    $labelRotation = $degTextAngle;
                    break;
            }

            $renderer->drawText(
                $labelBoundings,
                $step->label,
                $labelAlignement,
                new ezcGraphRotation(
                    $labelRotation,
                    $position
                )
            );

            // major grid
            if ( $axis->majorGrid )
            {
                $this->drawGrid( 
                    $renderer, 
                    $gridBoundings, 
                    $position,
                    $stepSize,
                    $axis->majorGrid
                );
            }
            
            // major step
            $this->drawStep( 
                $renderer, 
                $position,
                $this->direction, 
                $axis->position, 
                $this->majorStepSize, 
                $axis->border
            );
        }
    }
    
    /**
     * Modify chart data position
     *
     * Optionally additionally modify the coodinate of a data point
     * 
     * @param ezcGraphCoordinate $coordinate Data point coordinate
     * @return ezcGraphCoordinate Modified coordinate
     */
    public function modifyChartDataPosition( ezcGraphCoordinate $coordinate )
    {
        return new ezcGraphCoordinate(
            $coordinate->x * abs( $this->direction->y ) +
                ( $coordinate->x * ( 1 - abs( $this->offset ) ) + max( 0, $this->offset ) ) * abs( $this->direction->x ),
            $coordinate->y * abs( $this->direction->x ) +
                ( $coordinate->y * ( 1 - abs( $this->offset ) ) + max( 0, $this->offset ) ) * abs( $this->direction->y )
        );
    }
}
?>
