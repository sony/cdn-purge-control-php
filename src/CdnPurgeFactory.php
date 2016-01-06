<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge;

/**
 * Factory class for CdnPurge
 * CdnPurge is library provides purge capabilities for CDN
 *
 * Supported CDNs are CloudFront and Limelight
 */
class CdnPurgeFactory
{

    /**
     * Builds a new CDN client using an array of configuration options
     *
     * @param CdnType $config CDN type. For example, 'cloudFront' or 'limelight'
     * @param array $credential CDN client credential data for authentication
     * @param array $config CDN client configuration data
     *
     * @return CdnPurge\Common\Client\ClientInterface
     * @throws CdnClientException if either of $cdnType or $credential or $config is invalid
     */
    public static function build($cdnType, array $credential = array(), array $config = array())
    {
        if (!CdnType::isValidName($cdnType)) {
            throw new CdnClientException("Invalid CDN type: " . $cdnType);
        }

        $clientName = ucwords($cdnType);
        $cdnClient = __NAMESPACE__ . "\\$clientName\\$clientName" . "Client";
        return new $cdnClient($credential, $config);
    }
}
