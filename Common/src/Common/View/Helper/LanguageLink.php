<?php

/**
 * Language Link
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Common\Preference\Language;

/**
 * Language Link
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LanguageLink extends AbstractHelper implements FactoryInterface
{
    /**
     * @var Language
     */
    private $languagePref;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->languagePref = $serviceLocator->getServiceLocator()->get('LanguagePreference');

        return $this;
    }

    public function __invoke()
    {
        if ($this->languagePref->getPreference() === Language::OPTION_CY) {
            return '<a href="?lang=en">English</a>';
        } else {
            return '<a href="?lang=cy">Cymraeg</a>';
        }
    }
}
