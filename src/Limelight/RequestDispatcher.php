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
            $uriPath = '/account' . '/' . $this->_shortname . ApiConstants::LL_PATH_PURGE_REQUEST;
            $uri = $this->_host . $this->_endpoint . $this->_version . $uriPath;

            $purgeEntries = $this->getAllPurgeEntries($purgePaths);
            $data = array(
                'patterns' => $purgeEntries,
            );

            if (!empty($this->_email)) {
                $emailInfo = $this->_email;
                $emailReq = array();
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_SUBJECT])) {
                    $emailReq['subject'] = $emailInfo[ApiConstants::CONF_EMAIL_SUBJECT];
                }
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_TO])) {
                    $emailReq['to'] = $emailInfo[ApiConstants::CONF_EMAIL_TO];
                }
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_CC])) {
                    $emailReq['cc'] = $emailInfo[ApiConstants::CONF_EMAIL_CC];
                }
                if (isset($emailInfo[ApiConstants::CONF_EMAIL_BCC])) {
                    $emailReq['bcc'] = $emailInfo[ApiConstants::CONF_EMAIL_BCC];
                }
                $data['email'] = $emailReq;
            }

            if (!empty($this->_callback)) {
                $data['callback'] = $this->_callback;
            }

            $dataJson = json_encode($data);
            $dataJson = str_replace('\/', '/', $dataJson);

            $httpClient = new \GuzzleHttp\Client();
            $httpOpts = $this->getHttpClientOpts('POST', $uriPath, '', $dataJson);
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
            $uriPath = '/account' . '/' . $this->_shortname . ApiConstants::LL_PATH_PURGE_REQUEST . '/' . $requestId;
            $uri = $this->_host . $this->_endpoint . $this->_version . $uriPath;

            $httpClient = new \GuzzleHttp\Client();
            $httpOpts = $this->getHttpClientOpts('GET', $uriPath, '', '');
            $response = $httpClient->request('GET', $uri, $httpOpts);

            $responseArr = json_decode($response->getBody(), true);
            if (empty(array_search('complete', array_column($responseArr['states'], 'state')))) {
                // states has no 'completed' state; might still be in progress
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
     * @param string $callback   HTTP(S) callback URL for purge request state transition notifications
     * @param string $proxy      Proxy for http client
     */
    protected function __construct($username, $sharedKey, $shortname, $publishUrl, $email, $callback, $proxy)
    {
        $this->_host = ApiConstants::LL_HOST;
        $this->_endpoint = ApiConstants::LL_ENDPOINT;
        $this->_version = ApiConstants::LL_VERSION;
        $this->_username = $username;
        $this->_sharedKey = $sharedKey;
        $this->_shortname = $shortname;
        $this->_publishUrl = $publishUrl;
        $this->_email = $email;
        $this->_callback = $callback;
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
        if (!isset($config[ApiConstants::CONF_EMAIL]) || empty($config[ApiConstants::CONF_EMAIL])) {
            return NULL;
        }

        return $config[ApiConstants::CONF_EMAIL];
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
        if (!isset($config[ApiConstants::CONF_CALLBACK]) || empty($config[ApiConstants::CONF_CALLBACK])) {
            // callbacks must be a list
            return NULL;
        }

        return $config[ApiConstants::CONF_CALLBACK];
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
                'pattern' => $path,
                'evict' => false,
                'exact' => false,
                'incqs' => false
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
        return number_format(time() * 1000, 0, '.', '');
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
        $datastring = "$datastring$this->_endpoint$this->_version$path$params$timestamp$data";

        // generate hash
        $mac = $this->generateMac($this->_sharedKey, $datastring);
        return $mac;
    }
}
