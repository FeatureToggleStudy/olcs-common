<?php

/**
 * Document Dispatch Helper Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Common\Service\Helper;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Data\CategoryDataService;

/**
 * Document Dispatch Helper Service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentDispatchHelperService extends AbstractHelperService
{
    public function process($file, $params = [])
    {
        if (!isset($params['licence'])) {
            throw new \RuntimeException('Please provide a licence parameter');
        }

        if (!isset($params['description'])) {
            throw new \RuntimeException('Please provide a document description parameter');
        }

        $licenceId = $params['licence'];

        $description = $params['description'];

        $licence = $this->getServiceLocator()
            ->get('Entity\Licence')
            ->getWithOrganisation($licenceId);

        $organisation = $licence['organisation'];

        // we have to create the document early doors because we need its ID
        // if we're going to go on to email it
        $documentId = $this->getServiceLocator()->get('Entity\Document')->createFromFile($file, $params);

        if (!$organisation['allowEmail']) {
            return $this->attemptPrint($licence, $file, $description);
        }

        // all good; but we need to check we have >= 1 admin
        // user to send the email to
        $orgUsers = $this->getServiceLocator()
            ->get('Entity\Organisation')
            ->getAdminUsers($organisation['id']);

        $users = [];
        foreach ($orgUsers as $user) {
            if (isset($user['user']['emailAddress'])) {
                $details = $user['user']['contactDetails']['person'];
                $users[] = sprintf(
                    '%s %s <%s>',
                    $details['forename'],
                    $details['familyName'],
                    $user['user']['emailAddress']
                );
            }
        }

        if (empty($users)) {
            // oh well, fallback to a printout
            return $this->attemptPrint($licence, $file, $description);
        }

        $this->getServiceLocator()
            ->get('Entity\CorrespondenceInbox')
            ->save(
                [
                    'document' => $documentId,
                    'licence'  => $licenceId
                ]
            );

        $url = $this->getServiceLocator()->get('Helper\Url')->fromRouteWithHost(
            UrlHelperService::EXTERNAL_HOST,
            'correspondence_inbox'
        );

        $params = [
            $licence['licNo'],
            $url
        ];

        $this->getServiceLocator()
            ->get('Email')
            ->sendTemplate(
                $licence['translateToWelsh'],
                null,
                null,
                $users,
                'email.licensing-information.subject',
                'markup-email-dispatch-document',
                $params
            );

        // even if we've successfully emailed we always create a translation task for Welsh licences
        if ($licence['translateToWelsh']) {
            return $this->generateTranslationTask($licence, $description);
        }
    }

    private function attemptPrint($licence, $file, $description)
    {
        if ($licence['translateToWelsh']) {
            return $this->generateTranslationTask($licence, $description);
        }

        // okay; go ahead and print

        return $this->getServiceLocator()
            ->get('PrintScheduler')
            ->enqueueFile($file, $description);
    }

    private function generateTranslationTask($licence, $description)
    {
        $this->getServiceLocator()
            ->get('Entity\Task')
            ->save(
                [
                    'category' => CategoryDataService::CATEGORY_LICENSING,
                    'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_LICENSING_GENERAL_TASK,
                    'description' => 'Welsh translation required: ' . $description,
                    'actionDate' => $this->getServiceLocator()->get('Helper\Date')->getDate(),
                    'urgent' => 'Y',
                    'licence' => $licence['id'],
                    // @TODO: need proper auth solution here
                    'assignedToUser' => null,
                    'assignedToTeam' => null
                ]
            );
    }
}
