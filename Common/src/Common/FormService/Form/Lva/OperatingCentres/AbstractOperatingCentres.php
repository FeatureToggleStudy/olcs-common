<?php

/**
 * Abstract Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\AbstractFormService;
use Zend\Form\Form;

/**
 * Abstract Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractOperatingCentres extends AbstractFormService
{
    protected $mainTableConfigName = 'lva-operating-centres';

    public function getForm($params)
    {
        $form = $this->getFormHelper()->createForm('Lva\OperatingCentres');

        $table = $this->getFormServiceLocator()->getServiceLocator()->get('Table')
            ->prepareTable($this->mainTableConfigName, $params['operatingCentres']);

        $this->getFormHelper()->populateFormTable($form->get('table'), $table);

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm(Form $form, array $params)
    {
        if (!$params['canHaveSchedule41']) {
            $form->get('table')->get('table')->getTable()->removeAction('schedule41');
        }

        if (!$params['canHaveCommunityLicences']) {
            $this->getFormHelper()->remove($form, 'data->totCommunityLicences');
        }

        if ($params['isPsv']) {
            $this->alterFormForPsvLicences($form, $params);
            $this->alterFormTableForPsv($form);
        } else {
            $this->alterFormForGoodsLicences($form);
        }

        // modify the table validation message
        $this->getFormHelper()
            ->getValidator($form, 'table->table', 'Common\Form\Elements\Validators\TableRequiredValidator')
            ->setMessage('OperatingCentreNoOfOperatingCentres.required', 'required');

        if (empty($params['operatingCentres'])) {
            $this->getFormHelper()->remove($form, 'dataTrafficArea');

            return $form;
        }

        $trafficArea = isset($params['licence']['trafficArea'])
            ? $params['licence']['trafficArea']
            : $params['trafficArea'];

        $trafficAreaId = $trafficArea ? $trafficArea['id'] : null;

        $dataTrafficAreaFieldset = $form->get('dataTrafficArea');

        if (!empty($trafficAreaId)) {

            $dataTrafficAreaFieldset->get('enforcementArea')
                ->setValueOptions($params['possibleEnforcementAreas']);

            $this->getFormHelper()->remove($form, 'dataTrafficArea->trafficArea');

            $dataTrafficAreaFieldset->get('trafficAreaSet')
                ->setValue($trafficArea['name'])
                ->setOption('hint-suffix', '-operating-centres');

            return $form;
        }

        $dataTrafficAreaFieldset->remove('trafficAreaSet')
            ->remove('enforcementArea')
            ->get('trafficArea')
            ->setValueOptions($params['possibleTrafficAreas']);

        return $form;
    }

    protected function alterFormForPsvLicences(Form $form, array $params)
    {
        $dataOptions = $form->get('data')->getOptions();
        $dataOptions['hint'] .= '.psv';
        $form->get('data')->setOptions($dataOptions);

        $removeFields = [
            'totAuthTrailers'
        ];

        if (!$params['canHaveLargeVehicles']) {
            $removeFields[] = 'totAuthLargeVehicles';
        }

        $this->getFormHelper()->removeFieldList($form, 'data', $removeFields);
    }

    protected function alterFormTableForPsv(Form $form)
    {
        $table = $form->get('table')->get('table')->getTable();

        $table->removeColumn('noOfTrailersRequired');

        $footer = $table->getFooter();
        if (isset($footer['total']['content'])) {
            $footer['total']['content'] .= '-psv';
            unset($footer['trailersCol']);
            $table->setFooter($footer);
        }
    }

    protected function alterFormForGoodsLicences(Form $form)
    {
        $removeFields = [
            'totAuthSmallVehicles',
            'totAuthMediumVehicles',
            'totAuthLargeVehicles'
        ];

        $this->getFormHelper()->removeFieldList($form, 'data', $removeFields);
    }
}