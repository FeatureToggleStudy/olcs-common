<?php

/**
 * FormElement Test
 *
 * @author Jakub Igla <jakub.igla@gmail.com>
 */
namespace CommonTest\Form\View\Helper;

use Zend\View\HelperPluginManager;
use Zend\View\Renderer\JsonRenderer;
use Zend\Form\View\Helper;

/**
 * FormElement Test
 *
 * @author Jakub Igla <jakub.igla@gmail.com>
 */
class FormElementTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Zend\Form\Element
     */
    protected $element;

    private function prepareElement($type = 'Text', $options = array())
    {
        if (strpos($type, '\\') === false) {
            $type = '\Zend\Form\Element\\' . ucfirst($type);
        }

        $options = array_merge(
            array(
                'type' => $type,
                'label' => 'Label',
                'hint' => 'Hint',
            ),
            $options
        );

        $this->element = new $type('test');
        $this->element->setOptions($options);
        $this->element->setAttribute('class', 'class');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderWithNoRendererPlugin()
    {
        $this->prepareElement();
        $view = new JsonRenderer();

        $viewHelper = new \Common\Form\View\Helper\FormElement();
        $viewHelper->setView($view);
        $viewHelper($this->element, 'formElement', '/');

        $this->expectOutputString('');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForTextElement()
    {
        $this->prepareElement();

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex(
            '/^<input type="text" name="(.*)" class="(.*)" id="(.*)" '.
            'value="(.*)"> \\r\\n <div class="hint">(.*)<\/div>$/'
        );
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForPlainTextElement()
    {
        $this->prepareElement('\Common\Form\Elements\Types\PlainText');

        $viewHelper = $this->prepareViewHelper();
        $this->element->setValue('plain');

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^plain$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForActionLinkElementWithRoute()
    {
        $options = ['route' => 'route'];
        $this->prepareElement('\Common\Form\Elements\InputFilters\ActionLink', $options);

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<a href="(.*)" class="(.*)">(.*)<\/a>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForActionLinkElementWithUrl()
    {
        $this->prepareElement('\Common\Form\Elements\InputFilters\ActionLink');
        $this->element->setValue('url');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<a href="(.*)" class="(.*)">(.*)<\/a>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlElement()
    {
        $this->prepareElement('\Common\Form\Elements\Types\Html');
        $this->element->setValue('<div></div>');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div><\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForTermsBoxElement()
    {
        $this->prepareElement('\Common\Form\Elements\Types\TermsBox');
        $this->element->setValue('foo');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div name="test" class="class&#x20;terms--box" id="test">foo<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForTermsBoxElementWithoutClass()
    {
        $this->prepareElement('\Common\Form\Elements\Types\TermsBox');
        $this->element->setAttribute('class', null);
        $this->element->setValue('foo');

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div name="test" class="&#x20;terms--box" id="test">foo<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlTranslatedElement()
    {
        $this->prepareElement('\Common\Form\Elements\Types\HtmlTranslated');
        $this->element->setValue('some-translation-key');

        $translations = ['some-translation-key' => 'actual translated string'];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^actual translated string$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlTranslatedElementWithoutValue()
    {
        $this->prepareElement('\Common\Form\Elements\Types\HtmlTranslated');

        $viewHelper = $this->prepareViewHelper([]);

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEmpty($markup);
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlTranslatedElementWithTokens()
    {
        $this->prepareElement('\Common\Form\Elements\Types\HtmlTranslated');
        $this->element->setValue('<div>%s and then %s</div>');
        $this->element->setTokens(['foo-key', 'bar-key']);

        $translations = [
            'foo-key' => 'foo string',
            'bar-key' => 'bar string'
        ];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div>foo string and then bar string<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForHtmlTranslatedElementWithTokensViaOptions()
    {
        $this->prepareElement('\Common\Form\Elements\Types\HtmlTranslated');
        $this->element->setValue('<div>%s and then %s</div>');
        $this->element->setOptions(['tokens' => ['foo-key', 'bar-key']]);

        $translations = [
            'foo-key' => 'foo string',
            'bar-key' => 'bar string'
        ];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div>foo string and then bar string<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForTableElement()
    {
        $this->prepareElement('\Common\Form\Elements\Types\Table');

        $mockTable = $this->getMockBuilder('\Common\Service\Table\TableBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('render'))
            ->getMock();

        $mockTable->expects($this->any())
            ->method('render')
            ->will($this->returnValue('<table></table>'));

        $this->element->setTable($mockTable);

        $viewHelper = $this->prepareViewHelper();

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<table><\/table>$/');
    }

    private function prepareViewHelper($translateMap = null)
    {
        $translator = new \CommonTest\Util\DummyTranslator();
        if (!is_null($translateMap)) {
            $translator->setMap($translateMap);
        }

        $translateHelper = new \Zend\I18n\View\Helper\Translate();
        $translateHelper->setTranslator($translator);

        $view = $this->getMock('\Zend\View\Renderer\PhpRenderer', array('url'));
        $view->expects($this->any())
            ->method('url')
            ->will($this->returnValue('url'));

        $plainTextService = new \Common\Form\View\Helper\FormPlainText();
        $plainTextService->setTranslator($translator);
        $plainTextService->setView($view);

        $helpers = new HelperPluginManager();
        $helpers->setService('form_text', new Helper\FormText());
        $helpers->setService('form_input', new Helper\FormInput());
        $helpers->setService('form_file', new Helper\FormFile());
        $helpers->setService('translate', $translateHelper);
        $helpers->setService('form_plain_text', $plainTextService);
        $helpers->setService('form', new Helper\Form());

        $view->setHelperPluginManager($helpers);

        $viewHelper = new \Common\Form\View\Helper\FormElement();
        $viewHelper->setView($view);

        return $viewHelper;
    }

    public function testRenderForTrafficAreaSet()
    {
        $this->prepareElement('\\Common\Form\Elements\Types\TrafficAreaSet');

        $this->element->setValue('ABC');

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEquals(
            '<p>trafficAreaSet.trafficArea</p><h4>ABC</h4><p class="hint">Hint</p>',
            $markup
        );
    }

    public function testRenderForTrafficAreaSetWithoutHint()
    {
        $this->prepareElement('\\Common\Form\Elements\Types\TrafficAreaSet');

        $this->element->setValue('ABC');
        $this->element->setOption('hint', null);

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEquals(
            '<p>trafficAreaSet.trafficArea</p><h4>ABC</h4>',
            $markup
        );
    }

    public function testRenderForTrafficAreaSetWithSuffix()
    {
        $this->prepareElement('\\Common\Form\Elements\Types\TrafficAreaSet');

        $this->element->setValue('ABC');
        $this->element->setOption('hint-suffix', '-foo');

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEquals(
            '<p>trafficAreaSet.trafficArea</p><h4>ABC</h4><p class="hint">Hint</p>',
            $markup
        );
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForGuidanceTranslatedElement()
    {
        $this->prepareElement('\Common\Form\Elements\Types\GuidanceTranslated');
        $this->element->setValue('some-translation-key');

        $translations = ['some-translation-key' => 'actual translated string'];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div class="guidance">actual translated string<\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForGuidanceTranslatedElementWithoutValue()
    {
        $this->prepareElement('\Common\Form\Elements\Types\GuidanceTranslated');

        $viewHelper = $this->prepareViewHelper([]);

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $this->assertEmpty($markup);
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForGuidanceTranslatedElementWithTokens()
    {
        $this->prepareElement('\Common\Form\Elements\Types\GuidanceTranslated');
        $this->element->setValue('<div>%s and then %s</div>');
        $this->element->setTokens(['foo-key', 'bar-key']);

        $translations = [
            'foo-key' => 'foo string',
            'bar-key' => 'bar string'
        ];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div class="guidance"><div>foo string and then bar string<\/div><\/div>$/');
    }

    /**
     * @outputBuffering disabled
     */
    public function testRenderForGuidanceTranslatedElementWithTokensViaOptions()
    {
        $this->prepareElement('\Common\Form\Elements\Types\GuidanceTranslated');
        $this->element->setValue('<div>%s and then %s</div>');
        $this->element->setOptions(['tokens' => ['foo-key', 'bar-key']]);

        $translations = [
            'foo-key' => 'foo string',
            'bar-key' => 'bar string'
        ];
        $viewHelper = $this->prepareViewHelper($translations);

        echo $viewHelper($this->element, 'formCollection', '/');

        $this->expectOutputRegex('/^<div class="guidance"><div>foo string and then bar string<\/div><\/div>$/');
    }

    public function testRenderForAttachFilesButton()
    {
        $this->prepareElement('\\Common\Form\Elements\Types\AttachFilesButton');

        $this->element->setValue('My Button');

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $expected = '<ul class="attach-action__list"><li class="attach-action">'
            . '<label class="attach-action__label"> '
            . '<input type="file" name="test" class="class&#x20;attach-action__input" id="test">'
            . '</label>'
            . '<p class="attach-action__hint">Hint</p></li></ul>';

        $this->assertEquals(
            $expected,
            $markup
        );
    }

    public function testRenderForAttachFilesButtonWithNoClass()
    {
        $this->prepareElement('\\Common\Form\Elements\Types\AttachFilesButton');

        $this->element->setValue('My Button');
        $this->element->setAttribute('class', null);

        $viewHelper = $this->prepareViewHelper();

        $markup = $viewHelper($this->element, 'formCollection', '/');

        $expected = '<ul class="attach-action__list"><li class="attach-action">'
            . '<label class="attach-action__label"> '
            . '<input type="file" name="test" class="&#x20;attach-action__input" id="test">'
            . '</label>'
            . '<p class="attach-action__hint">Hint</p></li></ul>';

        $this->assertEquals(
            $expected,
            $markup
        );
    }
}
