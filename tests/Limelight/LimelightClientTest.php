<?php
namespace CdnPurge\Tests\Limelight;

use CdnPurge\Limelight\LimelightClient;

class LimelightClientTest extends \PHPUnit_Framework_TestCase
{

    private $user;
    private $sharedKey;
    private $shortname;
    private $publishUrl;
    private $publishUrlWithTrailingSlash;

    protected function setUp()
    {
        $this->user = getenv('LL_USERNAME');
        $this->sharedKey = getenv('LL_SHARED_KEY');
        $this->shortname = getenv('LL_API_SHORTNAME');
        $this->publishUrl = getenv('LL_PUBLISH_URL');
        $this->publishUrlWithTrailingSlash = rtrim($this->publishUrl) . '/';
    }

    private function getClient($user = NULL, $sharedKey = NULL, $publishUrl = NULL, $email = NULL, $callbacks = NULL)
    {
        $config = array(
            'limelight' => array(
                'shortname' => $this->shortname
            )
        );

        if ($publishUrl) {
            $config['limelight']['publish_url'] = $publishUrl;
        }

        if ($email) {
            $config['limelight']['email'] = $email;
        }
        if ($callbacks) {
            $config['limelight']['callbacks'] = $callbacks;
        }
        $credential = array(
            'limelight' => array(
                'username' => (empty($user) ? $this->user : $user),
                'shared_key' => (empty($sharedKey) ? $this->sharedKey : $sharedKey)
            )
        );

        return new LimelightClient($credential, $config);
    }

    public function testCanConstructLimelightClient()
    {
        $this->assertInstanceOf('CdnPurge\Limelight\LimelightClient', $this->getClient());
    }

    /**
    * @dataProvider configAndCredentialProvider
    */
    public function testCannotConstructLimelightClientWhenConfigOrCredentialIsInvalid($config, $credential, $expectedExceptionMessage)
    {
        $this->setExpectedException(
        'CdnPurge\CdnClientException', $expectedExceptionMessage
    );

    new LimelightClient($credential, $config);
}

public function configAndCredentialProvider()
{
    return array(
        'credential is empty' => array(array('foo' => 'bar'), array(), 'Invalid client credential or config. Cannot be empty.'),
        'config is empty' => array(array(), array('foo' => 'bar'), 'Invalid client credential or config. Cannot be empty.'),
        'credential has no root' => array(array('limelight' => array('foo' => 'bar')), array('foo' => 'bar'), 'Invalid client credential or config. Root not found.'),
        'config has no root' => array(array('foo' => 'bar'), array('limelight' => array('foo' => 'bar')), 'Invalid client credential or config. Root not found.'),
        'credential has empty root' => array(array('limelight' => array('foo' => 'bar')), array('limelight' => array()), 'Invalid client credential or config. Root not found.'),
        'config has empty root' => array(array('limelight' => array()), array('limelight' => array('foo' => 'bar')), 'Invalid client credential or config. Root not found.'),
        'credential doesnt contain username' => array(array('limelight' => array('foo' => 'bar')), array('limelight' => array('hoge' => 'fuga')), 'Not found required credential: username'),
        'credential doesnt contain sharedKey' => array(array('limelight' => array('foo' => 'bar')), array('limelight' => array('username' => 'dummyUser')), 'Not found required credential: shared_key'),
        'config doesnt contain shortname' => array(array('limelight' => array('foo' => 'bar')), array('limelight' => array('username' => 'dummyUser', 'shared_key' => 'abc123')), 'Not found required config: shortname'),
        'sharedKey is not hex string' => array(array('limelight' => array('shortname' => 'hoge')), array('limelight' => array('username' => 'dummyUser', 'shared_key' => 'dummyKey')), 'Limelight SharedKey must be a hex string.')
    );
}

/**
* @dataProvider apiDataProvider
*/
public function testCanCreatePurgeRequestAndGetStatus($user, $sharedKey, $publishUrl = NULL, $email = NULL, $callbacks = NULL)
{
    $client = $this->getClient($user, $sharedKey, $publishUrl, $email, $callbacks);

    $testPath = "/disttool-test.txt";
    if (empty($publishUrl)) {
        $testPath = $this->publishUrl . $testPath;
    }

    $requestId = $client->createPurgeRequest(array($testPath));
    $this->assertFalse(empty($requestId));

    $this->assertEquals('InProgress', $client->getPurgeStatus($requestId));
}

public function apiDataProvider()
{
    $email = array(
        'type' => 'detail',
        'subject' => 'Purge Summary',
        'to' => 'test@example.com',
        'cc' => 'test@example.com',
        'bcc' => 'test@example.com'
    );

    $emailWithoutType = array(
        'subject' => 'Purge Summary',
        'to' => 'test@example.com',
        'cc' => 'test@example.com',
        'bcc' => 'test@example.com'
    );

    $callbacks = array(
        array(
            'type' => 'request',
            'url' => 'http://test/callback.php'
        )
    );

    $callbacksWithoutType = array(
        array(
            'url' => 'http://test/callback.php'
        )
    );

    return array(
        'basic call' => array($this->user, $this->sharedKey, $this->publishUrl),
        'call with publish url having a trailing slash' => array($this->user, $this->sharedKey, $this->publishUrlWithTrailingSlash),
        'call without publish url' => array($this->user, $this->sharedKey),
        'call with email' => array($this->user, $this->sharedKey, $this->publishUrl, $email, array()),
        'call without email type' => array($this->user, $this->sharedKey, $this->publishUrl, $emailWithoutType),
        'call with cb' => array($this->user, $this->sharedKey, $this->publishUrl, array(), $callbacks),
        'call without cb type' => array($this->user, $this->sharedKey, $this->publishUrl, array(), $callbacksWithoutType),
        'call with email & cb' => array($this->user, $this->sharedKey, $this->publishUrl, $email, $callbacks)
    );
}

/**
* @expectedException CdnPurge\CdnClientException
*/
public function testCannotCreatePurgeRequestWithInvalidCredential()
{
    $this->getClient('dummyUser', 'abc123')->createPurgeRequest(array("$this->publishUrl/disttool-test.txt"));
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
