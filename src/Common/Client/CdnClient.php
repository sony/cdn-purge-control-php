<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\Common\Client;

/**
 * Abstract CDN client
 */
abstract class CdnClient implements ClientInterface
{
    /** @var array CDN client credentials */
    protected $credential;

    /** @var array CDN client configurations */
    protected $config;

    /**
     * Validate input parameters for CDN clients
     *
     * @param string $root               Constant root. For example, 'cloudfront', 'limelight'
     * @param array $requiredCredential  List of all the required credentials for the client
     * @param array $requiredConfig      List of all the required configurations for the client
     *
     * @throws \CdnPurge\CdnClientException  if client credential or config is invalid
     */
    protected function validateInParams($root, array $requiredCredential = array(), array $requiredConfig = array())
    {
        if (empty($this->credential) || empty($this->config)) {
            throw new \CdnPurge\CdnClientException("Invalid client credential or config. Cannot be empty.");
        }

        // check constant root
        if (empty($this->credential[$root]) || empty($this->config[$root])) {
            throw new \CdnPurge\CdnClientException("Invalid client credential or config. Root not found.");
        }

        // check credentials
        foreach ($requiredCredential as $key => $required) {
            if (!array_key_exists($required, $this->credential[$root])) {
                throw new \CdnPurge\CdnClientException("Not found required credential: $required");
            }
        }

        // check configurations
        foreach ($requiredConfig as $key => $required) {
            if (!array_key_exists($required, $this->config[$root])) {
                throw new \CdnPurge\CdnClientException("Not found required config: $required");
            }
        }
    }

    /**
     * @param array $credential CDN Credentials
     * @param array $config Configuration options
     */
    public function __construct(array $credential, array $config)
    {
        $this->credential = $credential;
        $this->config = $config;
    }

}
