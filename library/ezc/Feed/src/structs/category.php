<?php
/**
 * File containing the ezcFeedCategoryElement class.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package Feed
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @filesource
 */

/**
 * Class defining a category.
 *
 * @property string $term
 *                  The readable value of the category.
 * @property string $scheme
 *                  The scheme (domain) value of the category.
 * @property string $label
 *                  The label value of the category.
 * @property ezcFeedCategoryElement $category
 *                                  A subcategory of the category.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedCategoryElement extends ezcFeedElement
{
    /**
     * The term (name) of the category.
     *
     * @var string
     */
    public $term;

    /**
     * The scheme (domain) for the category.
     *
     * @var string
     */
    public $scheme;

    /**
     * The label for the category.
     *
     * @var string
     */
    public $label;

    /**
     * Subcategory for the category.
     *
     * @var ezcFeedCategoryElement
     */
    public $category;

    /**
     * Adds a new element with name $name to the feed item and returns it.
     *
     * The subcategory is only used by the iTunes module (ezcFeedITunesModule).
     *
     * Example:
     * <code>
     * // $feed is an ezcFeed object
     * $category = $feed->add( 'category' );
     * $category->term = 'Technology';
     * $subCategory = $category->add( 'category' );
     * $subCategory->term = 'Gadgets';
     * </code>
     *
     * @param string $name The name of the element to add
     * @return ezcFeedCategoryElement
     */
    public function add( $name )
    {
        if ( $name === 'category' )
        {
            $this->category = new ezcFeedCategoryElement();
            return $this->category;
        }
        else
        {
            return parent::add( $name );
        }
    }
}
?>
