<?php
/**
 * File containing the ezcFeedTextInputElement class.
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
 * Class defining a text input feed element.
 *
 * @property string $name
 *                  The name of the text input element.
 * @property string $link
 *                  The URL that the text input points at.
 * @property string $title
 *                  The title of the text input.
 * @property string $description
 *                  The description of the text input.
 * @property string $about
 *                  An identifier for the text input (usually the same value
 *                  as the link property). Used only by RSS1.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedTextInputElement extends ezcFeedElement
{
    /**
     * The name of the text input element.
     *
     * @var string
     */
    public $name;

    /**
     * The link that the text input points at.
     *
     * @var string
     */
    public $link;

    /**
     * The title of the text input.
     *
     * @var string
     */
    public $title;

    /**
     * The description for the text input.
     *
     * @var string
     */
    public $description;

    /**
     * The identifier for the text input.
     *
     * @var string
     */
    public $about;
}
?>
