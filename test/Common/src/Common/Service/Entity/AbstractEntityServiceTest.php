<?php

/**
 * Abstract Entity Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CommonTest\Service\Entity;

/**
 * Abstract Entity Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractEntityServiceTest extends AbstractEntityServiceTestCase
{
    protected function setUp()
    {
        $this->sut = $this->getMockForAbstractClass('\Common\Service\Entity\AbstractEntityService');

        parent::setUp();
    }

    /**
     * @group entity_services
     *
     * @expectedException \Common\Exception\ConfigurationException
     */
    public function testSaveWithoutDefiningEntity()
    {
        $data = array();

        $this->sut->save($data);
    }

    /**
     * @group entity_services
     *
     * @expectedException \Common\Exception\ConfigurationException
     */
    public function testDeleteWithoutDefiningEntity()
    {
        $id = 1;

        $this->sut->delete($id);
    }

    /**
     * @group entity_services
     */
    public function testDelete()
    {
        $id = 1;

        $this->setEntity('Foo');

        $this->expectOneRestCall('Foo', 'DELETE', array('id' => $id));

        $this->sut->delete($id);
    }

    /**
     * @group entity_services
     */
    public function testUpdate()
    {
        $id = 1;
        $data = array(
            'foo' => 'bar'
        );

        $this->setEntity('Foo');

        $this->expectOneRestCall('Foo', 'PUT', array('id' => $id, 'foo' => 'bar'));

        $this->sut->update($id, $data);
    }

    /**
     * @group entity_services
     */
    public function testForceUpdate()
    {
        $id = 1;
        $data = array(
            'foo' => 'bar'
        );

        $this->setEntity('Foo');

        $this->expectOneRestCall('Foo', 'PUT', ['id' => $id, 'foo' => 'bar', '_OPTIONS_' => ['force' => true]]);

        $this->sut->forceUpdate($id, $data);
    }

    /**
     * @group entity_services
     */
    public function testMultiUpdate()
    {
        $data = array(
            array(
                'foo' => 'bar'
            ),
            array(
                'bar' => 'cake'
            )
        );

        $this->setEntity('Foo');

        $this->expectOneRestCall(
            'Foo',
            'PUT',
            [['foo' => 'bar'], ['bar' => 'cake'], '_OPTIONS_' => ['multiple' => true]]
        );

        $this->sut->multiUpdate($data);
    }

    /**
     * @group entity_services
     */
    public function testDeleteListByIds()
    {
        $ids = ['id' => [1,2]];

        $this->setEntity('Foo');

        $this->expectedRestCallInOrder('Foo', 'DELETE', array('id' => 1));
        $this->expectedRestCallInOrder('Foo', 'DELETE', array('id' => 2));

        $this->sut->deleteListByIds($ids);
    }

    /**
     * @group entity_services
     */
    public function testDeleteListByIdsNoIdIndex()
    {
        $ids = ['foo' => '1'];

        $this->setEntity('Foo');

        $this->expectOneRestCall('Foo', 'DELETE', array('foo' => 1));

        $this->sut->deleteListByIds($ids);
    }

    /**
     * @group entity_services
     *
     * @expectedException \Common\Exception\ConfigurationException
     */
    public function testDeleteListByIdsWithoutDefiningEntity()
    {
        $id = 1;

        $this->sut->deleteListByIds(['id' => $id]);
    }
}
