<?php

/**
 * Generic Index Action Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Controller\Traits;

/**
 * Generic Index Action Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GenericIndexActionTest extends \PHPUnit\Framework\TestCase
{
    private $sut;

    protected function setUp()
    {
        $this->sut = $this->getMockForTrait(
            '\Common\Controller\Traits\GenericIndexAction',
            array(),
            '',
            true,
            true,
            true,
            array('renderSection')
        );
    }

    /**
     * @group controller_traits
     * @group generic_section_controller_traits
     */
    public function testIndexAction()
    {
        $this->sut->expects($this->once())
            ->method('renderSection');

        $this->sut->indexAction();
    }
}
