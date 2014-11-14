<?php
namespace CommonTest\Util;

use PHPUnit_Framework_TestCase;
use Common\Util\DateTimeProcessor as DateTimeProcessor;

/**
 * Test Api resolver
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class DateTimeProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dpCalculateDate
     *
     * @param \DateTime|string $inDate
     * @param integer $days
     * @param boolean $we
     * @param boolean $bh
     * @param \DateTime|string $outDate
     */
    public function testCalculateDate($inDate, $days, $we, $bh, $outDate)
    {
        $sut = new DateTimeProcessor();

        $this->assertEquals($outDate, $sut->calculateDate($inDate, $days, $we, $bh));
    }

    public function dpCalculateDate()
    {
        return [
            [
                '2014-11-03',
                '11',
                false, // weekends
                false,
                '2014-11-14'
            ],
            [ // weekeds skipped
                '2014-11-03',
                '9',
                true, // weekends
                false, // public holidays
                '2014-11-14'
            ],
            [ // no days skipped
                '2014-04-28',
                '17',
                false, // weekends
                false, // public holidays
                '2014-05-15'
            ],
            [ // weekends and public holidays skipped
                '2014-04-28',
                '13',
                true, // weekends
                false, // public holidays
                '2014-05-15'
            ],
            /* [ // no weekends but public holidays are skipped
                '2014-04-28',
                '13',
                false, // weekends
                true, // public holidays
                '2014-05-15'
            ] */
        ];
    }

    /**
     * @dataProvider dpProcessWorkingDays
     *
     * @param \DateTime $inDateTime
     * @param integer $workingDays
     * @param \DateTime $outDateTime
     */
    public function testProcessWorkingDays($inDateTime, $workingDays, $outDateTime)
    {
        $sut = new DateTimeProcessor();

        $this->assertEquals($outDateTime, $sut->processWorkingDays($inDateTime, $workingDays));
    }

    public function dpProcessWorkingDays()
    {
        return array(
            [
                \DateTime::createFromFormat('Y-m-d', '2014-10-01'),
                '14',
                \DateTime::createFromFormat('Y-m-d', '2014-10-21'),
            ],
            [
                \DateTime::createFromFormat('Y-m-d', '2010-01-01'),
                '1281',
                \DateTime::createFromFormat('Y-m-d', '2014-12-01'),
            ],
            [ // starts on saturday, ends on sunday + 1 day
                \DateTime::createFromFormat('Y-m-d', '2014-09-06'),
                '55',
                \DateTime::createFromFormat('Y-m-d', '2014-11-24'),
            ],
            [ // starts on sunday, ends on sunday + 1 day
                \DateTime::createFromFormat('Y-m-d', '2014-09-07'),
                '55',
                \DateTime::createFromFormat('Y-m-d', '2014-11-24'),
            ],
            [ // start date on sunday
                \DateTime::createFromFormat('Y-m-d', '2006-01-01'),
                '2609',
                \DateTime::createFromFormat('Y-m-d', '2016-01-01'),
            ]
        );
    }
}
