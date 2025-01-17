<?php

namespace Common\View\Helper;

use Zend\View\Helper\FlashMessenger as ZendFlashMessenger;
use Zend\Mvc\Controller\Plugin\FlashMessenger as PluginFlashMessenger;

/**
 * Flash messenger view helper (Extends zends flash messenger)
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FlashMessenger extends ZendFlashMessenger
{
    /**
     * Templates for the open/close/separators for message tags
     *
     * @var string
     */
    protected $messageCloseString = '</p></div>';
    protected $messageOpenFormat = '<div %s><p role="alert">';
    protected $messageSeparatorString = '</p></div><div %s><p>';

    /**
     * Whether the template has already been rendered
     *
     * @var bool
     */
    protected $isRendered = false;

    /**
     * Holds the wrapper format
     *
     * @var string
     */
    private $wrapper = '<div class="notice-container">%s</div>';

    /**
     * Invoke
     *
     * @param string $namespace Namespace
     *
     * @return string
     */
    public function __invoke($namespace = null)
    {
        if ($namespace === 'norender') {
            return $this;
        }

        return $this->render();
    }

    /**
     * Get messages from namespace
     *
     * @param string $namespace Namespace
     *
     * @return array
     */
    public function getMessagesFromNamespace($namespace)
    {
        $fm = $this->getPluginFlashMessenger();

        return $fm->getMessagesFromNamespace($namespace);
    }

    /**
     * Set isRendered
     *
     * @param bool $isRendered Is rendered
     *
     * @return FlashMessenger
     */
    public function setIsRendered($isRendered)
    {
        $this->isRendered = $isRendered;
        return $this;
    }

    /**
     * Get isRendered
     *
     * @return bool
     */
    public function getIsRendered()
    {
        return $this->isRendered;
    }

    /**
     * Render messages
     *
     * @param string    $namespace  Namespace
     * @param array     $classes    Classes
     * @param bool|null $autoEscape AutoEscape
     *
     * @return string
     */
    public function render
    (
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $classes = [],
        $autoEscape = null
    ) {
        if ($this->getIsRendered()) {
            return '';
        }

        $markup = $this->renderAllFromNamespace('error', array('notice--danger'));
        $markup .= $this->renderAllFromNamespace('success', array('notice--success'));
        $markup .= $this->renderAllFromNamespace('warning', array('notice--warning'));
        $markup .= $this->renderAllFromNamespace('info', array('notice--info'));
        $markup .= $this->renderAllFromNamespace('default', array('notice--info'));

        if (empty($markup)) {
            return '';
        }

        $this->setIsRendered(true);

        return sprintf($this->wrapper, $markup);
    }

    /**
     * Render all from namespace
     *
     * @param string $namespace Namespace
     * @param array  $classes   Classes
     *
     * @return string
     */
    protected function renderAllFromNamespace
    (
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $classes = []
    ) {
        return parent::render($namespace, $classes) .
            $this->renderCurrent($namespace, $classes);
    }

    /**
     * Render current messages
     *
     * @param string    $namespace  Namespace
     * @param array     $classes    Classes
     * @param bool|null $autoEscape AutoEscape
     *
     * @return string
     */
    public function renderCurrent
    (
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $classes = [],
        $autoEscape = null
    ) {
        $content = parent::renderCurrent($namespace, $classes);

        $fmHelper = $this->getServiceLocator()->getServiceLocator()->get('Helper\FlashMessenger');

        $content .= $this->renderMessages(
            $namespace,
            $fmHelper->getCurrentMessages($namespace),
            $classes,
            $autoEscape
        );

        return $content;
    }

    /**
     * Majority of this is copied from Zend's however I have removed the code to escape html, as we need to display HTML
     * in our flash messengers, and we shouldn't ever need to escape it as our messages will never contain user entered
     * info
     *
     * @param string    $namespace  Namespace
     * @param array     $messages   Messages
     * @param array     $classes    Classes
     * @param bool|null $autoEscape AutoEscape
     *
     * @return string
     */
    protected function renderMessages(
        $namespace = PluginFlashMessenger::NAMESPACE_DEFAULT,
        array $messages = [],
        array $classes = [],
        $autoEscape = null
    ) {
        // Flatten message array
        $messagesToPrint = array();
        $translator = $this->getTranslator();
        $translatorTextDomain = $this->getTranslatorTextDomain();

        array_walk_recursive(
            $messages,
            function ($item) use (&$messagesToPrint, $translator, $translatorTextDomain) {
                if ($translator !== null) {
                    $item = $translator->translate(
                        $item,
                        $translatorTextDomain
                    );
                }

                $messagesToPrint[] = $item;
            }
        );

        if (empty($messagesToPrint)) {
            return '';
        }

        // Generate markup
        $markup = sprintf(
            $this->getMessageOpenFormat(),
            'class="' . implode(' ', $classes) . '"'
        );

        $markup .= implode(
            sprintf(
                $this->getMessageSeparatorString(),
                'class="' . implode(' ', $classes) . '"'
            ),
            $messagesToPrint
        );

        $markup .= $this->getMessageCloseString();

        return $markup;
    }
}
