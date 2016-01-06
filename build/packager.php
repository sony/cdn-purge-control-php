<?php
date_default_timezone_set('UTC');

require __DIR__ . '/Burgomaster.php';
$stageDirectory = __DIR__ . '/artifacts/staging';
$projectRoot = __DIR__ . '/../';
$packager = new \Burgomaster($stageDirectory, $projectRoot);

// Copy basic files to the stage directory.
$metaFiles = ['README.md', 'LICENSE'];
foreach ($metaFiles as $file) {
    $packager->deepCopy($file, $file);
}

// Copy each dependency to the staging directory. Copy *.php and *.pem files.
$packager->recursiveCopy('src', 'CdnPurge', ['php']);
$packager->recursiveCopy('vendor/aws/aws-sdk-php/src', 'Aws');
$packager->recursiveCopy('vendor/mtdowling/jmespath.php/src', 'JmesPath');
$packager->recursiveCopy('vendor/guzzlehttp/guzzle/src', 'GuzzleHttp');
$packager->recursiveCopy('vendor/guzzlehttp/psr7/src', 'GuzzleHttp/Psr7');
$packager->recursiveCopy('vendor/guzzlehttp/promises/src', 'GuzzleHttp/Promise');
$packager->recursiveCopy('vendor/psr/http-message/src', 'Psr/Http/Message');

// create autoloader
$packager->createAutoloader([
    'Aws/functions.php',
    'GuzzleHttp/functions.php',
    'GuzzleHttp/Psr7/functions.php',
    'GuzzleHttp/Promise/functions.php',
    'JmesPath/JmesPath.php',
]);

$packager->createPhar($projectRoot . '/artifacts/cdn-purge-control.phar');
$packager->createZip($projectRoot . '/artifacts/cdn-purge-control.zip');
