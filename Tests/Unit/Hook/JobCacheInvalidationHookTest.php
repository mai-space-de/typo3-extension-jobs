<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Tests\Unit\Hook;

use Maispace\MaiJobs\Hook\JobCacheInvalidationHook;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Extbase\Service\CacheService;

final class JobCacheInvalidationHookTest extends TestCase
{
    private CacheService&MockObject $cacheService;

    private DataHandler&MockObject $dataHandler;

    protected function setUp(): void
    {
        $this->cacheService = $this->createMock(CacheService::class);
        $this->dataHandler = $this->createMock(DataHandler::class);
    }

    #[Test]
    public function jobRecordUpdateFlushesExtbaseCacheTagsTest(): void
    {
        $this->cacheService->expects(self::once())->method('clearCacheForRecord')->with('tx_maijobs_job', 21);
        $this->cacheService->expects(self::once())->method('clearCachesOfRegisteredPageIds');

        (new JobCacheInvalidationHook($this->cacheService))->processDatamap_afterDatabaseOperations(
            'update',
            'tx_maijobs_job',
            21,
            ['title' => 'Updated job'],
            $this->dataHandler,
        );
    }
}
