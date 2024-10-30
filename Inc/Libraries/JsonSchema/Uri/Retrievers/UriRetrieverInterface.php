<?php

/*
 * This file is part of the FacturareOnline\Inc\Libraries\JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FacturareOnline\Inc\Libraries\JsonSchema\Uri\Retrievers;

/**
 * Interface for URI retrievers
 *
 * @author Sander Coolen <sander@jibber.nl>
 */
interface UriRetrieverInterface
{
    /**
     * Retrieve a schema from the specified URI
     *
     * @param string $uri URI that resolves to a JSON schema
     *
     * @throws \FacturareOnline\Inc\Libraries\JsonSchema\Exception\ResourceNotFoundException
     *
     * @return mixed string|null
     */
    public function retrieve($uri);

    /**
     * Get media content type
     *
     * @return string
     */
    public function getContentType();
}
