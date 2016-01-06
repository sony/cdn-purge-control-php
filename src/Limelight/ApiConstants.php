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
    const CONF_EMAIL = 'email';
    const CONF_EMAIL_TYPE = 'type';
    const CONF_EMAIL_SUBJECT = 'subject';
    const CONF_EMAIL_TO = 'to';
    const CONF_EMAIL_CC = 'cc';
    const CONF_EMAIL_BCC = 'bcc';
    const CONF_CALLBACKS = 'callbacks';
    const CONF_CALLBACK_TYPE = 'type';
    const CONF_CALLBACK_URL = 'url';
    const CONF_HTTP = 'http';
    const CONF_PROXY = 'proxy';

    /** Credential related constants */
    const CREDENTIAL_USERNAME = 'username';
    const CREDENTIAL_SHARED_KEY = 'shared_key';

    /** Client related constants */
    const LL_HOST = 'http://control.llnw.com';
    const LL_PORT = '80';
    const LL_ENDPOINT = '/purge-api';
    const LL_VERSION = '/v1';
    const LL_PATH_PURGE_REQUEST = '/request';
    const LL_PATH_PURGE_STATUS = '/requestStatus';
    const LL_OPTION_ENABLE_REGEX = 'true';
    const LL_OPTION_ENABLE_DELETE = 'false';
}
