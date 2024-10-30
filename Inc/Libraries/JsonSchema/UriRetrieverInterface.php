<?php

/*
 * This file is part of the FacturareOnline\Inc\Libraries\JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FacturareOnline\Inc\Libraries\JsonSchema;

/**
 * @package \FacturareOnline\Inc\Libraries\JsonSchema
 */
interface UriRetrieverInterface
{
    /**
     * Retrieve a URI
     *
     * @param string      $uri     JSON Schema URI
     * @param null|string $baseUri
     *
     * @return object JSON Schema contents
     */
    public function retrieve($uri, $baseUri = null);
}
