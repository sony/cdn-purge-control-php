<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\Limelight;

use CdnPurge\Common\Enum\BasicEnum;

/**
 * Limelight specific constants
 */
abstract class ApiConstants extends BasicEnum
{
    /** constants root */
    const CONST_ROOT = 'limelight';

    /** Configuration related constants */
    const CONF_SHORTNAME = 'shortname';
    const CONF_PUBLISH_URL = 'publish_url';
    const CONF_EVICT = 'evict';
    const CONF_EXACT = 'exact';
    const CONF_INCQS = 'incqs';
    const CONF_EMAIL = 'email';
    const CONF_EMAIL_SUBJECT = 'subject';
    const CONF_EMAIL_TO = 'to';
    const CONF_EMAIL_CC = 'cc';
    const CONF_EMAIL_BCC = 'bcc';
    const CONF_CALLBACK = 'callback';
    const CONF_CALLBACK_URL = 'url';
    const CONF_HTTP = 'http';
    const CONF_PROXY = 'proxy';

    /** Credential related constants */
    const CREDENTIAL_USERNAME = 'username';
    const CREDENTIAL_SHARED_KEY = 'shared_key';

    /** Client related constants */
    const LL_HOST = 'https://purge.llnw.com';
    const LL_ENDPOINT = '/purge';
    const LL_VERSION = '/v1';
    const LL_PATH_PURGE_REQUEST = '/requests';
}
