Generating Documentation
========================

[phpDocumentor](http://www.phpdoc.org) is used for documentation. You can generate HTML locally with the following steps:

1. Install [phpDocumentor](http://www.phpdoc.org) using [PEAR](http://pear.php.net).

```
pear channel-discover pear.phpdoc.org
pear install phpdoc/phpDocumentor
```

2. Build the docs. (from the root directory)

```
make docs
```
