<?php

namespace CommonTest\Service\Table;

use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use CommonTest\Bootstrap;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Service\Table\TableBuilder
 */
class TableBuilderTest extends MockeryTestCase
{
    const TRANSLATED = '_TRSLTD_';

    /**
     * @todo the Date formatter now appears to rely on global constants defined
     * in the Common\Module::modulesLoaded method which can cause this test to
     * fail :(
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        if (!defined('DATE_FORMAT')) {
            define('DATE_FORMAT', 'd/m/Y');
        }
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Get Mock Table Builder
     *
     * @return \Common\Service\Table\TableBuilder | \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockTableBuilder($methods = array())
    {
        return $this->getMockBuilder(TableBuilder::class)
            ->setMethods($methods)
            ->setConstructorArgs([$this->getMockServiceLocator()])
            ->getMock();
    }

    private function getMockServiceLocator($config = true)
    {
        $mockTranslator = $this->createPartialMock(\Zend\Mvc\I18n\Translator::class, ['translate']);
        $mockTranslator->expects(static::any())
            ->method('translate')
            ->willReturnCallback(
                function ($desc) {
                    if (!is_string($desc)) {
                        return $desc;
                    }

                    return self::TRANSLATED . $desc;
                }
            );

        $mockSm = $this->createPartialMock('\Zend\ServiceManager\ServiceManager', array('get'));
        $mockControllerPluginManager = $this->createPartialMock('\Zend\Mvc\Controller\PluginManager', array('get'));
        $mockAuthService = $this->createPartialMock('\stdClass', array('isGranted'));

        $servicesMap = [
            ['Config', true, ($config
                ? array(
                    'tables' => array(
                        'config' => array(__DIR__ . '/TestResources/'),
                        'partials' => array(
                            'html' => ''
                        )
                    ),
                    'csrf' => [
                        'timeout' => 9999,
                    ],
                )
                : array())
            ],
            ['translator', true, $mockTranslator],
            ['ControllerPluginManager', true, $mockControllerPluginManager],
            ['ZfcRbac\Service\AuthorizationService', true, $mockAuthService],
        ];

        $mockSm
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($servicesMap));

        return $mockSm;
    }

    /**
     * Test getContentHelper
     */
    public function testGetContentHelper()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $contentHelper = $table->getContentHelper();

        $this->assertTrue($contentHelper instanceof \Common\Service\Table\ContentHelper);

        $contentHelper2 = $table->getContentHelper();

