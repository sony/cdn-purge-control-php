<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\Common\Client;

/**
 * Interface that CdnPurge clients implement
 */
interface ClientInterface
{
    /**
     * Create a purge request for all the given paths
     *
     * @param array $paths  Array of all the paths to purge cache from
     *
     * @return string A purge request id is returned on success
     */
    public function createPurgeRequest(array $paths);

    /**
     * Get the status for a given purge request
     *
     * @param string $requestId  RequestId for the purge request as received from stagePurgeRequest()
     *
     * @return CdnPurgeStatus purge status
     */
    public function getPurgeStatus($requestId);
}
