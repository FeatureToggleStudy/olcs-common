<?php

namespace CommonTest\Form\View\Helper\Readonly;

use Common\Form\Elements;
use Common\Form\View\Helper\Readonly\FormRow;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\Form\Element as ZendElement;

/**
 * @covers \Common\Form\View\Helper\Readonly\FormRow
 */
class FormRowTest extends MockeryTestCase
{
    const STANDARD_RENDER_RESULT = 'STANDARD-RENDER-RESULT';

    protected function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /**
     * @dataProvider provideTestInvoke
     */
    public function testInvoke($element, $expected)
    {
        $mockHtmlHelper = m::mock(\Zend\View\Helper\EscapeHtml::class);
        $mockHtmlHelper
            ->shouldReceive('__invoke')
            ->andReturnUsing(
                function ($v) {
                    return ($v === null ? $v : '@' . $v . '@');
                }
            );

        $mockElementHelper = m::mock(\Common\Form\View\Helper\Readonly\FormItem::class);
        $mockElementHelper->shouldReceive('__invoke')->andReturnUsing(
            function ($v) {
                return $v->getValue();
            }
        );

        $mockTableHelper = m::mock(\Common\Form\View\Helper\Readonly\FormTable::class);
        $mockTableHelper->shouldReceive('__invoke')->andReturn('<table></table>');

        $mockTranslater = m::mock(\Zend\I18n\Translator\TranslatorInterface::class);
        $mockTranslater
            ->shouldReceive('translate')
            ->andReturnUsing(
                function ($v) {
                    return '_' . $v . '_';
                }
            );

        $mockFormElm = m::mock(\Zend\Form\ElementInterface::class);
        $mockFormElm->shouldReceive('render')->andReturn(self::STANDARD_RENDER_RESULT);

        $mockView = m::mock('Zend\View\Renderer\PhpRenderer');
        $mockView->shouldReceive('plugin')->with('FormElement')->andReturn($mockFormElm);
        $mockView->shouldReceive('plugin')->with('escapehtml')->andReturn($mockHtmlHelper);
        $mockView->shouldReceive('plugin')->with('readonlyformitem')->andReturn($mockElementHelper);
        $mockView->shouldReceive('plugin')->with('readonlyformselect')->andReturn($mockElementHelper);
        $mockView->shouldReceive('plugin')->with('readonlyformtable')->andReturn($mockTableHelper);

        $sut = new FormRow();

        $sut->setView($mockView);
        $sut->setTranslator($mockTranslater);

        $expected = (($expected !== null) ? $expected : $sut);
        $this->assertEquals($expected, $sut($element));
    }

