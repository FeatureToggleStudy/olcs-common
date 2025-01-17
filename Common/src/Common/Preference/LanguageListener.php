<?php

namespace Common\Preference;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Common\Service\Helper\FlashMessengerHelperService;
use Zend\I18n\Translator\Translator;

/**
 * Language Listener
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LanguageListener implements ListenerAggregateInterface, FactoryInterface
{
    use ListenerAggregateTrait;

    /**
     * @var Language
     */
    private $languagePref;

    /**
     * @var FlashMessengerHelperService
     */
    private $flashMessenger;

    /**
     * @var Translator
     */
    private $translator;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->languagePref = $serviceLocator->get('LanguagePreference');
        $this->flashMessenger = $serviceLocator->get('Helper\FlashMessenger');
        $this->translator = $serviceLocator->get('translator');

        return $this;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'), $priority);
    }

    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();
        if (!($request instanceof HttpRequest)) {
            return;
        }

        $lang = $request->getQuery('lang');

        if ($lang !== null) {
            try {
                $this->languagePref->setPreference($lang);

            } catch (\Exception $ex) {
                $this->flashMessenger->addCurrentErrorMessage('Only English and Welsh languages are supported');
            }
        }

        $this->translator->setLocale($this->languagePref->getPreference() . '_GB');
    }
}
