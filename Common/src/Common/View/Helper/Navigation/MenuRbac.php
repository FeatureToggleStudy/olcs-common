<?php

namespace Common\View\Helper\Navigation;

use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Page\AbstractPage;
use Zend\View\Helper\Navigation\Menu;

/**
 * Navigation Menu with RBAC functions
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class MenuRbac extends Menu
{
    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param AbstractContainer $container [optional] container to operate on
     *
     * @return self
     */
    public function __invoke($container = null)
    {
        parent::__invoke($container);

        $this->filter();

        return $this;
    }

    /**
     * Filter pages by RBAC
     *
     * @param AbstractPage $container Page
     *
     * @return $this
     */
    public function filter(AbstractContainer $container = null)
    {
        if ($container === null) {
            $container = $this->getContainer();
        }

        $container->setPages(
            array_filter(
                $container->getPages(),
                function (AbstractPage $item) {
                    return $this->accept($item, false);
                }
            )
        );

        return $this;
    }
}