    public function provideTestInvoke()
    {
        //need tests for Select, TextArea
        $mockHidden = m::mock(\Zend\Form\ElementInterface::class);
        $mockHidden->shouldReceive('getAttribute')->with('type')->andReturn('hidden');

        /** @var m\MockInterface $mockRemoveIfReadOnly */
        $mockRemoveIfReadOnly = m::mock(\Zend\Form\ElementInterface::class);
        $mockRemoveIfReadOnly
            ->shouldReceive('getAttribute')->with('type')->andReturnNull()
            ->shouldReceive('getAttribute')->with('class')->andReturnNull('unit_CssClass')
            ->shouldReceive('getOption')->with('remove_if_readonly')->andReturn(true);

        $mockText = m::mock(\Zend\Form\ElementInterface::class);
        $mockText
            ->shouldReceive('getAttribute')->with('type')->andReturn('textarea')
            ->shouldReceive('getAttribute')->with('class')->andReturnNull()
            ->shouldReceive('getLabel')->andReturn('Label')
            ->shouldReceive('getValue')->andReturn('Value')
            ->shouldReceive('getOption')->with('remove_if_readonly')->andReturnNull();

        $mockSelect = m::mock(ZendElement\Select::class);
        $mockSelect
            ->shouldReceive('getAttribute')->with('type')->andReturn('select')
            ->shouldReceive('getLabel')->andReturn('Label')
            ->shouldReceive('getValue')->andReturn('Value')
            ->shouldReceive('getLabelOption')->andReturn(false)
            ->shouldReceive('getAttribute')->andReturn(false)
            ->shouldReceive('getOption')->with('remove_if_readonly')->andReturnNull();

        $mockHtmlTranslated = m::mock(\Common\Form\Elements\Types\HtmlTranslated::class);
        $mockHtmlTranslated
            ->shouldReceive('getValue')->andReturn('<b>text</b>')
            ->shouldReceive('getAttribute')->andReturn('html-translated')
            ->shouldReceive('getOption')->with('remove_if_readonly')->andReturnNull()
            ->shouldReceive('getLabel')->andReturn('')
            ->shouldReceive('getLabelOption')->andReturn(false);

        $columns = [
            0 => [
                'name' => 'foo',
            ],
            1 => [
                'name' => 'checkbox',
                'type' => 'Checkbox',
            ],
        ];
        $newColumns = [
            0 => [
                'name' => 'foo',
            ],
        ];
        $mockTableBuilder = m::mock(\Common\Service\Table\TableBuilder::class);
        $mockTableBuilder
            ->shouldReceive('setDisabled')->with(true)
            ->shouldReceive('getColumns')->andReturn($columns)
            ->shouldReceive('setColumns')->with($newColumns);

        $mockTable = m::mock(Elements\Types\Table::class);
        $mockTable
            ->shouldReceive('getAttribute')->with('type')->andReturnNull()
            ->shouldReceive('getOption')->with('remove_if_readonly')->andReturnNull()
            ->shouldReceive('getTable')->andReturn($mockTableBuilder)
            ->shouldReceive('render')->andReturn('<table></table>');

        return [
            [
                'element' => null,
                'expected' => null,
            ],
            [$mockHidden, ''],
            [$mockRemoveIfReadOnly, ''],
            'text' => [
                'element' => $mockText,
                'expect' => '<li class="definition-list__item readonly">' .
                    '<dt>@_Label_@</dt><dd>_Value_</dd></li>',
            ],
            'select' => [
                'element' => $mockSelect,
                'expect' => '<li class="definition-list__item readonly"><dt>@_Label_@</dt><dd>_Value_</dd></li>',
            ],
            [$mockTable, '<table></table>'],
            'htmlTranslated' => [
                'element' => $mockHtmlTranslated,
                'expect' => '<li class="definition-list__item readonly">' .
                    '<dt>@@</dt><dd>' . self::STANDARD_RENDER_RESULT . '</dd></li>',
            ],
            'htmlTranslatedNoLabel' => [
                'element' => m::mock(\Common\Form\Elements\Types\HtmlTranslated::class)
                        ->shouldReceive('getValue')->andReturn('<b>text</b>')
                        ->shouldReceive('getAttribute')->andReturn('unit_CssClass')
                        ->shouldReceive('getOption')->andReturnNull()
                        ->shouldReceive('getLabel')->andReturn(null)
                        ->shouldReceive('getLabelOption')->andReturnNull()
                        ->getMock(),
                'expect' => '<li class="definition-list__item readonly">STANDARD-RENDER-RESULT</li>',
            ],
            [
                'element' => m::mock(ZendElement\Csrf::class),
                'expected' => self::STANDARD_RENDER_RESULT,
            ],
            [
                'element' => m::mock(Elements\InputFilters\ActionButton::class)
                    ->shouldReceive('getOption')->with('keepForReadonly')->andReturn(true)
                    ->getMock(),
                'expected' => self::STANDARD_RENDER_RESULT,
            ],
            [
                'element' => m::mock(Elements\InputFilters\ActionButton::class)
                    ->shouldReceive('getOption')->with('keepForReadonly')->andReturn(false)
                    ->getMock(),
                'expected' => '',
            ],
            [
                'element' => m::mock(Elements\Types\AttachFilesButton::class),
                'expected' => '',
            ],
        ];
    }
}
