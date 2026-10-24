<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Controller;

use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiBase\Controller\Traits\AppendDataToPluginVariablesTrait;
use Maispace\MaiBase\Controller\Traits\PageRendererTrait;
use Maispace\MaiJobs\Domain\Model\Application;
use Maispace\MaiJobs\Domain\Model\Job;
use Maispace\MaiJobs\Domain\Repository\JobRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * List over Job doktype pages. Detail renders the current Job page (+ application form).
 */
class JobController extends AbstractActionController
{
    use AppendDataToPluginVariablesTrait;
    use PageRendererTrait;

    public function __construct(
        private readonly JobRepository $jobRepository,
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function injectPageRenderer(PageRenderer $pageRenderer): void
    {
        $this->pageRenderer = $pageRenderer;
    }

    public function injectAssetCollector(AssetCollector $assetCollector): void
    {
        $this->assetCollector = $assetCollector;
    }

    public function listAction(): ResponseInterface
    {
        $settings = $this->getSettings();

        $pageUids = $this->resolveParentPageUids();
        $categoryUid = (int) ($settings['categoryUid'] ?? 0);
        $statusFilter = (string) ($settings['statusFilter'] ?? '');

        if ($pageUids !== [] && $categoryUid > 0 && $statusFilter !== '') {
            $jobs = $this->jobRepository->findFromPagesByCategoryAndStatus($pageUids, $categoryUid, $statusFilter);
        } elseif ($pageUids !== [] && $categoryUid > 0) {
            $jobs = $this->jobRepository->findFromPagesByCategory($pageUids, $categoryUid);
        } elseif ($pageUids !== [] && $statusFilter !== '') {
            $jobs = $this->jobRepository->findFromPagesByStatus($pageUids, $statusFilter);
        } elseif ($pageUids !== []) {
            $jobs = $this->jobRepository->findFromPages($pageUids);
        } elseif ($categoryUid > 0) {
            $jobs = $this->jobRepository->findByCategoryUid($categoryUid);
        } elseif ($statusFilter !== '') {
            $jobs = $this->jobRepository->findByStatus($statusFilter);
        } else {
            $jobs = $this->jobRepository->findAll();
        }

        $categories = $this->resolveCategories($settings);

        $this->view->assignMultiple([
            'jobs' => $jobs,
            'categories' => $categories,
            'activeCategoryUid' => $categoryUid,
            'statusFilter' => $statusFilter,
            'settings' => $settings,
        ]);

        return $this->htmlResponse();
    }

    public function detailAction(?Job $job = null): ResponseInterface
    {
        if ($job === null) {
            $job = $this->resolveJobFromCurrentPage();
        }

        if ($job === null) {
            $message = LocalizationUtility::translate('job.notFound', 'mai_jobs') ?? 'Job not found.';
            return $this->htmlResponse('<p class="mai-jobs__empty">' . htmlspecialchars($message) . '</p>');
        }

        $this->view->assignMultiple([
            'job' => $job,
            'application' => new Application(),
            'settings' => $this->getSettings(),
        ]);

        return $this->htmlResponse();
    }

    private function resolveJobFromCurrentPage(): ?Job
    {
        $routing = $this->request->getAttribute('routing');
        $pageUid = $routing instanceof PageArguments ? $routing->getPageId() : 0;
        if ($pageUid <= 0) {
            return null;
        }

        return $this->jobRepository->findByUid($pageUid);
    }

    /**
     * @return list<int>
     */
    private function resolveParentPageUids(): array
    {
        $pages = trim((string) ($this->settings['pages'] ?? ''));
        if ($pages === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(static fn(string $uid): int => (int) $uid, explode(',', $pages)),
            static fn(int $uid): bool => $uid > 0,
        ));
    }

    /**
     * @param array<string, mixed> $settings
     * @return list<array{uid: int|string, title: string}>
     */
    private function resolveCategories(array $settings): array
    {
        $categoryUids = $settings['categoryUids'] ?? '';
        if (empty($categoryUids)) {
            return [];
        }

        $uids = array_filter(
            array_map('intval', explode(',', (string) $categoryUids)),
            static fn(int $uid): bool => $uid > 0,
        );

        $categories = [];
        foreach ($uids as $uid) {
            $qb = $this->connectionPool->getQueryBuilderForTable('sys_category');
            $row = $qb->select('uid', 'title')
                ->from('sys_category')
                ->where($qb->expr()->eq('uid', $qb->createNamedParameter($uid, Connection::PARAM_INT)))
                ->executeQuery()
                ->fetchAssociative();
            if ($row !== false) {
                $categories[] = $row;
            }
        }

        return $categories;
    }
}
