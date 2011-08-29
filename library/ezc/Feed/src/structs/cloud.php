<?php
/**
 * File containing the ezcFeedCloudElement class.
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
 * Class defining a cloud element.
 *
 * @property string $domain
 *                  The domain of the cloud element.
 * @property string $port
 *                  The port of the cloud element.
 * @property string $path
 *                  The path of the cloud element.
 * @property string $registerProcedure
 *                  The registerProcedure of the cloud element.
 * @property string $protocol
 *                  The protocol of the cloud element.
 *
 * @package Feed
 * @version //autogentag//
 */
class ezcFeedCloudElement extends ezcFeedElement
{
    /**
     * The domain of the cloud.
     *
     * @var string
     */
    public $domain;

    /**
     * The port of the cloud.
     *
     * @var string
     */
    public $port;

    /**
     * The path in the cloud.
     *
     * @var string
     */
    public $path;

    /**
     * The procedure in the cloud.
     *
     * @var string
     */
    public $registerProcedure;

    /**
     * The protocol for the cloud.
     *
     * @var string
     */
    public $protocol;
}
?>
