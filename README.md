CdnPurge, Multi CDN purge control library for PHP
=================================================

[![Build Status](https://travis-ci.org/sony/cdn-purge-control-php.svg)](https://travis-ci.org/sony/cdn-purge-control-php)
[![Stable Version](https://img.shields.io/packagist/v/sony/cdn-purge-control-php.svg)](https://packagist.org/packages/sony/cdn-purge-control-php)
[![MIT license](https://img.shields.io/packagist/l/sony/cdn-purge-control-php.svg)](https://github.com/sony/cdn-purge-control-php/blob/master/LICENSE)

**CdnPurge** is a lightweight PHP CDN client which makes it easier to purge contents for
multiple CDN providers. Currently, CdnPurge supports [AWS CloudFront](https://aws.amazon.com/cloudfront/)
and [Limelight](https://www.limelight.com/).

- Simple common interface to purge contents against multiple CDNs and get purge status.
- Easily extensible to other CDN providers.
- Easy code maintenance.
- Requires PHP >= 5.5 compiled with cURL extension and cURL 7.16.2+ compiled with a TLS backend (e.g. NSS or OpenSSL).
- Uses [Guzzle](https://github.com/guzzle/guzzle) to make http rest api calls.

## Installing CdnPurge

The recommended way to install CdnPurge is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of CdnPurge:

```bash
composer.phar require sony/cdn-purge-control-php
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update CdnPurge using composer:

 ```bash
composer.phar update
 ```

## Quick Examples

### Make purge request

```php
<?php
// Require the Composer autoloader.
require 'vendor/autoload.php';

use CdnPurge\CdnPurgeFactory;
use CdnPurge\CdnType;

$config = array(
    'cloudfront' => array(
            'distribution_id' => 'your cloudfront distribution id'
        ),
    'limelight' => array(
            'shortname' => 'your limelight api shortname'
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
    $cfClient = CdnPurgeFactory::build(CdnType::CLOUDFRONT, $credential, $config);
    $cfRequestId = $client->createPurgeRequest(array(
        '/my-path-1',
        '/my-path-2'
    ));

    // Make a purge request against Limelight
    $llClient = CdnPurgeFactory::build(CdnType::LIMELIGHT, $credential, $config);
    $llRequestId = $client->createPurgeRequest(array(
        'http://my-limelight-domain/my-path-1',
        '/my-path-2'
    ));

} catch (CdnPurge\CdnClientException $e) {
    echo "An error occurred: " . $e->getMessage();
}
```

### Get purge status

```php
<?php
// Get the purge status
try {
    $client->getPurgeStatus($requestId);
    // 'InProgress' or 'Complete'
} catch (CdnPurge\CdnClientException $e) {
    echo "There was an error getting purge status.\n";
}
```

See [example](https://github.com/sony/cdn-purge-control-php/tree/master/example) for a running example of how to use this library.

## Specifying credentials
Credentials are specified as an array.

### AWS CloudFront

| Credential key        | Type    | Required  | Description   |
| -------------         | ------  | --------  | ------------  |
| cloudfront['key']     | String  | Yes       | AWS IAM user Access Key Id. See [here](http://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSGettingStartedGuide/AWSCredentials.html) for details |
| cloudfront['secret']  | String  | Yes       | AWS IAM user Secret Access Key. See [here](http://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSGettingStartedGuide/AWSCredentials.html) for details |

### Limelight

| Credential key            | Type    | Required  | Description   |
| -------------             | ------  | --------  | ------------  |
| limelight['username']     | String  | Yes       | Limelight account username |
| limelight['shared_key']   | String  | Yes       | Limelight account share key |

## Specifying configurations
Configurations are specified as an array.

### AWS CloudFront

| Config key                    | Type    | Required  | Description   |
| -------------                 | ------  | --------  | ------------  |
| cloudfront['distribution_id'] | String  | Yes       | AWS CloudFront Distribution Id |
| http['proxy']                 | String  | No        | Specify http proxy for the client. For example: 'my-company.proxy.com:1234' |

### Limelight

| Config key                        | Type    | Required  | Description   |
| -------------                     | ------- | --------  | -----------   |
| limelight['shortname']            | String  | Yes       | Limelight api shortname |
| limelight['publish_url']          | String  | No        | Limelight publish url. This is prepended to the purge path if the path doesn't start with 'http' or 'https' |
| limelight['email']                | Array   | No        | Array of email info to send purge completion details to |
| limelight['email']['subject']     | String  | No        | Subject of sent mail |
| limelight['email']['to']          | String  | Yes       | Email recipient address. A comma is used to separate multiple recipients |
| limelight['email']['cc']          | String  | No        | Email carbon copy. A comma is used to separate multiple recipients |
| limelight['email']['bcc']         | String  | No        | Email blind carbon copy. A comma is used to separate multiple recipients |
| limelight['callback']            | Array   | No        | HTTP(S) callback URL for purge request state transition notifications |
| limelight['callback']['url']     | String  | Yes       | Callback url
| http['proxy']                     | String  | No        | Specify http proxy for the client. For example: 'my-company.proxy.com:1234' |

Development
-----------

- [Running tests](https://github.com/sony/cdn-purge-control-php/blob/master/tests/README.md)
- [Generating documentation](https://github.com/sony/cdn-purge-control-php/blob/master/docs/README.md)

License
-------

The MIT License (MIT)

See [LICENSE](https://github.com/sony/cdn-purge-control-php/blob/master/LICENSE) for details.
