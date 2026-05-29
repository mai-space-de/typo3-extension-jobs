<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Hook;

use Maispace\MaiBase\Hook\AbstractRecordCacheInvalidationHook;

/**
 * Flushes list/detail page cache tags when a job record is saved or deleted.
 */
final class JobCacheInvalidationHook extends AbstractRecordCacheInvalidationHook
{
    protected function getWatchedTable(): string
    {
        return 'tx_maijobs_job';
    }
}
