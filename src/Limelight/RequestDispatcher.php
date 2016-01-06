<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\Limelight;

/**
 * Purge request dispatcher for Limelight client
 */
class RequestDispatcher
{

    /**
     * Create a request dispatcher and connect it with client
     *
     * @param array $credential  Limelight Credentials
     * @param array $config Configuration options
     *
     * @return RequestDispatcher
     */
    public static function connect($credential, $config)
    {
        $shortname = $config[ApiConstants::CONF_SHORTNAME];

        $publishUrl = NULL;
        if (isset($config[ApiConstants::CONF_PUBLISH_URL])) {
            $publishUrl = $config[ApiConstants::CONF_PUBLISH_URL];
        }

        $proxy = NULL;
        if (isset($config[ApiConstants::CONF_HTTP]) && isset($config[ApiConstants::CONF_HTTP][ApiConstants::CONF_PROXY])) {
            $proxy = $config[ApiConstants::CONF_HTTP][ApiConstants::CONF_PROXY];
        }

        // configure email and callbacks if any
        $email = self::configureEmail($config);
        $callbacks = self::configureCallbacks($config);

        $username = $credential[ApiConstants::CREDENTIAL_USERNAME];
        $sharedKey = $credential[ApiConstants::CREDENTIAL_SHARED_KEY];

        return new self($username, $sharedKey, $shortname, $publishUrl, $email, $callbacks, $proxy);
    }

