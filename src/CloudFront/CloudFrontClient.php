<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\CloudFront;

use CdnPurge\Common\Client\CdnClient;

/**
 * The CloudFront client
 * CloudFront client is build using following credential and configuration
 *
 * Array of credentials:
 *
 * - cloudfront['key']: (string)  AWS IAM user Access Key Id. Required
 * - cloudfront['secret']: (string) AWS IAM user Secret Access Key. Required
 *
 * Array of configurations:
 *
 * - cloudfront['distribution_id']: (string)  AWS CloudFront Distribution Id. Required
 * - http['proxy']: (string) Http proxy for the client. For example: my-company.proxy.com:1234
 */
class CloudFrontClient extends CdnClient
{

    /**
     * Create a purge request for all the given paths
     *
     * @param array $purgePaths  Array of all the paths to purge cache from
     *                           For example: array('/foo/bar/file.txt', 'foo/bar/file2.txt')
     *
     * @return string A purge request id is returned on success
     * @throws CdnClientException if CloudFront returns any error
     */
    public function createPurgeRequest(array $purgePaths)
    {
        $reqDispatcher = RequestDispatcher::connect($this->credential[ApiConstants::CONST_ROOT], $this->config[ApiConstants::CONST_ROOT]);
        return $reqDispatcher->createPurgeRequest($purgePaths);
    }

    /**
     * Get the status of a given purge request
     *
     * @param string $requestId  RequestId for the purge request as received from createPurgeRequest()
     *
     * @return CdnPurgeStatus purge status
     * @throws CdnClientException if CloudFront returns any error
     */
    public function getPurgeStatus($requestId)
    {
        $reqDispatcher = RequestDispatcher::connect($this->credential[ApiConstants::CONST_ROOT], $this->config[ApiConstants::CONST_ROOT]);
        return $reqDispatcher->getPurgeStatus($requestId);
    }

    /**
     * @param array $credential CloudFront Credentials
     * @param array $config Configuration options
     *
     * @throws CdnClientException if either credential or config is invalid
     */
    public function __construct(array $credential, array $config)
    {
        parent::__construct($credential, $config);

        $this->validateInParams(ApiConstants::CONST_ROOT, $this->getRequiredCredential(), $this->getRequiredConfig());
    }

    /**
     * Get all the configurations required for the client
     *
     * @return array Required configurations for CloudFront
     */
    private function getRequiredConfig()
    {
        return array(
            ApiConstants::CONF_DISTRIBUTION_ID
        );
    }

    /**
     * Get all the credentials required for the client
     *
     * @return array Required credentials for CloudFront
     */
    private function getRequiredCredential()
    {
        return array(
            ApiConstants::CREDENTIAL_ACCESS_KEY_ID,
            ApiConstants::CREDENTIAL_SECRET_ACCESS_KEY
        );
    }

}
