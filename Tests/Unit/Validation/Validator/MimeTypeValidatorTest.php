<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Tests\Unit\Validation\Validator;

use Maispace\MaiJobs\Validation\Validator\MimeTypeValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

final class MimeTypeValidatorTest extends TestCase
{
    private MimeTypeValidator&ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = $this->getMockBuilder(MimeTypeValidator::class)
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();

        $this->validator
            ->method('translateErrorMessage')
            ->willReturnArgument(0);
    }

    // ── Null / Empty ─────────────────────────────────────────────────────────

    #[Test]
    public function passesWhenCvIsNull(): void
    {
        $result = $this->validator->validate(null);
        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function passesWhenCvIsEmptyObjectStorage(): void
    {
        $result = $this->validator->validate(new ObjectStorage());
        self::assertFalse($result->hasErrors());
    }

    // ── Allowed MIME types ───────────────────────────────────────────────────

    #[Test]
    public function passesWhenFileIsPdf(): void
    {
        $cv = $this->createObjectStorageWithFile('application/pdf');
        $result = $this->validator->validate($cv);
        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function passesWhenFileIsDoc(): void
    {
        $cv = $this->createObjectStorageWithFile('application/msword');
        $result = $this->validator->validate($cv);
        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function passesWhenFileIsDocx(): void
    {
        $cv = $this->createObjectStorageWithFile('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $result = $this->validator->validate($cv);
        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function passesWhenFileIsOdt(): void
    {
        $cv = $this->createObjectStorageWithFile('application/vnd.oasis.opendocument.text');
        $result = $this->validator->validate($cv);
        self::assertFalse($result->hasErrors());
    }

    // ── Disallowed MIME types ────────────────────────────────────────────────

    #[Test]
    public function failsWhenFileIsPng(): void
    {
        $cv = $this->createObjectStorageWithFile('image/png');
        $result = $this->validator->validate($cv);
        self::assertTrue($result->hasErrors());
    }

    #[Test]
    public function failsWhenFileIsJpeg(): void
    {
        $cv = $this->createObjectStorageWithFile('image/jpeg');
        $result = $this->validator->validate($cv);
        self::assertTrue($result->hasErrors());
    }

    #[Test]
    public function failsWhenFileIsTextPlain(): void
    {
        $cv = $this->createObjectStorageWithFile('text/plain');
        $result = $this->validator->validate($cv);
        self::assertTrue($result->hasErrors());
    }

    // ── Invalid value type ───────────────────────────────────────────────────

    #[Test]
    public function failsWhenValueIsStringInsteadOfObjectStorage(): void
    {
        $result = $this->validator->validate('not-an-object-storage');
        self::assertTrue($result->hasErrors());
    }

    #[Test]
    public function failsWhenValueIsArrayInsteadOfObjectStorage(): void
    {
        $result = $this->validator->validate(['some-file']);
        self::assertTrue($result->hasErrors());
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function createObjectStorageWithFile(string $mimeType): ObjectStorage
    {
        $originalResource = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $originalResource
            ->method('getMimeType')
            ->willReturn($mimeType);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference
            ->method('getOriginalResource')
            ->willReturn($originalResource);

        $storage = new ObjectStorage();
        $storage->attach($fileReference);

        return $storage;
    }
}
