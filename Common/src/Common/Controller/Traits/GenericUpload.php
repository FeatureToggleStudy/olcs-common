<?php

/**
 * Generic Upload
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Common\Controller\Traits;

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
     * @param Form $form
     * @param string $selector
     * @param string $uploadCallback
     * @param string $deleteCallback
     * @param string $loadCallback
     * @return bool
     */
    protected function processFiles($form, $selector, $uploadCallback, $deleteCallback, $loadCallback)
    {
        $uploadHelper = $this->getServiceLocator()->get('Helper\FileUpload');

        $uploadHelper->setForm($form)
            ->setSelector($selector)
            ->setUploadCallback($uploadCallback)
            ->setDeleteCallback($deleteCallback)
            ->setLoadCallback($loadCallback)
            ->setRequest($this->getRequest());

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
                'filename'      => $file->getName(),
                'identifier'    => $file->getIdentifier(),
                'size'          => $file->getSize(),
                'fileExtension' => 'doc_' . $file->getExtension()
            ),
            $data
        );
        return $this->getServiceLocator()->get('Entity\Document')->save($docData);
    }
}