        $this->assertTrue($contentHelper === $contentHelper2);
    }

    /**
     * Test getContentHelper without configured partials
     *
     * @expectedException \Exception
     */
    public function testGetContentHelperWithoutConfig()
    {
        $table = new TableBuilder($this->getMockServiceLocator(false));

        $table->getContentHelper();
    }
    /**
     * Test getContentHelper without configured partials for current content type
     *
     * @expectedException \Exception
     */
    public function testGetContentHelperWithoutConfigForType()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setContentType('csv');

        $table->getContentHelper();
    }

    /**
     * Test getPaginationHelper
     */
    public function testGetPaginationHelper()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $paginationHelper = $table->getPaginationHelper();

        $this->assertTrue($paginationHelper instanceof \Common\Service\Table\PaginationHelper);

        $paginationHelper2 = $table->getPaginationHelper();

        $this->assertTrue($paginationHelper === $paginationHelper2);
    }

    /**
     * Test getConfigFromFile
     */
    public function testGetConfigFromFile()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $config = $table->getConfigFromFile('sample');

        $this->assertEquals(array('foo' => 'bar'), $config);
    }

    /**
     * Test getConfigFromFile with missing file
     *
     * @expectedException \Exception
     */
    public function testGetConfigFromFileWithMissingFile()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $table->getConfigFromFile('DoesntExist');
    }

    /**
     * Test build table calls all of the relevant methods
     */
    public function testBuildTable()
    {
        $table = $this->getMockTableBuilder(
            [
                'loadConfig',
                'loadData',
                'loadParams',
                'setupAction',
                'render',
            ]
        );

        $table->expects($this->at(0))
            ->method('loadConfig');

        $table->expects($this->at(1))
            ->method('loadData');

        $table->expects($this->at(2))
            ->method('loadParams');

        $table->expects($this->at(3))
            ->method('setupAction');

        $table->expects($this->at(4))
            ->method('render')
            ->will($this->returnValue('SomeHTML'));

        $this->assertEquals('SomeHTML', $table->buildTable('test'));
    }

    /**
     * Test build table without render
     */
    public function testBuildTableWithoutRender()
    {
        $table = $this->getMockTableBuilder(
            [
                'loadConfig',
                'loadData',
                'loadParams',
                'setupAction',
            ]
        );

        $table->expects($this->at(0))
            ->method('loadConfig');

        $table->expects($this->at(1))
            ->method('loadData');

        $table->expects($this->at(2))
            ->method('loadParams');

        $table->expects($this->at(3))
            ->method('setupAction');

        $this->assertEquals($table, $table->buildTable('test', array(), array(), false));
    }

    /**
     * Test loadConfig without table config set
     *
     * @expectedException \Exception
     */
    public function testLoadConfigWithoutTableConfig()
    {
        $table = new TableBuilder($this->getMockServiceLocator(false));

        $table->loadConfig('test');
    }

    /**
     * Test loadConfig with empty array
     */
    public function testLoadConfigWithEmptyArray()
    {
        $tableConfig = array(
        );

        $table = $this->getMockTableBuilder(array('getConfigFromFile'));

        $table->expects($this->once())
            ->method('getConfigFromFile')
            ->will($this->returnValue($tableConfig));

        $this->assertTrue($table->loadConfig('test'));

        $this->assertEquals(null, $table->getSetting('paginate'));

        $this->assertEquals('default', $table->getSetting('paginate', 'default'));

        $this->assertEquals(array(), $table->getAttributes());
        $this->assertEquals(array(), $table->getColumns());
        $this->assertEquals(array('hidden' => 'default'), $table->getVariables());
    }

    /**
     * Test loadConfig With pagination settings With limit
     */
    public function testLoadConfigWithPaginationWithLimit()
    {
        $paginate = array(
            'limit' => array(
                'default' => 20,
                'options' => array(
                    5, 10, 20
                )
            )
        );

        $tableConfig = array(
            'settings' => array(
                'paginate' => $paginate
            )
        );

        $table = $this->getMockTableBuilder(array('getConfigFromFile'));

        $table->expects($this->once())
            ->method('getConfigFromFile')
            ->will($this->returnValue($tableConfig));

        $this->assertTrue($table->loadConfig('test'));

        $this->assertEquals($paginate, $table->getSetting('paginate'));
    }

    /**
     * Test loadConfig With pagination settings Without limit
     */
    public function testLoadConfigWithPaginationWithoutLimit()
    {
        $paginate = array(
            'limit' => array(
                'default' => 10,
                'options' => array(
                    10, 25, 50
                )
            )
        );

        $tableConfig = array(
            'settings' => array(
                'paginate' => array()
            )
        );

        $table = $this->getMockTableBuilder(array('getConfigFromFile'));

        $table->expects($this->once())
            ->method('getConfigFromFile')
            ->will($this->returnValue($tableConfig));

        $this->assertTrue($table->loadConfig('test'));

        $this->assertEquals($paginate, $table->getSetting('paginate'));
    }

    /**
     * Test loadConfig With action field name
     */
    public function testLoadConfigWithActionFieldNameAndFormName()
    {
        $paginate = array(
            'limit' => array(
                'default' => 10,
                'options' => array(
                    10, 25, 50
                )
            )
        );

        $tableConfig = array(
            'settings' => array(
                'paginate' => array(),
                'crud' => array(
                    'formName' => 'bob',
                    'action_field_name' => 'blah'
                )
            )
        );

        $table = $this->getMockTableBuilder(array('getConfigFromFile'));

        $table->expects($this->once())
            ->method('getConfigFromFile')
            ->will($this->returnValue($tableConfig));

        $this->assertTrue($table->loadConfig('test'));

        $this->assertEquals($paginate, $table->getSetting('paginate'));
    }

    /**
     * Test loadData without data
     */
    public function testLoadDataWithoutData()
    {
        $data = array();

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->loadData($data);

        $this->assertEquals(array(), $table->getRows());
        $this->assertFalse($table->hasRows());

        $this->assertEquals(0, $table->getTotal());
    }

    /**
     * Test loadData with rows of data
     */
    public function testLoadDataWithDataRows()
    {
        $data = array(
            array('foo' => 'bar'),
            array('foo' => 'bar')
        );

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->loadData($data);

        $this->assertEquals($data, $table->getRows());
        $this->assertTrue($table->hasRows());

        $this->assertEquals(2, $table->getTotal());
    }

    /**
     * Test loadData with 1 row of data
     */
    public function testLoadDataWithOneRow()
    {
        $data = array(
            array('foo' => 'bar'),
        );

        $sl = $this->getMockServiceLocator();

        $table = new TableBuilder($sl);

        $table->setVariable('title', 'Things');
        $table->setVariable('titleSingular', 'Thing');

        $table->loadData($data);

        $this->assertEquals($data, $table->getRows());
        $this->assertTrue($table->hasRows());

        $this->assertEquals(1, $table->getTotal());
        self::assertEquals(self::TRANSLATED . 'Thing', $table->getVariable('title'));
    }

    /**
     * Test loadData with result data
     */
    public function testLoadDataWithResultData()
    {
        $rows = array(
            array('foo' => 'bar'),
            array('foo' => 'bar')
        );

        $data = array(
            'Results' => $rows,
            'Count' => 10
        );

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->loadData($data);

        $this->assertEquals($rows, $table->getRows());

        $this->assertEquals(10, $table->getTotal());
    }

    /**
     * Test loadParams Without Url
     *
     * @doesNotPerformAssertions
     */
    public function testLoadParamsWithoutUrl()
    {
        $params = array(
        );

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->loadParams($params);
    }

    /**
     * Test loadParams With limit
     */
    public function testLoadParamsWithLimit()
    {
        $url = new \stdClass();

        $params = array(
            'url' => $url,
            'limit' => 10
        );

        $expected = array_merge(array('page' => 1, 'sort' => '', 'order' => 'ASC'), $params);

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->loadParams($params);

        $this->assertSame($url, $table->getUrl());

        $this->assertEquals(10, $table->getLimit());
        $this->assertEquals('', $table->getSort());
        $this->assertEquals('ASC', $table->getOrder());
        $this->assertEquals($expected, $table->getVariables());
    }

    /**
     * Test loadParams With default limit
     */
    public function testLoadParamsWithDefaultLimit()
    {
        $url = new \stdClass();

        $params = array(
            'url' => $url
        );

        $tableConfig = array(
            'variables' => array(
                'foo' => 'bar',
                'title' => 'Test',
            ),
            'settings' => array(
                'paginate' => array()
            )
        );

        $expectedVariables = $params;

        $expectedVariables['foo'] = 'bar';
        $expectedVariables['title'] = self::TRANSLATED . 'Test';

        $expectedVariables['hidden'] = 'default';
        $expectedVariables['limit'] = 10;
        $expectedVariables['page'] = 1;
        $expectedVariables['sort'] = '';
        $expectedVariables['order'] = 'ASC';

        $table = $this->getMockTableBuilder(array('getConfigFromFile'));

        $table->expects($this->once())
            ->method('getConfigFromFile')
            ->will($this->returnValue($tableConfig));

        $table->loadConfig('test');

        $table->loadParams($params);

        $this->assertSame($url, $table->getUrl());

        $this->assertEquals(10, $table->getLimit());
        $this->assertEquals('', $table->getSort());
        $this->assertEquals('ASC', $table->getOrder());
        $this->assertEquals($expectedVariables, $table->getVariables());
    }

    /**
     * Test loadParams With Query
     */
    public function testLoadParamsWithQuery()
    {
        $query = new \stdClass();

        $params = array(
            'url' => 'foo',
            'query' => $query,
        );

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->loadParams($params);

        $this->assertSame($query, $table->getQuery());
    }

    /**
     * Test setupAction with action set
     */
    public function testSetupActionWithActionSet()
    {
        $variables = array(
            'action' => '/'
        );

        $table = $this->getMockTableBuilder(array('getVariables', 'getUrl'));

        $table->expects($this->any())
            ->method('getVariables')
            ->will($this->returnValue($variables));

        $table->setupAction();

        $table->expects($this->never())
            ->method('getUrl');
    }

    /**
     * Test setupAction without action set
     */
    public function testSetupActionWithoutActionSet()
    {
        $variables = array();

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));

        $mockUrl->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue('/someaction'));

        $table = $this->getMockTableBuilder(array('getVariables', 'getUrl'));

        $table->expects($this->any())
            ->method('getVariables')
            ->will($this->returnValue($variables));

        $table->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->setupAction();
    }

    /**
     * Test setupAction with action_route set
     */
    public function testSetupActionWithActionRouteSet()
    {
        $variables = array(
            'action_route' => array('route' => 'someroute', 'params' => array('foo' => 'bar'))
        );

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));

        $mockUrl->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue('/someaction'));

        $table = $this->getMockTableBuilder(array('getVariables', 'getUrl'));

        $table->expects($this->any())
            ->method('getVariables')
            ->will($this->returnValue($variables));

        $table->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->setupAction();
    }

    /**
     * Test render
     */
    public function testRender()
    {

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('HTML', array())
            ->will($this->returnValue('MORE HTML'));

        $table = $this->getMockTableBuilder(array('renderTable', 'getVariables', 'getContentHelper'));

        $table->expects($this->once())
            ->method('renderTable')
            ->will($this->returnValue('HTML'));

        $table->expects($this->once())
            ->method('getVariables')
            ->will($this->returnValue(array()));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $this->assertEquals('MORE HTML', $table->render());
    }

    /**
     * Test renderTableFooter without footer
     */
    public function testRenderTableFooterWithoutFooter()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $this->assertEquals('', $table->renderTableFooter());
    }

    /**
     * Test renderTableFooter
     */
    public function testRenderTableFooter()
    {
        $footer = array(
            array(
                'type' => 'th',
                'colspan' => 2,
                'content' => 'foo',
                'formatter' => function () {
                    return 'ABC';
                },
                'align' => 'right',
            ),
            array(
                'format' => 'HTML'
            )
        );

        $table = $this->getMockTableBuilder(
            [
                'getContentHelper',
            ]
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->any())
            ->method('replaceContent')
            ->will(
                $this->returnCallback(
                    function ($string) {
                        return $string;
                    }
                )
            );

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->setFooter($footer);

        $this->assertEquals('{{[elements/tableFooter]}}', $table->renderTableFooter());
    }

    /**
     * Test renderTable For Hybrid
     */
    public function testRenderTableForHybrid()
    {
        $settings = array(
            'crud' => 'foo',
            'paginate' => 'bar'
        );

        $table = $this->getMockTableBuilder(array('setType', 'renderLayout'));

        $table->expects($this->once())
            ->method('setType')
            ->with(TableBuilder::TYPE_HYBRID);

        $table->expects($this->once())
            ->method('renderLayout')
            ->with('crud');

        $table->setSettings($settings);

        $table->renderTable();
    }

    /**
     * Test renderTable For Crud
     */
    public function testRenderTableForCrud()
    {
        $settings = array(
            'crud' => 'foo'
        );

        $table = $this->getMockTableBuilder(array('setType', 'renderLayout'));

        $table->expects($this->once())
            ->method('setType')
            ->with(TableBuilder::TYPE_CRUD);

        $table->expects($this->once())
            ->method('renderLayout')
            ->with('crud');

        $table->setSettings($settings);

        $table->renderTable();

        $csrfElm = $table->getCsrfElement();
        static::assertEquals('security', $csrfElm->getName());
        static::assertEquals(
            [
                'csrf_options' => [
                    'timeout' => 9999,
                ],
            ],
            $csrfElm->getOptions()
        );
    }

    /**
     * Test renderTable For SubmissionSection
     */
    public function testRenderTableForSubmissionSection()
    {
        $settings = array(
            'submission_section' => 'foo'
        );

        $table = $this->getMockTableBuilder(array('setType', 'renderLayout'));

        $table->expects($this->once())
            ->method('setType')
            ->with(TableBuilder::TYPE_DEFAULT);

        $table->expects($this->once())
            ->method('renderLayout')
            ->with('submission-section');

        $table->setSettings($settings);

        $table->renderTable();
    }

    /**
     * Test renderTable For Crud within form
     */
    public function testRenderTableForCrudWithinForm()
    {
        $settings = array(
            'crud' => 'foo'
        );

        $variables = array(
            'within_form' => true
        );

        $table = $this->getMockTableBuilder(array('setType', 'renderLayout'));

        $table->expects($this->once())
            ->method('setType')
            ->with(TableBuilder::TYPE_FORM_TABLE);

        $table->expects($this->once())
            ->method('renderLayout')
            ->with('default');

        $table->setSettings($settings);

        $table->setVariables($variables);

        $table->renderTable();
    }

    /**
     * Test renderTable For pagination
     */
    public function testRenderTableForPagination()
    {
        $settings = array(
            'paginate' => 'foo'
        );

        $table = $this->getMockTableBuilder(array('setType', 'renderLayout'));

        $table->expects($this->once())
            ->method('setType')
            ->with(TableBuilder::TYPE_PAGINATE);

        $table->expects($this->once())
            ->method('renderLayout')
            ->with('default');

        $table->setSettings($settings);

        $table->renderTable();
    }

    /**
     * Test renderTable default
     */
    public function testRenderTableDefault()
    {
        $settings = array(
        );

        $table = $this->getMockTableBuilder(array('setType', 'renderLayout'));

        $table->expects($this->once())
            ->method('setType')
            ->with(TableBuilder::TYPE_DEFAULT);

        $table->expects($this->once())
            ->method('renderLayout')
            ->with('default');

        $table->setSettings($settings);

        $table->renderTable();
    }

    /**
     * Test renderLayout
     */
    public function testRenderLayout()
    {
        $name = 'foo';

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $mockContentHelper = $this->createPartialMock('\stdClass', array('renderLayout'));

        $mockContentHelper->expects($this->once())
            ->method('renderLayout')
            ->with($name)
            ->will($this->returnValue($name));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $this->assertEquals($name, $table->renderLayout($name));
    }

    /**
     * Test renderTotal Without pagination
     */
    public function testRenderTotalWithoutPagination()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setType(TableBuilder::TYPE_CRUD);

        $this->assertEquals('', $table->renderTotal());
    }

    /**
     * Test renderTotal With pagination
     */
    public function testRenderTotalWithPagination()
    {
        $total = 10;

        $expectedTotal = 10;

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with(' {{[elements/total]}}', array('total' => $expectedTotal))
            ->will($this->returnValue($expectedTotal));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'shouldPaginate'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->expects($this->once())
            ->method('shouldPaginate')
            ->will($this->returnValue(true));

        $table->setTotal($total);

        $this->assertEquals($expectedTotal, $table->renderTotal());
    }

    /**
     * Test renderTotal With pagination With 1 result
     */
    public function testRenderTotalWithPaginationWith1()
    {
        $total  = 1;

        $expectedTotal = 1;

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with(' {{[elements/total]}}', array('total' => $expectedTotal))
            ->will($this->returnValue($expectedTotal));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'shouldPaginate'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->expects($this->once())
            ->method('shouldPaginate')
            ->will($this->returnValue(true));

        $table->setTotal($total);

        $this->assertEquals($expectedTotal, $table->renderTotal());
    }

    /**
     * Test renderTotal Without pagination but with showTotal setting
     */
    public function testRenderTotalWithoutPaginationWithShowTotal()
    {
        $total = 10;
        $expectedTotal = 10;

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with(' {{[elements/total]}}', array('total' => $expectedTotal))
            ->will($this->returnValue($expectedTotal));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'shouldPaginate', 'getSetting'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->expects($this->once())
            ->method('shouldPaginate')
            ->will($this->returnValue(false));

        $table->expects($this->once())
            ->method('getSetting')
            ->with('showTotal', false)
            ->will($this->returnValue(true));

        $table->setTotal($total);

        $this->assertEquals($expectedTotal, $table->renderTotal());
    }

    /**
     * Test renderActions With Pagination
     */
    public function testRenderActionsWithoutCrud()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setType(TableBuilder::TYPE_PAGINATE);

        $this->assertEquals('', $table->renderActions());
    }

    /**
     * Test renderActions without actions
     */
    public function testRenderActionsWithoutActions()
    {
        $settings = array(
            'crud' => array(
            )
        );

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setType(TableBuilder::TYPE_CRUD);

        $table->setSettings($settings);

        $this->assertEquals('', $table->renderActions());
    }

    public function testTrimActionsHaveRows()
    {
        $settings = [
            'crud' => [
                'actions' => [
                    'action_1' => [
                        'requireRows' => true,
                    ],
                    'action_2' => [
                        'requireRows' => false,
                    ],
                ],
            ]
        ];

        $mockContentHelper = m::mock(\Common\Service\Table\ContentHelper::class);
        $mockContentHelper
            ->shouldReceive('replaceContent')
            ->times(2)
            ->with('{{[elements/actionButton]}}', m::any())
            ->andReturnUsing(
                function ($content, $details) {
                    return $details['name'];
                }
            );
        $mockContentHelper
            ->shouldReceive('replaceContent')
            ->once()
            ->with(
                '{{[elements/actionContainer]}}',
                [
                    'content' => 'action_1action_2',
                ]
            );

        $table = $this->getMockTableBuilder(['getContentHelper']);
        $table
            ->setType(TableBuilder::TYPE_CRUD)
            ->setRows(['HAVE ROWS'])
            ->setSettings($settings);

        $table->expects($this->any())
            ->method('getContentHelper')
            ->willReturn($mockContentHelper);

        $table->renderActions();
    }

    public function testTrimActionsReadOnlyUser()
    {
        $settings = [
            'crud' => [
                'actions' => [
                    'action_1' => [
                    ],
                    'action_3' => [
                        'keepForReadOnly' => true,
                    ],
                    'action_4' => [
                        'keepForReadOnly' => false,
                    ],
                ],
            ]
        ];

        $mockContentHelper = m::mock(\Common\Service\Table\ContentHelper::class);
        $mockContentHelper
            ->shouldReceive('replaceContent')
            ->with('{{[elements/actionButton]}}', m::any())
            ->andReturnUsing(
                function ($content, $details) {
                    return $details['name'];
                }
            );
        $mockContentHelper
            ->shouldReceive('replaceContent')
            ->once()
            ->with(
                '{{[elements/actionContainer]}}',
                [
                    'content' => 'action_3',
                ]
            );

        $table = $this->getMockTableBuilder(['getContentHelper', 'isInternalReadOnly']);
        $table
            ->setType(TableBuilder::TYPE_CRUD)
            ->setRows([])
            ->setSettings($settings);

        $table->expects($this->any())
            ->method('getContentHelper')
            ->willReturn($mockContentHelper);

        $table->expects($this->exactly(2))
            ->method('isInternalReadOnly')
            ->willReturn(true);

        $table->renderActions();
    }

    public function testTrimActionsNoRows()
    {
        $settings = [
            'crud' => [
                'actions' => [
                    'action_1' => [
                        'requireRows' => true,
                    ],
                    'action_2' => [
                        'requireRows' => false,
                    ],
                    'action_3' => [
                    ],
                ],
            ]
        ];

        $mockContentHelper = m::mock(\Common\Service\Table\ContentHelper::class);
        $mockContentHelper
            ->shouldReceive('replaceContent')
            ->with('{{[elements/actionButton]}}', m::any())
            ->andReturnUsing(
                function ($content, $details) {
                    return $details['name'];
                }
            );
        $mockContentHelper
            ->shouldReceive('replaceContent')
            ->once()
            ->with(
                '{{[elements/actionContainer]}}',
                [
                    'content' => 'action_2action_3',
                ]
            );

        $table = $this->getMockTableBuilder(['getContentHelper', 'isInternalReadOnly']);
        $table
            ->setType(TableBuilder::TYPE_CRUD)
            ->setRows([])
            ->setSettings($settings);

        $table->expects($this->any())
            ->method('getContentHelper')
            ->willReturn($mockContentHelper);

        $table->expects($this->any())
            ->method('isInternalReadOnly')
            ->willReturn(false);

        $table->renderActions();
    }

    /**
     * Test renderActions
     */
    public function testRenderActions()
    {
        $settings = array(
            'crud' => array(
                'actions' => array(
                    'add' => array(),
                    'edit' => array(),
                    'foo' => array(),
                    'bar' => array(),
                    'cake' => array(),
                    'baz' => array(),
                )
            )
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->any())
            ->method('replaceContent')
            ->with('{{[elements/actionContainer]}}')
            ->will(
                $this->returnCallback(
                    function ($content, $vars) {
                        unset($content);
                        return $vars;
                    }
                )
            );

        $table = $this->getMockTableBuilder(array('getContentHelper', 'renderButtonActions'));

        $table->setType(TableBuilder::TYPE_CRUD);
        $table->setSettings($settings);

        $table->expects($this->once())
            ->method('renderButtonActions')
            ->willReturn('EXPECTED');

        $table->expects($this->once())
            ->method('getContentHelper')
            ->willReturn($mockContentHelper);

        $this->assertEquals(['content' => 'EXPECTED'], $table->renderActions());
    }

    /**
     * Test renderActions With Dropdown
     */
    public function testRenderActionsWithDropdown()
    {
        $settings = array(
            'crud' => array(
                'actions' => array(
                    'add' => array(),
                    'edit' => array(),
                    'foo' => array(),
                    'bar' => array(),
                    'cake' => array(),
                    'baz' => array(),
                    'top' => array()
                )
            )
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->any())
            ->method('replaceContent')
            ->with('{{[elements/actionContainer]}}')
            ->will(
                $this->returnCallback(
                    function ($content, $vars) {
                        unset($content);
                        return $vars;
                    }
                )
            );

        $table = $this->getMockTableBuilder(array('getContentHelper', 'renderDropdownActions'));

        $table->expects($this->once())
            ->method('renderDropdownActions')
            ->will($this->returnValue('DROPDOWN'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->setType(TableBuilder::TYPE_CRUD);

        $table->setSettings($settings);

        $this->assertEquals(array('content' => 'DROPDOWN'), $table->renderActions());
    }

    /**
     * Test renderActions with format override
     * (Default behaviour is dropdown for > 4 actions)
     */
    public function testRenderActionsWithFormatOverrideButtons()
    {
        $settings = array(
            'crud' => array(
                'actions' => array(
                    'add' => array(),
                    'edit' => array(),
                    'foo' => array(),
                    'bar' => array(),
                    'cake' => array()
                )
            ),
            'actionFormat' => 'buttons'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->any())
            ->method('replaceContent')
            ->with('{{[elements/actionContainer]}}')
            ->will(
                $this->returnCallback(
                    function ($content, $vars) {
                        unset($content);
                        return $vars;
                    }
                )
            );

        $table = $this->getMockTableBuilder(array('getContentHelper', 'renderButtonActions'));

        $table->expects($this->once())
            ->method('renderButtonActions')
            ->will($this->returnValue('BUTTONS'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->setType(TableBuilder::TYPE_CRUD);

        $table->setSettings($settings);

        $this->assertEquals(array('content' => 'BUTTONS'), $table->renderActions());
    }

    /**
     * Test renderActions with format override
     * (Default behaviour is buttons for <= 4 actions)
     */
    public function testRenderActionsWithFormatOverrideDropdown()
    {
        $settings = array(
            'crud' => array(
                'actions' => array(
                    'foo' => array(),
                    'bar' => array(),
                )
            ),
            'actionFormat' => 'dropdown'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->any())
            ->method('replaceContent')
            ->with('{{[elements/actionContainer]}}')
            ->will(
                $this->returnCallback(
                    function ($content, $vars) {
                        unset($content);
                        return $vars;
                    }
                )
            );

        $table = $this->getMockTableBuilder(array('getContentHelper', 'renderDropdownActions'));

        $table->expects($this->once())
            ->method('renderDropdownActions')
            ->will($this->returnValue('DROPDOWN'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->setType(TableBuilder::TYPE_CRUD);

        $table->setSettings($settings);

        $this->assertEquals(array('content' => 'DROPDOWN'), $table->renderActions());
    }

    /**
     * Test renderAttributes
     */
    public function testRenderAttributes()
    {
        $attributes = array();

        $mockContentHelper = $this->createPartialMock('\stdClass', array('renderAttributes'));

        $mockContentHelper->expects($this->once())
            ->method('renderAttributes')
            ->with($attributes);

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderAttributes($attributes);
    }

    /**
     * Test renderAttributes without attributes
     */
    public function testRenderAttributesWithoutAttributes()
    {
        $mockContentHelper = $this->createPartialMock('\stdClass', array('renderAttributes'));

        $mockContentHelper->expects($this->once())
            ->method('renderAttributes')
            ->with(array());

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderAttributes();
    }

    /**
     * Test renderDropdownActions
     */
    public function testRenderDropdownActions()
    {
        $actions = array(
            array(
                'foo' => 'bar'
            ),
            array(
                'foo' => 'bar'
            )
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/actionOption]}}');

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/actionOption]}}');

        $mockContentHelper->expects($this->at(2))
            ->method('replaceContent')
            ->with('{{[elements/actionSelect]}}');

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderDropdownActions($actions);
    }

    /**
     * Test renderButtonActions
     */
    public function testRenderButtonActions()
    {
        $actions = array(
            array(
                'foo' => 'bar'
            ),
            array(
                'foo' => 'bar'
            )
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/actionButton]}}');

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/actionButton]}}');

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderButtonActions($actions);
    }

    /**
     * Test renderButtonActions with collapseAt value set
     */
    public function testRenderButtonActionsCollapse()
    {
        $actions = array(
            array(
                'foo' => 'bar'
            ),
            array(
                'bar' => 'cake'
            ),
            [
                'action_3' => 'unit_1|',
            ],
            [
                'action_4' => 'unit_2|',
            ],
        );

        $mockContentHelper = m::mock(\Common\Service\Table\ContentHelper::class);
        $mockContentHelper
            ->shouldReceive('replaceContent')
            ->times(4)
            ->with('{{[elements/actionButton]}}', m::any())
            ->andReturnUsing(
                function ($content, $details) {
                    return key($details) . '-' . current($details);
                }
            );
        $mockContentHelper
            ->shouldReceive('replaceContent')
            ->with(
                '{{[elements/moreActions]}}',
                [
                    'content' => 'action_3-unit_1|action_4-unit_2|',
                    'label' => self::TRANSLATED . 'table_button_more_actions',
                ]
            );

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->willReturn($mockContentHelper);

        $table->renderButtonActions($actions, 2);
    }

    /**
     * Test renderFooter Without pagination
     */
    public function testRenderFooterWithoutPagination()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setType(TableBuilder::TYPE_CRUD);

        $this->assertEquals('', $table->renderFooter());
    }

    /**
     * Test renderFooter
     */
    public function testRenderFooter()
    {
        $settings = array(
            'paginate' => array(
                'limit' => array(
                    'options' => array(10, 20, 30)
                )
            )
        );

        $table = $this->getMockTableBuilder(array('renderLayout'));

        $table->expects($this->once())
            ->method('renderLayout')
            ->with('pagination');

        $table->setSettings($settings);

        $table->setType(TableBuilder::TYPE_PAGINATE);

        $table->setLimit(10);

        $table->setTotal(100);

        $table->renderFooter();
    }

    /**
     * Test renderLimitOptions Without limit options
     */
    public function testRenderLimitOptionsWithoutLimitOptions()
    {
        $settings = array(
            'paginate' => array(
                'limit' => array(
                    'options' => array()
                )
            )
        );

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setSettings($settings);

        $this->assertEquals('', $table->renderLimitOptions());
    }

    /**
     * Test renderLimitOptions
     */
    public function testRenderLimitOptions()
    {
        $settings = array(
            'paginate' => array(
                'limit' => array(
                    'options' => array(
                        10, 20, 30
                    )
                )
            )
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/limitOption]}}', array('class' => 'current', 'option' => '10'));

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/limitLink]}}')
            ->will($this->returnValue('20'));

        $mockContentHelper->expects($this->at(2))
            ->method('replaceContent')
            ->with('{{[elements/limitOption]}}', array('class' => '', 'option' => '20'));

        $mockContentHelper->expects($this->at(3))
            ->method('replaceContent')
            ->with('{{[elements/limitLink]}}')
            ->will($this->returnValue('30'));

        $mockContentHelper->expects($this->at(4))
            ->method('replaceContent')
            ->with('{{[elements/limitOption]}}', array('class' => '', 'option' => '30'));

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'getUrl'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->setSettings($settings);

        $table->setLimit(10);

        $this->assertEquals('', $table->renderLimitOptions());
    }

    /**
     * Test renderLimitOptions with query enabled
     */
    public function testRenderLimitOptionsWithQueryEnabled()
    {
        $settings = array(
            'paginate' => array(
                'limit' => array(
                    'options' => array(
                        10, 20, 30
                    )
                )
            )
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/limitOption]}}', array('class' => 'current', 'option' => '10'));

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/limitLink]}}')
            ->will($this->returnValue('20'));

        $mockContentHelper->expects($this->at(2))
            ->method('replaceContent')
            ->with('{{[elements/limitOption]}}', array('class' => '', 'option' => '20'));

        $mockContentHelper->expects($this->at(3))
            ->method('replaceContent')
            ->with('{{[elements/limitLink]}}', array('option' => '30', 'link' => '?foo=bar&page=1&limit=30'))
            ->will($this->returnValue('30'));

        $mockContentHelper->expects($this->at(4))
            ->method('replaceContent')
            ->with('{{[elements/limitOption]}}', array('class' => '', 'option' => '30'));

        //$mockQuery = $this->createMock('\stdClass', array('toArray'));
        $mockQuery = [
            'foo' => 'bar',
            'page' => '1',
            'limit' => '30'
        ];

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));
        $mockUrl->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue('?' . http_build_query($mockQuery)));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'getQuery', 'getUrl'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($mockQuery));

        $table->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->setSettings($settings);

        $table->setLimit(10);

        $this->assertEquals('', $table->renderLimitOptions());
    }

    /**
     * Test renderPageOptions without options
     */
    public function testRenderPageOptionsWithoutOptions()
    {
        $options = array(

        );

        $mockPaginationHelper = $this->createPartialMock('\stdClass', array('getOptions'));

        $mockPaginationHelper->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $table = $this->getMockTableBuilder(array('getPaginationHelper'));

        $table->expects($this->once())
            ->method('getPaginationHelper')
            ->will($this->returnValue($mockPaginationHelper));

        $this->assertEquals('', $table->renderPageOptions());
    }

    /**
     * Test renderPageOptions
     */
    public function testRenderPageOptions()
    {
        $options = array(
            array(
                'page' => null,
                'label' => '...'
            ),
            array(
                'page' => 1,
                'label' => '1'
            ),
            array(
                'page' => 2,
                'label' => '2'
            )
        );

        $mockPaginationHelper = $this->createPartialMock('\stdClass', array('getOptions'));

        $mockPaginationHelper->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/paginationItem]}}');

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/paginationLink]}}');

        $mockContentHelper->expects($this->at(2))
            ->method('replaceContent')
            ->with('{{[elements/paginationItem]}}');

        $mockContentHelper->expects($this->at(3))
            ->method('replaceContent')
            ->with('{{[elements/paginationItem]}}');

        $table = $this->getMockTableBuilder(array('getPaginationHelper', 'getUrl', 'getContentHelper'));

        $table->setPage(2);

        $table->expects($this->once())
            ->method('getPaginationHelper')
            ->will($this->returnValue($mockPaginationHelper));

        $table->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $this->assertEquals('', $table->renderPageOptions());
    }

    /**
     * Test renderHeaderColumn Without options
     */
    public function testRenderHeaderColumnWithoutOptions()
    {
        $column = array(

        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/th]}}');

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderHeaderColumn($column);
    }

    /**
     * Test renderHeaderColumn With custom content
     */
    public function testRenderHeaderColumnWithCustomContent()
    {
        $column = array(

        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/foo]}}');

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderHeaderColumn($column, '{{[elements/foo]}}');
    }

    /**
     * Test renderHeaderColumn With sort current order asc
     */
    public function testRenderHeaderColumnWithSortCurrentOrderAsc()
    {
        $column = array(
            'sort' => 'foo'
        );

        $expectedColumn = array(
            'sort' => 'foo',
            'class' => 'sortable ascending',
            'order' => 'DESC',
            'link' => 'LINK'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/sortColumn]}}', $expectedColumn);

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/foo]}}');

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));

        $mockUrl->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue('LINK'));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'getUrl'));

        $table->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->setSort('foo');
        $table->setOrder('ASC');

        $table->renderHeaderColumn($column, '{{[elements/foo]}}');
    }

    /**
     * Test renderHeaderColumn With sort current order desc
     */
    public function testRenderHeaderColumnWithSortCurrentOrderDesc()
    {
        $column = array(
            'sort' => 'foo'
        );

        $expectedColumn = array(
            'sort' => 'foo',
            'class' => 'sortable descending',
            'order' => 'ASC',
            'link' => 'LINK'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/sortColumn]}}', $expectedColumn);

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/foo]}}');

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));

        $mockUrl->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue('LINK'));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'getUrl'));

        $table->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->setSort('foo');
        $table->setOrder('DESC');

        $table->renderHeaderColumn($column, '{{[elements/foo]}}');
    }

    /**
     * Test renderHeaderColumn With sort
     */
    public function testRenderHeaderColumnWithSort()
    {
        $column = array(
            'sort' => 'foo'
        );

        $expectedColumn = array(
            'sort' => 'foo',
            'class' => 'sortable',
            'order' => 'ASC',
            'link' => 'LINK'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/sortColumn]}}', $expectedColumn);

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/foo]}}');

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));

        $mockUrl->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue('LINK'));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'getUrl'));

        $table->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->setSort('bar');
        $table->setOrder('DESC');

        $table->renderHeaderColumn($column, '{{[elements/foo]}}');
    }

    /**
     * Test renderHeaderColumn With pre-set width
     */
    public function testRenderHeaderColumnWithWidthAndTitle()
    {
        $column = array(
            'width' => 'checkbox',
            'title' => 'Title',
        );

        $expectedColumn = array(
            'width' => '20px',
            'title' => self::TRANSLATED . 'Title',
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/th]}}', $expectedColumn);

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderHeaderColumn($column);
    }

    /**
     * Test renderHeaderColumn when disabled
     */
    public function testRenderHeaderColumnWhenDisabled()
    {
        $column = array(
            'hideWhenDisabled' => true
        );

        $table = $this->getMockTableBuilder(array('getContentHelper'));
        $table->setDisabled(true);

        $response = $table->renderHeaderColumn($column);

        $this->assertEquals(null, $response);
    }


    /**
     * Test renderHeaderColumn with alignment
     */
    public function testRenderHeaderColumnWithAlign()
    {
        $column = array(
            'align' => 'right',
        );

        $expectedColumn = array(
            'class' => 'right',
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/th]}}', $expectedColumn);

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderHeaderColumn($column);
    }


    /**
     * Test renderHeaderColumn with sort and alignment
     */
    public function testRenderHeaderColumnWithSortAndAlign()
    {
        $column = [
            'sort' => 'foo',
            'align' => 'right',
        ];

        $expectedColumn = [
            'class' => 'right sortable',
            'sort' => 'foo',
            'order' => 'ASC',
            'link' => 'LINK',
        ];

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('{{[elements/sortColumn]}}', $expectedColumn);

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/th]}}');

        $mockUrl = $this->createPartialMock('\stdClass', array('fromRoute'));

        $mockUrl->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue('LINK'));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'getUrl'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($mockUrl));

        $table->renderHeaderColumn($column);
    }

    /**
     * Test renderBodyColumn when disabled
     */
    public function testRenderBodyColumnWhenDisabled()
    {
        $column = array(
            'hideWhenDisabled' => true
        );

        $table = $this->getMockTableBuilder(array('getContentHelper'));
        $table->setDisabled(true);

        $response = $table->renderBodyColumn([], $column);

        $this->assertEquals(null, $response);
    }

    /**
     * Test renderHeaderColumn when incorrect permission set
     */
    public function testRenderHeaderColumnWhenPermissionWontAllow()
    {
        $column = array(
            'permissionRequisites' => ['incorrectPermission']
        );

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $response = $table->renderHeaderColumn($column);

        $this->assertEquals(null, $response);
    }

    /**
     * Test renderBodyColumn when incorrect permission set
     */
    public function testRenderBodyColumnWhenPermissionWontAllow()
    {
        $column = array(
            'permissionRequisites' => ['incorrectPermission']
        );

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $response = $table->renderBodyColumn([], $column);

        $this->assertEquals(null, $response);
    }

    /**
     * Test renderHeaderColumn when correct permission set
     */
    public function testRenderHeaderColumnWhenPermissionWillAllow()
    {
        $column = array(
            'permissionRequisites' => ['correctPermission']
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));
        $mockContentHelper->expects($this->once())
            ->method('replaceContent');

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $mockAuthService = $this->createPartialMock('\StdClass', array('isGranted'));
        $mockAuthService->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        $table->setAuthService($mockAuthService);

        $response = $table->renderHeaderColumn($column);

        $this->assertEquals(null, $response);
    }

    /**
     * Test renderBodyColumn when correct permission set
     */
    public function testRenderBodyColumnWhenPermissionWillAllow()
    {
        $column = array(
            'permissionRequisites' => ['correctPermission']
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));
        $mockContentHelper->expects($this->once())
            ->method('replaceContent');

        $mockAuthService = $this->createPartialMock('\StdClass', array('isGranted'));
        $mockAuthService->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);

        $table = $this->getMockTableBuilder(array('getContentHelper'));
        $table->setAuthService($mockAuthService);
        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $response = $table->renderBodyColumn([], $column);

        $this->assertEquals(null, $response);
    }

    /**
     * Test renderBodyColumn With Empty Row With Empty Column
     */
    public function testRenderBodyColumnEmptyRowEmptyColumn()
    {
        $row = array();

        $column = array();

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => '', 'attrs' => ''));

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Name
     */
    public function testRenderBodyColumnWithName()
    {
        $row = array(
            'foo' => 'bar'
        );

        $column = array(
            'name' => 'foo'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => 'bar', 'attrs' => ''));

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Align
     */
    public function testRenderBodyColumnWithAlign()
    {
        $row = array(
            'foo' => 'bar'
        );

        $column = array(
            'name' => 'foo',
            'align' => 'right',
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => 'bar', 'attrs' => ' class="right"'));

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn with data heading
     */
    public function testRenderBodyColumnWithDataHeading()
    {
        $row = array(
            'foo' => 'bar'
        );

        $column = array(
            'name' => 'foo',
            'title' => '<div>Foo</div>',
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => 'bar', 'attrs' => ' data-heading="_TRSLTD_Foo"'));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'getColumns'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->expects($this->once())
            ->method('getColumns')
            ->will($this->returnValue([$column]));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn with data custom attributes
     */
    public function testRenderBodyColumnWithAttributes()
    {
        $row = array(
            'foo' => 'bar'
        );

        $column = array(
            'name' => 'foo',
            'align' => 'centre'
        );

        $customAttributes = ['colspan' => '2', 'class' => 'a-class', 'data-empty' => ' '];

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => 'bar', 'attrs' => ' class="centre a-class" colspan="2"'));

        $table = $this->getMockTableBuilder(array('getContentHelper', 'getColumns'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->expects($this->once())
            ->method('getColumns')
            ->will($this->returnValue([$column]));

        $table->renderBodyColumn($row, $column, '{{[elements/td]}}', $customAttributes);
    }

    /**
     * Test renderBodyColumn Custom Wrapper
     */
    public function testRenderBodyColumnCustomWrapper()
    {
        $row = array();

        $column = array();

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/foo]}}', array('content' => '', 'attrs' => ''));

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column, '{{[elements/foo]}}');
    }

    /**
     * Test renderBodyColumn With Format
     */
    public function testRenderBodyColumnWithFormat()
    {
        $row = array(
            'test' => 'bar'
        );

        $column = array(
            'format' => 'FOO'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('FOO', $row)
            ->will($this->returnValue('FOOBAR'));

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => 'FOOBAR', 'attrs' => ''));

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Formatter
     */
    public function testRenderBodyColumnWithFormatter()
    {
        $row = array(
            'date' => date('Y-m-d')
        );

        $column = array(
            'formatter' => 'Date',
            'name' => 'date'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => date('d/m/Y'), 'attrs' => ''));

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Formatter And Action Type
     */
    public function testRenderBodyColumnWithFormatterAndActionType()
    {
        $row = array(
            'id' => 1,
            'date' => date('Y-m-d')
        );

        $column = array(
            'type' => 'Action',
            'class' => '',
            'action' => 'edit',
            'formatter' => 'Date',
            'name' => 'date'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $expected = '<input type="submit" class="" name="action[edit][1]" value="' . date('d/m/Y') . '"  />';
        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with(
                '{{[elements/td]}}',
                array('content' => $expected, 'attrs' => '')
            );

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Invalid Formatter
     */
    public function testRenderBodyColumnWithInvalidFormatter()
    {
        $row = array(
            'date' => date('Y-m-d')
        );

        $column = array(
            'formatter' => 'Blah'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => '', 'attrs' => ''));

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Formatter Returning Array
     */
    public function testRenderBodyColumnWithFormatterReturningArray()
    {
        $row = array(
            'date' => date('Y-m-d')
        );

        $column = array(
            'formatter' => function () {
                return array('date' => 'Something Else');
            },
            'name' => 'date'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/td]}}', array('content' => 'Something Else', 'attrs' => ''));

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Selector Type
     */
    public function testRenderBodyColumnWithSelectorType()
    {
        $row = array(
            'id' => 1
        );

        $column = array(
            'type' => 'Selector'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with(
                '{{[elements/td]}}',
                ['content' => '<input type="radio" name="id" value="1" id="[id][1]" />', 'attrs' => '']
            );

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Selector Type And Fieldset
     */
    public function testRenderBodyColumnWithSelectorTypeAndFieldset()
    {
        $row = array(
            'id' => 1
        );

        $column = array(
            'type' => 'Selector'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with(
                '{{[elements/td]}}',
                ['content' => '<input type="radio" name="table[id]" value="1" id="table[id][1]" />', 'attrs' => '']
            );

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->setFieldset('table');

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Action Type
     */
    public function testRenderBodyColumnWithActionType()
    {
        $row = array(
            'id' => 1,
            'foo' => 'bar'
        );

        $column = array(
            'type' => 'Action',
            'name' => 'foo',
            'class' => '',
            'action' => 'edit'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with(
                '{{[elements/td]}}',
                [
                    'content' => '<input type="submit" class="" name="action[edit][1]" value="bar"  />',
                    'attrs' => ''
                ]
            );

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderBodyColumn With Action Type And Fieldset
     */
    public function testRenderBodyColumnWithActionTypeAndFieldset()
    {
        $row = array(
            'id' => 1,
            'foo' => 'bar'
        );

        $column = array(
            'type' => 'Action',
            'name' => 'foo',
            'class' => '',
            'action' => 'edit'
        );

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with(
                '{{[elements/td]}}',
                [
                    'content' => '<input type="submit" class="" name="table[action][edit][1]" value="bar"  />',
                    'attrs' => ''
                ]
            );

        $table = $this->getMockTableBuilder(array('getContentHelper'));

        $table->setFieldset('table');

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $table->renderBodyColumn($row, $column);
    }

    /**
     * Test renderExtraRows with rows
     */
    public function testRenderExtraRowsWithRows()
    {
        $table = $this->getMockTableBuilder(array('getRows'));

        $table->expects($this->once())
            ->method('getRows')
            ->will($this->returnValue(array('foo' => 'bar')));

        $this->assertEquals('', $table->renderExtraRows());
    }

    /**
     * Test renderExtraRows without rows with custom message
     */
    public function testRenderExtraRowsWithoutRowsCustomMessage()
    {
        $table = $this->getMockTableBuilder(array('getRows', 'getColumns', 'getContentHelper', 'getServiceLocator'));

        $mockTranslator = $this->createPartialMock('\stdClass', array('translate'));

        $mockTranslator->expects($this->any())
            ->method('translate')
            ->will(
                $this->returnCallback(
                    function ($string) {
                        return $string;
                    }
                )
            );

        $mockServiceLocator = $this->createPartialMock('\stdClass', array('get'));

        $mockServiceLocator->expects($this->any())
            ->method('get')
            ->with('translator')
            ->will($this->returnValue($mockTranslator));

        $table->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));

        $table->setVariables(array('empty_message' => 'Empty'));

        $table->expects($this->once())
            ->method('getRows')
            ->will($this->returnValue(array()));

        $table->expects($this->once())
            ->method('getColumns')
            ->will($this->returnValue(array('foo')));

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->at(0))
            ->method('replaceContent')
            ->with('Empty')
            ->will($this->returnValue('Empty'));

        $mockContentHelper->expects($this->at(1))
            ->method('replaceContent')
            ->with('{{[elements/emptyRow]}}', array('colspan' => 1, 'message' => 'Empty'))
            ->will($this->returnValue('CONTENT'));

        $table->expects($this->any())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $this->assertEquals('CONTENT', $table->renderExtraRows());
    }

    /**
     * Test renderExtraRows without rows
     */
    public function testRenderExtraRowsWithoutRows()
    {
        $table = $this->getMockTableBuilder(array('getRows', 'getColumns', 'getContentHelper', 'getServiceLocator'));

        $mockTranslator = $this->createPartialMock('\stdClass', array('translate'));

        $mockTranslator->expects($this->any())
            ->method('translate')
            ->will(
                $this->returnCallback(
                    function ($string) {
                        return $string;
                    }
                )
            );

        $mockServiceLocator = $this->createPartialMock('\stdClass', array('get'));

        $mockServiceLocator->expects($this->any())
            ->method('get')
            ->with('translator')
            ->will($this->returnValue($mockTranslator));

        $table->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));

        $table->expects($this->once())
            ->method('getRows')
            ->will($this->returnValue(array()));

        $table->expects($this->once())
            ->method('getColumns')
            ->will($this->returnValue(array('foo')));

        $mockContentHelper = $this->createPartialMock('\stdClass', array('replaceContent'));

        $mockContentHelper->expects($this->once())
            ->method('replaceContent')
            ->with('{{[elements/emptyRow]}}', array('colspan' => 1, 'message' => 'The table is empty'))
            ->will($this->returnValue('CONTENT'));

        $table->expects($this->once())
            ->method('getContentHelper')
            ->will($this->returnValue($mockContentHelper));

        $this->assertEquals('CONTENT', $table->renderExtraRows());
    }

    /**
     * Test getServiceLocator method
     */
    public function testGetServiceLocator()
    {
        $tableFactory = new TableFactory();
        $serviceLocator = $this->createPartialMock('\Zend\ServiceManager\ServiceManager', array('get'));
        $tableBuilder = $tableFactory->createService($serviceLocator)->getTableBuilder();

        $newServiceLocator = $tableBuilder->getServiceLocator();

        $this->assertTrue($newServiceLocator instanceof \Zend\ServiceManager\ServiceManager);
        $this->assertTrue($newServiceLocator === $serviceLocator);
    }

    /**
     * Test action field name and fieldset
     */
    public function testActionFieldNameAndFieldset()
    {
        $actionName = 'foo';

        $fieldset = 'table';

        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setActionFieldName($actionName);

        $this->assertEquals($actionName, $table->getActionFieldName());

        $table->setFieldset($fieldset);

        $this->assertEquals($fieldset, $table->getFieldset());

        $this->assertEquals($fieldset . '[' . $actionName . ']', $table->getActionFieldName());
    }

    /**
     * Test get and set footer
     */
    public function testGetFooter()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setFooter(array('Foo' => 'Bar'));

        $this->assertEquals(array('Foo' => 'Bar'), $table->getFooter());
    }

    /**
     * Test get and set variable
     */
    public function testGetVariable()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $vars = array(
            'foo' => 'bar',
            'bar' => 'cake'
        );

        $table->setVariables($vars);

        $this->assertEquals('bar', $table->getVariable('foo'));

        $table->setVariable('foo', 'cake');

        $this->assertEquals('cake', $table->getVariable('foo'));
    }

    /**
     * Test remove column method
     */
    public function testRemoveColumn()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $columns = array(
            array('name' => 'name1'),
            array('name' => 'name2')
        );

        $table->setColumns($columns);

        $this->assertTrue($table->hasColumn('name1'));

        $table->removeColumn('name1');

        $this->assertFalse($table->hasColumn('name1'));
    }

    /**
     * Test remove column method if no name property exists
     */
    public function testRemoveColumnNoNameExists()
    {
        $table = new TableBuilder($this->getMockServiceLocator());
        $columns = array(
            array('name' => 'name1'),
            array('foo' => 'bar')
        );
        $table->setColumns($columns);
        $table->removeColumn('name1');
        $newColumns = $table->getColumns();
        $this->assertEquals(count($newColumns), 1);
    }

    /**
     * Test get and set settings
     */
    public function testGetSettings()
    {
        $table = new TableBuilder($this->getMockServiceLocator());

        $table->setSettings(array('Foo' => 'Bar'));

        $this->assertEquals(array('Foo' => 'Bar'), $table->getSettings());
    }

    public function testIsRowDisabled()
    {
        // Stubbed data
        $settings = [];
        $row = [];

        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with(m::type('string'))
            ->andReturn(true);
        $mockTranslatorService = m::mock(\Zend\Mvc\I18n\Translator::class);

        // Setup
        /** @var \Zend\ServiceManager\ServiceManager $sm */
        $sm = m::mock(\Zend\ServiceManager\ServiceManager::class)->makePartial();
        $sm->setAllowOverride(true);
        $sm->setService('Config', array());
        $sm->setService('ZfcRbac\Service\AuthorizationService', $mockAuthService);
        $sm->setService('translator', $mockTranslatorService);

        $sut = new TableBuilder($sm);

        $sut->setSettings($settings);

        $this->assertFalse($sut->isRowDisabled($row));
    }

    /**
     * @dataProvider providerIsRowDisabled
     */
    public function testIsRowDisabledWithDisabled($disabled)
    {
        // Stubbed data
        $settings = [
            'row-disabled-callback' => function ($row) {
                return $row['disabled'];
            }
        ];
        $row = [
            'disabled' => $disabled
        ];

        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with(m::type('string'))
            ->andReturn(true);
        $mockTranslatorService = m::mock(\Zend\Mvc\I18n\Translator::class);

        // Setup
        /** @var \Zend\ServiceManager\ServiceManager $sm */
        $sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $sm->setAllowOverride(true);
        $sm->setService('Config', array());
        $sm->setService('ZfcRbac\Service\AuthorizationService', $mockAuthService);
        $sm->setService('translator', $mockTranslatorService);

        $sut = new TableBuilder($sm);

        $sut->setSettings($settings);

        $this->assertEquals($disabled, $sut->isRowDisabled($row));
    }

    public function providerIsRowDisabled()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testRemoveActions()
    {
        $tableConfig = array(
            'settings' => array(
                'paginate' => array(),
                'crud' => array(
                    'actions' => array(
                        'foo' => array(),
                        'bar' => array()
                    )
                )
            )
        );

        $table = $this->getMockTableBuilder(array('getConfigFromFile', 'removeAction', 'removeColumn'));

        $table->expects($this->once())
            ->method('getConfigFromFile')
            ->will($this->returnValue($tableConfig));

        $table->loadConfig('test');

        $table->expects($this->at(0))
            ->method('removeAction')
            ->with('foo');

        $table->expects($this->at(1))
            ->method('removeAction')
            ->with('bar');

        $table->expects($this->once())
            ->method('removeColumn')
            ->with('actionLinks');

        $table->removeActions();
    }

    public function testDisableAction()
    {
        $tableConfig = array(
            'settings' => array(
                'paginate' => array(),
                'crud' => array(
                    'actions' => array(
                        'foo' => array(),
                        'bar' => array()
                    )
                )
            )
        );

        $table = $this->getMockTableBuilder(array('getConfigFromFile'));

        $table->expects($this->once())
            ->method('getConfigFromFile')
            ->will($this->returnValue($tableConfig));

        $table->loadConfig('test');

        $table->disableAction('foo');

        $this->assertEquals(
            array(
                'foo' => array('disabled' => 'disabled'),
                'bar' => array(),
            ),
            $table->getSettings()['crud']['actions']
        );
    }

    public function testGetEmptyMessage()
    {
        $message = 'foo';
        $config = [
            'tables' => [
                'config' => [__DIR__ . '/TestResources/'],
                'partials' => [
                    'html' => '',
                    'csv' => ''
                ]
            ]
        ];
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with(m::type('string'))
            ->andReturn(true);

        $mockTranslator = m::mock();
        $mockTranslator->shouldReceive('translate')
            ->with($message)
            ->andReturn($message);

        $sm = Bootstrap::getServiceManager();
        $sm->setService('Config', $config);
        $sm->setService('translator', $mockTranslator);
        $sm->setService('ZfcRbac\Service\AuthorizationService', $mockAuthService);

        $sut = new TableBuilder($sm);

        $sut->setEmptyMessage($message);
        $this->assertEquals($message, $sut->getEmptyMessage());
    }

    public function testAddAction()
    {
        $tableConfig = array(
            'settings' => array(
                'paginate' => array(),
                'crud' => array(
                    'actions' => array(
                        'foo' => array(),
                        'bar' => array()
                    )
                )
            )
        );

        $table = $this->getMockTableBuilder(array('getConfigFromFile'));

        $table->expects($this->once())
            ->method('getConfigFromFile')
            ->will($this->returnValue($tableConfig));

        $table->loadConfig('test');

        $table->addAction('new', ['key' => 'value']);

        $settings = $table->getSetting('crud');

        $this->assertEquals(
            array(
                'actions' => array(
                    'foo' => array(),
                    'bar' => array(),
                    'new' => array('key' => 'value')
                )
            ),
            $settings
        );
    }

    public function testCheckForActionLinks()
    {
        $tableConfig = [
            'settings' => [
                'paginate' => [],
                'crud' => [
                    'actions' => []
                ]
            ],
            'columns' => [
                ['bar'],
                [
                    'type' => 'ActionLinks',
                    'keepForReadOnly' => true,
                ],
                [
                    'type' => 'ActionLinks',
                ],
                [
                    'type' => 'DeltaActionLinks'
                ]
            ]
        ];

        $mockTranslator = m::mock();
        $mockTranslator->shouldReceive('translate')
            ->with(m::type('string'))
            ->andReturn('foo');

        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->with('internal-user')
            ->andReturn(true)
            ->once()
            ->shouldReceive('isGranted')
            ->with('internal-edit')
            ->andReturn(false)
            ->once()
            ->getMock();

        $sm = Bootstrap::getServiceManager();
        $sm->setService('ZfcRbac\Service\AuthorizationService', $mockAuthService);
        $sm->setService('Config', m::mock());
        $sm->setService('translator', $mockTranslator);

        $sut = new TableBuilder($sm);

        $sut->loadConfig($tableConfig);
        $this->assertEquals(
            [
                ['bar'],
                [
                    'type' => 'ActionLinks',
                    'keepForReadOnly' => true,
                ],
            ],
            $sut->getColumns()
        );
    }

    public function testSetSetting()
    {
        $table = new TableBuilder($this->getMockServiceLocator());
        $table->setSetting('collapseAt', 2);
        $this->assertEquals(2, $table->getSetting('collapseAt'));
    }

    public function testGetAction()
    {
        $table = new TableBuilder($this->getMockServiceLocator());
        $table->setSetting('crud', ['actions' => []]);
        $action = ['foo', 'bar'];
        $table->addAction('add', $action);
        $this->assertEquals($table->getAction('add'), $action);
    }
}
