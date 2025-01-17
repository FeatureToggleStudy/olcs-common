<?php

/**
 * Internal / Common Variation People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Controller\Lva\Adapters\VariationPeopleAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * Internal / Common Variation People Adapter Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VariationPeopleAdapterTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut = new VariationPeopleAdapter();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testCanModify()
    {
        $this->assertTrue($this->sut->canModify(123));
    }
}
