<?php
namespace CdnPurge\Tests\CloudFront;

use CdnPurge\CloudFront\CloudFrontClient;

class CloudFrontClientTest extends \PHPUnit_Framework_TestCase
{

    private $key;
    private $secret;
    private $distId;

    protected function setUp()
    {
        $this->key = getenv('AWS_ACCESS_KEY_ID');
        $this->secret = getenv('AWS_SECRET_ACCESS_KEY');
        $this->distId = getenv('AWS_CF_DIST_ID');
    }

    private function getClient($key = NULL, $secret = NULL)
    {
        $config = array(
            'cloudfront' => array(
                'distribution_id' => $this->distId
            )
        );
        $credential = array(
            'cloudfront' => array(
                'key' => (empty($key) ? $this->key : $key),
                'secret' => (empty($secret) ? $this->secret : $secret)
            )
        );

        return new CloudFrontClient($credential, $config);
    }

    public function testCanConstructCloudFrontClient()
    {
        $this->assertInstanceOf('CdnPurge\CloudFront\CloudFrontClient', $this->getClient());
    }

    /**
    * @dataProvider configAndCredentialProvider
    */
    public function testCannotConstructCloudFrontClientWhenConfigOrCredentialIsInvalid($config, $credential, $expectedExceptionMessage)
    {
        $this->setExpectedException(
        'CdnPurge\CdnClientException', $expectedExceptionMessage
    );

    new CloudFrontClient($credential, $config);
}

public function configAndCredentialProvider()
{
    return array(
        'credential is empty' => array(array('foo' => 'bar'), array(), 'Invalid client credential or config. Cannot be empty.'),
        'config is empty' => array(array(), array('foo' => 'bar'), 'Invalid client credential or config. Cannot be empty.'),
        'credential has no root' => array(array('cloudfront' => array('foo' => 'bar')), array('foo' => 'bar'), 'Invalid client credential or config. Root not found.'),
        'config has no root' => array(array('foo' => 'bar'), array('cloudfront' => array('foo' => 'bar')), 'Invalid client credential or config. Root not found.'),
        'credential has empty root' => array(array('cloudfront' => array('foo' => 'bar')), array('cloudfront' => array()), 'Invalid client credential or config. Root not found.'),
        'config has empty root' => array(array('cloudfront' => array()), array('cloudfront' => array('foo' => 'bar')), 'Invalid client credential or config. Root not found.'),
        'credential doesnt contain key' => array(array('cloudfront' => array('foo' => 'bar')), array('cloudfront' => array('hoge' => 'fuga')), 'Not found required credential: key'),
        'credential doesnt contain secret' => array(array('cloudfront' => array('foo' => 'bar')), array('cloudfront' => array('key' => 'dummyKey')), 'Not found required credential: secret'),
        'config doesnt contain distId' => array(array('cloudfront' => array('foo' => 'bar')), array('cloudfront' => array('key' => 'dummyKey', 'secret' => 'dummySecret')), 'Not found required config: distribution_id')
    );
}

public function testCanCreatePurgeRequestAndGetStatus()
{
    if (getenv('PHP_BUILT_WITH_GNUTLS') === 'true') {
        // skip this test since cURL will throw error when PHP is built with gnutls instead of openssl
        // error details: AWS HTTP error: cURL error 35: gnutls_handshake() failed: A TLS fatal alert has been received. (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)

        $this->markTestSkipped(
            'PHP was built with GnuTLS instead of OpenSSL.'
        );
        return;
    }

    $client = $this->getClient();

    $requestId = $client->createPurgeRequest(array('/1008/invalid-list/list'));
    $this->assertFalse(empty($requestId));

    $this->assertEquals('InProgress', $client->getPurgeStatus($requestId));
}

/**
* @expectedException CdnPurge\CdnClientException
*/
public function testCannotCreatePurgeRequestWithInvalidCredential()
{
    $this->getClient('dummyKey', 'dummySecret')->createPurgeRequest(array('/1008/invalid-list/list'));
}

/**
* @expectedException CdnPurge\CdnClientException
*/
public function testCannotGetStatusForInvalidRequestId()
{
    $this->getClient()->getPurgeStatus('invalid-id');
}

}

?>
