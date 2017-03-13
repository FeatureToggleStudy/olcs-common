<?php

/**
 * Transport Manager Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Service\Helper;

use Common\Service\Data\CategoryDataService;

/**
 * Transport Manager Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerHelperService extends AbstractHelperService
{
    public function getCertificateFiles($tmId)
    {
        return $this->getServiceLocator()->get('Entity\TransportManager')
            ->getDocuments(
                $tmId,
                null,
                null,
                CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
            );
    }

    public function getCertificateFileData($tmId, $file)
    {
        return [
            'transportManager' => $tmId,
            'description' => $file['name'],
            'issuedDate' => $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s'),
            'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
        ];
    }

    public function alterResponsibilitiesFieldset($fieldset, $ocOptions, $otherLicencesTable)
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $fieldset->get('operatingCentres')->setValueOptions($ocOptions);

        $formHelper->removeOption($fieldset->get('tmType'), 'tm_t_b');

        $formHelper->populateFormTable($fieldset->get('otherLicences'), $otherLicencesTable);
    }

    public function getResponsibilityFileData($tmId)
    {
        return [
            'transportManager' => $tmId,
            'issuedDate' => $this->getServiceLocator()->get('Helper\Date')->getDate(\DateTime::W3C),
            'description' => 'Additional information',
            'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL
        ];
    }

    public function getConvictionsAndPenaltiesTable($transportManagerId)
    {
        $result = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\PreviousConviction\GetList::create(['transportManager' => $transportManagerId])
        );
        $results = $result['results'];

        return $this->getServiceLocator()->get('Table')->prepareTable(
            'tm.convictionsandpenalties',
            $results
        );
    }

    /**
     * Execute a query DTO
     *
     * @param \Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return array of results
     * @throws \RuntimeException
     */
    protected function handleQuery($dto)
    {
        $annotationBuilder = $this->getServiceLocator()->get('TransferAnnotationBuilder');
        $queryService = $this->getServiceLocator()->get('QueryService');
        $response = $queryService->send($annotationBuilder->createQuery($dto));

        if (!$response->isOk()) {
            throw new \RuntimeException('Error fetching query '. get_class($dto));
        }

        return $response->getResult();
    }


    public function getPreviousLicencesTable($transportManagerId)
    {
        $result = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\OtherLicence\GetList::create(['transportManager' => $transportManagerId])
        );

        $results = $result['results'];

        return $this->getServiceLocator()->get('Table')->prepareTable(
            'tm.previouslicences',
            $results
        );
    }

    /**
     * This method superseeds alterPreviousHistoryFieldset
     *
     * @param \Zend\Form\Fieldset $fieldset
     * @param array               $tm
     */
    public function alterPreviousHistoryFieldsetTm($fieldset, $tm)
    {
        $convictionsAndPenaltiesTable = $this->getServiceLocator()->get('Table')->prepareTable(
            'tm.convictionsandpenalties',
            $tm['previousConvictions']
        );
        $previousLicencesTable = $this->getServiceLocator()->get('Table')->prepareTable(
            'tm.previouslicences',
            $tm['otherLicences']
        );

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $formHelper->populateFormTable(
            $fieldset->get('convictions'),
            $convictionsAndPenaltiesTable,
            'convictions'
        );
        $formHelper->populateFormTable(
            $fieldset->get('previousLicences'),
            $previousLicencesTable,
            'previousLicences'
        );
    }

    public function alterPreviousHistoryFieldset($fieldset, $tmId)
    {
        $transportManager = $this->getTransportManager($tmId);
        $convictionsAndPenaltiesTable = $this->getConvictionsAndPenaltiesTable($transportManager['id']);
        $previousLicencesTable = $this->getPreviousLicencesTable($transportManager['id']);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $formHelper->populateFormTable(
            $fieldset->get('convictions'),
            $convictionsAndPenaltiesTable,
            'convictions'
        );
        $formHelper->populateFormTable(
            $fieldset->get('previousLicences'),
            $previousLicencesTable,
            'previousLicences'
        );

        if (!is_null($transportManager['removedDate'])) {
            $fieldset->get('convictions')->get('table')->getTable()->setDisabled(true);
            $fieldset->get('previousLicences')->get('table')->getTable()->setDisabled(true);

            // remove hyperlinks from table
            $column = $fieldset->get('convictions')->get('table')->getTable()->getColumn('convictionDate');
            unset($column['type']);
            $fieldset->get('convictions')->get('table')->getTable()->setColumn('convictionDate', $column);

            $column = $fieldset->get('previousLicences')->get('table')->getTable()->getColumn('licNo');
            unset($column['type']);
            $fieldset->get('previousLicences')->get('table')->getTable()->setColumn('licNo', $column);
        }
    }

    private function getTransportManager($tmId)
    {
        return $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Tm\TransportManager::create(
                [
                    'id' => $tmId
                ]
            )
        );
    }

    /**
     * This method superseeds prepareOtherEmploymentTable
     *
     * @param \Zend\Form\Element $element
     * @param array              $tm      Transport Manager data
     */
    public function prepareOtherEmploymentTableTm($element, $tm)
    {
        $table = $this->getServiceLocator()->get('Table')->prepareTable('tm.employments', $tm['employments']);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $formHelper->populateFormTable($element, $table, 'employment');
    }

    public function prepareOtherEmploymentTable($element, $tmId)
    {
        $table = $this->getOtherEmploymentTable($tmId);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $formHelper->populateFormTable($element, $table, 'employment');
    }

    public function getOtherEmploymentTable($tmId)
    {
        $results = $this->getServiceLocator()->get('Entity\TmEmployment')->getAllEmploymentsForTm($tmId);

        return $this->getServiceLocator()->get('Table')->prepareTable('tm.employments', $results);
    }

    public function getOtherEmploymentData($id)
    {
        $employment = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\TmEmployment\GetSingle::create(
                [
                    'id' => $id
                ]
            )
        );

        $data = [
            'tm-employment-details' => [
                'id' => $employment['id'],
                'version' => $employment['version'],
                'position' => $employment['position'],
                'hoursPerWeek' => $employment['hoursPerWeek'],
            ],
            'tm-employer-name-details' => [
                'employerName' => $employment['employerName']
            ]
        ];

        if (isset($employment['contactDetails']['address'])) {
            $data['address'] = $employment['contactDetails']['address'];
        }

        return $data;
    }
}
