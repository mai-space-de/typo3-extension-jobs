<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Tests\Unit\Indexer;

use Maispace\MaiJobs\Domain\Model\Job;
use Maispace\MaiJobs\Indexer\JobsIndexer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JobsIndexerTest extends TestCase
{
    private JobsIndexer $subject;

    protected function setUp(): void
    {
        $this->subject = new JobsIndexer();
    }

    #[Test]
    public function getTypeReturnsJobs(): void
    {
        self::assertSame('jobs', $this->subject->getType());
    }

    #[Test]
    public function supportsJobsTable(): void
    {
        self::assertTrue($this->subject->supports('tx_maijobs_job'));
    }

    #[Test]
    public function doesNotSupportOtherTables(): void
    {
        self::assertFalse($this->subject->supports('tx_mainews_news'));
        self::assertFalse($this->subject->supports('pages'));
        self::assertFalse($this->subject->supports('tt_content'));
    }

    #[Test]
    public function getIconReturnsExpectedValue(): void
    {
        self::assertSame('content-jobs', $this->subject->getIcon('jobs'));
    }

    #[Test]
    public function buildContentStripsHtmlTags(): void
    {
        $job = new Job();
        $job->setDescription('<p>We are hiring a <strong>PHP Developer</strong>.</p>');
        $job->setRequirements('<ul><li>5 years experience</li><li>Team player</li></ul>');

        $content = $this->invokeBuildContent($job);

        self::assertStringNotContainsString('<p>', $content);
        self::assertStringNotContainsString('<strong>', $content);
        self::assertStringNotContainsString('<ul>', $content);
        self::assertStringNotContainsString('<li>', $content);
        self::assertStringContainsString('PHP Developer', $content);
        self::assertStringContainsString('5 years experience', $content);
        self::assertStringContainsString('Team player', $content);
    }

    #[Test]
    public function buildContentIncludesDescriptionAndRequirements(): void
    {
        $job = new Job();
        $job->setDescription('Great job opportunity.');
        $job->setRequirements('PHP experience required.');

        $content = $this->invokeBuildContent($job);

        self::assertStringContainsString('Great job opportunity.', $content);
        self::assertStringContainsString('PHP experience required.', $content);
    }

    #[Test]
    public function buildContentOmitsRequirementsWhenEmpty(): void
    {
        $job = new Job();
        $job->setDescription('Job description only.');

        $content = $this->invokeBuildContent($job);

        self::assertStringContainsString('Job description only.', $content);
    }

    #[Test]
    public function buildContentReturnsEmptyStringForNonJobRecord(): void
    {
        $content = $this->invokeBuildContent(new \stdClass());

        self::assertSame('', $content);
    }

    #[Test]
    public function formatResultReturnsSearchResultWithCorrectType(): void
    {
        $solrDoc = [
            'title_s' => 'PHP Developer',
            'content_t' => 'We are looking for a PHP developer.',
            'url_s' => '/jobs/php-developer',
            'score' => 2.5,
        ];

        $result = $this->subject->formatResult($solrDoc);

        self::assertSame('jobs', $result->type);
        self::assertSame('PHP Developer', $result->title);
        self::assertSame('/jobs/php-developer', $result->url);
        self::assertSame('content-jobs', $result->icon);
        self::assertSame(2.5, $result->score);
    }

    #[Test]
    public function formatResultDefaultsToEmptyStringsWhenFieldsAreMissing(): void
    {
        $result = $this->subject->formatResult([]);

        self::assertSame('', $result->title);
        self::assertSame('', $result->url);
        self::assertSame(0.0, $result->score);
        self::assertNull($result->date);
    }

    private function invokeBuildContent(object $record): string
    {
        $reflection = new \ReflectionMethod($this->subject, 'buildContent');
        $reflection->setAccessible(true);

        /** @var string $result */
        return $reflection->invoke($this->subject, $record);
    }
}