    /**
     * Create a purge request for all the given paths
     *
     * @param array $purgePaths  Array of all the paths to purge cache from
     *                      For example: array('foo/bar/file.txt', 'http://test.com/foo/bar/file2.txt')
     *                      Note that relative paths will be prepended with 'publish_url' if present in config
     *
     * @return string A purge request id is returned on success
     * @throws CdnClientException if CloudFront returns any error
     */
    public function createPurgeRequest(array $purgePaths)
    {
        try {
            $uri = $this->_host . ':' . $this->_port . $this->_endpoint . $this->_version . ApiConstants::LL_PATH_PURGE_REQUEST;

            $purgeEntries = $this->getAllPurgeEntries($purgePaths);
            $data = array(
                'entries' => $purgeEntries,
            );

            if (!empty($this->_email)) {
                $emailInfo = $this->_email;
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_TYPE])) {
                    $data['emailType'] = $emailInfo[ApiConstants::CONF_EMAIL_TYPE];
                }
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_SUBJECT])) {
                    $data['emailSubject'] = $emailInfo[ApiConstants::CONF_EMAIL_SUBJECT];
                }
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_TO])) {
                    $data['emailTo'] = $emailInfo[ApiConstants::CONF_EMAIL_TO];
                }
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_CC])) {
                    $data['emailCC'] = $emailInfo[ApiConstants::CONF_EMAIL_CC];
                }
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_BCC])) {
                    $data['emailBCC'] = $emailInfo[ApiConstants::CONF_EMAIL_BCC];
                }
            }

            if (!empty($this->_callbacks)) {
                $data['callbacks'] = $this->_callbacks;
            }

            $dataJson = json_encode($data);
            $dataJson = str_replace('\/', '/', $dataJson);

            $httpClient = new \GuzzleHttp\Client();
            $httpOpts = $this->getHttpClientOpts('POST', ApiConstants::LL_PATH_PURGE_REQUEST, '', $dataJson);
            $response = $httpClient->request('POST', $uri, $httpOpts);

            $responseArr = json_decode($response->getBody(), true);
            return $responseArr['id'];

        } catch (\Exception $e) {
            throw new \CdnPurge\CdnClientException($e->getMessage(), $e->getCode());
        }
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
        try {
            $uri = $this->_host . ':' . $this->_port . $this->_endpoint . $this->_version . ApiConstants::LL_PATH_PURGE_STATUS . '/' . $requestId;

            $httpClient = new \GuzzleHttp\Client();
            $httpOpts = $this->getHttpClientOpts('GET', ApiConstants::LL_PATH_PURGE_STATUS . '/' . $requestId, '', '');
            $response = $httpClient->request('GET', $uri, $httpOpts);

            $responseArr = json_decode($response->getBody(), true);

            if ($responseArr['completedEntries'] != $responseArr['totalEntries']) {
                return 'InProgress';
            }
            return 'Completed';

        } catch (\Exception $e) {
            throw new \CdnPurge\CdnClientException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param string $host       Limelight Api host
     * @param integer $port      Limelight Api port
     * @param string $endpoint   Limelight Api endpoint. For example, '/purge-api'
     * @param string $version    Limelight Api verison. For example, 'v1'
     * @param string $username   Limelight user
     * @param string $sharedKey  User shared key
     * @param string $shortname  Limelight Api shortname
     * @param string $publishUrl Limelight publish url
     * @param string $email      Array of email info to return purge completion details to
     * @param string $callbacks  List of callbacks (simple HTTP POST to specific URL) that will be executed after purge is completed
     * @param string $proxy      Proxy for http client
     */
    protected function __construct($username, $sharedKey, $shortname, $publishUrl, $email, $callbacks, $proxy)
    {
        $this->_host = ApiConstants::LL_HOST;
        $this->_port = ApiConstants::LL_PORT;
        $this->_endpoint = ApiConstants::LL_ENDPOINT;
        $this->_version = ApiConstants::LL_VERSION;
        $this->_username = $username;
        $this->_sharedKey = $sharedKey;
        $this->_shortname = $shortname;
        $this->_publishUrl = $publishUrl;
        $this->_email = $email;
        $this->_callbacks = $callbacks;
        $this->_proxy = $proxy;
    }

    /**
     * Configure email information to be used after purge is completed.
     * When configured, a report is sent to the recipient(s) provided
     *
     * @param array $config Limelight configurations containing email info
     *
     * @return array Email details
     */
    private static function configureEmail($config)
    {
        $email = NULL;
        if (!isset($config[ApiConstants::CONF_EMAIL]) || empty($config[ApiConstants::CONF_EMAIL])) {
            return $email;
        }
        $email = $config[ApiConstants::CONF_EMAIL];

        if (!isset($email[ApiConstants::CONF_EMAIL_TYPE])) {
            // default email type is detailed
            $email[ApiConstants::CONF_EMAIL_TYPE] = 'detail';
        }

        return $email;
    }

    /**
     * Configure callbacks to be executed after purge request is completed
     * Each callback consists of callback type and a url
     *
     * @param array $config Limelight configurations containing email info
     *
     * @return array Callbacks details
     */
    private static function configureCallbacks($config)
    {
        if (!isset($config[ApiConstants::CONF_CALLBACKS]) || !is_array($config[ApiConstants::CONF_CALLBACKS])) {
            // callbacks must be a list
            return NULL;
        }

        $callbackCandidate = array();
        foreach ($config[ApiConstants::CONF_CALLBACKS] as $key => $cb) {
            if (!isset($cb[ApiConstants::CONF_CALLBACK_URL])) {
                // url is required
                continue;
            }
            $callback = array(
                'callbackUrl' => $cb[ApiConstants::CONF_CALLBACK_URL]
            );

            if (!isset($cb[ApiConstants::CONF_CALLBACK_TYPE])) {
                // defualt is request
                $cb[ApiConstants::CONF_CALLBACK_TYPE] = 'request';
            }
            $callback['callbackType'] = $cb[ApiConstants::CONF_CALLBACK_TYPE];

            array_push($callbackCandidate, $callback);
        }

        return $callbackCandidate;
    }

    /**
     * Create all the purge entries for Limelight Api
     *
     * @param array $purgePaths Array of paths as obtained from user
     *
     * @return array All the purge entries
     */
    private function getAllPurgeEntries($purgePaths)
    {
        $purgeEntries = array();
        foreach ($purgePaths as $key => $relativePath) {
            $path = $this->getPurgePath($this->_publishUrl, $relativePath);
            if (!$path) {
                // empty path; just ignore it
                continue;
            }

            // push to entries
            array_push($purgeEntries, array(
                'url' => $path,
                'regex' => ApiConstants::LL_OPTION_ENABLE_REGEX,
                'shortname' => $this->_shortname,
                'delete' => ApiConstants::LL_OPTION_ENABLE_DELETE
            ));
        }

        return $purgeEntries;
    }

    /**
     * Create a purge path using publish url and relative path.
     *
     * A purge path is basically (publish url + relative path as received from user)
     * This method will not throw an error when publish url is not provided & will try to purge as it is.
     *
     * @param string $publishUrl Limelight's publish url
     * @param string $relativePath The purge path as received from user. This may be a full url, in which case publish url is ignored
     *
     * @return string The complete purge path
     */
    private function getPurgePath($publishUrl, $relativePath)
    {
        // if the path is empty, just ignore it
        if (empty($relativePath)) {
            return FALSE;
        }

        if (empty($publishUrl)) {
            // publish url was not provided.
            // use the relative path as it is
            return $relativePath;
        }

        // prepend publish url to relative path if relative path doesnt start with 'http' or 'https'
        if ($this->hasHttpScheme($relativePath)) {
            // relative path already is a complete url
            return $relativePath;
        }

        return rtrim($publishUrl, '/') . '/' . ltrim($relativePath, '/');
    }

    /**
     * Check if the given path has any http scheme or not
     *
     * @param string $path The given path to be tested
     *
     * @return boolean TRUE if path contains http/https scheme. FALSE otherwise
     */
    private function hasHttpScheme($path)
    {
        // search for http:// https:// HTTP:// HTTPS://
        if (preg_match("~^(?:ht)tps?://~i", $path)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Create request options for Limelight Api to be sent to Guzzle client
     *
     * @param string $method Http Method (GET | POST)
     * @param string $path   Api path. For example, '/request'
     * @param string $params Query parameters
     * @param string $data   Raw post data
     *
     * @return array Api request options
     */
    private function getHttpClientOpts($method, $path, $params, $data) {
        $headers = $this->getHeaders($method, $path, $params, $data);

        $httpOpts = [
            'headers' => $headers
        ];
        if ('POST' == $method && !empty($data)) {
            $httpOpts['body'] = $data;
        }
        if ($this->_proxy) {
            $httpOpts['proxy'] = $this->_proxy;
        }

        return $httpOpts;
    }

    /**
     * Create request headers for Limelight Api
     *
     * @param string $method Http Method (GET | POST)
     * @param string $path   Api path. For example, '/request'
     * @param string $params Query parameters
     * @param string $data   Raw post data
     *
     * @return array Api request headers
     */
    private function getHeaders($method, $path, $params, $data)
    {
        $timestamp = $this->getTimestamp();
        $mac = $this->getMac($method, $path, $params, $timestamp, $data);

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-LLNW-Security-Token' => $mac,
            'X-LLNW-Security-Principal' => $this->_username,
            'X-LLNW-Security-Timestamp' => $timestamp
        ];

        if ('POST' == $method && !empty($data)) {
            $headers['Content-Length'] = strlen($data);
        }

        return $headers;
    }

    /**
     * Get system time in milliseconds
     *
     * @return string System time as a string
     */
    private function getTimestamp()
    {
        return number_format(time()*1000,0,'.','');
    }

    /**
     * Generate MAC hash digest for the given data string
     *
     * @param string $sharedKey  User shared key
     * @param string $datastring A string containing, REQUEST_METHOD + URL + QUERY_STRING (if present) + TIMESTAMP + REQUEST_BODY (id present)
     *
     * @return string Hash digest
     */
    private function generateMac($sharedKey, $datastring)
    {
        $dataKey = pack('H*', $sharedKey);
        return hash_hmac('sha256', $datastring, "$dataKey");
    }

    /**
     * Prepare data to create MAC hash digest to be used as a request security token
     *
     * @param string $method     Http Method. (GET | POST)
     * @param string $path       Api path. For example, '/request'
     * @param string $timestamp  System time in milliseconds
     * @param string $data       Request body
     *
     * @return string MAC hash digest
     */
    private function getMac($method, $path, $params, $timestamp, $data = '')
    {
        $datastring = "$method$this->_host";
        // do not include port 80
        if ($this->_port != ApiConstants::LL_PORT) {
            $datastring = "$datastring:$this->_port";
        }
        $datastring = "$datastring$this->_endpoint$this->_version$path$params$timestamp$data";

        // generate hash
        $mac = $this->generateMac($this->_sharedKey, $datastring);
        return $mac;
    }
}
