<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Domain\Repository;

use Maispace\MaiBase\Backend\CustomPageModule\DoktypeIds;
use Maispace\MaiJobs\Domain\Model\Job;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Reads Job doktype pages (122). Parent UIDs in FlexForm `pages` filter by pid.
 *
 * @extends Repository<Job>
 */
class JobRepository extends Repository
{
    protected $defaultOrderings = [
        'sorting' => QueryInterface::ORDER_ASCENDING,
    ];

    public function initializeObject(): void
    {
        $querySettings = $this->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function findByStatus(string $status): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $this->doktypeConstraint($query),
                $query->equals('status', $status),
            ),
        );

        return $query->execute();
    }

    public function findByCategoryUid(int $categoryUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $this->doktypeConstraint($query),
                $query->contains('categories', $categoryUid),
            ),
        );

        return $query->execute();
    }

    /**
     * @param list<int> $parentPageUids Parent page UIDs (Job pages live as children)
     */
    public function findFromPages(array $parentPageUids): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [$this->doktypeConstraint($query)];
        if ($parentPageUids !== []) {
            $constraints[] = $query->in('pid', $parentPageUids);
        }
        $query->matching($query->logicalAnd(...$constraints));

        return $query->execute();
    }

    /**
     * @param list<int> $parentPageUids
     */
    public function findFromPagesByStatus(array $parentPageUids, string $status): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [
            $this->doktypeConstraint($query),
            $query->equals('status', $status),
        ];
        if ($parentPageUids !== []) {
            $constraints[] = $query->in('pid', $parentPageUids);
        }
        $query->matching($query->logicalAnd(...$constraints));

        return $query->execute();
    }

    /**
     * @param list<int> $parentPageUids
     */
    public function findFromPagesByCategory(array $parentPageUids, int $categoryUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [
            $this->doktypeConstraint($query),
            $query->contains('categories', $categoryUid),
        ];
        if ($parentPageUids !== []) {
            $constraints[] = $query->in('pid', $parentPageUids);
        }
        $query->matching($query->logicalAnd(...$constraints));

        return $query->execute();
    }

    /**
     * @param list<int> $parentPageUids
     */
    public function findFromPagesByCategoryAndStatus(
        array $parentPageUids,
        int $categoryUid,
        string $status,
    ): QueryResultInterface {
        $query = $this->createQuery();
        $constraints = [
            $this->doktypeConstraint($query),
            $query->contains('categories', $categoryUid),
            $query->equals('status', $status),
        ];
        if ($parentPageUids !== []) {
            $constraints[] = $query->in('pid', $parentPageUids);
        }
        $query->matching($query->logicalAnd(...$constraints));

        return $query->execute();
    }

    public function findAll(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($this->doktypeConstraint($query));

        return $query->execute();
    }

    public function findByUid($uid): ?Job
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('uid', (int) $uid),
                $this->doktypeConstraint($query),
            ),
        );
        $query->setLimit(1);
        /** @var Job|null $result */
        $result = $query->execute()->getFirst();

        return $result;
    }

    private function doktypeConstraint(QueryInterface $query): \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface
    {
        return $query->equals('doktype', DoktypeIds::JOB);
    }
}
