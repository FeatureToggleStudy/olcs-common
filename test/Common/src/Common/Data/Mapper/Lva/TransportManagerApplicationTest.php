<?php

/**
 * Transport Manager application test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Data\Mapper\Lva\TransportManagerApplication;

/**
 * Transport Manager application test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerApplicationTest extends MockeryTestCase
{
    public function testMapFromError()
    {
        $formMessages = [
            'data' => [
                'registeredUser' => [['error']]
            ]
        ];
        $globalMessages = [
            'global' => ['message']
        ];
        $messages = [
            'registeredUser' => ['error'],
            'global' => ['message']
        ];
        $mockForm = m::mock()
            ->shouldReceive('setMessages')
            ->with($formMessages)
            ->once()
            ->getMock();

        $errors = TransportManagerApplication::mapFromErrors($mockForm, $messages);
        $this->assertEquals($errors, $globalMessages);
    }

    public function testMapForSections()
    {
        $translationHelper = m::mock(TranslationHelperService::class);
        
        $data = TransportManagerApplication::mapForSections(["__TEST__"], $translationHelper);
        $this->assertInternalType('array', $data);
    }

    public function transportMnagerDataProvider()
    {
        
    }
}
