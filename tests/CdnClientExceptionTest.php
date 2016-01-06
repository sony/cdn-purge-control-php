<?php
namespace CdnPurge\Tests;

use CdnPurge\CdnClientException;

class CdnClientExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testProvidesErrorInformation()
    {
        $e = new CdnClientException('Error occurred', -1);

        $this->assertFalse(empty((string)$e));
        $this->assertEquals('Error occurred', $e->getMessage());
        $this->assertEquals(-1, $e->getCode());
        $this->assertEquals(__DIR__ . '/CdnClientExceptionTest.php', $e->getFile());
        $this->assertEquals(11, $e->getLine());
        $this->assertFalse(empty($e->getTrace()));
        $this->assertFalse(empty($e->getTraceAsString()));
    }

    /**
    * @expectedException CdnPurge\CdnClientException
    * @expectedExceptionMessage Unknown CdnPurge\CdnClientException
    */
    public function testEnsuresMessageIsProvided()
    {
        new CdnClientException(NULL);
    }
}

?>
