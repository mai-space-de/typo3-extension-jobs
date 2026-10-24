<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Hook;

use Maispace\MaiBase\Backend\CustomPageModule\DoktypeIds;
use Maispace\MaiBase\Hook\AbstractRecordCacheInvalidationHook;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Flushes list/detail page cache tags when a Job page changes.
 */
final class JobCacheInvalidationHook extends AbstractRecordCacheInvalidationHook
{
    protected function getWatchedTable(): string
    {
        return 'pages';
    }

    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        int|string $id,
        array $fieldArray,
        DataHandler $dataHandler,
    ): void {
        if ($table !== 'pages' || !$this->isJobPageChange($fieldArray, $dataHandler)) {
            return;
        }

        parent::processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $dataHandler);
    }

    public function processCmdmap_deleteAction(
        string $table,
        int $id,
        array $recordToDelete,
        bool &$recordWasDeleted,
        DataHandler $dataHandler,
    ): void {
        if ($table !== 'pages') {
            return;
        }

        $doktype = (int) ($recordToDelete['doktype'] ?? 0);
        if ($doktype !== DoktypeIds::JOB) {
            return;
        }

        parent::processCmdmap_deleteAction($table, $id, $recordToDelete, $recordWasDeleted, $dataHandler);
    }

    /**
     * @param array<string, mixed> $fieldArray
     */
    private function isJobPageChange(array $fieldArray, DataHandler $dataHandler): bool
    {
        if (isset($fieldArray['doktype']) && (int) $fieldArray['doktype'] === DoktypeIds::JOB) {
            return true;
        }

        foreach (array_keys($fieldArray) as $field) {
            if (str_starts_with((string) $field, 'tx_maijobs_')) {
                return true;
            }
        }

        $currentDoktype = (int) ($dataHandler->checkValue_currentRecord['doktype'] ?? 0);

        return $currentDoktype === DoktypeIds::JOB;
    }
}
