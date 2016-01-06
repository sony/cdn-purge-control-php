<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\CloudFront;

/**
 * Purge request dispatcher for CloudFront client
 */
class RequestDispatcher
{

    /**
     * Create a request dispatcher and connect it with client
     *
     * @param array $credential  CloudFront Credentials
     * @param array $config Configuration options
     *
     * @return RequestDispatcher
     */
    public static function connect(array $credential, array $config)
    {
        $distId = $config[ApiConstants::CONF_DISTRIBUTION_ID];

        $proxy = NULL;
        if (isset($config[ApiConstants::CONF_HTTP]) && isset($config[ApiConstants::CONF_HTTP][ApiConstants::CONF_PROXY])) {
            $proxy = $config[ApiConstants::CONF_HTTP][ApiConstants::CONF_PROXY];
        }

        $key = $credential[ApiConstants::CREDENTIAL_ACCESS_KEY_ID];
        $secret = $credential[ApiConstants::CREDENTIAL_SECRET_ACCESS_KEY];

        return new self($key, $secret, $distId, $proxy);
    }

    /**
     * Create a purge request for all the given paths
     *
     * @param array $purgePaths  Array of all the paths to purge cache from
     *                           For example: array('/foo/bar/file.txt', 'foo/bar/file2.txt')
     *
     * @return string A purge request id is returned on success
     * @throws CdnClientException if CloudFront returns any error
     */
    public function createPurgeRequest($purgePaths)
    {
        try {
            $client = new \Aws\CloudFront\CloudFrontClient($this->getClientOptions());
            // create an invalidation request
            $result = $client->createInvalidation(array(
                'DistributionId' => $this->_distId,
                'InvalidationBatch' => array(
                    'CallerReference' => "invalidations_" . count($purgePaths) . "_" .time(),
                    'Paths' => array(
                        'Quantity' => count($purgePaths),
                        'Items' => $purgePaths,
                    ),
                ),
            ));

            // extract invalidation id
            return $result['Invalidation']['Id'];

        } catch (\Exception $e) {
            throw new \CdnPurge\CdnClientException($e->getMessage(), $e->getCode());
        }
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

        try {
            $client = new \Aws\CloudFront\CloudFrontClient($this->getClientOptions());
            // create an invalidation status request
            $result = $client->getInvalidation(array(
                'DistributionId' => $this->_distId,
                'Id' => $requestId,
            ));

            // extract invalidation status
            return $result['Invalidation']['Status'];

        } catch (\Exception $e) {
            throw new \CdnPurge\CdnClientException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param string $key    AWS IAM account Access Key Id
     * @param string $secret AWS IAM account Secret Access Key
     * @param string $distId CloudFront distribution id
     * @param string $proxy  Proxy for Http client
     */
    protected function __construct($key, $secret, $distId, $proxy)
    {
        $this->_key = $key;
        $this->_secret = $secret;
        $this->_distId = $distId;
        $this->_proxy = $proxy;
    }

    /**
     * Get CloudFront client options from config
     *
     * @return array CloudFront client options
     */
    private function getClientOptions()
    {
        // prepare client options
        $options = array(
            'version' => ApiConstants::CF_VERSION,
            'region' => ApiConstants::CF_REGION,
            'credentials' => array(
                'key' => $this->_key,
                'secret' => $this->_secret
            )
        );

        // set http proxy if configured
        if ($this->_proxy) {
            $options['http'] = array(
                'proxy' => $this->_proxy
            );
        }

        return $options;
    }
}
