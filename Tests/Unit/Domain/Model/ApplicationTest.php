<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Tests\Unit\Domain\Model;

use Maispace\MaiJobs\Domain\Model\Application;
use Maispace\MaiJobs\Domain\Model\Job;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

final class ApplicationTest extends TestCase
{
    // ── Default values ──────────────────────────────────────────────────────

    #[Test]
    public function defaultFirstNameIsEmptyString(): void
    {
        $application = new Application();
        self::assertSame('', $application->getFirstName());
    }

    #[Test]
    public function defaultLastNameIsEmptyString(): void
    {
        $application = new Application();
        self::assertSame('', $application->getLastName());
    }

    #[Test]
    public function defaultEmailIsEmptyString(): void
    {
        $application = new Application();
        self::assertSame('', $application->getEmail());
    }

    #[Test]
    public function defaultMessageIsEmptyString(): void
    {
        $application = new Application();
        self::assertSame('', $application->getMessage());
    }

    #[Test]
    public function defaultStatusIsPending(): void
    {
        $application = new Application();
        self::assertSame('pending', $application->getStatus());
    }

    #[Test]
    public function defaultSubmittedAtIsZero(): void
    {
        $application = new Application();
        self::assertSame(0, $application->getSubmittedAt());
    }

    #[Test]
    public function defaultJobIsNull(): void
    {
        $application = new Application();
        self::assertNull($application->getJob());
    }

    #[Test]
    public function constructorInitializesCvAsObjectStorage(): void
    {
        $application = new Application();
        self::assertInstanceOf(ObjectStorage::class, $application->getCv());
    }

    #[Test]
    public function constructorCreatesFreshEmptyCvObjectStorage(): void
    {
        $application = new Application();
        self::assertCount(0, $application->getCv());
    }

    // ── initializeObject ────────────────────────────────────────────────────

    #[Test]
    public function initializeObjectCreatesFreshCvObjectStorage(): void
    {
        $application = new Application();
        $original = $application->getCv();
        $application->initializeObject();
        self::assertInstanceOf(ObjectStorage::class, $application->getCv());
        self::assertNotSame($original, $application->getCv());
    }

    // ── firstName getter / setter ───────────────────────────────────────────

    #[Test]
    public function setFirstNameStoresTheValue(): void
    {
        $application = new Application();
        $application->setFirstName('Jane');
        self::assertSame('Jane', $application->getFirstName());
    }

    #[Test]
    public function setFirstNameOverwritesPreviousValue(): void
    {
        $application = new Application();
        $application->setFirstName('Jane');
        $application->setFirstName('John');
        self::assertSame('John', $application->getFirstName());
    }

    #[Test]
    public function setFirstNameAcceptsEmptyString(): void
    {
        $application = new Application();
        $application->setFirstName('Jane');
        $application->setFirstName('');
        self::assertSame('', $application->getFirstName());
    }

    // ── lastName getter / setter ────────────────────────────────────────────

    #[Test]
    public function setLastNameStoresTheValue(): void
    {
        $application = new Application();
        $application->setLastName('Doe');
        self::assertSame('Doe', $application->getLastName());
    }

    #[Test]
    public function setLastNameAcceptsEmptyString(): void
    {
        $application = new Application();
        $application->setLastName('Doe');
        $application->setLastName('');
        self::assertSame('', $application->getLastName());
    }

    // ── email getter / setter ───────────────────────────────────────────────

    #[Test]
    public function setEmailStoresTheValue(): void
    {
        $application = new Application();
        $application->setEmail('jane.doe@example.com');
        self::assertSame('jane.doe@example.com', $application->getEmail());
    }

    #[Test]
    public function setEmailAcceptsEmptyString(): void
    {
        $application = new Application();
        $application->setEmail('jane.doe@example.com');
        $application->setEmail('');
        self::assertSame('', $application->getEmail());
    }

    // ── message getter / setter ─────────────────────────────────────────────

    #[Test]
    public function setMessageStoresTheValue(): void
    {
        $application = new Application();
        $application->setMessage('I am interested in this position.');
        self::assertSame('I am interested in this position.', $application->getMessage());
    }

    #[Test]
    public function setMessageAcceptsEmptyString(): void
    {
        $application = new Application();
        $application->setMessage('Some message');
        $application->setMessage('');
        self::assertSame('', $application->getMessage());
    }

    // ── status getter / setter ──────────────────────────────────────────────

    #[Test]
    public function setStatusStoresTheValue(): void
    {
        $application = new Application();
        $application->setStatus('accepted');
        self::assertSame('accepted', $application->getStatus());
    }

    #[Test]
    public function setStatusOverwritesPreviousValue(): void
    {
        $application = new Application();
        $application->setStatus('pending');
        $application->setStatus('rejected');
        self::assertSame('rejected', $application->getStatus());
    }

    // ── submittedAt getter / setter ─────────────────────────────────────────

    #[Test]
    public function setSubmittedAtStoresTheValue(): void
    {
        $application = new Application();
        $application->setSubmittedAt(1735689600);
        self::assertSame(1735689600, $application->getSubmittedAt());
    }

    #[Test]
    public function setSubmittedAtAcceptsZero(): void
    {
        $application = new Application();
        $application->setSubmittedAt(1735689600);
        $application->setSubmittedAt(0);
        self::assertSame(0, $application->getSubmittedAt());
    }

    // ── job getter / setter ─────────────────────────────────────────────────

    #[Test]
    public function setJobStoresTheJobObject(): void
    {
        $application = new Application();
        $job = new Job();
        $job->setTitle('PHP Developer');
        $application->setJob($job);
        self::assertSame($job, $application->getJob());
    }

    #[Test]
    public function setJobAcceptsNull(): void
    {
        $application = new Application();
        $job = new Job();
        $application->setJob($job);
        $application->setJob(null);
        self::assertNull($application->getJob());
    }

    // ── cv getter / setter ──────────────────────────────────────────────────

    #[Test]
    public function setCvStoresTheObjectStorage(): void
    {
        $application = new Application();
        $storage = new ObjectStorage();
        $application->setCv($storage);
        self::assertSame($storage, $application->getCv());
    }

    #[Test]
    public function setCvAcceptsNull(): void
    {
        $application = new Application();
        $application->setCv(null);
        self::assertNull($application->getCv());
    }

    #[Test]
    public function twoApplicationInstancesHaveIndependentCvStorages(): void
    {
        $application1 = new Application();
        $application2 = new Application();
        self::assertNotSame($application1->getCv(), $application2->getCv());
    }
}
