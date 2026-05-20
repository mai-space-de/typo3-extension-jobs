<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Tests\Unit\Domain\Model;

use Maispace\MaiJobs\Domain\Model\Job;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class JobTest extends TestCase
{
    // ── Default values ──────────────────────────────────────────────────────

    #[Test]
    public function defaultTitleIsEmptyString(): void
    {
        $job = new Job();
        self::assertSame('', $job->getTitle());
    }

    #[Test]
    public function defaultDescriptionIsEmptyString(): void
    {
        $job = new Job();
        self::assertSame('', $job->getDescription());
    }

    #[Test]
    public function defaultRequirementsIsEmptyString(): void
    {
        $job = new Job();
        self::assertSame('', $job->getRequirements());
    }

    #[Test]
    public function defaultDeadlineIsZero(): void
    {
        $job = new Job();
        self::assertSame(0, $job->getDeadline());
    }

    #[Test]
    public function defaultStatusIsOpen(): void
    {
        $job = new Job();
        self::assertSame('open', $job->getStatus());
    }

    #[Test]
    public function constructorInitializesCategoriesAsObjectStorage(): void
    {
        $job = new Job();
        self::assertInstanceOf(ObjectStorage::class, $job->getCategories());
    }

    #[Test]
    public function constructorCreatesFreshEmptyObjectStorage(): void
    {
        $job = new Job();
        self::assertCount(0, $job->getCategories());
    }

    // ── initializeObject ────────────────────────────────────────────────────

    #[Test]
    public function initializeObjectCreatesFreshObjectStorage(): void
    {
        $job = new Job();
        $original = $job->getCategories();
        $job->initializeObject();
        self::assertInstanceOf(ObjectStorage::class, $job->getCategories());
        self::assertNotSame($original, $job->getCategories());
    }

    // ── title getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setTitleStoresTheValue(): void
    {
        $job = new Job();
        $job->setTitle('PHP Developer');
        self::assertSame('PHP Developer', $job->getTitle());
    }

    #[Test]
    public function setTitleOverwritesPreviousValue(): void
    {
        $job = new Job();
        $job->setTitle('First Title');
        $job->setTitle('Second Title');
        self::assertSame('Second Title', $job->getTitle());
    }

    #[Test]
    public function setTitleAcceptsEmptyString(): void
    {
        $job = new Job();
        $job->setTitle('Non-empty');
        $job->setTitle('');
        self::assertSame('', $job->getTitle());
    }

    // ── description getter / setter ─────────────────────────────────────────

    #[Test]
    public function setDescriptionStoresTheValue(): void
    {
        $job = new Job();
        $job->setDescription('We are looking for a developer.');
        self::assertSame('We are looking for a developer.', $job->getDescription());
    }

    #[Test]
    public function setDescriptionAcceptsEmptyString(): void
    {
        $job = new Job();
        $job->setDescription('Some description');
        $job->setDescription('');
        self::assertSame('', $job->getDescription());
    }

    // ── requirements getter / setter ────────────────────────────────────────

    #[Test]
    public function setRequirementsStoresTheValue(): void
    {
        $job = new Job();
        $job->setRequirements('5 years of PHP experience.');
        self::assertSame('5 years of PHP experience.', $job->getRequirements());
    }

    #[Test]
    public function setRequirementsAcceptsEmptyString(): void
    {
        $job = new Job();
        $job->setRequirements('Some requirements');
        $job->setRequirements('');
        self::assertSame('', $job->getRequirements());
    }

    // ── deadline getter / setter ────────────────────────────────────────────

    #[Test]
    public function setDeadlineStoresTheValue(): void
    {
        $job = new Job();
        $job->setDeadline(1735689600);
        self::assertSame(1735689600, $job->getDeadline());
    }

    #[Test]
    public function setDeadlineAcceptsZero(): void
    {
        $job = new Job();
        $job->setDeadline(1735689600);
        $job->setDeadline(0);
        self::assertSame(0, $job->getDeadline());
    }

    // ── status getter / setter ──────────────────────────────────────────────

    #[Test]
    public function setStatusStoresTheValue(): void
    {
        $job = new Job();
        $job->setStatus('closed');
        self::assertSame('closed', $job->getStatus());
    }

    #[Test]
    public function setStatusOverwritesPreviousValue(): void
    {
        $job = new Job();
        $job->setStatus('open');
        $job->setStatus('draft');
        self::assertSame('draft', $job->getStatus());
    }

    // ── isOpen ──────────────────────────────────────────────────────────────

    #[Test]
    public function isOpenReturnsTrueWhenStatusIsOpen(): void
    {
        $job = new Job();
        self::assertTrue($job->isOpen());
    }

    #[Test]
    public function isOpenReturnsFalseWhenStatusIsClosed(): void
    {
        $job = new Job();
        $job->setStatus('closed');
        self::assertFalse($job->isOpen());
    }

    #[Test]
    public function isOpenReturnsFalseWhenStatusIsDraft(): void
    {
        $job = new Job();
        $job->setStatus('draft');
        self::assertFalse($job->isOpen());
    }

    // ── categories getter / setter ──────────────────────────────────────────

    #[Test]
    public function setCategoriesStoresTheObjectStorage(): void
    {
        $job = new Job();
        $storage = new ObjectStorage();
        $job->setCategories($storage);
        self::assertSame($storage, $job->getCategories());
    }

    #[Test]
    public function getCategoriesReturnsSameInstanceAfterSet(): void
    {
        $job = new Job();
        $storage = new ObjectStorage();
        $job->setCategories($storage);
        self::assertSame($storage, $job->getCategories());
    }

    #[Test]
    public function twoJobInstancesHaveIndependentCategoryStorages(): void
    {
        $job1 = new Job();
        $job2 = new Job();
        self::assertNotSame($job1->getCategories(), $job2->getCategories());
    }
}
