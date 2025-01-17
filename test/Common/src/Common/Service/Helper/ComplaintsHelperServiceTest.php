<?php

/**
 * Complaints Helper Service Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace CommonTest\Service\Helper;

use Common\Service\Helper\ComplaintsHelperService;

/**
 * Complaints Helper Service Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ComplaintsHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Setup the helper
     */
    public function setUp()
    {
        $this->helper = new ComplaintsHelperService();
    }

    /**
     * test sortCasesOpenClosed
     */
    public function testSortCasesOpenClosed()
    {
        $cases = [
            [
                'complaintDate' => 'complaintDate',
                'complainantContactDetails' => 'complainantContactDetails',
                'description' => 'description',
                'status' => ['id' => 'ecst_closed'],
            ],
            [
                'complaintDate' => 'complaintDate',
                'complainantContactDetails' => 'complainantContactDetails',
                'description' => 'description',
                'status' => ['id' => 'ecst_closed'],
            ],
            [
                'complaintDate' => 'complaintDate',
                'complainantContactDetails' => 'complainantContactDetails',
                'description' => 'description',
                'status' => ['id' => 'ecst_open'],
            ],
        ];
        $expected = [
            $cases[2],
            $cases[0],
            $cases[1],
        ];

        $result = $this->helper->sortCasesOpenClosed($cases);
        $this->assertEquals($expected, $result);
    }
}
