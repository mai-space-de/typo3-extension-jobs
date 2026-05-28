<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Application extends AbstractEntity
{
    protected string $firstName = '';

    protected string $lastName = '';

    protected string $email = '';

    protected string $message = '';

    /**
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>|null
     */
    protected ?ObjectStorage $cv = null;

    protected string $status = 'pending';

    protected int $submittedAt = 0;

    protected ?Job $job = null;

    public function __construct()
    {
        $this->cv = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->cv = new ObjectStorage();
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getCv(): ?ObjectStorage
    {
        return $this->cv;
    }

    public function setCv(?ObjectStorage $cv): void
    {
        $this->cv = $cv;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSubmittedAt(): int
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(int $submittedAt): void
    {
        $this->submittedAt = $submittedAt;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): void
    {
        $this->job = $job;
    }
}
