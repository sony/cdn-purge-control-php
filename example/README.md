Example
=======

This example runs purge request against both [AWS CloudFront](https://aws.amazon.com/cloudfront/)
and [Limelight](https://www.limelight.com/) and obtains their respective purge status.

Setup & Run
-----------

1. Install dependencies using [Composer](http://getcomposer.org).

```
composer install
```

2. Open [CdnPurgeClient.php](https://github.com/sony/cdn-purge-control-php/blob/master/example/CdnPurgeClient.php) file in the example directory and provide config & credential details.

3. Run the example.

```
php CdnPurgeClient.php
```
