<?php
/**
 * File containing the ezcFeedImageElement class.
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
 * Class defining an image element.
 *
 * @property string $link
 *                  The URL where the image is stored.
 * @property string $title
 *                  The title of the image.
 * @property string $url
 *                  The URL the image points at.
 * @property string $description
 *                  A description for the image.
 * @property int $width
 *               The width of the image in pixels.
 * @property int $height
 *               The height of the image in pixels.
 * @property string $about
 *                  An identifier for the image (usually the same value as $link).
 *                  Used only by RSS1.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedImageElement extends ezcFeedElement
{
    /**
     * The URL to the image.
     *
     * @var string
     */
    public $link;

    /**
     * The title for the image.
     *
     * @var string
     */
    public $title;

    /**
     * The URL the image points at.
     *
     * @var string
     */
    public $url;

    /**
     * A description for the image.
     *
     * @var string
     */
    public $description;

    /**
     * The width of the image in pixels.
     *
     * @var int
     */
    public $width;

    /**
     * The height of the image in pixels.
     *
     * @var int
     */
    public $height;

    /**
     * The identifier of the image.
     *
     * @var string
     */
    public $about;

    /**
     * Returns the link attribute.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->link . '';
    }
}
?>
