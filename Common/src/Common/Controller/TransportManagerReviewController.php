<?php

/**
 * Transport Manager Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller;

use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\Review;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Transport Manager Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerReviewController extends ZendAbstractActionController
{
    public function indexAction()
    {
        $response = $this->handleQuery(Review::create(['id' => $this->params('id')]));
        $data = $response->getResult();

        $view = new ViewModel(['content' => $data['markup']]);

        $view->setTerminal(true);
        $view->setTemplate('layout/blank');

        return $view;
    }
}
