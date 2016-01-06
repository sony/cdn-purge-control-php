<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\CloudFront;

use CdnPurge\Common\Enum\BasicEnum;

/**
 * CloudFront specific constants
 */
abstract class ApiConstants extends BasicEnum
{
    /** constants root */
    const CONST_ROOT = 'cloudfront';

    /** Configuration related constants */
    const CONF_DISTRIBUTION_ID = 'distribution_id';
    const CONF_HTTP = 'http';
    const CONF_PROXY = 'proxy';

    /** Credential related constants */
    const CREDENTIAL_ACCESS_KEY_ID = 'key';
    const CREDENTIAL_SECRET_ACCESS_KEY = 'secret';

    /** Client related constants */
    const CF_REGION = 'us-east-1';
    const CF_VERSION = '2015-07-27';
}
