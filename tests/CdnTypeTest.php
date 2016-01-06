<?php
namespace CdnPurge\Tests;

use CdnPurge\CdnType;

class CdnTypeTest extends \PHPUnit_Framework_TestCase
{

    public function testHasNames()
    {
        $this->assertEquals(array('CLOUDFRONT', 'LIMELIGHT'), CdnType::names());
    }

    public function testHasValues()
    {
        $this->assertEquals(array(CdnType::CLOUDFRONT, CdnType::LIMELIGHT), CdnType::values());
    }

    /**
    * @dataProvider gettersProvider
    */
    public function testHasGetters($expected, $value)
    {
        $this->assertEquals($expected, CdnType::getName($value));
    }

    public function gettersProvider()
    {
        return array(
            'return name CLOUDFRONT' => array('CLOUDFRONT', CdnType::CLOUDFRONT),
            'return name LIMELIGHT' => array('LIMELIGHT', CdnType::LIMELIGHT),
            'return name FALSE' => array(FALSE, 'unknown')
        );
    }

    /**
    * @dataProvider validNameProvider
    */
    public function testValidNames($expected, $name, $strict = FALSE)
    {
        $this->assertEquals($expected, CdnType::isValidName($name, $strict));
    }

    public function validNameProvider()
    {
        return array(
            'valid name non-strict' => array(TRUE, 'cloudfront'),
            'valid name strict' => array(TRUE, 'CLOUDFRONT', TRUE),
            'invalid name' => array(FALSE, 'unknown')
        );
    }

    /**
    * @dataProvider validValueProvider
    */
    public function testValidValues($expected, $value)
    {
        $this->assertEquals($expected, CdnType::isValidValue($value));
    }

    public function validValueProvider()
    {
        return array(
            'valid value' => array(TRUE, 'limelight'),
            'invalid value' => array(FALSE, 'unknown')
        );
    }
}

?>
