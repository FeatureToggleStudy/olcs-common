<?php

/**
 * Shared logic between Operating Centres controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Lva;

use Common\Controller\Lva\Interfaces\AdapterAwareInterface;

/**
 * Shared logic between Operating Centres controllers
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractOperatingCentresController extends AbstractController implements AdapterAwareInterface
{
    use Traits\CrudTableTrait,
        Traits\AdapterAwareTrait;

    protected $section = 'operating_centres';

    public function indexAction()
    {
        $this->getAdapter()->addMessages($this->getIdentifier());

        $request = $this->getRequest();

        $id = $this->getIdentifier();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = $this->getAdapter()->getOperatingCentresFormData($id);
        }

        $data = $this->getAdapter()->alterFormData($id, $data);

        $form = $this->getAdapter()->getMainForm()->setData($data);

        if ($request->isPost()) {

            $crudAction = $this->getCrudAction(array($data['table']));

            if ($crudAction !== null) {
                $this->getAdapter()->disableValidation($form);
            }

            if ($form->isValid()) {

                $this->getAdapter()->saveMainFormData($form->getData());

                $this->postSave('operating_centres');

                if ($crudAction !== null) {
                    return $this->handleCrudAction($crudAction);
                }

                return $this->completeSection('operating_centres');
            }
        }

        $this->alterForm($form);

        $this->getAdapter()->attachMainScripts();

        return $this->render('operating_centres', $form);
    }

    public function addAction()
    {
        return $this->addOrEdit('add');
    }

    public function editAction()
    {
        return $this->addOrEdit('edit');
    }

    protected function addOrEdit($mode)
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $data = $this->getAdapter()->alterFormDataOnPost($mode, $data, $this->params('child_id'));
        } else {
            if ($mode === 'edit') {
                $data = $this->getAdapter()->getAddressData($this->params('child_id'));
            } else {
                $data = [];
            }
            $data = $this->getAdapter()->formatCrudDataForForm($data, $mode);
        }

        $form = $this->getAdapter()->getActionForm($mode, $request)->setData($data);

        $hasProcessedPostcode = $this->getAdapter()->processAddressLookupForm($form, $request);

        if ($form->has('advertisements')) {
            $hasProcessedFiles = $this->processFiles(
                $form,
                'advertisements->file',
                array($this, 'processAdvertisementFileUpload'),
                array($this, 'deleteFile'),
                array($this->getAdapter(), 'getDocuments')
            );
        } else {
            $hasProcessedFiles = false;
        }

        if (!$hasProcessedFiles && !$hasProcessedPostcode && $request->isPost() && $form->isValid()) {

            $this->getAdapter()->saveActionFormData($mode, $data, $form->getData());

            return $this->handlePostSave();
        }

        $this->getServiceLocator()->get('Script')->loadFile('add-operating-centre');

        return $this->render($mode . '_operating_centre', $form);
    }

    protected function delete()
    {
        return $this->getAdapter()->delete();
    }

    /**
     * Handle the file upload
     *
     * @param array $file
     */
    public function processAdvertisementFileUpload($file)
    {
        $categoryService = $this->getServiceLocator()->get('category');

        // The top-level category is *always* application; this is correct
        $category = $categoryService->getCategoryByDescription('Application');
        $subCategory = $categoryService->getCategoryByDescription('Advert Digital', 'Document');

        $this->uploadFile(
            $file,
            array_merge(
                array(
                    'description' => 'Advertisement',
                    'category'    => $category['id'],
                    'subCategory' => $subCategory['id'],
                    'isExternal'  => $this->isExternal()
                ),
                $this->getAdapter()->getDocumentProperties()
            )
        );
    }

    /**
     * Gives a new translation key to use for the delete modal text.
     *
     * @return string The message translation key.
     */
    protected function getDeleteMessage()
    {
        return 'review-operating_centres_delete';
    }

    /**
     * Override the delete title.
     *
     * @return string The modal message key.
     */
    protected function getDeleteTitle()
    {
        return 'delete-oc';
    }

    protected function alterForm($form)
    {
        return $form;
    }
}
