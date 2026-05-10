<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Domain\Repository;

use Maispace\MaiJobs\Domain\Model\Job;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class JobRepository extends Repository
{
    protected $defaultOrderings = [
        'sorting' => QueryInterface::ORDER_ASCENDING,
    ];

    public function findByStatus(string $status): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('status', $status)
        );

        return $query->execute();
    }

    public function findByCategoryUid(int $categoryUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->contains('categories', $categoryUid)
        );

        return $query->execute();
    }

    public function findFromPages(array $pageUids): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds($pageUids);

        return $query->execute();
    }

    public function findFromPagesByStatus(array $pageUids, string $status): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds($pageUids);
        $query->matching(
            $query->equals('status', $status)
        );

        return $query->execute();
    }

    public function findFromPagesByCategory(array $pageUids, int $categoryUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds($pageUids);
        $query->matching(
            $query->contains('categories', $categoryUid)
        );

        return $query->execute();
    }

    public function findFromPagesByCategoryAndStatus(array $pageUids, int $categoryUid, string $status): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setStoragePageIds($pageUids);
        $query->matching(
            $query->logicalAnd(
                $query->contains('categories', $categoryUid),
                $query->equals('status', $status),
            )
        );

        return $query->execute();
    }
}
