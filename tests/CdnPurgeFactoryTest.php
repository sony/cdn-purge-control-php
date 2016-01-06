<?php
namespace CdnPurge\Tests;

use CdnPurge\CdnPurgeFactory;
use CdnPurge\CdnType;

class CdnPurgeFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $key;
    private $secret;
    private $distId;
    private $user;
    private $sharedKey;
    private $shortname;
    private $proxy;

    protected function setUp()
    {
        $this->key = getenv('AWS_ACCESS_KEY_ID');
        $this->secret = getenv('AWS_SECRET_ACCESS_KEY');
        $this->distId = getenv('AWS_CF_DIST_ID');
        $this->user = getenv('LL_USERNAME');
        $this->sharedKey = getenv('LL_SHARED_KEY');
        $this->shortname = getenv('LL_API_SHORTNAME');
    }

    public function testCanBuildCloudFrontClient()
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

        $client = CdnPurgeFactory::build(CdnType::CLOUDFRONT, $credential, $config);

        $this->assertInstanceOf('CdnPurge\CloudFront\CloudFrontClient', $client);
    }

    public function testCanBuildLimelightClient()
    {
        $config = array(
            'limelight' => array(
                'shortname' => $this->shortname
            )
        );
        $credential = array(
            'limelight' => array(
                'username' => (empty($user) ? $this->user : $user),
                'shared_key' => (empty($sharedKey) ? $this->sharedKey : $sharedKey)
            )
        );

        $client = CdnPurgeFactory::build(CdnType::LIMELIGHT, $credential, $config);

        $this->assertInstanceOf('CdnPurge\Limelight\LimelightClient', $client);
    }

    /**
    * @expectedException CdnPurge\CdnClientException
    * @expectedExceptionMessage Invalid CDN type: unknown
    */
    public function testCannotBuildForUnknownClient()
    {
        CdnPurgeFactory::build('unknown', array(), array());
    }

}
?>
