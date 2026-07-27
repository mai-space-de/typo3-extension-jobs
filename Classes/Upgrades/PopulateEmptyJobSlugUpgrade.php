<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Upgrades;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Upgrades\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Core\Upgrades\RepeatableInterface;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Backfills empty tx_maijobs_job.slug values from the title field.
 *
 * Repeatable: shows up again whenever records with an empty slug exist
 * (e.g. after imports or seed runs).
 */
#[UpgradeWizard('maiJobsPopulateEmptyJobSlug')]
final readonly class PopulateEmptyJobSlugUpgrade implements UpgradeWizardInterface, RepeatableInterface
{
    private const string TABLE = 'tx_maijobs_job';

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    public function getTitle(): string
    {
        return 'Populate empty job slugs from title';
    }

    public function getDescription(): string
    {
        return sprintf(
            'Generates URL slugs for %d job records that have an empty slug field.',
            $this->countRowsNeedingUpdate(),
        );
    }

    public function updateNecessary(): bool
    {
        return $this->countRowsNeedingUpdate() > 0;
    }

    /**
     * @return list<class-string>
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where($this->emptySlugConstraint($queryBuilder))
            ->executeQuery()
            ->fetchAllAssociative();

        $slugHelper = $this->createSlugHelper();
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);

        foreach ($rows as $row) {
            $uid = (int) ($row['uid'] ?? 0);
            $pid = (int) ($row['pid'] ?? 0);
            if ($uid <= 0) {
                continue;
            }

            $proposal = $slugHelper->generate($row, $pid);
            if ($proposal === '' || $proposal === '/') {
                $proposal = 'job-' . $uid;
            }

            $state = RecordStateFactory::forName(self::TABLE)->fromArray($row, $pid, $uid);
            $slug = $slugHelper->buildSlugForUniqueInSite($proposal, $state);

            $connection->update(
                self::TABLE,
                ['slug' => $slug],
                ['uid' => $uid],
                ['slug' => Connection::PARAM_STR, 'uid' => Connection::PARAM_INT],
            );
        }

        return true;
    }

    private function countRowsNeedingUpdate(): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->removeAll();

        return (int) $queryBuilder
            ->count('*')
            ->from(self::TABLE)
            ->where($this->emptySlugConstraint($queryBuilder))
            ->executeQuery()
            ->fetchOne();
    }

    private function emptySlugConstraint(QueryBuilder $queryBuilder): CompositeExpression
    {
        return $queryBuilder->expr()->or(
            $queryBuilder->expr()->eq(
                'slug',
                $queryBuilder->createNamedParameter('', Connection::PARAM_STR),
            ),
            $queryBuilder->expr()->isNull('slug'),
        );
    }

    private function createSlugHelper(): SlugHelper
    {
        return GeneralUtility::makeInstance(
            SlugHelper::class,
            self::TABLE,
            'slug',
            $this->slugFieldConfiguration(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function slugFieldConfiguration(): array
    {
        return [
            'type' => 'slug',
            'eval' => 'uniqueInSite',
            'fallbackCharacter' => '-',
            'prependSlash' => false,
            'generatorOptions' => [
                'fields' => ['title'],
                'fieldSeparator' => '-',
                'replacements' => ['/' => ''],
            ],
        ];
    }
}
