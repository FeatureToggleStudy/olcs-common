<?php

/**
 * Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva;

use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\Application\Review;

/**
 * Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReviewController extends AbstractController implements Interfaces\AdapterAwareInterface
{
    use Traits\AdapterAwareTrait;

    public function indexAction()
    {
        $response = $this->handleQuery(Review::create(['id' => $this->params('application')]));
        $data = $response->getResult();

        $view = new ViewModel(['content' => $data['markup']]);

        $view->setTerminal(true);
        $view->setTemplate('layout/blank');

        return $view;
    }
}