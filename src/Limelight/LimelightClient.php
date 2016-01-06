<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\Limelight;

use CdnPurge\Common\Client\CdnClient;

/**
 * The Limelight client
 *
 * Limelight client is build using following credential and configuration
 *
 * Array of credentials:
 *
 * - limelight['username']: (string)  Limelight account username. Required
 * - limelight['shared_key']: (string) Limelight account share key. Required
 *
 * Array of configurations:
 *
 * - limelight['shortname']: (string)  Limelight api shortname. Required
 * - limelight['publish_url']: (string)  Limelight publish url. Publish url is prepended to the paths provided in createPurgeRequest() api call if the path doesnt start with 'http or https'. Optional
 * - limelight['email']: (array) Array of email info to send purge completion details to. Optional
 * - limelight['email']['type']: (string) Email type: 'detail' or 'summary'
 * - limelight['email']['subject']: (string) Email type: 'detail' or 'summary'
 * - limelight['email']['to']: (string) Email recipient address. A comma is used to separate multiple recipients
 * - limelight['email']['cc']: (string) Email carbon copy. A comma is used to separate multiple recipients
 * - limelight['email']['bcc']: (string) Email blind carbon copy. A comma is used to separate multiple recipients
 * - limelight['callbacks']: (array) List of callbacks (simple HTTP POST to specific URL) that will be executed after purge is completed. Optional
 * - limelight['callbacks']['type']: (string) Callback type: 'entry' (callback after each path entry) or 'request' (single callback after the request completes)
 * - limelight['callbacks']['url']: (string) Callback url
 * - http['proxy']: (string) Http proxy for the client. For example: my-company.proxy.com:1234
 */
class LimelightClient extends CdnClient
{

    /**
     * Create a purge request for all the given paths
     *
     * @param array $paths  Array of all the paths to purge cache from
     *                      For example: array('http://test.com/foo/bar/file.txt', 'http://test.com/foo/bar/file2.txt')
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
     * Get the status for a given purge request
     *
     * @param string $requestId  RequestId for the purge request as received from createPurgeRequest()
     *
     * @return CdnPurgeStatus purge status
     * @throws CdnClientException if Limelight returns any error
     */
    public function getPurgeStatus($requestId)
    {
        $reqDispatcher = RequestDispatcher::connect($this->credential[ApiConstants::CONST_ROOT], $this->config[ApiConstants::CONST_ROOT]);
        return $reqDispatcher->getPurgeStatus($requestId);
    }

    /**
     * @param array $credential Limelight Credentials
     * @param array $config Configuration options
     *
     * @throws CdnClientException if either credential or config is invalid
     */
    public function __construct(array $credential, array $config)
    {
        parent::__construct($credential, $config);

        $this->validateInParams(ApiConstants::CONST_ROOT, $this->getRequiredCredential(), $this->getRequiredConfig());

        // check if the sharedKey is hex string or not
        if (!ctype_xdigit($credential[ApiConstants::CONST_ROOT][ApiConstants::CREDENTIAL_SHARED_KEY])) {
            throw new \CdnPurge\CdnClientException("Limelight SharedKey must be a hex string.");
        }
    }

    /**
     * Get all the configurations required for the client
     *
     * @return array Required configurations for Limelight
     */
    private function getRequiredConfig()
    {
        return array(
            ApiConstants::CONF_SHORTNAME
        );
    }

    /**
     * Get all the credentials required for the client
     *
     * @return array Required credentials for Limelight
     */
    private function getRequiredCredential()
    {
        return array(
            ApiConstants::CREDENTIAL_USERNAME,
            ApiConstants::CREDENTIAL_SHARED_KEY
        );
    }
}
