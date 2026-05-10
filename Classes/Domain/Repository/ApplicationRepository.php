<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ApplicationRepository extends Repository
{
    protected $defaultOrderings = [
        'submitted_at' => QueryInterface::ORDER_DESCENDING,
    ];

    public function findByJob(int $jobUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('job', $jobUid)
        );

        return $query->execute();
    }

    public function findByJobAndStatus(int $jobUid, string $status): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('job', $jobUid),
                $query->equals('status', $status),
            )
        );

        return $query->execute();
    }
}
