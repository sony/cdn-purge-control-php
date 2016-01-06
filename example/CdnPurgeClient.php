<?php
error_reporting(-1);
date_default_timezone_set('UTC');

// Require the Composer autoloader.
require 'vendor/autoload.php';

use CdnPurge\CdnPurgeFactory;
use CdnPurge\CdnType;

// Specify config & credentials
$config = array(
    'cloudfront' => array(
            'distribution_id' => 'your cloudfront distribution id'
        ),
    'limelight' => array(
            'shortname' => 'your limelight api shortname',
            'publish_url' => 'your limelight publish url'
        )
);
$credential = array(
    'cloudfront' => array(
            'key' => 'aws iam account access key id',
            'secret' => 'aws iam account secret access key'
        ),
    'limelight' => array(
            'username' => 'limelight account username',
            'shared_key' => 'limelight account shared key'
        )
);

try {
    // Make a purge request against AWS cloudfront
    echo "\nAWS CloudFront purge start ...\n";

    $cfClient = CdnPurgeFactory::build(CdnType::CLOUDFRONT, $credential, $config);
    $cfRequestId = $cfClient->createPurgeRequest(array(
        '/my-path-1',
        '/my-path-2'
    ));

    echo "AWS CloudFront purge request created: " . $cfRequestId . "\n";

    // check status
    echo "CloudFront purge status: " . $cfClient->getPurgeStatus($cfRequestId) . "\n";

    // Make a purge request against Limelight
    echo "\nLimelight purge start ...\n";

    $llClient = CdnPurgeFactory::build(CdnType::LIMELIGHT, $credential, $config);
    $llRequestId = $llClient->createPurgeRequest(array(
        'http://my-limelight-domain/my-path-1',
        '/my-path-2'
    ));

    echo "Limelight purge request created: " . $llRequestId . "\n";

    // check status
    echo "Limelight purge status: " . $llClient->getPurgeStatus($llRequestId) . "\n";

} catch (CdnPurge\CdnClientException $e) {
    echo "An error occurred: " . $e->getMessage();
}
