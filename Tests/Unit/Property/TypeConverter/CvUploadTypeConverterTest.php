<?php

declare(strict_types=1);

namespace Maispace\MaiJobs\Tests\Unit\Property\TypeConverter;

use Maispace\MaiJobs\Property\TypeConverter\CvUploadTypeConverter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\File as CoreFile;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;

final class CvUploadTypeConverterTest extends TestCase
{
    private CvUploadTypeConverter $converter;
    private ResourceStorage $resourceStorageMock;
    private ResourceFactory $resourceFactoryMock;
    private Folder $folderMock;

    protected function setUp(): void
    {
        $this->resourceStorageMock = $this->createMock(ResourceStorage::class);
        $this->resourceFactoryMock = $this->createMock(ResourceFactory::class);
        $this->folderMock = $this->createMock(Folder::class);

        $this->converter = new CvUploadTypeConverter();
        $this->converter->injectResourceFactory($this->resourceFactoryMock);
        $this->converter->injectResourceStorage($this->resourceStorageMock);
    }

    private function createValidUploadInfo(string $mimeType = 'application/pdf'): array
    {
        return [
            'name' => 'cv.pdf',
            'tmp_name' => '/tmp/php12345',
            'size' => 1024,
            'error' => \UPLOAD_ERR_OK,
            'type' => $mimeType,
        ];
    }

    private function configureSuccessfulStorage(array $uploadInfo): CoreFileReference
    {
        $coreFile = $this->createMock(CoreFile::class);
        $coreFile->method('getUid')->willReturn(123);

        $coreFileReference = $this->createMock(CoreFileReference::class);

        $this->resourceStorageMock
            ->method('getDefaultFolder')
            ->willReturn($this->folderMock);

        $this->resourceStorageMock
            ->method('addFile')
            ->with(
                $uploadInfo['tmp_name'],
                $this->folderMock,
                $uploadInfo['name'],
                DuplicationBehavior::RENAME,
            )
            ->willReturn($coreFile);

        $this->resourceFactoryMock
            ->method('createFileReferenceObject')
            ->willReturn($coreFileReference);

        return $coreFileReference;
    }

    // ── Null / Empty ─────────────────────────────────────────────────────────

    #[Test]
    public function returnsNullWhenSourceIsNull(): void
    {
        $result = $this->converter->convertFrom(null, FileReference::class);
        self::assertNull($result);
    }

    #[Test]
    public function returnsNullWhenSourceIsEmptyString(): void
    {
        $result = $this->converter->convertFrom('', FileReference::class);
        self::assertNull($result);
    }

    #[Test]
    public function returnsNullWhenSourceIsEmptyArray(): void
    {
        $result = $this->converter->convertFrom([], FileReference::class);
        self::assertNull($result);
    }

    // ── No file uploaded ─────────────────────────────────────────────────────

    #[Test]
    public function returnsNullWhenNoFileUploaded(): void
    {
        $result = $this->converter->convertFrom(
            ['error' => \UPLOAD_ERR_NO_FILE],
            FileReference::class,
        );
        self::assertNull($result);
    }

    #[Test]
    public function returnsNullWhenUploadedFileHasNoFileError(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getError')->willReturn(\UPLOAD_ERR_NO_FILE);
        $uploadedFile->method('getClientFilename')->willReturn(null);
        $uploadedFile->method('getTemporaryFileName')->willReturn(null);
        $uploadedFile->method('getSize')->willReturn(null);
        $uploadedFile->method('getClientMediaType')->willReturn(null);

        $result = $this->converter->convertFrom($uploadedFile, FileReference::class);
        self::assertNull($result);
    }

    // ── Upload errors ────────────────────────────────────────────────────────

    #[Test]
    public function returnsErrorWhenUploadExceedsIniSize(): void
    {
        $result = $this->converter->convertFrom(
            ['error' => \UPLOAD_ERR_INI_SIZE, 'type' => 'application/pdf'],
            FileReference::class,
        );
        self::assertInstanceOf(Error::class, $result);
    }

    #[Test]
    public function returnsErrorWhenUploadIsPartial(): void
    {
        $result = $this->converter->convertFrom(
            ['error' => \UPLOAD_ERR_PARTIAL, 'type' => 'application/pdf'],
            FileReference::class,
        );
        self::assertInstanceOf(Error::class, $result);
    }

    // ── MIME type validation — allowed types ─────────────────────────────────

    #[Test]
    public function convertsValidPdfUpload(): void
    {
        $uploadInfo = $this->createValidUploadInfo('application/pdf');
        $coreFileReference = $this->configureSuccessfulStorage($uploadInfo);

        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);

