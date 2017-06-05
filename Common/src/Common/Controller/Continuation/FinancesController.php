<?php

namespace Common\Controller\Continuation;

use Common\Form\Model\Form\Continuation\Start;
use Zend\View\Model\ViewModel;

/**
 * FinancesController
 */
class FinancesController extends AbstractContinuationController
{
    /**
     * Index page
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $continuationDetailId = $this->getContinuationDetailId();
        // @todo Create new form
        $form = $this->getForm(Start::class);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $this->redirect()->toRoute('continuation/declaration', [], [], true);
            }
        }

        return $this->getViewModel('[THIS IS THE LIC NO]', $form);
    }
}
