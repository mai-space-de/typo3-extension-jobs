<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Tests\Unit\Domain\Repository;

use Maispace\MaiJobs\Domain\Repository\ApplicationRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class ApplicationRepositoryTest extends TestCase
{
    #[Test]
    public function repositoryExtendsTYPO3BaseRepository(): void
    {
        self::assertTrue(
            is_subclass_of(ApplicationRepository::class, Repository::class),
            ApplicationRepository::class . ' must extend ' . Repository::class,
        );
    }

    #[Test]
    public function defaultOrderingsContainSubmittedAtDescending(): void
    {
        $reflection = new \ReflectionClass(ApplicationRepository::class);
        $defaults = $reflection->getDefaultProperties();

        self::assertArrayHasKey('defaultOrderings', $defaults);
        self::assertIsArray($defaults['defaultOrderings']);
        self::assertArrayHasKey('submitted_at', $defaults['defaultOrderings']);
        self::assertSame(QueryInterface::ORDER_DESCENDING, $defaults['defaultOrderings']['submitted_at']);
    }

    #[Test]
    public function defaultOrderingsContainExactlyOneSortKey(): void
    {
        $reflection = new \ReflectionClass(ApplicationRepository::class);
        $defaults = $reflection->getDefaultProperties();

        self::assertCount(1, $defaults['defaultOrderings']);
    }
}