        self::assertInstanceOf(FileReference::class, $result);
    }

    #[Test]
    public function convertsValidDocUpload(): void
    {
        $uploadInfo = $this->createValidUploadInfo('application/msword');
        $this->configureSuccessfulStorage($uploadInfo);

        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
        self::assertInstanceOf(FileReference::class, $result);
    }

    #[Test]
    public function convertsValidDocxUpload(): void
    {
        $uploadInfo = $this->createValidUploadInfo(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        );
        $this->configureSuccessfulStorage($uploadInfo);

        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
        self::assertInstanceOf(FileReference::class, $result);
    }

    #[Test]
    public function convertsValidOdtUpload(): void
    {
        $uploadInfo = $this->createValidUploadInfo(
            'application/vnd.oasis.opendocument.text',
        );
        $this->configureSuccessfulStorage($uploadInfo);

        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
        self::assertInstanceOf(FileReference::class, $result);
    }

    // ── MIME type validation — disallowed types ──────────────────────────────

    #[Test]
    public function returnsErrorForPngUpload(): void
    {
        $uploadInfo = $this->createValidUploadInfo('image/png');
        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
        self::assertInstanceOf(Error::class, $result);
    }

    #[Test]
    public function returnsErrorForJpegUpload(): void
    {
        $uploadInfo = $this->createValidUploadInfo('image/jpeg');
        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
        self::assertInstanceOf(Error::class, $result);
    }

    #[Test]
    public function returnsErrorForTextPlainUpload(): void
    {
        $uploadInfo = $this->createValidUploadInfo('text/plain');
        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
        self::assertInstanceOf(Error::class, $result);
    }

    #[Test]
    public function returnsErrorForUnknownMimeType(): void
    {
        $uploadInfo = $this->createValidUploadInfo('application/octet-stream');
        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
        self::assertInstanceOf(Error::class, $result);
    }

    // ── UploadedFile (PSR-7) source ──────────────────────────────────────────

    #[Test]
    public function convertsValidUploadedFileObject(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientFilename')->willReturn('cv.pdf');
        $uploadedFile->method('getTemporaryFileName')->willReturn('/tmp/php54321');
        $uploadedFile->method('getSize')->willReturn(2048);
        $uploadedFile->method('getError')->willReturn(\UPLOAD_ERR_OK);
        $uploadedFile->method('getClientMediaType')->willReturn('application/pdf');

        $coreFile = $this->createMock(CoreFile::class);
        $coreFile->method('getUid')->willReturn(456);
        $coreFileReference = $this->createMock(CoreFileReference::class);

        $this->resourceStorageMock->method('getDefaultFolder')->willReturn($this->folderMock);
        $this->resourceStorageMock->method('addFile')->willReturn($coreFile);
        $this->resourceFactoryMock->method('createFileReferenceObject')->willReturn($coreFileReference);

        $result = $this->converter->convertFrom($uploadedFile, FileReference::class);
        self::assertInstanceOf(FileReference::class, $result);
    }

    #[Test]
    public function returnsErrorForDisallowedUploadedFileObject(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientFilename')->willReturn('image.png');
        $uploadedFile->method('getTemporaryFileName')->willReturn('/tmp/php99999');
        $uploadedFile->method('getSize')->willReturn(4096);
        $uploadedFile->method('getError')->willReturn(\UPLOAD_ERR_OK);
        $uploadedFile->method('getClientMediaType')->willReturn('image/png');

        $result = $this->converter->convertFrom($uploadedFile, FileReference::class);
        self::assertInstanceOf(Error::class, $result);
    }

    // ── Storage failure ──────────────────────────────────────────────────────

    #[Test]
    public function returnsErrorWhenStorageFails(): void
    {
        $uploadInfo = $this->createValidUploadInfo('application/pdf');

        $this->resourceStorageMock->method('getDefaultFolder')->willReturn($this->folderMock);
        $this->resourceStorageMock->method('addFile')
            ->willThrowException(new \RuntimeException('Storage failure'));

        $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
        self::assertInstanceOf(Error::class, $result);
    }

    // ── Server-side MIME detection (when client type is empty/missing) ───────

    #[Test]
    public function fallsBackToServerMimeDetectionWhenClientTypeIsEmpty(): void
    {
        // Create a real temp file with PDF content for server-side detection
        $tmpFile = tempnam(sys_get_temp_dir(), 'cv_test_');
        file_put_contents($tmpFile, '%PDF-1.4 fake pdf content');

        $uploadInfo = [
            'name' => 'document',
            'tmp_name' => $tmpFile,
            'size' => 100,
            'error' => \UPLOAD_ERR_OK,
            'type' => '',
        ];

        $coreFile = $this->createMock(CoreFile::class);
        $coreFile->method('getUid')->willReturn(789);
        $coreFileReference = $this->createMock(CoreFileReference::class);

        $this->resourceStorageMock->method('getDefaultFolder')->willReturn($this->folderMock);
        $this->resourceStorageMock->method('addFile')->willReturn($coreFile);
        $this->resourceFactoryMock->method('createFileReferenceObject')->willReturn($coreFileReference);

        try {
            $result = $this->converter->convertFrom($uploadInfo, FileReference::class);
            self::assertInstanceOf(FileReference::class, $result);
        } finally {
            @unlink($tmpFile);
        }
    }
}
