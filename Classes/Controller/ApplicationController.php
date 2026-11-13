<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Controller;

use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiJobs\Domain\Model\Application;
use Maispace\MaiJobs\Domain\Model\Job;
use Maispace\MaiJobs\Domain\Repository\ApplicationRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Attribute\RateLimit;

class ApplicationController extends AbstractActionController
{
    public function __construct(
        private readonly ApplicationRepository $applicationRepository,
        private readonly Context $context,
    ) {}

    /**
     * The form template renders the validation errors itself; the generic Extbase message
     * ("An error occurred while trying to call ...") would only duplicate them and expose class names.
     */
    protected function getErrorFlashMessage(): bool
    {
        return false;
    }

    #[IgnoreValidation(['argumentName' => 'application'])]
    public function applyAction(Job $job, ?Application $application = null): ResponseInterface
    {
        if ($application === null) {
            $application = new Application();
        }

        $this->view->assignMultiple([
            'job' => $job,
            'application' => $application,
            'settings' => $this->getSettings(),
        ]);

        return $this->htmlResponse();
    }

    #[RateLimit(limit: 3, interval: '1 hour')]
    public function confirmAction(Job $job, Application $application): ResponseInterface
    {
        if (!$this->isCaptchaValid()) {
            $this->flashCaptchaError();

            return $this->redirect('apply', null, null, ['job' => $job]);
        }

        $application->setJob($job);
        $application->setSubmittedAt((int) $this->context->getPropertyFromAspect('date', 'timestamp'));

        $this->applicationRepository->add($application);

        $this->view->assignMultiple([
            'job' => $job,
            'application' => $application,
            'settings' => $this->getSettings(),
        ]);

        return $this->htmlResponse();
    }
}
