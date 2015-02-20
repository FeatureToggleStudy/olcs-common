<?php

/**
 * Abstract people adapter
 *
 * Contains common logic which used to just live in the abstract
 * people controller, i.e. the "plain" unmodified behaviour
 */
namespace Common\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractAdapter;
use Common\Controller\Lva\Interfaces\PeopleAdapterInterface;

/**
 * Abstract people adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractPeopleAdapter extends AbstractAdapter implements PeopleAdapterInterface
{
    protected $tableConfig = 'lva-people';

    public function addMessages($orgType)
    {
    }

    public function alterFormForOrganisation(Form $form, $table, $orgId, $orgType)
    {
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId, $orgType)
    {
    }

    public function canModify($orgId)
    {
        return true;
    }

    public function createTable($orgId)
    {
        return $this->getServiceLocator()
            ->get('Table')
            ->prepareTable($this->tableConfig, $this->getTableData($orgId));
    }

    /**
     * Get the table data for the main form
     *
     * @param int $orgId
     * @return array
     */
    protected function getTableData($orgId)
    {
        $results = $this->getServiceLocator()->get('Entity\Person')
            ->getAllForOrganisation($orgId);

        $final = array();
        foreach ($results['Results'] as $row) {
            // flatten the person's position if it's non null
            if (isset($row['position'])) {
                $row['person']['position'] = $row['position'];
            }
            $final[] = $row['person'];
        }
        return $final;
    }

    public function attachMainScripts()
    {
        $this->getServiceLocator()->get('Script')->loadFile('lva-crud');
    }
}
