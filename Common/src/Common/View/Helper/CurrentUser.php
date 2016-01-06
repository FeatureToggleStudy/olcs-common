<?php

/**
 * Current User view helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Common\View\Helper;

use Common\Rbac\User;
use Zend\View\Helper\AbstractHelper;
use ZfcRbac\Service\AuthorizationService;

/**
 * Current User view helper
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CurrentUser extends AbstractHelper
{
    protected $userData;

    /**
     * @var AuthorizationService
     */
    private $authService;

    public function __construct(AuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function getFullName()
    {
        if (!$this->isLoggedIn()) {
            return 'Not logged in';
        }

        $userData = $this->getUserData();

        $name = $this->view->personName($userData['contactDetails']['person'], ['forename', 'familyName']);

        if (empty(trim($name))) {
            $name = $userData['loginId'];
        }

        return $name;
    }

    /**
     * Get organisation name
     *
     * @return string
     */
    public function getOrganisationName()
    {
        if (!$this->isLoggedIn()) {
            return '';
        }

        $userData = $this->getUserData();

        switch ($userData['userType']) {
            case User::USER_TYPE_OPERATOR:
            case User::USER_TYPE_TRANSPORT_MANAGER:
                return current($userData['organisationUsers'])['organisation']['name'];
            case User::USER_TYPE_PARTNER:
                return $userData['partnerContactDetails']['description'];
            case User::USER_TYPE_LOCAL_AUTHORITY:
                return $userData['localAuthority']['description'];
        }

        return '';
    }

    /**
     * Checks whether the current user is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        $userData = $this->getUserData();
        return (!empty($userData['userType']) && ($userData['userType'] !== User::USER_TYPE_ANON));
    }

    /**
     * Get the user's unique id
     *
     * @return string
     */
    public function getUniqueId()
    {
        if (!$this->isLoggedIn()) {
            return '';
        }

        $userData = $this->getUserData();

        return hash('sha256', $userData['pid']);
    }

    /**
     * Get user data
     *
     * @return array
     */
    private function getUserData()
    {
        if (!$this->userData) {
            if ($this->authService->getIdentity()) {
                $this->userData = $this->authService->getIdentity()->getUserData();
            }
        }

        return $this->userData;
    }
}
