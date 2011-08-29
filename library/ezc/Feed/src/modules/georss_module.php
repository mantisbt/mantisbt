<?php
/**
 * File containing the ezcFeedGeoRssModule class.
 *
 * @package Feed
 * @version //autogentag//
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Support for the GeoRss module: data container, generator, parser.
 *
 * Specifications: {@link http://www.w3.org/2003/01/geo/}.
 *
 * Create example:
 *
 * <code>
 * <?php
 * // $feed is an ezcFeed object
 * $item = $feed->add( 'item' );
 * $module = $item->addModule( 'GeoRss' );
 * $module->lat = 26.58;
 * $module->long = -97.83;
 * ?>
 * </code>
 *
 * Parse example:
 *
 * <code>
 * <?php
 * // $item is an ezcFeedEntryElement object
 * $lat = isset( $item->GeoRss->lat ) ? $item->GeoRss->lat->__toString() : null;
 * $long = isset( $item->GeoRss->long ) ? $item->GeoRss->long->__toString() : null;
 * ?>
 * </code>
 *
 * @property ezcFeedTextElement $lat
 *                              {@link http://en.wikipedia.org/wiki/WGS84 WGS84} latitude
 *                              on the globe as decimal degrees
 *                              (eg. 25.03358300). Can also be negative.
 * @property ezcFeedTextElement $long
 *                              {@link http://en.wikipedia.org/wiki/WGS84 WGS84} longitude
 *                              on the globe as decimal degrees
 *                              (eg. 121.56430000). Can also be negative.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedGeoRssModule extends ezcFeedModule
{
    /**
     * Constructs a new ezcFeedContentModule object.
     *
     * @param string $level The level of the data container ('feed' or 'item')
     */
    public function __construct( $level = 'feed' )
    {
        parent::__construct( $level );
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name is not defined
     *
     * @param string $name The property name
     * @param mixed $value The property value
     * @ignore
     */
    public function __set( $name, $value )
    {
        if ( $this->isElementAllowed( $name ) )
        {
            $node = $this->add( $name );
            $node->text = $value;
        }
        else
        {
            parent::__set( $name, $value );
        }
    }

    /**
     * Returns the value of property $name.
     *
     * @throws ezcBasePropertyNotFoundException
     *         if the property $name is not defined
     *
     * @param string $name The property name
     * @return mixed
     * @ignore
     */
    public function __get( $name )
    {
        if ( $this->isElementAllowed( $name ) )
        {
            return $this->properties[$name];
        }
        else
        {
            return parent::__get( $name );
        }
    }

    /**
     * Returns if the property $name is set.
     *
     * @param string $name The property name
     * @return bool
     * @ignore
     */
    public function __isset( $name )
    {
        if ( $this->isElementAllowed( $name ) )
        {
            return isset( $this->properties[$name] );
        }
        else
        {
            return parent::__isset( $name );
        }
    }

    /**
     * Returns true if the element $name is allowed in the current module at the
     * current level (feed or item), and false otherwise.
     *
     * @param string $name The element name to check if allowed in the current module and level (feed or item)
     * @return bool
     */
    public function isElementAllowed( $name )
    {
        switch ( $this->level )
        {
            case 'feed':
                if ( in_array( $name, array( 'lat', 'long', 'point', 'where', 'box' ) ) )
                {
                    return true;
                }
                break;

            case 'item':
                if ( in_array( $name, array( 'lat', 'long', 'point', 'where', 'box' ) ) )
                {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * Adds a new ezcFeedElement element with name $name to this module and
     * returns it.
     *
     * @throws ezcFeedUnsupportedElementException
     *         if trying to add an element which is not supported.
     *
     * @param string $name The element name
     * @return ezcFeedElement
     */
    public function add( $name )
    {
        if ( $this->isElementAllowed( $name ) )
        {
            switch ( $name )
            {
                case 'lat':
                case 'long':
                case 'point':
                case 'where':
                case 'box':
                    $node = new ezcFeedTextElement();
                    break;
            }

            $this->properties[$name] = $node;
            return $node;
        }
        else
        {
            throw new ezcFeedUnsupportedElementException( $name );
        }
    }

    /**
     * Adds the module elements to the $xml XML document, in the container $root.
     *
     * @param DOMDocument $xml The XML document in which to add the module elements
     * @param DOMNode $root The parent node which will contain the module elements
     */
    public function generate( DOMDocument $xml, DOMNode $root )
    {
        if ( isset( $this->lat ) && isset( $this->long ) )
        {
            $elementTag = $xml->createElement( $this->getNamespacePrefix() . ':' . 'point' );
            $root->appendChild( $elementTag );

            $elementTag->nodeValue = "{$this->lat} {$this->long}";
        }
    }

    /**
     * Parses the XML element $node and creates a feed element in the current
     * module with name $name.
     *
     * @param string $name The name of the element belonging to the module
     * @param DOMElement $node The XML child from which to take the values for $name
     */
    public function parse( $name, DOMElement $node )
    {
        if ( $this->isElementAllowed( $name ) )
        {
            switch ( $name )
            {
                case 'where':
                    $children = $node->childNodes;
                    $value = trim( $children->item(1)->textContent );
                    list( $lat, $long ) = explode( ' ', $value );

                    $element = $this->add( 'lat' );
                    $element->text = $lat;
                    $element = $this->add( 'long' );
                    $element->text = $long;
                    break;
                case 'point':
                    $value = $node->textContent;
                    list( $lat, $long ) = explode( ' ', $value );
                    $element = $this->add( 'lat' );
                    $element->text = $lat;
                    $element = $this->add( 'long' );
                    $element->text = $long;
                    break;
                case 'box':
                    $value = $node->textContent;
                    list( $lat1, $long1, $lat2, $long2 ) = explode( ' ', $value );
                    $element = $this->add( 'lat' );
                    $element->text = ( $lat1 + $lat2 ) / 2;
                    $element = $this->add( 'long' );
                    $element->text = ( $long1 + $long2 ) /2;
                    break;
            }
        }
    }

    /**
     * Returns the module name (GeoRss').
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'GeoRss';
    }

    /**
     * Returns the namespace for this module ('http://www.w3.org/2003/01/geo/wgs84_pos#').
     *
     * @return string
     */
    public static function getNamespace()
    {
        return 'http://www.georss.org/georss';
    }

    /**
     * Returns the namespace prefix for this module ('georss').
     *
     * @return string
     */
    public static function getNamespacePrefix()
    {
        return 'georss';
    }
}
?>
