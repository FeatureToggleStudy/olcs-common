<?php

/**
 * Generic Upload
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Common\Controller\Traits;

use Dvsa\Olcs\Transfer\Command\Document\CreateDocument;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument;

/**
 * Generic Upload
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait GenericUpload
{
    /**
     * Process files
     *
     * @param \Zend\Form\Form $form
     * @param string $selector - selector identifying the MultipleFileUpload element
     * @param callable $uploadCallback
     * @param callable $deleteCallback
     * @param callable $loadCallback
     * @param string $countSelector - optional selector identifying element to
     * update with number of files uploaded (e.g. for validation)
     * @return bool
     */
    public function processFiles(
        $form,
        $selector,
        $uploadCallback,
        $deleteCallback,
        $loadCallback,
        $countSelector = null
    ) {
        $uploadHelper = $this->getServiceLocator()->get('Helper\FileUpload');

        $uploadHelper->setForm($form)
            ->setSelector($selector)
            ->setUploadCallback($uploadCallback)
            ->setDeleteCallback($deleteCallback)
            ->setLoadCallback($loadCallback)
            ->setRequest($this->getRequest());

        if (!is_null($countSelector)) {
            $uploadHelper->setCountSelector($countSelector);
        }

        return $uploadHelper->process();
    }

    /**
     * Upload a file
     *
     * @param array $fileData
     * @param array $data
     * @return array
     */
    protected function uploadFile($fileData, $data)
    {
        $uploader = $this->getServiceLocator()->get('FileUploader')->getUploader();

        $uploader->setFile($fileData);

        $file = $uploader->upload();

        $docData = array_merge(
            array(
                'filename'   => $file->getName(),
                'identifier' => $file->getIdentifier(),
                'size'       => $file->getSize()
            ),
            $data
        );

        $response = $this->handleCommand(CreateDocument::create($docData));

        return $response->isOk();
    }

    /**
     * Delete file
     *
     * @param int $id
     */
    public function deleteFile($id)
    {
        $response = $this->handleCommand(DeleteDocument::create(['id' => $id]));

        return $response->isOk();
    }
}
