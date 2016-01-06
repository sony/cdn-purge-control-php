<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge;

use CdnPurge\Common\Enum\BasicEnum;

/**
 * All the supported CDN types
 * CdnPurge currently supports CloudFront and Limelight
 */
abstract class CdnType extends BasicEnum
{
    const CLOUDFRONT = 'cloudFront';
    const LIMELIGHT = 'limelight';
}
