<?php

/**
 * Application Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Common\Controller\Traits\Lva;

use Common\Service\Entity\ApplicationService;

/**
 * Application Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationControllerTrait
{
    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->isApplicationNew($applicationId)) {
            return $this->notFoundAction();
        }

        return $this->checkForRedirect($applicationId);
    }

    /**
     * Update application status
     *
     * @params int $applicationId
     */
    protected function updateCompletionStatuses($applicationId)
    {
        $this->getEntityService('ApplicationCompletion')->updateCompletionStatuses($applicationId);
    }

    /**
     * Check if the application is new
     *
     * @param int $applicationId
     * @return boolean
     */
    protected function isApplicationNew($applicationId)
    {
        return $this->getApplicationType($applicationId) === ApplicationService::APPLICATION_TYPE_NEW;
    }

    /**
     * Check if the application is variation
     *
     * @param int $applicationId
     * @return boolean
     */
    protected function isApplicationVariation($applicationId)
    {
        return $this->getApplicationType($applicationId) === ApplicationService::APPLICATION_TYPE_VARIATION;
    }

    /**
     * Get application type
     *
     * @param int $applicationId
     * @return int
     */
    protected function getApplicationType($applicationId)
    {
        return $this->getEntityService('Application')->getApplicationType($applicationId);
    }

    /**
     * Get application id
     *
     * @return int
     */
    protected function getApplicationId()
    {
        return $this->params('id');
    }

    /**
     * Get licence id
     *
     * @param int $applicationId
     * @return int
     */
    protected function getLicenceId($applicationId)
    {
        return $this->getEntityService('Application')->getLicenceIdForApplication($applicationId);
    }

    /**
     * Get type of licence data
     *
     * @return array
     */
    protected function getTypeOfLicenceData()
    {
        $licenceId = $this->getLicenceId($this->getApplicationId());

        return $this->getEntityService('Licence')->getTypeOfLicenceData($licenceId);
    }
}
