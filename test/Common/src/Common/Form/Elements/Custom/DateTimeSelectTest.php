<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\DateTimeSelect;

/**
 * DateTimeSelect Test
 */
class DateTimeSelectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DateTimeSelect
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new DateTimeSelect();
    }

    public function testSetValueNull()
    {
        $this->sut->setValue(null);
        $this->assertSame(null, $this->sut->getYearElement()->getValue());
        $this->assertSame(null, $this->sut->getMonthElement()->getValue());
        $this->assertSame(null, $this->sut->getDayElement()->getValue());
        $this->assertSame(null, $this->sut->getHourElement()->getValue());
        $this->assertSame(null, $this->sut->getMinuteElement()->getValue());
        $this->assertSame(null, $this->sut->getSecondElement()->getValue());
    }

    public function testSetValueString()
    {
        $this->sut->setValue('2016-06-14 14:33');
        $this->assertSame('2016', $this->sut->getYearElement()->getValue());
        $this->assertSame('06', $this->sut->getMonthElement()->getValue());
        $this->assertSame('14', $this->sut->getDayElement()->getValue());
        $this->assertSame('14', $this->sut->getHourElement()->getValue());
        $this->assertSame('33', $this->sut->getMinuteElement()->getValue());
        $this->assertSame('00', $this->sut->getSecondElement()->getValue());
    }

    public function testSetValueStringUtc()
    {
        $this->sut->setValue('2016-06-14 14:33+00:00');
        $this->assertSame('2016', $this->sut->getYearElement()->getValue());
        $this->assertSame('06', $this->sut->getMonthElement()->getValue());
        $this->assertSame('14', $this->sut->getDayElement()->getValue());
        $this->assertSame('15', $this->sut->getHourElement()->getValue());
        $this->assertSame('33', $this->sut->getMinuteElement()->getValue());
        $this->assertSame('00', $this->sut->getSecondElement()->getValue());
    }

    /**
     * @expectedException \Zend\Form\Exception\InvalidArgumentException
     */
    public function testSetValueStringInvalid()
    {
        $this->sut->setValue('foo');
        $this->assertSame(null, $this->sut->getYearElement()->getValue());
    }

    public function testSetValueDateTime()
    {
        $this->sut->setValue(new \DateTime('2016-06-14 14:33'));
        $this->assertSame('2016', $this->sut->getYearElement()->getValue());
        $this->assertSame('06', $this->sut->getMonthElement()->getValue());
        $this->assertSame('14', $this->sut->getDayElement()->getValue());
        $this->assertSame('14', $this->sut->getHourElement()->getValue());
        $this->assertSame('33', $this->sut->getMinuteElement()->getValue());
        $this->assertSame('00', $this->sut->getSecondElement()->getValue());
    }

    public function testSetValueDateTimeUtc()
    {
        $this->sut->setValue(new \DateTime('2016-06-14 14:33+00:00'));
        $this->assertSame('2016', $this->sut->getYearElement()->getValue());
        $this->assertSame('06', $this->sut->getMonthElement()->getValue());
        $this->assertSame('14', $this->sut->getDayElement()->getValue());
        $this->assertSame('15', $this->sut->getHourElement()->getValue());
        $this->assertSame('33', $this->sut->getMinuteElement()->getValue());
        $this->assertSame('00', $this->sut->getSecondElement()->getValue());
    }
}
