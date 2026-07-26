<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Job extends AbstractEntity
{
    protected string $title = '';

    protected string $slug = '';

    protected string $description = '';

    protected string $requirements = '';

    protected int $deadline = 0;

    protected string $status = 'open';

    /**
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected ObjectStorage $categories;

    public function __construct()
    {
        $this->categories = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->categories = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getRequirements(): string
    {
        return $this->requirements;
    }

    public function setRequirements(string $requirements): void
    {
        $this->requirements = $requirements;
    }

    public function getDeadline(): int
    {
        return $this->deadline;
    }

    public function setDeadline(int $deadline): void
    {
        $this->deadline = $deadline;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * @return ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * @param ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category> $categories
     */
    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }
}
