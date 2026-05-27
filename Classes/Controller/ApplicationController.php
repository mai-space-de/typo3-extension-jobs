<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Controller;

use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiJobs\Domain\Model\Application;
use Maispace\MaiJobs\Domain\Model\Job;
use Maispace\MaiJobs\Domain\Repository\ApplicationRepository;
use Maispace\MaiJobs\Validation\Validator\MimeTypeValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;

class ApplicationController extends AbstractActionController
{
    public function __construct(
        private readonly ApplicationRepository $applicationRepository,
        private readonly Context $context,
    ) {}

    protected function initializeConfirmAction(): void
    {
        /** @var ConjunctionValidator $validator */
        $validator = $this->arguments['application']->getValidator();
        $validator->addValidator(GeneralUtility::makeInstance(MimeTypeValidator::class));
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

    public function confirmAction(Job $job, Application $application): ResponseInterface
    {
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
